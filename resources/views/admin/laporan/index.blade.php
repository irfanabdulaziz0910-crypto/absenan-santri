@extends('layouts.admin')
@section('title', 'Laporan Absensi')
@section('breadcrumb', 'Laporan')

@section('content')
<div class="report-mobile-shell">
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Laporan Absensi</h1>
        <p class="text-slate-500 text-sm mt-1">Rekap data kehadiran santri per tanggal, kelas, dan sesi waktu.</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-700 font-semibold shadow-sm hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Laporan
        </button>
        <button onclick="alert('Export PDF telah berhasil diunduh.')" class="flex items-center gap-2 px-4 py-2 bg-[#1a4731] text-white rounded-xl font-semibold shadow-sm shadow-green-900/20 hover:bg-[#153c28] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </button>
    </div>
</div>

<div class="print:hidden">
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        {{-- Tabs: Harian | Mingguan | Bulanan | Semester --}}
        <div class="report-period-tabs inline-flex bg-white rounded-xl border border-slate-200 p-1 shadow-sm shrink-0">
            <button onclick="setTab('harian')" id="tab-harian" class="px-5 py-2 text-sm font-semibold rounded-lg transition {{ ($period ?? 'harian') === 'harian' ? 'bg-[#1a4731] text-white' : 'text-slate-500 hover:text-slate-700' }}">Harian</button>
            <button onclick="setTab('mingguan')" id="tab-mingguan" class="px-5 py-2 text-sm font-semibold rounded-lg transition {{ ($period ?? '') === 'mingguan' ? 'bg-[#1a4731] text-white' : 'text-slate-500 hover:text-slate-700' }}">Mingguan</button>
            <button onclick="setTab('bulanan')" id="tab-bulanan" class="px-5 py-2 text-sm font-semibold rounded-lg transition {{ ($period ?? '') === 'bulanan' ? 'bg-[#1a4731] text-white' : 'text-slate-500 hover:text-slate-700' }}">Bulanan</button>
            <button onclick="setTab('semester')" id="tab-semester" class="px-5 py-2 text-sm font-semibold rounded-lg transition {{ ($period ?? '') === 'semester' ? 'bg-[#1a4731] text-white' : 'text-slate-500 hover:text-slate-700' }}">Semester</button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 items-center w-full justify-end">
            {{-- Tanggal Picker --}}
            <div class="relative border border-slate-200 bg-white rounded-xl shadow-sm px-3 py-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <input type="date" id="filterTanggal" onchange="renderLaporan()" class="text-sm font-semibold text-slate-700 border-none outline-none bg-transparent cursor-pointer">
            </div>

            {{-- Filter Kelas --}}
            <div class="relative border border-slate-200 bg-white rounded-xl shadow-sm px-3 py-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <select id="filterKelas" class="text-sm font-semibold text-slate-700 border-none outline-none bg-transparent pr-4 cursor-pointer" onchange="renderLaporan()">
                    <option value="Semua Kelas">Semua Kelas</option>
                </select>
            </div>

            {{-- Filter Sesi --}}
            <div class="relative border border-slate-200 bg-white rounded-xl shadow-sm px-3 py-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <select id="filterSesi" class="text-sm font-semibold text-slate-700 border-none outline-none bg-transparent pr-4 cursor-pointer" onchange="renderLaporan()">
                    <option value="Semua Sesi">Semua Sesi</option>
                    <option value="Subuh">Subuh</option>
                    <option value="Dzuhur">Dzuhur</option>
                    <option value="Ashar">Ashar</option>
                    <option value="Isya">Isya</option>
                </select>
            </div>

            {{-- Search Santri --}}
            <div class="relative border border-slate-200 bg-white rounded-xl shadow-sm pl-3 pr-2 py-2 flex items-center gap-2 w-48">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" id="filterSearch" oninput="renderLaporan()" placeholder="Cari Santri..." class="text-sm text-slate-700 border-none outline-none bg-transparent w-full">
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Total<br>Santri</p>
            <div class="w-6 h-6 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
        </div>
        <p class="text-3xl font-extrabold text-[#1a4731]" id="statTotalSantri">0</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight mt-1">Hadir</p>
            <div class="w-6 h-6 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
        </div>
        <p class="text-3xl font-extrabold text-emerald-600" id="statHadir">0</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight mt-1">Izin</p>
            <div class="w-6 h-6 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <p class="text-3xl font-extrabold text-blue-600" id="statIzin">0</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight mt-1">Sakit</p>
            <div class="w-6 h-6 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
        </div>
        <p class="text-3xl font-extrabold text-amber-500" id="statSakit">0</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight mt-1">Alfa</p>
            <div class="w-6 h-6 bg-red-50 text-red-500 rounded-full flex items-center justify-center shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg></div>
        </div>
        <p class="text-3xl font-extrabold text-red-600" id="statAlfa">0</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight mt-1">Libur</p>
            <div class="w-6 h-6 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center shrink-0"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
        </div>
        <p class="text-3xl font-extrabold text-slate-600" id="statLibur">0</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden">
        <div class="flex justify-between items-start mb-2 relative z-10">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-tight mt-1">Persentase</p>
            <div class="w-6 h-6 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center shrink-0"><span class="font-bold text-[10px]">%</span></div>
        </div>
        <p class="text-3xl font-extrabold text-slate-800 relative z-10" id="statPersentase">0%</p>
    </div>
</div>

<div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-bold text-slate-800" id="laporanTableTitle">Detail Laporan Harian Santri</h2>
    <span class="text-xs font-semibold text-slate-500" id="laporanDateNotice">Tanggal: {{ $tanggal ?? $tglInput ?? date('Y-m-d') }}</span>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
    <div class="md:hidden px-4 pt-4 space-y-3">
        <div class="overflow-x-auto -mx-1 px-1" style="scrollbar-width: thin;">
            <div class="flex min-w-max gap-2" id="mobileSessionTabs">
                <button type="button" data-session="Semua Sesi" onclick="setMobileSession('Semua Sesi')" class="mobile-session-tab min-h-11 px-4 rounded-xl text-xs font-bold border border-slate-200 bg-[#1a4731] text-white">Semua</button>
                <button type="button" data-session="Subuh" onclick="setMobileSession('Subuh')" class="mobile-session-tab min-h-11 px-4 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600">Subuh</button>
                <button type="button" data-session="Dzuhur" onclick="setMobileSession('Dzuhur')" class="mobile-session-tab min-h-11 px-4 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600">Dzuhur</button>
                <button type="button" data-session="Ashar" onclick="setMobileSession('Ashar')" class="mobile-session-tab min-h-11 px-4 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600">Asar</button>
                <button type="button" data-session="Maghrib" onclick="setMobileSession('Maghrib')" class="mobile-session-tab min-h-11 px-4 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600">Maghrib</button>
                <button type="button" data-session="Isya" onclick="setMobileSession('Isya')" class="mobile-session-tab min-h-11 px-4 rounded-xl text-xs font-bold border border-slate-200 bg-white text-slate-600">Isya</button>
            </div>
        </div>
        <div class="overflow-x-auto -mx-1 px-1" style="scrollbar-width: thin;">
            <div class="flex min-w-max gap-2" id="mobileStatusFilters">
                <button type="button" data-status="Semua" onclick="setMobileStatus('Semua')" class="mobile-status-filter min-h-10 px-3 rounded-lg text-xs font-bold border border-slate-200 bg-slate-800 text-white">Semua</button>
                <button type="button" data-status="Hadir" onclick="setMobileStatus('Hadir')" class="mobile-status-filter min-h-10 px-3 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-600">Hadir</button>
                <button type="button" data-status="Izin" onclick="setMobileStatus('Izin')" class="mobile-status-filter min-h-10 px-3 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-600">Izin</button>
                <button type="button" data-status="Sakit" onclick="setMobileStatus('Sakit')" class="mobile-status-filter min-h-10 px-3 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-600">Sakit</button>
                <button type="button" data-status="Alfa" onclick="setMobileStatus('Alfa')" class="mobile-status-filter min-h-10 px-3 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-600">Alpa</button>
                <button type="button" data-status="Libur" onclick="setMobileStatus('Libur')" class="mobile-status-filter min-h-10 px-3 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-600">Libur</button>
            </div>
        </div>
    </div>
    <div class="md:hidden px-4 pb-4 pt-3">
        <div id="mobileReportCards" class="space-y-3"></div>
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap" id="laporanMainTable">
            <!-- Table content dynamically rendered via JS -->
        </table>
    </div>
</div>

</div>

<style type="text/css" media="print">
    @page { size: landscape; margin: 1cm; }
    body { background: white !important; }
    aside, header { display: none !important; }
    .ml-\[220px\] { margin-left: 0 !important; }
    main { padding: 0 !important; }
</style>
<style>
    @media (max-width: 767px) {
        html,
        body {
            overflow-x: hidden;
        }

        #sidebar {
            display: none;
        }

        .ml-\[220px\] {
            margin-left: 0 !important;
        }

        main {
            padding: 1rem !important;
        }

        .report-mobile-shell {
            max-width: 100%;
            overflow-x: hidden;
        }

        .report-period-tabs {
            max-width: 100%;
            overflow-x: auto;
            scrollbar-width: thin;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    let santriData = @json($santris ?? []);
    const attendanceMap = @json($attendanceMap ?? []);
    let currentTab = '{{ $period ?? "harian" }}';
    let mobileSession = '{{ $sesiFilter ?? "Semua Sesi" }}' || 'Semua Sesi';
    let mobileStatus = 'Semua';

    function getTodayStr() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Set initial date picker to backend value or today
    document.getElementById('filterTanggal').value = '{{ $tanggal ?? $tglInput ?? date("Y-m-d") }}';

    // DYNAMIC CLASSROOM STORE & DISCOVERY
    function getDynamicClassrooms() {
        const dbClasses = @json($kelasList ?? []);
        if (Array.isArray(dbClasses) && dbClasses.length > 0) {
            return dbClasses.map(c => ({ id: c.id, name: c.name }));
        }
        return [];
    }

    // Populate kelas filter
    const klsSelect = document.getElementById('filterKelas');
    klsSelect.innerHTML = '<option value="Semua Kelas">Semua Kelas</option>';
    const dynamicClasses = getDynamicClassrooms();
    dynamicClasses.forEach(cls => {
        klsSelect.innerHTML += `<option value="${cls.name}">${cls.name}</option>`;
    });

    function setTab(tab) {
        currentTab = tab;
        const tgl = document.getElementById('filterTanggal').value || getTodayStr();
        const fKelas = document.getElementById('filterKelas').value;
        const fSesi = document.getElementById('filterSesi').value;

        const url = new URL(window.location.href);
        url.searchParams.set('period', tab);
        url.searchParams.set('tanggal', tgl);
        if (fKelas && fKelas !== 'Semua Kelas') url.searchParams.set('kelas', fKelas);
        else url.searchParams.delete('kelas');
        if (fSesi && fSesi !== 'Semua Sesi') url.searchParams.set('sesi', fSesi);
        else url.searchParams.delete('sesi');

        window.location.href = url.toString();
    }

    // Track the initial values from backend to detect changes
    const initialTanggal = '{{ $tanggal ?? $tglInput ?? date("Y-m-d") }}';
    const initialKelas = '{{ $kelasFilter ?? '' }}';
    const initialSesi = '{{ $sesiFilter ?? '' }}';

    // Set initial filter states from backend values
    if (initialKelas) {
        document.getElementById('filterKelas').value = initialKelas;
    }
    if (initialSesi) {
        document.getElementById('filterSesi').value = initialSesi;
    }

    function renderLaporan() {
        const tgl = document.getElementById('filterTanggal').value || getTodayStr();
        const fKelas = document.getElementById('filterKelas').value;
        const fSesi = document.getElementById('filterSesi').value;
        const query = (document.getElementById('filterSearch').value || '').toLowerCase();

        // If date/kelas/sesi changed from what backend rendered, reload from server
        const backendKelas = initialKelas || 'Semua Kelas';
        const backendSesi = initialSesi || 'Semua Sesi';
        if (tgl !== initialTanggal || fKelas !== backendKelas || fSesi !== backendSesi) {
            const url = new URL(window.location.href);
            url.searchParams.set('period', currentTab);
            url.searchParams.set('tanggal', tgl);
            if (fKelas && fKelas !== 'Semua Kelas') url.searchParams.set('kelas', fKelas);
            else url.searchParams.delete('kelas');
            if (fSesi && fSesi !== 'Semua Sesi') url.searchParams.set('sesi', fSesi);
            else url.searchParams.delete('sesi');
            window.location.href = url.toString();
            return;
        }

        // Client-side search filter only (date/kelas/sesi match backend data)
        let santriFiltered = santriData.filter(s => {
            let mK = fKelas === 'Semua Kelas' || s.kelas === fKelas;
            let mQ = !query || (s.name && s.name.toLowerCase().includes(query)) || (s.nis && String(s.nis).includes(query));
            return mK && mQ;
        });

        santriFiltered.sort((a,b) => a.name.localeCompare(b.name));

        const table = document.getElementById('laporanMainTable');
        table.innerHTML = '';

        const attendanceStats = @json($attendanceStats ?? []);
        const globalStats = @json($globalStats ?? []);

        let countTotalSantri = santriFiltered.length;
        let countHadir = 0; let countIzin = 0; let countSakit = 0; let countAlfa = 0; let countLibur = 0;

        if (document.getElementById('laporanDateNotice')) {
            document.getElementById('laporanDateNotice').textContent = `Periode: ${currentTab.toUpperCase()} (${tgl})`;
        }

        // Render Summary Table for Mingguan / Bulanan / Semester
        if (currentTab !== 'harian') {
            document.getElementById('laporanTableTitle').textContent = `Rekap Laporan ${currentTab.toUpperCase()} (${santriFiltered.length} Santri)`;

            let thead = `
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">NO</th>
                        <th class="px-5 py-4">NIS</th>
                        <th class="px-5 py-4">NAMA SANTRI</th>
                        <th class="px-5 py-4">KELAS</th>
                        <th class="px-5 py-4 text-center">HADIR</th>
                        <th class="px-5 py-4 text-center">IZIN</th>
                        <th class="px-5 py-4 text-center">SAKIT</th>
                        <th class="px-5 py-4 text-center">ALFA</th>
                        <th class="px-5 py-4 text-center">LIBUR</th>
                        <th class="px-5 py-4 text-center">PERSENTASE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
            `;

            let tbodyHtml = '';
            santriFiltered.forEach((s, idx) => {
                const st = attendanceStats[s.id] || { hadir: 0, izin: 0, sakit: 0, alfa: 0, libur: 0, persentase: 0 };
                tbodyHtml += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-center font-medium text-slate-500">${idx + 1}</td>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${s.nis}</td>
                        <td class="px-5 py-4 font-bold text-slate-800">${s.name}</td>
                        <td class="px-5 py-4 text-slate-600 font-medium">${s.kelas}</td>
                        <td class="px-5 py-4 text-center font-bold text-emerald-600">${st.hadir}</td>
                        <td class="px-5 py-4 text-center font-bold text-blue-600">${st.izin}</td>
                        <td class="px-5 py-4 text-center font-bold text-amber-600">${st.sakit}</td>
                        <td class="px-5 py-4 text-center font-bold text-red-600">${st.alfa}</td>
                        <td class="px-5 py-4 text-center font-bold text-slate-600">${st.libur}</td>
                        <td class="px-5 py-4 text-center font-extrabold text-slate-800">${st.persentase}%</td>
                    </tr>
                `;
            });

            table.innerHTML = thead + tbodyHtml + '</tbody>';

            // Populate Stats Cards
            document.getElementById('statTotalSantri').textContent = santriFiltered.length;
            document.getElementById('statHadir').textContent = globalStats.hadir || 0;
            document.getElementById('statIzin').textContent = globalStats.izin || 0;
            document.getElementById('statSakit').textContent = globalStats.sakit || 0;
            document.getElementById('statAlfa').textContent = globalStats.alfa || 0;
            if (document.getElementById('statLibur')) {
                document.getElementById('statLibur').textContent = globalStats.libur || 0;
            }
            document.getElementById('statPersentase').textContent = `${globalStats.persentase || 0}%`;
            renderMobileReportCards(santriFiltered, tgl);
            return;
        }

        if (fSesi === 'Semua Sesi') {
            document.getElementById('laporanTableTitle').textContent = `Detail Laporan Harian Santri (Semua Sesi)`;

            let thead = `
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">NO</th>
                        <th class="px-5 py-4">NIS</th>
                        <th class="px-5 py-4">NAMA SANTRI</th>
                        <th class="px-5 py-4">KELAS</th>
                        <th class="px-5 py-4 text-center">SUBUH</th>
                        <th class="px-5 py-4 text-center">DZUHUR</th>
                        <th class="px-5 py-4 text-center">ASHAR</th>
                        <th class="px-5 py-4 text-center">ISYA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
            `;

            let tbodyHtml = '';
            const sesis = ['Subuh', 'Dzuhur', 'Ashar', 'Isya'];

            santriFiltered.forEach((s, idx) => {
                let rowCols = '';

                sesis.forEach(sesiName => {
                    const sesKey = sesiName.toLowerCase();
                    const rec = attendanceMap[s.id]?.[tgl]?.[sesKey === 'ashar' ? 'asar' : sesKey];

                    let statusText = 'Belum Absen';
                    let badgeBg = 'bg-slate-100 text-slate-500';

                    if (rec) {
                        statusText = rec.status;
                        const st = (rec.status || '').toLowerCase();
                        if (st === 'libur') { badgeBg = 'bg-slate-200 text-slate-700 border border-slate-300 font-bold'; countLibur++; }
                        else if (st === 'hadir') { badgeBg = 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold'; countHadir++; }
                        else if (st === 'izin') { badgeBg = 'bg-blue-50 text-blue-700 border border-blue-200 font-bold'; countIzin++; }
                        else if (st === 'sakit') { badgeBg = 'bg-amber-50 text-amber-700 border border-amber-200 font-bold'; countSakit++; }
                        else if (st === 'alfa') { badgeBg = 'bg-red-50 text-red-700 border border-red-200 font-bold'; countAlfa++; }
                    }

                    rowCols += `
                        <td class="px-5 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-md text-xs ${badgeBg}">${statusText}</span>
                        </td>
                    `;
                });

                tbodyHtml += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-center font-medium text-slate-500">${idx + 1}</td>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${s.nis}</td>
                        <td class="px-5 py-4 font-bold text-slate-800">${s.name}</td>
                        <td class="px-5 py-4 text-slate-600 font-medium">${s.kelas}</td>
                        ${rowCols}
                    </tr>
                `;
            });

            table.innerHTML = thead + tbodyHtml + '</tbody>';

        } else {
            const sesKey = fSesi.toLowerCase();
            document.getElementById('laporanTableTitle').textContent = `Detail Laporan Sesi ${fSesi}`;

            let thead = `
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">NO</th>
                        <th class="px-5 py-4">NIS</th>
                        <th class="px-5 py-4">NAMA SANTRI</th>
                        <th class="px-5 py-4">KELAS</th>
                        <th class="px-5 py-4">JAM ABSEN</th>
                        <th class="px-5 py-4 text-center">STATUS</th>
                        <th class="px-5 py-4">METODE</th>
                        <th class="px-5 py-4">KETERANGAN / ALASAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
            `;

            let tbodyHtml = '';

            santriFiltered.forEach((s, idx) => {
                const rec = attendanceMap[s.id]?.[tgl]?.[sesKey === 'ashar' ? 'asar' : sesKey];

                let statusText = 'Belum Absen';
                let badgeBg = 'bg-slate-100 text-slate-500';
                let jamAbsen = '-';
                let metode = '-';
                let keterangan = '-';

                if (rec) {
                    statusText = rec.status;
                    jamAbsen   = rec.time || '-';
                    metode     = rec.status === 'Libur' ? 'Jadwal' : 'Sistem/Manual';
                    keterangan = rec.notes || 'Tercatat di sistem';

                    const st = (rec.status || '').toLowerCase();
                    if (st === 'libur') { badgeBg = 'bg-slate-200 text-slate-700 border border-slate-300 font-bold'; countLibur++; }
                    else if (st === 'hadir') { badgeBg = 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold'; countHadir++; }
                    else if (st === 'izin') { badgeBg = 'bg-blue-50 text-blue-700 border border-blue-200 font-bold'; countIzin++; }
                    else if (st === 'sakit') { badgeBg = 'bg-amber-50 text-amber-700 border border-amber-200 font-bold'; countSakit++; }
                    else if (st === 'alfa') { badgeBg = 'bg-red-50 text-red-700 border border-red-200 font-bold'; countAlfa++; }
                }

                tbodyHtml += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-center font-medium text-slate-500">${idx + 1}</td>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${s.nis}</td>
                        <td class="px-5 py-4 font-bold text-slate-800">${s.name}</td>
                        <td class="px-5 py-4 text-slate-600 font-medium">${s.kelas}</td>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${jamAbsen}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-md text-xs ${badgeBg}">${statusText}</span>
                        </td>
                        <td class="px-5 py-4 text-xs font-semibold text-slate-500">${metode}</td>
                        <td class="px-5 py-4 text-xs text-slate-600">${keterangan}</td>
                    </tr>
                `;
            });

            table.innerHTML = thead + tbodyHtml + '</tbody>';
        }

        // Render Stats Cards
        document.getElementById('statTotalSantri').textContent = countTotalSantri;
        document.getElementById('statHadir').textContent = countHadir;
        document.getElementById('statIzin').textContent = countIzin;
        document.getElementById('statSakit').textContent = countSakit;
        document.getElementById('statAlfa').textContent = countAlfa;
        if (document.getElementById('statLibur')) {
            document.getElementById('statLibur').textContent = countLibur;
        }

        const totalScored = countHadir + countIzin + countSakit + countAlfa;
        const pct = totalScored > 0 ? Math.round((countHadir / totalScored) * 100) : 0;
        document.getElementById('statPersentase').textContent = `${pct}%`;
        renderMobileReportCards(santriFiltered, tgl);
    }

    function setMobileSession(session) {
        mobileSession = session;
        document.querySelectorAll('.mobile-session-tab').forEach(tab => {
            const active = tab.dataset.session === session;
            tab.classList.toggle('bg-[#1a4731]', active);
            tab.classList.toggle('text-white', active);
            tab.classList.toggle('bg-white', !active);
            tab.classList.toggle('text-slate-600', !active);
        });
        renderMobileReportCards();
    }

    function setMobileStatus(status) {
        mobileStatus = status;
        document.querySelectorAll('.mobile-status-filter').forEach(filter => {
            const active = filter.dataset.status === status;
            filter.classList.toggle('bg-slate-800', active);
            filter.classList.toggle('text-white', active);
            filter.classList.toggle('bg-white', !active);
            filter.classList.toggle('text-slate-600', !active);
        });
        renderMobileReportCards();
    }

    function renderMobileReportCards(data = santriData, date = document.getElementById('filterTanggal').value) {
        const container = document.getElementById('mobileReportCards');
        if (!container) return;

        const sessions = mobileSession === 'Semua Sesi'
            ? ['Subuh', 'Dzuhur', 'Asar', 'Maghrib', 'Isya']
            : [mobileSession];
        const sessionKey = session => session.toLowerCase() === 'ashar' ? 'asar' : session.toLowerCase();
        const normalizedStatus = status => status === 'Alpa' ? 'Alfa' : status;
        const badgeClass = status => ({
            Hadir: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            Izin: 'bg-blue-50 text-blue-700 border-blue-200',
            Sakit: 'bg-amber-50 text-amber-700 border-amber-200',
            Alfa: 'bg-red-50 text-red-700 border-red-200',
            Libur: 'bg-slate-100 text-slate-700 border-slate-300',
            'Belum Absen': 'bg-slate-50 text-slate-500 border-slate-200'
        }[status] || 'bg-slate-50 text-slate-500 border-slate-200');

        const cards = [];
        data.forEach(santri => {
            sessions.forEach(session => {
                const record = attendanceMap[santri.id]?.[date]?.[sessionKey(session)];
                const status = record?.status || 'Belum Absen';
                if (mobileStatus !== 'Semua' && normalizedStatus(mobileStatus) !== status) return;

                cards.push(`<article class="border border-slate-200 rounded-xl p-4 bg-white shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-800 text-sm break-words">${santri.name}</h3>
                            <p class="text-[11px] text-slate-500 mt-1 break-all">RFID: ${santri.rfid_barcode || '-'}</p>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 rounded-lg border text-[11px] font-bold ${badgeClass(status)}">${status === 'Alfa' ? 'Alpa' : status}</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-3 text-xs">
                        <span class="font-semibold text-slate-600">${session}</span>
                        <span class="text-slate-500 font-mono">${record?.time || '-'}</span>
                    </div>
                    ${record?.notes ? `<p class="mt-2 text-xs text-slate-500 break-words">${record.notes}</p>` : ''}
                </article>`);
            });
        });

        container.innerHTML = cards.length
            ? cards.join('')
            : '<div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-xs font-semibold text-slate-500">Tidak ada data sesuai filter.</div>';
    }

    renderLaporan();
    setMobileSession(mobileSession);
</script>
@endpush
