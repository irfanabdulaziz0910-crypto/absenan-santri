@extends('layouts.wali-kelas')
@section('title', 'Dashboard Wali Kelas')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard {{ $assignedKelas ? 'Kelas ' . $assignedKelas : 'Wali Kelas' }}</h1>
    <p class="text-slate-500 text-sm mt-1">Pantau kehadiran santri kelas Anda.</p>
</div>

@if(isset($pendingRequests) && $pendingRequests->count() > 0)
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-amber-800 font-bold">
                <svg class="w-5 h-5 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Permintaan Absensi Mengajar Menunggu Persetujuan ({{ $pendingRequests->count() }})</span>
            </div>
            <span class="text-xs text-amber-700">Wali Kelas: {{ $assignedKelas }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($pendingRequests as $req)
                <div class="bg-white border border-amber-200 rounded-xl p-4 flex flex-col justify-between gap-3 shadow-sm">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 text-sm">{{ $req->guru->name ?? 'Guru' }}</span>
                            <span class="text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full">{{ $req->session ?: 'Subuh' }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Kitab: <strong>{{ $req->kitab }}</strong> &bull; {{ $req->date ? $req->date->format('d/m/Y') : '' }}</p>
                        <p class="text-xs text-slate-600 italic mt-1 bg-slate-50 p-2 rounded-lg border border-slate-100">"{{ $req->materi }}"</p>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                        <form method="POST" action="{{ route('wali-kelas.teacher-attendance.reject', $req->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                TOLAK
                            </button>
                        </form>
                        <form method="POST" action="{{ route('wali-kelas.teacher-attendance.approve', $req->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                SETUJUI
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- 4 Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Santri --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">TOTAL SANTRI</p>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-extrabold text-slate-800" id="statTotalSantri">{{ $totalSantri }}</span>
                <span class="text-xs text-slate-400 font-medium">Santri</span>
            </div>
        </div>
        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>

    {{-- Hadir Hari Ini --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">HADIR HARI INI</p>
            <p class="text-3xl font-extrabold text-slate-800" id="statHadir">{{ $hadirCount }}</p>
            <p class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                <span>&rarr;</span> <span id="statHadirCompare">Hari ini</span>
            </p>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
    </div>

    {{-- Tidak Hadir --}}
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">TIDAK HADIR</p>
            <p class="text-3xl font-extrabold text-slate-800" id="statTidakHadir">{{ $tidakHadir }}</p>
            <p class="text-[11px] text-slate-500 font-medium mt-1" id="statTidakHadirBreakdown">{{ $izinCount }} Izin, {{ $sakitCount }} Sakit, {{ $alfaCount }} Alfa</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-red-50 text-red-400 border border-red-100 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
    </div>

    {{-- Persentase Kehadiran --}}
    <div class="bg-[#1a4731] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between relative overflow-hidden">
        <div class="flex justify-between items-start">
            <p class="text-[10px] font-bold text-emerald-300/80 uppercase tracking-wider">PERSENTASE<br>KEHADIRAN</p>
            <svg class="w-6 h-6 text-emerald-300/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </div>
        <div>
            <p class="text-3xl font-extrabold" id="statPct">{{ $persentaseHadir }}%</p>
            <div class="w-full bg-emerald-900/60 rounded-full h-2 mt-2 overflow-hidden">
                <div id="statPctBar" class="bg-emerald-400 h-full transition-all duration-500" style="width: {{ $persentaseHadir }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left Chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-slate-800 text-base">Grafik Kehadiran per Sesi Hari Ini</h3>
        </div>

        <div class="h-64 relative">
            <canvas id="kehadiranChart"></canvas>
        </div>
    </div>

    {{-- Right Ringkasan Hari Ini --}}
    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-slate-800 text-base mb-6">Ringkasan Hari Ini</h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#1a4731]"></span>
                        <span class="font-semibold text-slate-700">Hadir</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $hadirCount }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">{{ $persentaseHadir }}%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span class="font-semibold text-slate-700">Izin</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $izinCount }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">{{ $totalSantri > 0 ? round(($izinCount / $totalSantri) * 100) : 0 }}%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                        <span class="font-semibold text-slate-700">Sakit</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $sakitCount }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">{{ $totalSantri > 0 ? round(($sakitCount / $totalSantri) * 100) : 0 }}%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <span class="font-semibold text-slate-700">Alfa</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">{{ $alfaCount }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-bold">{{ $totalSantri > 0 ? round(($alfaCount / $totalSantri) * 100) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('wali-kelas.laporan') }}" class="w-full py-2.5 mt-6 bg-[#e8f4ee] text-[#1a4731] hover:bg-[#d5ebd9] font-bold text-xs rounded-xl transition text-center block">
            Lihat Detail &rarr;
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const sesiData = @json($sesiChartData ?? []);
    
    function renderChart() {
        const ctx = document.getElementById('kehadiranChart').getContext('2d');
        const labels = ['Subuh', 'Dzuhur', 'Ashar', 'Isya'];
        const dataHadir = [
            sesiData.subuh || 0,
            sesiData.dzuhur || 0,
            sesiData.ashar || 0,
            sesiData.isya || 0
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Hadir',
                    data: dataHadir,
                    backgroundColor: '#1a4731',
                    borderRadius: 8,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }

    renderChart();
</script>
@endpush
