@extends('layouts.app')

@section('title', 'Laporan Harian - Absensi Santri')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8 font-sans bg-[#f8f9fa] min-h-screen">
        
        <!-- Top Header Area -->
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-8">
            <div>
                <span class="inline-flex items-center rounded-full bg-[#0f4a30] px-3 py-1 text-[10px] font-bold text-white tracking-widest uppercase mb-3 shadow-sm">
                    HQ Management
                </span>
                <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Overview</h1>
                <p class="text-sm text-slate-500 mt-2 font-medium max-w-xl">
                    Here's what's happening at the pesantren today. Attendance is holding steady, but there are a few pending requests needing your attention.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0f4a30] px-4 py-2 text-xs font-bold text-white shadow-sm cursor-default">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    SYSTEM OPTIMAL
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-[#e8eff5] px-4 py-2 text-xs font-bold text-slate-600">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> Live Data
                </span>
            </div>
        </div>

        <!-- Top Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
            <!-- Global Trend Equivalent (Total Hadir) -->
            <div class="md:col-span-5 bg-[#0f4a30] rounded-[24px] p-8 text-white relative overflow-hidden shadow-lg border border-[#165c3e]">
                <div class="relative z-10 flex flex-col h-full justify-between">
                    <div class="flex justify-between items-start">
                        <h3 class="text-xs font-bold tracking-widest text-emerald-100/70 uppercase">Global Trend ({{ $session ?: 'Semua' }})</h3>
                        <div class="bg-white/10 p-2 rounded-full">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 mb-8">
                        <span class="text-6xl font-extrabold">{{ $stats['Hadir'] ?? 0 }}</span>
                    </div>
                    <div class="space-y-2">
                        <div class="h-1.5 w-full bg-black/20 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 w-[92%] rounded-full"></div>
                        </div>
                        <div class="flex justify-between text-xs font-medium text-emerald-100/80">
                            <span>Total Santri: {{ $totalSantri ?? 0 }}</span>
                            <span>Target: 95%</span>
                        </div>
                    </div>
                </div>
                <!-- Abstract Design Background -->
                <div class="absolute -right-10 -top-10 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl"></div>
            </div>

            <!-- Active Santri Equivalent (Sakit & Izin) -->
            <div class="md:col-span-4 bg-[#f4f7fb] rounded-[24px] p-8 flex flex-col justify-between shadow-sm border border-slate-200/60">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xs font-bold tracking-widest text-slate-500 uppercase">Sakit & Izin</h3>
                    <div class="text-slate-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                    </div>
                </div>
                <div class="flex items-end gap-6 mt-auto">
                    <div>
                        <div class="text-4xl font-extrabold text-[#0f4a30]">{{ $stats['Sakit'] ?? 0 }}</div>
                        <div class="text-sm font-semibold text-slate-500 mt-1 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span> Sakit
                        </div>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-[#0f4a30]">{{ $stats['Izin'] ?? 0 }}</div>
                        <div class="text-sm font-semibold text-slate-500 mt-1 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-yellow-400"></span> Izin
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Notes Equivalent (Alfa / Belum Absen) -->
            <div class="md:col-span-3 bg-[#ffebeb] rounded-[24px] p-8 flex flex-col justify-between shadow-sm border border-red-100">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xs font-bold tracking-widest text-red-800 uppercase">Pending Notes</h3>
                    <div class="text-red-600 bg-red-200/50 p-1.5 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
                <div>
                    <div class="text-5xl font-extrabold text-[#8b1a1a]">{{ $stats['Belum Absen'] ?? 0 }}</div>
                    <div class="text-sm font-semibold text-red-700/80 mt-1">Alfa / Belum Absen</div>
                </div>
                <button onclick="document.getElementById('searchDaily').focus()" class="mt-5 w-full bg-[#8b1a1a] hover:bg-[#6e1414] text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                    Review Now <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Panel: Quick Actions & Filters -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Quick Actions (Filter Data) -->
                <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-extrabold text-slate-800">Quick Actions</h2>
                        <span class="p-1.5 bg-slate-100 rounded-full text-slate-400 cursor-pointer">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                        </span>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('attendance.daily.report') }}" class="space-y-3 mb-4">
                        <div class="group flex items-center justify-between p-3 rounded-2xl border border-slate-100 bg-slate-50 focus-within:ring-2 focus-within:ring-[#0f4a30]/20 transition-all">
                            <input type="date" name="date" value="{{ $date }}" class="w-full bg-transparent text-sm font-semibold text-slate-700 focus:outline-none px-2">
                        </div>

                        <div class="relative group flex items-center justify-between p-1 rounded-2xl border border-slate-100 bg-slate-50 focus-within:ring-2 focus-within:ring-[#0f4a30]/20 transition-all">
                            <select name="classroom_id" class="w-full appearance-none bg-transparent py-2.5 px-4 text-sm font-semibold text-slate-700 focus:outline-none cursor-pointer">
                                <option value="">Filter Kelas (Semua)</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute right-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>

                        <div class="relative group flex items-center justify-between p-1 rounded-2xl border border-slate-100 bg-slate-50 focus-within:ring-2 focus-within:ring-[#0f4a30]/20 transition-all">
                            <select name="session" class="w-full appearance-none bg-transparent py-2.5 px-4 text-sm font-semibold text-slate-700 focus:outline-none cursor-pointer">
                                <option value="">Filter Sesi (Semua)</option>
                                @foreach($sessions as $sessionName)
                                    <option value="{{ $sessionName }}" {{ $session == $sessionName ? 'selected' : '' }}>{{ $sessionName }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute right-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-[#0f4a30] hover:bg-[#0b3824] text-white text-xs font-bold py-3 rounded-xl transition-colors">Terapkan</button>
                            <a href="{{ route('attendance.daily.report') }}" class="flex-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-center text-xs font-bold py-3 rounded-xl transition-colors">Reset</a>
                        </div>
                    </form>

                    <!-- Export Button as Action List -->
                    <a href="{{ route('attendance.daily.export', request()->query()) }}" class="group flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-[#0f4a30] text-white hover:bg-[#0b3824] transition-all cursor-pointer mt-4">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm">Generate Report</h4>
                                <p class="text-[11px] text-emerald-100/80">Weekly PDF / Excel export</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-emerald-100/50 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Right Panel: Live Check-ins (Table) -->
            <div class="lg:col-span-2 bg-white rounded-[24px] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                <!-- Header Card Table -->
                <div class="p-6 pb-4 flex flex-col sm:flex-row justify-between items-center gap-4 border-b border-slate-100/50">
                    <h2 class="text-lg font-extrabold text-slate-800">Live Check-ins</h2>
                    
                    <div class="flex items-center gap-2 bg-[#f4f7fb] p-1 rounded-full">
                        <span class="px-3 py-1 bg-[#0f4a30] text-white text-xs font-bold rounded-full">All</span>
                        <span class="px-3 py-1 text-slate-500 text-xs font-bold rounded-full cursor-pointer hover:bg-slate-200 transition">Late</span>
                        <span class="px-3 py-1 text-slate-500 text-xs font-bold rounded-full cursor-pointer hover:bg-slate-200 transition">Absent</span>
                    </div>
                </div>

                <!-- Hidden Search Input for Filter Function (kept functional from your JS) -->
                <div class="px-6 py-2 bg-slate-50/50 border-b border-slate-100/50">
                    <input type="text" id="searchDaily" placeholder="Pencarian cepat nama santri..." class="w-full bg-transparent text-sm text-slate-600 focus:outline-none py-1">
                </div>

                <!-- Table List -->
                <div class="overflow-x-auto flex-1 p-2">
                    @if($session)
                        {{-- Mode: Filter sesi aktif --}}
                        <table class="w-full text-left text-sm border-separate border-spacing-y-2" id="dailyTable">
                            <tbody class="">
                                @foreach($records as $index => $record)
                                    <tr class="searchable-row group bg-white hover:bg-[#f4f7fb] transition-colors rounded-2xl">
                                        <td class="px-4 py-3 rounded-l-2xl">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-10 rounded-full bg-[#e8eff5] flex items-center justify-center text-[#0f4a30] font-extrabold text-sm border border-[#d1e0ec]">
                                                    {{ strtoupper(substr($record->santri?->name ?? '?', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 text-[13px] santri-name">{{ $record->santri?->name ?? 'Tidak Ditemukan' }}</div>
                                                    <div class="text-[11px] font-semibold text-slate-400 mt-0.5">Class {{ $record->santri?->classroom?->name ?? '-' }} • ID: {{ $record->santri?->id ?? '0000' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right rounded-r-2xl">
                                            @php
                                                $status = $record->status ?? 'Belum Absen';
                                                $badgeInfo = match($status) {
                                                    'Hadir' => ['text-[#0f4a30]', 'PRESENT'],
                                                    'Sakit' => ['text-slate-500', 'SICK (PENDING)'],
                                                    'Izin' => ['text-slate-500', 'PERMIT'],
                                                    'Alfa', 'Belum Absen' => ['text-[#8b1a1a]', 'LATE/ABSENT'],
                                                    default => ['text-slate-500', strtoupper($status)],
                                                };
                                            @endphp
                                            <div class="flex flex-col items-end">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold tracking-widest uppercase {{ $badgeInfo[0] }}">
                                                    @if($status == 'Hadir')
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    @elseif(in_array($status, ['Alfa', 'Belum Absen']))
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @else
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                                    @endif
                                                    {{ $badgeInfo[1] }}
                                                </span>
                                                <span class="text-[11px] font-bold text-slate-800 mt-1">
                                                    {{ $record->scan_time ? $record->scan_time->format('h:i A') : '--:-- AM' }}
                                                </span>
                                                @if($record->notes)
                                                    <span class="text-[10px] text-slate-400 mt-0.5">{{ $record->notes }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        {{-- Mode: Semua sesi (Rekap Harian Penuh) --}}
                        <table class="w-full text-left text-sm border-separate border-spacing-y-2" id="dailyTable">
                            <thead class="bg-transparent text-slate-400 font-bold uppercase text-[10px] tracking-wider">
                                <tr>
                                    <th class="px-4 py-2">Santri</th>
                                    <th class="px-4 py-2 text-center">Subuh</th>
                                    <th class="px-4 py-2 text-center">Dzuhur</th>
                                    <th class="px-4 py-2 text-center">Asar</th>
                                    <th class="px-4 py-2 text-center">Isya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $index => $record)
                                    <tr class="searchable-row group bg-white hover:bg-[#f4f7fb] transition-colors rounded-2xl">
                                        <td class="px-4 py-3 rounded-l-2xl">
                                            <div class="flex items-center gap-3">
                                                <div class="h-9 w-9 rounded-full bg-[#e8eff5] flex items-center justify-center text-[#0f4a30] font-extrabold text-[11px] border border-[#d1e0ec]">
                                                    {{ strtoupper(substr($record['santri']->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 text-[12px] santri-name">{{ $record['santri']->name }}</div>
                                                    <div class="text-[10px] font-semibold text-slate-400 mt-0.5">ID: {{ $record['santri']->id ?? '0000' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach(['Subuh', 'Dzuhur', 'Asar', 'Isya'] as $sess)
                                            <td class="px-4 py-3 text-center">
                                                @php
                                                    $st = $record['rows'][$sess]['status'];
                                                    $badge = match($st) {
                                                        'Hadir' => 'text-[#0f4a30]',
                                                        'Sakit' => 'text-orange-500',
                                                        'Izin' => 'text-yellow-500',
                                                        'Alfa', 'Belum Absen' => 'text-[#8b1a1a]',
                                                        default => 'text-slate-300',
                                                    };
                                                    $icon = match($st) {
                                                        'Hadir' => '<svg class="w-4 h-4 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>',
                                                        'Alfa', 'Belum Absen' => '<svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                                                        default => '<span class="text-xs font-bold font-sans">'.substr($st, 0, 1).'</span>'
                                                    };
                                                @endphp
                                                <div class="{{ $badge }}" title="{{ $st }}">
                                                    {!! $icon !!}
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <script>
        // JS Filter Data Tetap Aman Di Sini
        document.getElementById('searchDaily').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('#dailyTable .searchable-row').forEach(function(row) {
                const name = row.querySelector('.santri-name').textContent.toLowerCase();
                row.style.display = name.includes(query) ? '' : 'none';
            });
        });
    </script>
@endsection