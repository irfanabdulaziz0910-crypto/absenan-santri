@extends('layouts.app')

@section('title', 'Jurnal Bulanan')

@section('content')
<main class="mx-auto max-w-7xl px-4 py-8 space-y-6">
    <section class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-[#f8fbff] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Rekapitulasi Bulanan Semua Sesi</h1>
                <p class="text-sm text-slate-600 mt-1">Lihat distribusi kehadiran santri berdasarkan sesi dan status pada bulan tertentu.</p>
            </div>
            <a href="{{ route('attendance.monthly.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded border border-slate-300 bg-slate-200/80 px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-300 transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Excel (CSV)
            </a>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('attendance.monthly.journal') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Pilih Kelas</label>
                    <select name="classroom_id" class="mt-2 w-full appearance-none rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Pilih Bulan</label>
                    <select name="month" class="mt-2 w-full appearance-none rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                        @foreach($months as $monthOption)
                            <option value="{{ $monthOption }}" {{ $month == $monthOption ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $monthOption, 1)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Pilih Tahun</label>
                    <select name="year" class="mt-2 w-full appearance-none rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                        @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="rounded bg-blue-500 px-5 py-2 text-sm font-medium text-white hover:bg-blue-600 transition">Tampilkan Jurnal</button>
                    <a href="{{ route('attendance.monthly.journal') }}" class="rounded border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">Reset</a>
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
        <!-- Search Bar -->
        <div class="p-4 border-b border-slate-100">
            <div class="relative max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" id="searchMonthly" placeholder="Cari nama santri..." class="w-full rounded border border-slate-300 bg-white py-2 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm" id="monthlyTable">
                <thead class="bg-[#f8fafc] font-bold text-slate-700 uppercase text-xs border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">NO</th>
                        <th class="px-6 py-4">NAMA SANTRI</th>
                        <th class="px-6 py-4">KELAS</th>
                        <th class="px-6 py-4">SUBUH</th>
                        <th class="px-6 py-4">DZUHUR</th>
                        <th class="px-6 py-4">ASAR</th>
                        <th class="px-6 py-4">ISYA</th>
                        <th class="px-6 py-4">TOTAL KESELURUHAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @foreach($records as $index => $record)
                        <tr class="hover:bg-slate-50/50 searchable-row">
                            <td class="px-6 py-4 text-slate-700">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-slate-900 font-medium santri-name">{{ $record['santri']->name }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $record['santri']->classroom?->name ?? '-' }}</td>
                            @foreach(['Subuh', 'Dzuhur', 'Asar', 'Isya'] as $session)
                                <td class="px-6 py-4 text-slate-700">
                                    @php
                                        $sessionData = $record['rows'][$session];
                                    @endphp
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alfa' => 'A'] as $status => $label)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $status === 'Hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'Sakit' ? 'bg-amber-100 text-amber-700' : ($status === 'Izin' ? 'bg-yellow-100 text-yellow-700' : 'bg-rose-100 text-rose-700')) }}">{{ $label }}: {{ $sessionData[$status] }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            @endforeach
                            <td class="px-6 py-4 text-slate-700">
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['Hadir' => 'H', 'Sakit' => 'S', 'Izin' => 'I', 'Alfa' => 'A'] as $status => $label)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $status === 'Hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'Sakit' ? 'bg-amber-100 text-amber-700' : ($status === 'Izin' ? 'bg-yellow-100 text-yellow-700' : 'bg-rose-100 text-rose-700')) }}">{{ $label }}: {{ $record['totals'][$status] }}</span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    document.getElementById('searchMonthly').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#monthlyTable .searchable-row').forEach(function(row) {
            const name = row.querySelector('.santri-name').textContent.toLowerCase();
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection
