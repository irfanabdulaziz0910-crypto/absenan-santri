<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\Santri;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalSantri  = Santri::count();
        $totalKelas   = Classroom::count();
        $totalGuru    = Guru::where('status', 'aktif')->count();

        // Absensi hari ini
        $today = now()->toDateString();
        $todayAtts = Attendance::where(fn ($q) => $q->whereDate('date', $today)->orWhereDate('created_at', $today))->get();

        $hadir = $todayAtts->filter(fn ($a) => strtolower($a->status) === 'hadir')->count();
        $izin  = $todayAtts->filter(fn ($a) => strtolower($a->status) === 'izin')->count();
        $sakit = $todayAtts->filter(fn ($a) => strtolower($a->status) === 'sakit')->count();
        $alfa  = $todayAtts->filter(fn ($a) => strtolower($a->status) === 'alfa')->count();

        $kehadiranPersen = $totalSantri > 0 ? round(($hadir / max($totalSantri, 1)) * 100) : 0;

        // Absensi per kelas & session (untuk chart) - PURE DATABASE DYNAMIC
        $kelasData = Classroom::withCount('santris')->orderBy('name')->get();

        $chartData = [];
        foreach ($kelasData as $k) {
            $kAtts = Attendance::whereHas('santri', fn ($q) => $q->where('classroom_id', $k->id))
                ->where(fn ($q) => $q->whereDate('date', $today)->orWhereDate('created_at', $today))
                ->get();

            $hSemua  = $kAtts->filter(fn ($a) => strtolower($a->status) === 'hadir')->count();
            $hSubuh  = $kAtts->filter(fn ($a) => strtolower($a->status) === 'hadir' && strtolower($a->session) === 'subuh')->count();
            $hDzuhur = $kAtts->filter(fn ($a) => strtolower($a->status) === 'hadir' && strtolower($a->session) === 'dzuhur')->count();
            $hAshar  = $kAtts->filter(fn ($a) => strtolower($a->status) === 'hadir' && strtolower($a->session) === 'ashar')->count();
            $hIsya   = $kAtts->filter(fn ($a) => strtolower($a->status) === 'hadir' && strtolower($a->session) === 'isya')->count();

            $chartData[] = [
                'kelas'  => $k->name,
                'total'  => $k->santris_count,
                'hadir'  => $hSemua,
                'subuh'  => $hSubuh,
                'dzuhur' => $hDzuhur,
                'ashar'  => $hAshar,
                'isya'   => $hIsya,
            ];
        }

        // Absensi terbaru
        $absensiTerbaru = Attendance::with('santri.classroom')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($a) {
                return [
                    'nama'   => $a->santri->name ?? 'Unknown',
                    'kelas'  => $a->santri->classroom->name ?? '-',
                    'waktu'  => $a->scan_time ? $a->scan_time->format('H:i') : ($a->created_at ? $a->created_at->format('H:i') : '-'),
                    'status' => ucfirst(strtolower($a->status ?? 'hadir')),
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
