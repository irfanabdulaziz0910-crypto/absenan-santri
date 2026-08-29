<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\Santri;
use App\Models\TeacherAttendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaliKelasController extends Controller
{
    private function getWaliKelasData()
    {
        $attendanceService = app(\App\Services\AttendanceService::class);
        $user = Auth::guard('web')->user() ?: Auth::user();
        $guru = null;

        if ($user && $user->guru_id) {
            $guru = Guru::with('classroom')->find($user->guru_id);
        } elseif ($user) {
            $guru = Guru::with('classroom')->where('user_id', $user->id)->first();
        }

        // 1. Cek apakah ada kuncian kelas aktif dari sesi Absensi Mengajar
        $activeClassroomId = session('active_classroom_id');
        
        $assignedClassroom = null;
        if ($activeClassroomId) {
            $assignedClassroom = Classroom::find($activeClassroomId);
        }
        
        // 2. Jika tidak ada, gunakan default kelas wali
        if (!$assignedClassroom && $guru) {
            $assignedClassroom = $guru->classroom;
            if (!$assignedClassroom) {
                // Coba ambil dari relasi pivot (kelas pertama yang dipivot)
                $pivotFirst = $guru->classrooms()->first();
                $assignedClassroom = $pivotFirst ?: null;
            }
        }

        $assignedKelas = $assignedClassroom ? $assignedClassroom->name : '';

        // Query Santri strictly by classroom_id
        $santrisDb = $assignedClassroom
            ? Santri::with('classroom')->where('classroom_id', $assignedClassroom->id)->orderBy('name')->get()
            : collect();

        $santris = collect([]);
        foreach ($santrisDb as $s) {
            $santris->push([
                'id'            => $s->id,
                'name'          => $s->name,
                'nis'           => $s->nis ?? ('SAN' . str_pad($s->id, 3, '0', STR_PAD_LEFT)),
                'rfid_barcode'  => $s->rfid_barcode,
                'kelas'         => $s->classroom ? $s->classroom->name : $assignedKelas,
                'classroom_id'  => $s->classroom_id,
                'status'        => 'Aktif',
                'jenis_kelamin' => $s->jenis_kelamin ?: 'L',
            ]);
        }

        $totalSantri = $santris->count();
        $kelasList   = Classroom::orderBy('name')->get();

        // Absensi Real Database
        $today = now()->toDateString();
        $classroomId = $assignedClassroom ? $assignedClassroom->id : null;

        $todayAttendances = $classroomId
            ? Attendance::whereDate('date', $today)
                ->whereHas('santri', fn ($q) => $q->where('classroom_id', $classroomId))
                ->get()
            : collect();

        $hadirCount = $todayAttendances->filter(fn ($a) => strtolower($a->status) === 'hadir')->count();
        $izinCount  = $todayAttendances->filter(fn ($a) => strtolower($a->status) === 'izin')->count();
        $sakitCount = $todayAttendances->filter(fn ($a) => strtolower($a->status) === 'sakit')->count();
        $alfaCount  = $todayAttendances->filter(fn ($a) => strtolower($a->status) === 'alfa')->count();
        $tidakHadir = $izinCount + $sakitCount + $alfaCount;

        $persentaseHadir = $totalSantri > 0 ? round(($hadirCount / $totalSantri) * 100) : 0;

        // Session distribution chart data (Subuh, Dzuhur, Ashar, Isya)
        $sesiChartData = [];
        foreach (['subuh', 'dzuhur', 'asar', 'isya'] as $sesi) {
            $c = $classroomId
                ? Attendance::whereDate('date', $today)
                    ->where('session', $sesi)
                    ->where(fn ($q) => $q->where('status', 'hadir')->orWhere('status', 'Hadir'))
                    ->whereHas('santri', fn ($q) => $q->where('classroom_id', $classroomId))
                    ->count()
                : 0;
            $sesiChartData[$sesi] = $c;
        }

        // Attendance records mapped by santri_id, date, session
        $attRecords = $classroomId
            ? Attendance::whereHas('santri', fn ($q) => $q->where('classroom_id', $classroomId))->get()
            : collect();

        $attendanceMap = [];
        foreach ($attRecords as $att) {
            $sId = $att->santri_id;
            $d   = $att->date ? $att->date->format('Y-m-d') : ($att->created_at ? $att->created_at->format('Y-m-d') : now()->toDateString());
            $ses = strtolower($att->session);
            $attendanceMap[$sId][$d][$ses] = [
                'status' => ucfirst(strtolower($att->status)),
                'notes'  => $att->notes ?: 'Tercatat di sistem',
                'time'   => $att->scan_time ? $att->scan_time->format('H:i') : ($att->created_at ? $att->created_at->format('H:i') : '-'),
            ];
        }

        // Map HariLibur & Jadwal Nonaktif ke dalam attendanceMap
        $hariLiburs = \App\Models\HariLibur::all();
        $jadwalsNonaktif = \App\Models\Jadwal::where('status', 'nonaktif')->pluck('nama_kegiatan')->map(fn($n) => strtolower($n))->toArray();
        $allSessionsList = ['subuh', 'dzuhur', 'asar', 'isya'];

        foreach ($santris as $s) {
            $sId = $s['id'];
            foreach ($hariLiburs as $hl) {
                $dStr = $hl->tanggal ? ($hl->tanggal instanceof \Carbon\Carbon ? $hl->tanggal->format('Y-m-d') : substr((string)$hl->tanggal, 0, 10)) : null;
                if (!$dStr) continue;
                foreach ($allSessionsList as $ses) {
                    if ($attendanceService->isHolidayForSession($hl->keterangan, $ses)) {
                        if (!isset($attendanceMap[$sId][$dStr][$ses]) || $attendanceMap[$sId][$dStr][$ses]['status'] === 'Belum Absen') {
                            $attendanceMap[$sId][$dStr][$ses] = [
                                'status' => 'Libur',
                                'notes'  => $hl->keterangan ?: 'Hari Libur Pesantren',
                                'time'   => '-',
                            ];
                        }
                    }
                }
            }
            if (!empty($jadwalsNonaktif)) {
                $todayStr = now()->toDateString();
                foreach ($jadwalsNonaktif as $ses) {
                    if (!isset($attendanceMap[$sId][$todayStr][$ses]) || $attendanceMap[$sId][$todayStr][$ses]['status'] === 'Belum Absen') {
                        $attendanceMap[$sId][$todayStr][$ses] = [
                            'status' => 'Libur',
                            'notes'  => 'Jadwal Libur',
                            'time'   => '-',
                        ];
                    }
                }
            }
        }

        // Determine auto-selected $sesiAktif based on server time
        $now  = now();
        $hour = $now->hour;
        $time = $now->format('H:i');

        if ($time >= '04:00' && $time <= '08:00') {
            $sesiAktif = 'Subuh';
        } elseif ($time >= '11:30' && $time <= '14:59') {
            $sesiAktif = 'Dzuhur';
        } elseif ($time >= '15:00' && $time <= '17:59') {
            $sesiAktif = 'Ashar';
        } elseif ($time >= '18:00' && $time <= '23:59') {
            $sesiAktif = 'Isya';
        } else {
            // Fallback to most recent past session (e.g., Subuh for 08:01-11:29)
            if ($hour >= 8 && $hour < 11) {
                $sesiAktif = 'Subuh';
            } elseif ($hour >= 11 && $hour < 15) {
                $sesiAktif = 'Dzuhur';
            } elseif ($hour >= 15 && $hour < 18) {
                $sesiAktif = 'Ashar';
            } else {
                $sesiAktif = 'Isya';
            }
        }

        $pendingRequests = collect();
        if ($assignedClassroom) {
            $pendingRequests = TeacherAttendance::with('guru')
                ->where('classroom_id', $assignedClassroom->id)
                ->where('approval_status', 'pending')
                ->latest('date')
                ->get();
        }

        return compact(
            'santris',
            'assignedKelas',
            'assignedClassroom',
            'kelasList',
            'totalSantri',
            'guru',
            'hadirCount',
            'izinCount',
            'sakitCount',
            'alfaCount',
            'tidakHadir',
            'persentaseHadir',
            'sesiChartData',
            'attendanceMap',
            'sesiAktif',
            'pendingRequests'
        );
    }

    public function dashboard()
    {
        return view('wali-kelas.dashboard', $this->getWaliKelasData());
    }

    public function laporan(Request $request)
    {
        $attendanceService = app(\App\Services\AttendanceService::class);
        $period     = strtolower($request->input('period', 'harian'));
        $sesiFilter = $request->input('sesi', '');
        $sesiQuery  = $attendanceService->normalizeSession($sesiFilter);
        $tglInput   = $request->input('tanggal', $request->input('tgl', now()->toDateString()));
        $tanggal    = $tglInput;

        $user = Auth::guard('web')->user() ?: Auth::user();
        $guru = null;

        if ($user && $user->guru_id) {
            $guru = Guru::with('classroom')->find($user->guru_id);
        } elseif ($user) {
            $guru = Guru::with('classroom')->where('user_id', $user->id)->first();
        }

        // Ambil kelas wali TANPA fallback berbahaya
        $assignedClassroom = null;
        if ($guru) {
            $assignedClassroom = $guru->classroom;
            if (!$assignedClassroom) {
                $pivotFirst = $guru->classrooms()->first();
                $assignedClassroom = $pivotFirst ?: null;
            }
        }
        $assignedKelas = $assignedClassroom ? $assignedClassroom->name : '';
        $classroomId   = $assignedClassroom ? $assignedClassroom->id : null;

        // Date range based on period
        if ($period === 'harian') {
            $startDate = \Carbon\Carbon::parse($tglInput)->startOfDay();
            $endDate   = \Carbon\Carbon::parse($tglInput)->endOfDay();
            $totalHari = 1;
        } elseif ($period === 'bulanan') {
            $startDate = \Carbon\Carbon::parse($tglInput)->startOfMonth();
            $endDate   = \Carbon\Carbon::parse($tglInput)->endOfMonth();
            $totalHari = $startDate->daysInMonth;
        } elseif ($period === 'semester') {
            $startDate = now()->subMonths(6)->startOfMonth();
            $endDate   = now()->endOfMonth();
            $totalHari = 120;
        } else {
            // mingguan: SABTU -> JUMAT
            $dt = \Carbon\Carbon::parse($tglInput);
            if ($dt->dayOfWeek === \Carbon\Carbon::SATURDAY) {
                $startDate = $dt->copy()->startOfDay();
            } else {
                $startDate = $dt->copy()->previous(\Carbon\Carbon::SATURDAY)->startOfDay();
            }
            $endDate   = $startDate->copy()->addDays(6)->endOfDay();
            $totalHari = 7;
        }

        // Query Santri strictly by classroom_id
        $santrisDb = $classroomId
            ? Santri::with('classroom')->where('classroom_id', $classroomId)->orderBy('name')->get()
            : collect();

        $santris = collect([]);
        foreach ($santrisDb as $s) {
            $santris->push([
                'id'            => $s->id,
                'name'          => $s->name,
                'nis'           => $s->nis ?? ('SAN' . str_pad($s->id, 3, '0', STR_PAD_LEFT)),
                'rfid_barcode'  => $s->rfid_barcode,
                'kelas'         => $s->classroom ? $s->classroom->name : $assignedKelas,
                'classroom_id'  => $s->classroom_id,
                'status'        => 'Aktif',
                'jenis_kelamin' => $s->jenis_kelamin ?: 'L',
            ]);
        }

        $santriIds = $santris->pluck('id')->toArray();

        // Fetch ALL attendance records within date range
        $attQuery = Attendance::whereIn('santri_id', $santriIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        if ($sesiQuery && strtolower($sesiQuery) !== 'semua sesi') {
            $attQuery->whereRaw('LOWER(session) = ?', [strtolower($sesiQuery)]);
        }
        $allAtts = $attQuery->get();

        // Build attendanceMap
        $attendanceMap = [];
        foreach ($allAtts as $att) {
            $sId = $att->santri_id;
            $d   = $att->date ? $att->date->format('Y-m-d') : now()->toDateString();
            $ses = strtolower($att->session);
            $attendanceMap[$sId][$d][$ses] = [
                'status' => ucfirst(strtolower($att->status)),
                'notes'  => $att->notes ?: 'Tercatat di sistem',
                'time'   => $att->scan_time ? $att->scan_time->format('H:i') : ($att->created_at ? $att->created_at->format('H:i') : '-'),
            ];
        }

        // Check HariLibur & Jadwal nonaktif
        $hariLiburs = \App\Models\HariLibur::whereDate('tanggal', '>=', $startDate->toDateString())
            ->whereDate('tanggal', '<=', $endDate->toDateString())
            ->get();
        $jadwalsNonaktif = \App\Models\Jadwal::where('status', 'nonaktif')->pluck('nama_kegiatan')->map(fn($n) => strtolower($n))->toArray();
        $allSessionsList = ['subuh', 'dzuhur', 'asar', 'isya'];

        foreach ($santriIds as $sId) {
            $currDate = $startDate->copy();
            while ($currDate->lte($endDate)) {
                $dStr = $currDate->toDateString();
                $liburRecord = $hariLiburs->first(function ($hl) use ($dStr) {
                    if (!$hl->tanggal) return false;
                    $d = $hl->tanggal instanceof \Carbon\Carbon ? $hl->tanggal->format('Y-m-d') : substr((string)$hl->tanggal, 0, 10);
                    return $d === $dStr;
                });

                foreach ($allSessionsList as $ses) {
                    $isLibur = false;
                    $ketLibur = 'Jadwal Libur';

                    if ($liburRecord) {
                        if ($attendanceService->isHolidayForSession($liburRecord->keterangan, $ses)) {
                            $isLibur = true;
                            $ketLibur = $liburRecord->keterangan ?: 'Hari Libur Pesantren';
                        }
                    }

                    if (in_array($ses, $jadwalsNonaktif)) {
                        $isLibur = true;
                    }

                    if ($isLibur) {
                        $attendanceMap[$sId][$dStr][$ses] = [
                            'status' => 'Libur',
                            'notes'  => $ketLibur,
                            'time'   => '-',
                        ];
                    }
                }
                $currDate->addDay();
            }
        }

        // Compute per-santri stats and global stats
        $attendanceStats = [];
        $globalStats = [
            'total_hari' => $totalHari,
            'hadir'      => 0,
            'izin'       => 0,
            'sakit'      => 0,
            'alfa'       => 0,
            'libur'      => 0,
            'persentase' => 0,
        ];

        foreach ($santris as $s) {
            $sId = $s['id'];
            $sAtts = $allAtts->where('santri_id', $sId);

            $h  = $sAtts->filter(fn ($a) => strtolower($a->status) === 'hadir')->count();
            $i  = $sAtts->filter(fn ($a) => strtolower($a->status) === 'izin')->count();
            $sk = $sAtts->filter(fn ($a) => strtolower($a->status) === 'sakit')->count();
            $a  = $sAtts->filter(fn ($a) => strtolower($a->status) === 'alfa')->count();

            $l = 0;
            if (isset($attendanceMap[$sId])) {
                foreach ($attendanceMap[$sId] as $dateMap) {
                    foreach ($dateMap as $sesVal) {
                        if (($sesVal['status'] ?? '') === 'Libur') {
                            $l++;
                        }
                    }
                }
            }

            $totalAtt = $h + $i + $sk + $a;
            $pct = $totalAtt > 0 ? round(($h / $totalAtt) * 100) : 0;

            $attendanceStats[$sId] = [
                'hadir'      => $h,
                'izin'       => $i,
                'sakit'      => $sk,
                'alfa'       => $a,
                'libur'      => $l,
                'persentase' => $pct,
            ];

            $globalStats['hadir'] += $h;
            $globalStats['izin']  += $i;
            $globalStats['sakit'] += $sk;
            $globalStats['alfa']  += $a;
            $globalStats['libur'] += $l;
        }

        $totalGlobal = $globalStats['hadir'] + $globalStats['izin'] + $globalStats['sakit'] + $globalStats['alfa'];
        $globalStats['persentase'] = $totalGlobal > 0 ? round(($globalStats['hadir'] / $totalGlobal) * 100) : 0;

        $totalSantri = $santris->count();
        $hadirCount  = $globalStats['hadir'];
        $izinCount   = $globalStats['izin'];
        $sakitCount  = $globalStats['sakit'];
        $alfaCount   = $globalStats['alfa'];
        $tidakHadir  = $izinCount + $sakitCount + $alfaCount;
        $persentaseHadir = $globalStats['persentase'];

        return view('wali-kelas.laporan', compact(
            'period', 'tanggal', 'assignedKelas', 'assignedClassroom',
            'santris', 'totalSantri', 'hadirCount', 'izinCount', 'sakitCount',
            'alfaCount', 'tidakHadir', 'persentaseHadir', 'attendanceMap',
            'attendanceStats', 'globalStats'
        ));
    }

    public function santri()
    {
        return view('wali-kelas.santri', $this->getWaliKelasData());
    }

    public function absensiManual()
    {
        return view('wali-kelas.absensi-manual', $this->getWaliKelasData());
    }

    public function saveAbsensiManual(Request $request)
    {
        $attendanceService = app(AttendanceService::class);
        $validated = $request->validate([
            'date'                   => ['required', 'date'],
            'session'                => ['required', 'in:Subuh,Dzuhur,Ashar,Isya'],
            'attendance'             => ['required', 'array'],
            'attendance.*.santri_id' => ['required', 'exists:santris,id'],
            'attendance.*.status'    => ['required', 'in:Hadir,Izin,Sakit,Alfa,Belum Absen'],
            'attendance.*.notes'     => ['nullable', 'string'],
        ]);

        $tanggal = $validated['date'];
        $sesi    = $attendanceService->normalizeSession($validated['session']);

        // Block if session/date is Libur
        $liburRecord = \App\Models\HariLibur::whereDate('tanggal', $tanggal)->first();
        $isLibur = false;
        if ($liburRecord) {
            if ($attendanceService->isHolidayForSession($liburRecord->keterangan, $sesi)) {
                $isLibur = true;
            }
        }
        if ($isLibur) {
            return response()->json(['success' => false, 'message' => "Sesi {$sesi} pada tanggal {$tanggal} sedang LIBUR. Absensi manual ditutup."], 422);
        }

        foreach ($validated['attendance'] as $item) {
            $santriId = $item['santri_id'];
            $status   = $item['status'];
            $notes    = trim($item['notes'] ?? '');

            if ($status === 'Belum Absen') {
                continue;
            }

            // Abaikan jika status sudah Hadir dari RFID Tap
            $existingRfid = Attendance::where('santri_id', $santriId)
                ->whereDate('date', $tanggal)
                ->where('session', $sesi)
                ->whereRaw('LOWER(status) = ?', ['hadir'])
                ->whereRaw('LOWER(COALESCE(notes, \'\')) LIKE ?', ['%rfid%'])
                ->exists();

            if ($existingRfid) {
                continue;
            }

            // Status Sakit/Izin wajib alasan; Alfa/Hadir nullable
            if (in_array($status, ['Sakit', 'Izin'], true) && empty($notes)) {
                return response()->json([
                    'success' => false,
                    'message' => "Keterangan/Alasan wajib diisi untuk status {$status}.",
                ], 422);
            }

            if (empty($notes)) {
                $notes = 'Tanpa Keterangan';
            }

            Attendance::updateOrCreate(
                [
                    'santri_id' => $santriId,
                    'date'      => $tanggal,
                    'session'   => $sesi,
                ],
                [
                    'status'    => $status,
                    'notes'     => $notes,
                    'scan_time' => now(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Data absensi manual berhasil disimpan ke database!']);
    }

    public function notifikasi()
    {
        $user = Auth::guard('web')->user() ?: Auth::user();
        $guru = null;
        if ($user && $user->guru_id) {
            $guru = Guru::with('classroom')->find($user->guru_id);
        } elseif ($user) {
            $guru = Guru::with('classroom')->where('user_id', $user->id)->first();
        }

        $assignedClassroom = $guru?->classroom;
        if (!$assignedClassroom && $guru) {
            $assignedClassroom = $guru->classrooms()->first();
        }

        $pendingRequests = collect();
        $historyRequests = collect();

        if ($assignedClassroom) {
            $pendingRequests = TeacherAttendance::with(['guru', 'classroom'])
                ->where('classroom_id', $assignedClassroom->id)
                ->where('approval_status', 'pending')
                ->latest('date')
                ->latest('created_at')
                ->get();

            $historyRequests = TeacherAttendance::with(['guru', 'classroom', 'approvedBy'])
                ->where('classroom_id', $assignedClassroom->id)
                ->whereIn('approval_status', ['approved', 'rejected'])
                ->latest('date')
                ->latest('created_at')
                ->take(30)
                ->get();
        }

        return view('wali-kelas.notifikasi', compact(
            'guru',
            'assignedClassroom',
            'pendingRequests',
            'historyRequests'
        ));
    }

    public function approveTeacherAttendance($id)
    {
        $user = Auth::guard('web')->user() ?: Auth::user();
        if (!$user) {
            $user = Auth::guard('admin')->user();
        }

        $guru = $user?->guru_id ? Guru::find($user->guru_id) : Guru::where('user_id', $user?->id)->first();
        $attendance = TeacherAttendance::findOrFail($id);

        // Validasi jika role wali_kelas & punya kelas wali, hanya bisa approve kelas walinya
        if ($user?->role === 'wali_kelas' && $guru?->classroom_id && (int)$attendance->classroom_id !== (int)$guru->classroom_id) {
            return back()->with('error', 'Anda hanya dapat menyetujui permintaan mengajar untuk kelas wali Anda.');
        }

        $userIdInUsersTable = $user instanceof \App\Models\User ? $user->id : \App\Models\User::where('email', $user?->email)->orWhere('username', $user?->username)->first()?->id;

        $attendance->update([
            'approval_status' => 'approved',
            'approved_by'     => $userIdInUsersTable,
            'approved_at'     => now(),
            'rejection_reason'=> null,
        ]);

        return back()->with('success', 'Permintaan mengajar telah disetujui!');
    }

    public function rejectTeacherAttendance(Request $request, $id)
    {
        $user = Auth::guard('web')->user() ?: Auth::user();
        if (!$user) {
            $user = Auth::guard('admin')->user();
        }

        $guru = $user?->guru_id ? Guru::find($user->guru_id) : Guru::where('user_id', $user?->id)->first();
        $attendance = TeacherAttendance::findOrFail($id);

        // Validasi jika role wali_kelas & punya kelas wali, hanya bisa reject kelas walinya
        if ($user?->role === 'wali_kelas' && $guru?->classroom_id && (int)$attendance->classroom_id !== (int)$guru->classroom_id) {
            return back()->with('error', 'Anda hanya dapat menolak permintaan mengajar untuk kelas wali Anda.');
        }

        $userIdInUsersTable = $user instanceof \App\Models\User ? $user->id : \App\Models\User::where('email', $user?->email)->orWhere('username', $user?->username)->first()?->id;

        $attendance->update([
            'approval_status' => 'rejected',
            'approved_by'     => $userIdInUsersTable,
            'approved_at'     => now(),
            'rejection_reason'=> $request->input('rejection_reason', 'Permintaan ditolak oleh Wali Kelas'),
        ]);

        return back()->with('success', 'Permintaan mengajar telah ditolak.');
    }
}
