@extends('layouts.admin')
@section('title', 'Dashboard Absensi')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="max-w-full">

    {{-- PAGE HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Absensi</h1>
        <p class="text-slate-500 text-sm mt-1">Pantau kehadiran santri dan aktivitas absensi ngaji pesantren.</p>
    </div>

    {{-- ─── STAT CARDS ─── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Card 1: Total Santri --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Santri</p>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($totalSantri) }}</p>
            </div>
            <div class="w-11 h-11 bg-[#e6f4ec] rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-[#1a4731]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>

        {{-- Card 2: Total Kelas --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Kelas</p>
                <p class="text-3xl font-bold text-slate-800">{{ $totalKelas }}</p>
            </div>
            <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>

        {{-- Card 3: Total Guru --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Guru</p>
                <p class="text-3xl font-bold text-slate-800">{{ $totalGuru }}</p>
            </div>
            <div class="w-11 h-11 bg-[#e6f4ec] rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-[#1a4731]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
        </div>

        {{-- Card 4: Kehadiran Hari Ini --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kehadiran Hari Ini</p>
                <p class="text-3xl font-bold text-slate-800">{{ $kehadiranPersen ?? 0 }}%</p>
            </div>
            <div class="w-11 h-11 bg-[#e6f4ec] rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-[#1a4731]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ─── CHARTS ROW ─── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        {{-- Bar Chart: Absensi Per Kelas --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-bold text-slate-800">Total Absensi Per Kelas</h2>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <select id="chartFilter" onchange="filterChart(this.value)"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 text-slate-600 bg-white focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-400 cursor-pointer">
                        <option value="semua">Semua</option>
                        <option value="subuh">Subuh</option>
                        <option value="dzuhur">Dzuhur</option>
                        <option value="ashar">Ashar</option>
                        <option value="isya">Isya</option>
                    </select>
                </div>
            </div>
            <div class="h-52">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        {{-- Donut Chart: Ringkasan Hari Ini --}}
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
            <h2 class="text-base font-bold text-slate-800 mb-4">Ringkasan Hari Ini</h2>

            <div class="relative h-44 flex items-center justify-center">
                <canvas id="donutChart"></canvas>
                <div class="absolute text-center pointer-events-none">
                    <p class="text-2xl font-bold text-slate-800" id="donutCenter">{{ number_format($hadir ?? 0) }}</p>
                    <p class="text-xs text-slate-400 font-medium">Hadir</p>
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-4 space-y-2.5">
                @php
                    $legends = [
                        ['label' => 'Hadir', 'value' => $hadir ?? 0, 'color' => '#1a4731'],
                        ['label' => 'Izin',  'value' => $izin  ?? 0, 'color' => '#22c55e'],
                        ['label' => 'Sakit', 'value' => $sakit ?? 0, 'color' => '#ef4444'],
                        ['label' => 'Alfa',  'value' => $alfa  ?? 0, 'color' => '#94a3b8'],
                    ];
                @endphp
                @foreach ($legends as $l)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $l['color'] }}"></span>
                        <span class="text-slate-600 font-medium">{{ $l['label'] }}</span>
                    </div>
                    <span class="font-bold text-slate-800">{{ number_format($l['value']) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ─── ABSENSI TERBARU ─── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-800">Absensi Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">No</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Santri</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kelas</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Waktu</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($absensiTerbaru as $i => $a)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-3.5 text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e6f4ec] to-[#c5e8d1] flex items-center justify-center text-[#1a4731] text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($a['nama'], 0, 1)) }}
                                </div>
                                <span class="font-semibold text-slate-800">{{ $a['nama'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $a['kelas'] }}</td>
                        <td class="px-5 py-3.5 text-slate-600 font-medium">{{ $a['waktu'] }}</td>
                        <td class="px-5 py-3.5">
                            @php
                                $jadwal = $a['jadwal'] ?? '-';
                                $jadwalColors = ['Subuh'=>'blue','Dzuhur'=>'amber','Ashar'=>'orange','Isya'=>'purple'];
                                $c = $jadwalColors[$jadwal] ?? 'slate';
                            @endphp
                            <span class="px-2.5 py-1 bg-{{ $c }}-50 text-{{ $c }}-700 text-xs font-semibold rounded-lg">{{ $jadwal }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $statusMap = [
                                    'hadir' => ['bg-[#e6f4ec] text-[#1a4731]', 'Hadir'],
                                    'izin'  => ['bg-blue-50 text-blue-700',   'Izin'],
                                    'sakit' => ['bg-amber-50 text-amber-700', 'Sakit'],
                                    'alfa'  => ['bg-red-50 text-red-700',     'Alfa'],
                                ];
                                $st = $statusMap[$a['status']] ?? ['bg-slate-100 text-slate-600', ucfirst($a['status'])];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $st[0] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ str_contains($st[0], 'green') || str_contains($st[0], '1a4731') ? 'bg-[#1a4731]' : (str_contains($st[0], 'blue') ? 'bg-blue-500' : (str_contains($st[0], 'amber') ? 'bg-amber-500' : 'bg-red-500')) }}"></span>
                                {{ $st[1] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // ── BAR CHART DATA ──────────────────────────────────────────────
    const chartDataRaw = @json($chartData);

    const barDataSets = {
        semua:  chartDataRaw.map(d => d.hadir || 0),
        subuh:  chartDataRaw.map(d => d.subuh || 0),
        dzuhur: chartDataRaw.map(d => d.dzuhur || 0),
        ashar:  chartDataRaw.map(d => d.ashar || 0),
        isya:   chartDataRaw.map(d => d.isya || 0),
    };

    const barLabels = chartDataRaw.map(d => d.kelas);

    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: barLabels,
            datasets: [{
                data: barDataSets.semua,
                backgroundColor: 'rgba(199, 225, 209, 0.85)',
                borderColor: 'rgba(26, 71, 49, 0.3)',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(26, 71, 49, 0.75)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} Santri Hadir`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 12, weight: '600' }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    border: { display: false, dash: [4,4] },
                    ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 1 },
                    beginAtZero: true,
                }
            },
            animation: { duration: 600, easing: 'easeOutQuart' }
        }
    });

    function filterChart(val) {
        barChart.data.datasets[0].data = barDataSets[val] || barDataSets.semua;
        barChart.update();
    }

    // ── DONUT CHART ──────────────────────────────────────────────────
    const hadir = {{ $hadir }};
    const izin  = {{ $izin }};
    const sakit = {{ $sakit }};
    const alfa  = {{ $alfa }};

    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit', 'Alfa'],
            datasets: [{
                data: [hadir, izin, sakit, alfa],
                backgroundColor: ['#1a4731', '#22c55e', '#ef4444', '#94a3b8'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '78%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString('id-ID')}`
                    }
                }
            },
            animation: { animateRotate: true, duration: 900 }
        }
    });
</script>
@endpush
