<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceScanRequest;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Santri;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    public function scanPage()
    {
        $session = $this->attendanceService->getCurrentSession() ?: 'Subuh';
        $today   = now()->toDateString();

        $liburToday = \App\Models\HariLibur::whereDate('tanggal', $today)->first();
        $isLibur = false;

        if ($liburToday) {
            $ket = strtolower($liburToday->keterangan ?? '');
            $sesLower = strtolower($session);
                if ($this->attendanceService->isHolidayForSession($liburToday->keterangan, $session)) {
                $isLibur = true;
            }
        }

        $jadwalTarget = \App\Models\Jadwal::whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($session)])->first();
        if ($jadwalTarget && strtolower($jadwalTarget->status) === 'nonaktif') {
            $isLibur = true;
        }

        $statusSesi = $isLibur ? 'Libur' : 'Aktif';

        return view('attendance.scan', compact('session', 'isLibur', 'statusSesi'));
    }

    public function scan(AttendanceScanRequest $request)
    {
        $santri  = Santri::where('rfid_barcode', $request->barcode)->firstOrFail();
        $session = $this->attendanceService->normalizeSession($request->input('session') ?: ($this->attendanceService->getCurrentSession() ?: 'Subuh'));
        $today   = $request->input('date') ?: now()->toDateString();

        // 1. Cek apakah sesi ini Libur / Nonaktif pada tanggal tersebut
        $liburToday = \App\Models\HariLibur::whereDate('tanggal', $today)->first();
        $isLibur = false;

        if ($liburToday) {
            $ket = strtolower($liburToday->keterangan ?? '');
            $sesLower = strtolower($session);
                if ($this->attendanceService->isHolidayForSession($liburToday->keterangan, $session)) {
                $isLibur = true;
            }
        }

        $jadwalTarget = \App\Models\Jadwal::whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($session)])->first();
        if ($jadwalTarget && strtolower($jadwalTarget->status) === 'nonaktif') {
            $isLibur = true;
        }

        if ($isLibur) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => 'Sesi ' . $session . ' (' . $today . ') sedang LIBUR, absensi ditutup.'], 422);
            }
            return back()->with('error', 'Sesi ' . $session . ' (' . $today . ') sedang LIBUR, absensi ditutup.');
        }

        [$attendance, $created] = $this->attendanceService->storeRfidAttendance(
            $santri,
            $session,
            Carbon::parse($today, config('app.timezone'))
        );

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'created' => $created,
                'duplicate' => ! $created,
                'message' => $created
                    ? 'Absensi RFID berhasil disimpan.'
                    : 'Santri sudah melakukan absensi untuk sesi ini.',
                'attendance_id' => $attendance->getKey(),
            ], 200);
        }

        return back()->with('success', ($created ? 'Absensi RFID berhasil disimpan untuk ' : 'Santri sudah melakukan absensi untuk ') . $santri->name . ' (Sesi ' . $session . ')');
    }

    public function manualPage(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        $session = $request->input('session') ?? $this->attendanceService->getCurrentSession() ?? 'Subuh';

        $classrooms = Classroom::orderBy('name')->get();
        if (! $classroomId && $classrooms->isNotEmpty()) {
            $classroomId = $classrooms->first()->id;
        }

        $santriQuery = Santri::with('classroom')->orderBy('name');
        if ($classroomId) {
            $santriQuery->where('classroom_id', $classroomId);
        }

        $santriList = $santriQuery->get();

        $session = $this->attendanceService->normalizeSession($session);
        $attendanceQuery = Attendance::query()->where('date', now()->toDateString())->where('session', $session);
        if ($classroomId) {
            $attendanceQuery->whereHas('santri', fn ($query) => $query->where('classroom_id', $classroomId));
        }

        $attendanceBySantri = $attendanceQuery->get()->keyBy('santri_id');
        $sessions = ['Subuh', 'Dzuhur', 'Asar', 'Isya'];

        return view('attendance.manual', compact('santriList', 'classrooms', 'classroomId', 'session', 'sessions', 'attendanceBySantri'));
    }

    public function manual(Request $request)
    {
        if ($request->has('attendance')) {
            $validated = $request->validate([
                'attendance' => ['required', 'array'],
                'attendance.*.santri_id' => ['required', 'integer', 'exists:santris,id'],
                'attendance.*.status' => ['required', 'in:Hadir,Sakit,Izin,Alfa'],
                'attendance.*.notes' => ['nullable', 'string'],
                'attendance.*.scan_time' => ['nullable', 'date'],
                'session' => ['required', 'in:Subuh,Dzuhur,Asar,Ashar,Isya'],
                'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            ]);

            $errors = [];
            $saved = 0;

            foreach ($validated['attendance'] as $key => $item) {
                if (blank($item['santri_id']) || blank($item['status'])) {
                    continue;
                }

                if (in_array($item['status'], ['Sakit', 'Izin'], true) && blank($item['notes'])) {
                    $errors["attendance.$key.notes"] = 'Keterangan/Alasan wajib diisi untuk status Sakit atau Izin.';
                    continue;
                }

                $santri = Santri::find($item['santri_id']);
                if (! $santri) {
                    continue;
                }

                $tanggal  = $request->input('date') ?: now()->toDateString();
                $scanTime = ! blank($item['scan_time'] ?? null) ? Carbon::parse($item['scan_time']) : now();

                $existingRfid = Attendance::where('santri_id', $santri->id)
                    ->whereDate('date', $tanggal)
                    ->where('session', $this->attendanceService->normalizeSession($validated['session']))
                    ->whereRaw('LOWER(status) = ?', ['hadir'])
                    ->whereRaw('LOWER(COALESCE(notes, \'\')) LIKE ?', ['%rfid%'])
                    ->exists();

                if ($existingRfid) {
                    continue;
                }

                Attendance::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'date'      => $tanggal,
                        'session'   => $this->attendanceService->normalizeSession($validated['session']),
                    ],
                    [
                        'status'    => $item['status'],
                        'notes'     => $item['notes'] ?? null,
                        'scan_time' => $scanTime,
                    ]
                );
                $saved++;
            }

            if (! empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }

            return back()->with('success', "Berhasil menyimpan $saved absensi manual.");
        }

        if ($request->filled('barcode') || $request->filled('rfid_barcode')) {
            $request->merge([
                'barcode' => $request->input('barcode') ?? $request->input('rfid_barcode'),
                'session' => $request->input('session') ?? $request->input('sesi'),
                'notes' => $request->input('notes') ?? $request->input('keterangan'),
                'scan_time' => $request->input('scan_time') ?? $request->input('waktu_absen'),
            ]);

            $validated = $request->validate([
                'barcode' => ['required', 'exists:santris,rfid_barcode'],
                'status' => ['required', 'in:Hadir,Sakit,Izin,Alfa'],
                'session' => ['required', 'in:Subuh,Dzuhur,Asar,Ashar,Isya'],
                'notes' => ['nullable', 'string'],
                'scan_time' => ['required', 'date'],
            ]);

            if (in_array($validated['status'], ['Sakit', 'Izin'], true) && blank($validated['notes'])) {
                return back()->withErrors(['keterangan' => 'Keterangan/Alasan wajib diisi untuk status Sakit atau Izin.'])->withInput();
            }

            $santri = Santri::where('rfid_barcode', $validated['barcode'])->firstOrFail();

            try {
                $scanTime = Carbon::parse($validated['scan_time']);
                $this->attendanceService->storeAttendance($santri, $validated['status'], $validated['notes'], $scanTime, $validated['session']);
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }

            return back()->with('success', 'Absensi manual berhasil disimpan.');
        }

        return back()->withErrors(['attendance' => 'Tidak ada data absensi untuk disimpan.']);
    }

    public function dailyReport(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $classroomId = $request->input('classroom_id');
        $session = $request->input('session');

        $santriQuery = Santri::query()->with('classroom')->orderBy('name');
        if ($classroomId) {
            $santriQuery->where('classroom_id', $classroomId);
        }
        $santris = $santriQuery->get();

        $attendanceQuery = Attendance::query()->whereDate('date', $date);
        if ($classroomId) {
            $attendanceQuery->whereHas('santri.classroom', fn ($query) => $query->where('classrooms.id', $classroomId));
        }

        $attendanceData = $attendanceQuery->with('santri.classroom')->get()
            ->groupBy('santri_id')
            ->map(fn ($group) => $group->keyBy('session'));

        $allSessions = ['Subuh', 'Dzuhur', 'Asar', 'Isya'];
        $totalSantri = $santris->count();

        if ($session) {
            // Filter sesi aktif: satu baris per santri dengan status sesi tersebut
            $records = collect();
            foreach ($santris as $santri) {
                $santriAttendance = $attendanceData->get($santri->id, collect());
                $sessionRecord = $santriAttendance->get($session);

                $records->push((object) [
                    'santri' => $santri,
                    'status' => $sessionRecord ? $sessionRecord->status : 'Belum Absen',
                    'session' => $session,
                    'notes' => $sessionRecord ? $sessionRecord->notes : null,
                    'scan_time' => $sessionRecord ? $sessionRecord->scan_time : null,
                ]);
            }

            $stats = [
                'Hadir' => $records->where('status', 'Hadir')->count(),
                'Sakit' => $records->where('status', 'Sakit')->count(),
                'Izin' => $records->where('status', 'Izin')->count(),
                'Belum Absen' => $records->filter(fn ($r) => in_array($r->status, ['Alfa', 'Belum Absen'], true))->count(),
            ];

            return view('attendance.daily-report', compact('records', 'stats', 'date', 'classroomId', 'session', 'totalSantri') + [
                'classrooms' => Classroom::orderBy('name')->get(),
                'sessions' => $allSessions,
            ]);
        }

        // Tanpa filter sesi: satu baris per santri, dengan kolom per-sesi
        $records = [];
        $stats = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Belum Absen' => 0];

        $liburRecord = \App\Models\HariLibur::whereDate('tanggal', $date)->first();
        $jadwalsNonaktif = \App\Models\Jadwal::where('status', 'nonaktif')->pluck('nama_kegiatan')->map(fn($n) => strtolower($n))->toArray();

        foreach ($santris as $santri) {
            $santriAttendance = $attendanceData->get($santri->id, collect());
            $rows = [];

            foreach ($allSessions as $sess) {
                $sessKey = strtolower($sess);
                $isLibur = false;
                $ketLibur = 'Jadwal Libur';

                if ($liburRecord) {
                    $ket = strtolower($liburRecord->keterangan ?? '');
                        if ($this->attendanceService->isHolidayForSession($liburRecord->keterangan, $sessKey)) {
                        $isLibur = true;
                        $ketLibur = $liburRecord->keterangan ?: 'Hari Libur Pesantren';
                    }
                }

                if (in_array($sessKey, $jadwalsNonaktif)) {
                    $isLibur = true;
                }

                $rec = $santriAttendance->get($sess);
                $status = $rec ? $rec->status : ($isLibur ? 'Libur' : 'Belum Absen');

                $rows[$sess] = [
                    'status' => $status,
                    'notes' => $rec ? $rec->notes : ($isLibur ? $ketLibur : null),
                    'scan_time' => $rec ? $rec->scan_time : null,
                ];
            }

            $records[] = [
                'santri' => $santri,
                'rows' => $rows,
            ];
        }

        // Stats: hitung per-sesi unik per santri (total kemunculan status di semua sesi)
        foreach ($records as $record) {
            foreach ($record['rows'] as $row) {
                $st = $row['status'];
                if ($st === 'Hadir') $stats['Hadir']++;
                elseif ($st === 'Sakit') $stats['Sakit']++;
                elseif ($st === 'Izin') $stats['Izin']++;
                elseif (in_array($st, ['Alfa', 'Belum Absen'], true)) $stats['Belum Absen']++;
            }
        }

        $classrooms = Classroom::orderBy('name')->get();

        return view('attendance.daily-report', compact('records', 'stats', 'classrooms', 'date', 'classroomId', 'session', 'totalSantri') + [
            'sessions' => $allSessions,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $classroomId = $request->input('classroom_id');
        $session = $request->input('session');

        $santriQuery = Santri::query()->with('classroom')->orderBy('name');
        if ($classroomId) {
            $santriQuery->where('classroom_id', $classroomId);
        }
        $santris = $santriQuery->get();

        $attendanceQuery = Attendance::query()->whereDate('date', $date);
        if ($classroomId) {
            $attendanceQuery->whereHas('santri.classroom', fn ($query) => $query->where('classrooms.id', $classroomId));
        }

        $attendanceData = $attendanceQuery->with('santri.classroom')->get()
            ->groupBy('santri_id')
            ->map(fn ($group) => $group->keyBy('session'));

        $sessionLabel = $session ? "_{$session}" : '';
        $fileName = "Laporan_Harian_Absensi_{$date}{$sessionLabel}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        if ($session) {
            // Export per-sesi: satu baris per santri
            $columns = ['NO', 'NAMA SANTRI', 'KELAS', "STATUS ({$session})", 'KETERANGAN / ALASAN', 'WAKTU REKAM ABSEN'];

            $callback = function() use($santris, $attendanceData, $columns, $session) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($santris as $index => $santri) {
                    $santriAttendance = $attendanceData->get($santri->id, collect());
                    $rec = $santriAttendance->get($session);

                    fputcsv($file, [
                        $index + 1,
                        $santri->name,
                        $santri->classroom?->name ?? '-',
                        $rec ? $rec->status : 'Belum Absen',
                        $rec ? ($rec->notes ?? '-') : '-',
                        $rec && $rec->scan_time ? $rec->scan_time->format('H:i:s') : '-',
                    ]);
                }

                fclose($file);
            };
        } else {
            // Export semua sesi: satu baris per santri, kolom per-sesi
            $allSessions = ['Subuh', 'Dzuhur', 'Asar', 'Isya'];
            $columns = ['NO', 'NAMA SANTRI', 'KELAS'];
            foreach ($allSessions as $sess) {
                $columns[] = strtoupper($sess);
            }

            $callback = function() use($santris, $attendanceData, $columns, $allSessions) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($santris as $index => $santri) {
                    $santriAttendance = $attendanceData->get($santri->id, collect());
                    $row = [$index + 1, $santri->name, $santri->classroom?->name ?? '-'];

                    foreach ($allSessions as $sess) {
                        $rec = $santriAttendance->get($sess);
                        $row[] = $rec ? $rec->status : 'Belum Absen';
                    }

                    fputcsv($file, $row);
                }

                fclose($file);
            };
        }

        return response()->stream($callback, 200, $headers);
    }

    public function monthlyJournal(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $classroomId = $request->input('classroom_id');

        $santriQuery = Santri::query()->with('classroom');
        if ($classroomId) {
            $santriQuery->where('classroom_id', $classroomId);
        }

        $santris = $santriQuery->get();
        $records = [];

        $attendanceData = Attendance::query()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('santri.classroom')
            ->get()
            ->groupBy(fn ($attendance) => $attendance->santri_id)
            ->map(function ($group) {
                return $group->groupBy('session');
            });

        foreach ($santris as $santri) {
            $santriAttendance = $attendanceData->get($santri->id, collect());
            $rows = [];
            foreach (['Subuh', 'Dzuhur', 'Asar', 'Isya'] as $session) {
                $sessionRecords = $santriAttendance->get($session, collect());
                $rows[$session] = [
                    'Hadir' => $sessionRecords->where('status', 'Hadir')->count(),
                    'Sakit' => $sessionRecords->where('status', 'Sakit')->count(),
                    'Izin' => $sessionRecords->where('status', 'Izin')->count(),
                    'Alfa' => $sessionRecords->where('status', 'Alfa')->count(),
                ];
            }

            $records[] = [
                'santri' => $santri,
                'rows' => $rows,
                'totals' => [
                    'Hadir' => collect($rows)->sum(fn ($row) => $row['Hadir']),
                    'Sakit' => collect($rows)->sum(fn ($row) => $row['Sakit']),
                    'Izin' => collect($rows)->sum(fn ($row) => $row['Izin']),
                    'Alfa' => collect($rows)->sum(fn ($row) => $row['Alfa']),
                ],
            ];
        }

        $classrooms = Classroom::orderBy('name')->get();
        $months = collect(range(1, 12));
        $years = collect(range(now()->year - 1, now()->year + 1));

        return view('attendance.monthly-journal', compact('records', 'classrooms', 'months', 'years', 'month', 'year', 'classroomId'));
    }

    public function exportMonthlyJournal(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $classroomId = $request->input('classroom_id');

        $santriQuery = Santri::query()->with('classroom');
        if ($classroomId) {
            $santriQuery->where('classroom_id', $classroomId);
        }

        $santris = $santriQuery->get();

        $attendanceData = Attendance::query()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->with('santri.classroom')
            ->get()
            ->groupBy(fn ($attendance) => $attendance->santri_id)
            ->map(function ($group) {
                return $group->groupBy('session');
            });

        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        $fileName = "Jurnal_Bulanan_Absensi_{$monthName}_{$year}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NO', 'NAMA SANTRI', 'KELAS', 'SUBUH (H/S/I/A)', 'DZUHUR (H/S/I/A)', 'ASAR (H/S/I/A)', 'ISYA (H/S/I/A)', 'TOTAL HADIR', 'TOTAL SAKIT', 'TOTAL IZIN', 'TOTAL ALFA'];

        $callback = function() use($santris, $attendanceData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($santris as $index => $santri) {
                $santriAttendance = $attendanceData->get($santri->id, collect());
                $row = [$index + 1, $santri->name, $santri->classroom?->name ?? '-'];
                $totals = ['Hadir' => 0, 'Sakit' => 0, 'Izin' => 0, 'Alfa' => 0];

                foreach (['Subuh', 'Dzuhur', 'Asar', 'Isya'] as $session) {
                    $sessionRecords = $santriAttendance->get($session, collect());
                    $h = $sessionRecords->where('status', 'Hadir')->count();
                    $s = $sessionRecords->where('status', 'Sakit')->count();
                    $i = $sessionRecords->where('status', 'Izin')->count();
                    $a = $sessionRecords->where('status', 'Alfa')->count();
                    $row[] = "H:{$h} S:{$s} I:{$i} A:{$a}";
                    $totals['Hadir'] += $h;
                    $totals['Sakit'] += $s;
                    $totals['Izin'] += $i;
                    $totals['Alfa'] += $a;
                }

                $row[] = $totals['Hadir'];
                $row[] = $totals['Sakit'];
                $row[] = $totals['Izin'];
                $row[] = $totals['Alfa'];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
