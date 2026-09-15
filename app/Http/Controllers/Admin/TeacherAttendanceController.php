<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherAttendance::with(['guru', 'classroom', 'approvedBy'])->latest('date')->latest('attendance_time');

        $query->when($request->filled('guru_id'), fn ($q) => $q->where('guru_id', $request->guru_id));
        $query->when($request->filled('classroom_id'), fn ($q) => $q->where('classroom_id', $request->classroom_id));
        $query->when($request->filled('kitab'), fn ($q) => $q->where('kitab', 'like', '%' . $request->kitab . '%'));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));

        $period = $request->input('period');
        $tgl = $request->input('date');

        if ($tgl) {
            $baseDate = Carbon::parse($tgl);
            if ($period === 'harian') {
                $query->whereDate('date', $baseDate->toDateString());
            } elseif ($period === 'mingguan') {
                $start = $baseDate->copy()->startOfWeek(Carbon::SATURDAY);
                $end = $start->copy()->addDays(6);
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            } elseif ($period === 'bulanan') {
                $query->whereYear('date', $baseDate->year)->whereMonth('date', $baseDate->month);
            } elseif ($period === 'semester') {
                $start = $baseDate->copy()->subMonths(5)->startOfMonth();
                $query->whereBetween('date', [$start->toDateString(), $baseDate->endOfMonth()->toDateString()]);
            } else {
                $query->whereDate('date', $baseDate->toDateString());
            }
        } elseif ($period) {
            $now = Carbon::now();
            if ($period === 'harian') {
                $query->whereDate('date', $now->toDateString());
            } elseif ($period === 'mingguan') {
                $start = $now->copy()->startOfWeek(Carbon::SATURDAY);
                $end = $start->copy()->addDays(6);
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            } elseif ($period === 'bulanan') {
                $query->whereYear('date', $now->year)->whereMonth('date', $now->month);
            } elseif ($period === 'semester') {
                $start = $now->copy()->subMonths(5)->startOfMonth();
                $query->whereBetween('date', [$start->toDateString(), $now->endOfMonth()->toDateString()]);
            }
        }

        return view('admin.teacher-attendance.index', [
            'attendances' => $query->paginate(25)->withQueryString(),
            'gurus' => Guru::orderBy('name')->get(),
            'classrooms' => Classroom::orderBy('name')->get(),
            'filters' => [
                'guru_id' => $request->guru_id,
                'classroom_id' => $request->classroom_id,
                'date' => $request->date,
                'kitab' => $request->kitab,
                'status' => $request->status,
                'period' => $request->period,
            ],
        ]);
    }

    public function approve($id)
    {
        $attendance = TeacherAttendance::findOrFail($id);

        $adminUser = auth()->guard('admin')->user();
        $userInUsersTable = \App\Models\User::where('email', $adminUser?->email)
            ->orWhere('username', $adminUser?->username)
            ->first();

        $attendance->update([
            'approval_status' => 'approved',
            'approved_by'     => $userInUsersTable?->id,
            'approved_at'     => now(),
            'rejection_reason'=> null,
        ]);

        return back()->with('success', 'Permintaan mengajar telah disetujui!');
    }

    public function reject(Request $request, $id)
    {
        $attendance = TeacherAttendance::findOrFail($id);

        $adminUser = auth()->guard('admin')->user();
        $userInUsersTable = \App\Models\User::where('email', $adminUser?->email)
            ->orWhere('username', $adminUser?->username)
            ->first();

        $attendance->update([
            'approval_status' => 'rejected',
            'approved_by'     => $userInUsersTable?->id,
            'approved_at'     => now(),
            'rejection_reason'=> $request->input('rejection_reason', 'Permintaan ditolak oleh Admin'),
        ]);

        return back()->with('success', 'Permintaan mengajar telah ditolak.');
    }

    /**
     * Halaman Rekap Absensi Guru (Per Sesi, Per Hari, Per Minggu, Per Bulan, Per Semester, Per Tahun)
     */
    public function rekap(Request $request)
    {
        $attendanceService = app(\App\Services\AttendanceService::class);

        $jenisPeriode = $request->input('jenis_periode', 'harian');
        $tglInput     = $request->input('tanggal', now()->toDateString());
        $sesiFilter   = $request->input('sesi', '');
        $guruIdFilter = $request->input('guru_id', '');
        $bulanFilter  = (int) $request->input('bulan', now()->month);
        $tahunFilter  = (int) $request->input('tahun', now()->year);
        $semesterFilter = (int) $request->input('semester', now()->month >= 7 ? 1 : 2);

        $gurusQuery = Guru::orderBy('name');
        if ($guruIdFilter) {
            $gurusQuery->where('id', $guruIdFilter);
        }
        $gurus = $gurusQuery->get();

        $allGurusList = Guru::orderBy('name')->get();
        $availableSessions = ['Subuh', 'Dzuhur', 'Asar', 'Isya'];

        // Initial containers
        $rekapData = [];
        $startDate = null;
        $endDate   = null;
        $daysOfWeek = [];
        $globalSummary = [
            'total_guru' => $gurus->count(),
            'hadir'      => 0,
            'izin'       => 0,
            'sakit'      => 0,
            'alfa'       => 0,
            'libur'      => 0,
            'total_sesi' => 0,
            'persentase' => 0,
        ];

        if ($jenisPeriode === 'sesi') {
            // PER SESI
            $sesiAktif = $sesiFilter ?: 'Subuh';
            $startDate = Carbon::parse($tglInput)->startOfDay();
            $endDate   = Carbon::parse($tglInput)->endOfDay();

            $attQuery = TeacherAttendance::with(['guru', 'classroom'])
                ->whereDate('date', $tglInput);

            if ($guruIdFilter) {
                $attQuery->where('guru_id', $guruIdFilter);
            }

            if (in_array(strtolower($sesiAktif), ['asar', 'ashar'])) {
                $attQuery->whereIn(\DB::raw('LOWER(session)'), ['asar', 'ashar']);
            } else {
                $attQuery->whereRaw('LOWER(session) = ?', [strtolower($sesiAktif)]);
            }

            $attendances = $attQuery->get();

            // Cek hari libur
            $liburRecord = \App\Models\HariLibur::whereDate('tanggal', $tglInput)->first();
            $isLiburSesi = $liburRecord && $attendanceService->isHolidayForSession($liburRecord->keterangan, $sesiAktif);

            foreach ($gurus as $guru) {
                $att = $attendances->firstWhere('guru_id', $guru->id);
                $status = $att ? $att->status : ($isLiburSesi ? 'Libur' : '-');
                $rekapData[] = [
                    'guru'            => $guru,
                    'tanggal'         => $tglInput,
                    'sesi'            => $sesiAktif,
                    'status'          => $status,
                    'jam_absen'       => $att && $att->attendance_time ? $att->attendance_time->format('H:i') : '-',
                    'kitab'           => $att ? $att->kitab : '-',
                    'materi'          => $att ? $att->materi : '-',
                    'classroom_name'  => $att && $att->classroom ? $att->classroom->name : '-',
                    'keterangan'      => $isLiburSesi ? ($liburRecord->keterangan ?: 'Libur Pesantren') : '-',
                ];

                if ($status === 'Hadir') $globalSummary['hadir']++;
                elseif ($status === 'Izin') $globalSummary['izin']++;
                elseif ($status === 'Sakit') $globalSummary['sakit']++;
                elseif ($status === 'Alfa') $globalSummary['alfa']++;
                elseif ($status === 'Libur') $globalSummary['libur']++;
            }
        } elseif ($jenisPeriode === 'harian') {
            // PER HARI
            $startDate = Carbon::parse($tglInput)->startOfDay();
            $endDate   = Carbon::parse($tglInput)->endOfDay();

            $attQuery = TeacherAttendance::with(['guru', 'classroom'])
                ->whereDate('date', $tglInput);
            if ($guruIdFilter) {
                $attQuery->where('guru_id', $guruIdFilter);
            }
            $allAtts = $attQuery->get();
            $liburRecord = \App\Models\HariLibur::whereDate('tanggal', $tglInput)->first();

            $activeSessions = $sesiFilter ? [$sesiFilter] : $availableSessions;

            foreach ($gurus as $guru) {
                $guruAtts = $allAtts->where('guru_id', $guru->id);
                $sessionMap = [];
                $keteranganArr = [];

                foreach ($activeSessions as $ses) {
                    $isLiburSesi = $liburRecord && $attendanceService->isHolidayForSession($liburRecord->keterangan, $ses);

                    $matchingAtt = $guruAtts->first(function ($item) use ($ses, $attendanceService) {
                        return strtolower($attendanceService->normalizeSession($item->session)) === strtolower($attendanceService->normalizeSession($ses));
                    });

                    if ($matchingAtt) {
                        $sessionMap[$ses] = [
                            'status'    => $matchingAtt->status,
                            'time'      => $matchingAtt->attendance_time ? $matchingAtt->attendance_time->format('H:i') : '-',
                            'kitab'     => $matchingAtt->kitab,
                            'classroom' => $matchingAtt->classroom ? $matchingAtt->classroom->name : '-',
                        ];
                        if ($matchingAtt->status === 'Hadir') $globalSummary['hadir']++;
                        elseif ($matchingAtt->status === 'Izin') $globalSummary['izin']++;
                        elseif ($matchingAtt->status === 'Sakit') $globalSummary['sakit']++;
                        elseif ($matchingAtt->status === 'Alfa') $globalSummary['alfa']++;
                    } elseif ($isLiburSesi) {
                        $sessionMap[$ses] = [
                            'status'    => 'Libur',
                            'time'      => '-',
                            'kitab'     => '-',
                            'classroom' => '-',
                        ];
                        $keteranganArr[] = "Sesi {$ses}: Libur (" . ($liburRecord->keterangan ?: 'Pesantren') . ")";
                        $globalSummary['libur']++;
                    } else {
                        $sessionMap[$ses] = [
                            'status'    => '-',
                            'time'      => '-',
                            'kitab'     => '-',
                            'classroom' => '-',
                        ];
                    }
                }

                $rekapData[] = [
                    'guru'       => $guru,
                    'tanggal'    => $tglInput,
                    'sessions'   => $sessionMap,
                    'keterangan' => implode(', ', array_unique($keteranganArr)) ?: '-',
                ];
            }
        } elseif ($jenisPeriode === 'mingguan') {
            // PER MINGGU (SABTU s.d. JUMAT)
            $dt = Carbon::parse($tglInput);
            if ($dt->dayOfWeek === Carbon::SATURDAY) {
                $startDate = $dt->copy()->startOfDay();
            } else {
                $startDate = $dt->copy()->previous(Carbon::SATURDAY)->startOfDay();
            }
            $endDate = $startDate->copy()->addDays(6)->endOfDay();

            // Setup array 7 hari
            $curr = $startDate->copy();
            $dayNames  = ['Sabtu', 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            $dayShorts = ['Sab', 'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum'];
            for ($i = 0; $i < 7; $i++) {
                $daysOfWeek[] = [
                    'date'      => $curr->toDateString(),
                    'name'      => $dayNames[$i],
                    'short'     => $dayShorts[$i],
                    'formatted' => $curr->format('d/m'),
                ];
                $curr->addDay();
            }

            $attQuery = TeacherAttendance::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            if ($guruIdFilter) {
                $attQuery->where('guru_id', $guruIdFilter);
            }
            if ($sesiFilter) {
                if (in_array(strtolower($sesiFilter), ['asar', 'ashar'])) {
                    $attQuery->whereIn(\DB::raw('LOWER(session)'), ['asar', 'ashar']);
                } else {
                    $attQuery->whereRaw('LOWER(session) = ?', [strtolower($sesiFilter)]);
                }
            }
            $allAtts = $attQuery->get();

            $hariLiburs = \App\Models\HariLibur::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])->get();

            foreach ($gurus as $guru) {
                $guruAtts = $allAtts->where('guru_id', $guru->id);
                $dailyMap = [];
                $totalHadirGuru = 0;

                foreach ($daysOfWeek as $dayInfo) {
                    $dStr = $dayInfo['date'];
                    $attOnDay = $guruAtts->filter(fn($a) => ($a->date ? $a->date->format('Y-m-d') : '') === $dStr);
                    $liburRecord = $hariLiburs->first(fn($hl) => ($hl->tanggal ? substr((string)$hl->tanggal, 0, 10) : '') === $dStr);

                    $isLiburDay = false;
                    if ($liburRecord) {
                        $checkSession = $sesiFilter ?: 'Subuh';
                        if ($attendanceService->isHolidayForSession($liburRecord->keterangan, $checkSession)) {
                            $isLiburDay = true;
                        }
                    }

                    if ($attOnDay->count() > 0) {
                        // Priority status if multiple sessions: Hadir > Izin > Sakit > Alfa
                        if ($attOnDay->contains('status', 'Hadir')) {
                            $stCode = 'H';
                            $totalHadirGuru++;
                            $globalSummary['hadir']++;
                        } elseif ($attOnDay->contains('status', 'Izin')) {
                            $stCode = 'I';
                            $globalSummary['izin']++;
                        } elseif ($attOnDay->contains('status', 'Sakit')) {
                            $stCode = 'S';
                            $globalSummary['sakit']++;
                        } else {
                            $stCode = 'A';
                            $globalSummary['alfa']++;
                        }
                    } elseif ($isLiburDay) {
                        $stCode = 'L';
                        $globalSummary['libur']++;
                    } else {
                        $stCode = '-';
                    }

                    $dailyMap[$dStr] = $stCode;
                }

                $rekapData[] = [
                    'guru'        => $guru,
                    'daily_map'   => $dailyMap,
                    'total_hadir' => $totalHadirGuru,
                ];
            }
        } elseif (in_array($jenisPeriode, ['bulanan', 'semester', 'tahun'])) {
            // PER BULAN, PER SEMESTER, PER TAHUN
            if ($jenisPeriode === 'bulanan') {
                $startDate = Carbon::createFromDate($tahunFilter, $bulanFilter, 1)->startOfMonth();
                $endDate   = $startDate->copy()->endOfMonth();
            } elseif ($jenisPeriode === 'semester') {
                if ($semesterFilter === 1) {
                    $startDate = Carbon::createFromDate($tahunFilter, 7, 1)->startOfMonth();
                    $endDate   = Carbon::createFromDate($tahunFilter, 12, 31)->endOfMonth();
                } else {
                    $startDate = Carbon::createFromDate($tahunFilter, 1, 1)->startOfMonth();
                    $endDate   = Carbon::createFromDate($tahunFilter, 6, 30)->endOfMonth();
                }
            } else {
                // tahun
                $startDate = Carbon::createFromDate($tahunFilter, 1, 1)->startOfMonth();
                $endDate   = Carbon::createFromDate($tahunFilter, 12, 31)->endOfMonth();
            }

            $attQuery = TeacherAttendance::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            if ($guruIdFilter) {
                $attQuery->where('guru_id', $guruIdFilter);
            }
            if ($sesiFilter) {
                if (in_array(strtolower($sesiFilter), ['asar', 'ashar'])) {
                    $attQuery->whereIn(\DB::raw('LOWER(session)'), ['asar', 'ashar']);
                } else {
                    $attQuery->whereRaw('LOWER(session) = ?', [strtolower($sesiFilter)]);
                }
            }
            $allAtts = $attQuery->get();

            foreach ($gurus as $guru) {
                $gAtts = $allAtts->where('guru_id', $guru->id);

                $h  = $gAtts->filter(fn($a) => strtolower($a->status) === 'hadir')->count();
                $i  = $gAtts->filter(fn($a) => strtolower($a->status) === 'izin')->count();
                $s  = $gAtts->filter(fn($a) => strtolower($a->status) === 'sakit')->count();
                $a  = $gAtts->filter(fn($a) => strtolower($a->status) === 'alfa')->count();
                $tot = $h + $i + $s + $a;
                $pct = $tot > 0 ? round(($h / $tot) * 100, 2) : 0;

                $rekapData[] = [
                    'guru'       => $guru,
                    'hadir'      => $h,
                    'izin'       => $i,
                    'sakit'      => $s,
                    'alfa'       => $a,
                    'total_sesi' => $tot,
                    'persentase' => $pct,
                ];

                $globalSummary['hadir'] += $h;
                $globalSummary['izin']  += $i;
                $globalSummary['sakit'] += $s;
                $globalSummary['alfa']  += $a;
            }
        }

        $totalGlobalValid = $globalSummary['hadir'] + $globalSummary['izin'] + $globalSummary['sakit'] + $globalSummary['alfa'];
        $globalSummary['total_sesi'] = $totalGlobalValid;
        $globalSummary['persentase'] = $totalGlobalValid > 0 ? round(($globalSummary['hadir'] / $totalGlobalValid) * 100, 2) : 0;

        return view('admin.teacher-attendance.rekap', [
            'jenisPeriode'    => $jenisPeriode,
            'tanggal'         => $tglInput,
            'sesiFilter'      => $sesiFilter,
            'guruIdFilter'    => $guruIdFilter,
            'bulanFilter'     => $bulanFilter,
            'tahunFilter'     => $tahunFilter,
            'semesterFilter'  => $semesterFilter,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'daysOfWeek'      => $daysOfWeek,
            'allGurus'        => $allGurusList,
            'availableSessions' => $availableSessions,
            'rekapData'       => $rekapData,
            'globalSummary'   => $globalSummary,
        ]);
    }
}

