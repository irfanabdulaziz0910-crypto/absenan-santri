<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\Santri;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalSantri = Santri::count();

        $totalKelas = Classroom::count();

        $totalGuru = Guru::where('status', 'aktif')->count();


        /*
        |--------------------------------------------------------------------------
        | Absensi Hari Ini
        |--------------------------------------------------------------------------
        */

        $todayAtts = Attendance::where(function ($query) use ($today) {
            $query->whereDate('date', $today)
                  ->orWhereDate('created_at', $today);
        })
        ->get();


        $hadir = $todayAtts
            ->filter(fn ($a) => strtolower($a->status ?? '') === 'hadir')
            ->count();

        $izin = $todayAtts
            ->filter(fn ($a) => strtolower($a->status ?? '') === 'izin')
            ->count();

        $sakit = $todayAtts
            ->filter(fn ($a) => strtolower($a->status ?? '') === 'sakit')
            ->count();

        $alfa = $todayAtts
            ->filter(fn ($a) => strtolower($a->status ?? '') === 'alfa')
            ->count();


        $kehadiranPersen = $totalSantri > 0
            ? round(($hadir / $totalSantri) * 100)
            : 0;


        /*
        |--------------------------------------------------------------------------
        | Data Kelas
        |--------------------------------------------------------------------------
        */

        $kelasData = Classroom::withCount('santris')
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SEMUA ABSENSI KELAS SEKALI QUERY
        |--------------------------------------------------------------------------
        */

        $attendancePerKelas = Attendance::join(
            'santris',
            'attendances.santri_id',
            '=',
            'santris.id'
        )
        ->where(function ($query) use ($today) {
            $query->whereDate('attendances.date', $today)
                  ->orWhereDate('attendances.created_at', $today);
        })
        ->select(
            'santris.classroom_id',
            'attendances.status',
            'attendances.session'
        )
        ->get()
        ->groupBy('classroom_id');


        /*
        |--------------------------------------------------------------------------
        | Chart Data
        |--------------------------------------------------------------------------
        */

        $chartData = [];

        foreach ($kelasData as $k) {

            $kAtts = $attendancePerKelas->get($k->id, collect());

            $chartData[] = [
                'kelas' => $k->name,
                'total' => $k->santris_count,

                'hadir' => $kAtts
                    ->filter(fn ($a) => strtolower($a->status ?? '') === 'hadir')
                    ->count(),

                'subuh' => $kAtts
                    ->filter(fn ($a) =>
                        strtolower($a->status ?? '') === 'hadir'
                        && strtolower($a->session ?? '') === 'subuh'
                    )
                    ->count(),

                'dzuhur' => $kAtts
                    ->filter(fn ($a) =>
                        strtolower($a->status ?? '') === 'hadir'
                        && strtolower($a->session ?? '') === 'dzuhur'
                    )
                    ->count(),

                'ashar' => $kAtts
                    ->filter(fn ($a) =>
                        strtolower($a->status ?? '') === 'hadir'
                        && strtolower($a->session ?? '') === 'ashar'
                    )
                    ->count(),

                'isya' => $kAtts
                    ->filter(fn ($a) =>
                        strtolower($a->status ?? '') === 'hadir'
                        && strtolower($a->session ?? '') === 'isya'
                    )
                    ->count(),
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Absensi Terbaru
        |--------------------------------------------------------------------------
        */

        $absensiTerbaru = Attendance::with('santri.classroom')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($a) {

                return [
                    'nama' => $a->santri->name ?? 'Unknown',

                    'kelas' => $a->santri?->classroom?->name ?? '-',

                    'waktu' => $a->scan_time
                        ? $a->scan_time->format('H:i')
                        : (
                            $a->created_at
                                ? $a->created_at->format('H:i')
                                : '-'
                        ),

                    'status' => ucfirst(
                        strtolower($a->status ?? 'hadir')
                    ),
                ];
            });


        return view('admin.dashboard', compact(
            'totalSantri',
            'totalKelas',
            'totalGuru',
            'kehadiranPersen',
            'hadir',
            'izin',
            'sakit',
            'alfa',
            'chartData',
            'absensiTerbaru'
        ));
    }
}
