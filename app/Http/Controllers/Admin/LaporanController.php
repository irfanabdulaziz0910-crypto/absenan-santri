<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Santri;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $attendanceService = app(AttendanceService::class);
        $period      = $request->input('period', 'harian');
        $kelasFilter = $request->input('kelas', '');
        $search      = $request->input('search', '');
        $sesiFilter  = $request->input('sesi', '');
        $sesiQuery   = $attendanceService->normalizeSession($sesiFilter);
        $tglInput    = $request->input('tanggal', $request->input('tgl', now()->toDateString()));
        $tanggal     = $tglInput;

        // Date range based on period
        if ($period === 'harian') {
            $startDate = Carbon::parse($tglInput)->startOfDay();
            $endDate   = Carbon::parse($tglInput)->endOfDay();
            $totalHari = 1;
        } elseif ($period === 'bulanan') {
            $startDate = Carbon::parse($tglInput)->startOfMonth();
            $endDate   = Carbon::parse($tglInput)->endOfMonth();
            $totalHari = $startDate->daysInMonth;
        } elseif ($period === 'semester') {
            $startDate = now()->subMonths(6)->startOfMonth();
            $endDate   = now()->endOfMonth();
            $totalHari = 120;
        } else {
            // mingguan: SABTU s/d JUMAT
            $dt = Carbon::parse($tglInput);
            if ($dt->dayOfWeek === Carbon::SATURDAY) {
                $startDate = $dt->copy()->startOfDay();
            } else {
                $startDate = $dt->copy()->previous(Carbon::SATURDAY)->startOfDay();
            }
            $endDate   = $startDate->copy()->addDays(6)->endOfDay();
            $totalHari = 7;
        }

        $kelasList = Classroom::orderBy('name')->get();

        // Build santri query
        $query = Santri::with('classroom');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%");
            });
        }
        if ($kelasFilter) {
            $query->whereHas('classroom', fn ($q) => $q->where('name', $kelasFilter));
        }

        $santris = $query->orderBy('name')->get()->map(function ($s) {
            return [
                'id'           => $s->id,
                'name'         => $s->name,
                'nis'          => $s->nis ?? ('SAN' . str_pad($s->id, 3, '0', STR_PAD_LEFT)),
                'rfid_barcode' => $s->rfid_barcode,
                'classroom_id' => $s->classroom_id,
                'kelas'        => $s->classroom ? $s->classroom->name : 'Tanpa Kelas',
                'status'       => 'Aktif',
            ];
        });

        // Fetch ALL attendance records within the date range in one query
        $santriIds = $santris->pluck('id')->toArray();

        $attQuery = Attendance::whereIn('santri_id', $santriIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        if ($sesiQuery) {
            $attQuery->whereRaw('LOWER(session) = ?', [strtolower($sesiQuery)]);
        }
        $allAtts = $attQuery->get();

        // Build attendanceMap: [santri_id][date][session] => {status, notes, time}
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

        // Check HariLibur and Jadwal nonaktif
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

            // Hitung total sesi libur untuk santri ini dari attendanceMap
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

        return view('admin.laporan.index', compact(
            'period', 'kelasList', 'kelasFilter', 'search', 'sesiFilter',
            'santris', 'attendanceStats', 'globalStats', 'attendanceMap', 'tglInput', 'tanggal'
        ));
    }
}
