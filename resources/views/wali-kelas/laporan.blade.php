@extends('layouts.wali-kelas')
@section('title', 'Laporan Wali Kelas')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Laporan Absensi {{ $assignedKelas ? 'Kelas ' . $assignedKelas : 'Wali Kelas' }}</h1>
        <p class="text-slate-500 text-sm mt-1">Rekapitulasi kehadiran seluruh santri untuk memantau kedisiplinan dan keaktifan kegiatan mengaji.</p>
    </div>
    <div class="flex flex-wrap gap-2.5">
        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-700 font-semibold shadow-sm hover:bg-slate-50 transition text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
        <button onclick="showToast('Ekspor PDF berhasil diunduh.', 'success')" class="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-xl bg-white text-slate-700 font-semibold shadow-sm hover:bg-slate-50 transition text-xs">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF
        </button>
        <button onclick="showToast('Ekspor Excel berhasil diunduh.', 'success')" class="flex items-center gap-2 px-4 py-2 bg-[#1a4731] text-white rounded-xl font-bold shadow-md shadow-green-900/20 hover:bg-[#153c28] transition text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Excel
        </button>
    </div>
</div>

{{-- Filter Card --}}
<div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm mb-6 print:hidden">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 flex flex-col justify-center">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">PERIODE</span>
                <select id="fPeriode" onchange="applyFilters()" class="text-xs font-bold text-slate-800 bg-transparent outline-none cursor-pointer">
                    <option value="Harian" {{ ($period ?? 'harian') == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="Mingguan" {{ ($period ?? 'harian') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="Bulanan" {{ ($period ?? 'harian') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="Semester" {{ ($period ?? 'harian') == 'semester' ? 'selected' : '' }}>Semester</option>
                </select>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 flex flex-col justify-center">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL</span>
                <input type="date" id="fTanggal" onchange="applyFilters()" class="text-xs font-bold text-slate-800 bg-transparent outline-none cursor-pointer">
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 flex flex-col justify-center">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">SESI NGAJI</span>
                <select id="fSesi" onchange="applyFilters()" class="text-xs font-bold text-slate-800 bg-transparent outline-none cursor-pointer">
                    <option value="Semua Sesi">Semua Sesi</option>
                    <option value="Subuh">Subuh</option>
                    <option value="Dzuhur">Dzuhur</option>
                    <option value="Ashar">Ashar</option>
                    <option value="Isya">Isya</option>
                </select>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 flex flex-col justify-center min-w-[180px]">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">CARI SANTRI</span>
                <input type="text" id="fSearch" oninput="applyFilters()" placeholder="Cari nama santri..." class="text-xs font-semibold text-slate-800 bg-transparent outline-none">
            </div>
        </div>

        <button onclick="applyFilters()" class="px-5 py-2.5 bg-[#4ade80] text-[#052e16] hover:bg-[#38c172] rounded-xl font-bold text-xs transition shadow-sm">
            Terapkan
        </button>
    </div>
</div>

{{-- 5 Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full">Aktif</span>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">TOTAL HADIR</p>
            <p class="text-2xl font-extrabold text-slate-800" id="stHadir">0</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">IZIN</p>
            <p class="text-2xl font-extrabold text-slate-800" id="stIzin">0</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">SAKIT</p>
            <p class="text-2xl font-extrabold text-slate-800" id="stSakit">0</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-start mb-2">
            <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[10px] font-bold rounded-full">Alfa</span>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">ALFA</p>
            <p class="text-2xl font-extrabold text-slate-800" id="stAlfa">0</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">PERSENTASE</p>
            <p class="text-2xl font-extrabold text-slate-800" id="stAvg">0%</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden mt-1.5">
                <div id="stAvgBar" class="bg-[#1a4731] h-full rounded-full" style="width:0%"></div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left Table Area --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-800 text-base" id="lblTableTitle">Laporan Harian</h3>
                <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full font-bold text-xs" id="lblSantriCount">0 Santri</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap" id="tblLaporan">
                    <!-- Dynamic table content -->
                </table>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
                <p id="pagInfoText">Menampilkan seluruh data santri (Satu Halaman)</p>
            </div>
        </div>
    </div>

    {{-- Right Charts Area --}}
    <div class="space-y-6">
        {{-- Tren Mingguan --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-4">Tren Kehadiran</h3>
            <div class="h-44 relative">
                <canvas id="chartTrenMingguan"></canvas>
            </div>
        </div>

        {{-- Distribusi Sesi --}}
        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-4">Distribusi Sesi</h3>
            <div class="space-y-3" id="distribusiSesiBox">
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-slate-700">Subuh</span>
                        <span class="text-slate-800 font-bold" id="distSubuh">100%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="distSubuhBar" class="bg-[#1a4731] h-full" style="width: 100%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-slate-700">Dzuhur</span>
                        <span class="text-slate-800 font-bold" id="distDzuhur">100%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="distDzuhurBar" class="bg-emerald-400 h-full" style="width: 100%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-slate-700">Ashar</span>
                        <span class="text-slate-800 font-bold" id="distAshar">100%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="distAsharBar" class="bg-amber-400 h-full" style="width: 100%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-slate-700">Isya</span>
                        <span class="text-slate-800 font-bold" id="distIsya">100%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="distIsyaBar" class="bg-[#1a4731] h-full" style="width: 100%"></div>
                    </div>
                </div>
            </div>
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
@endsection

@push('scripts')
<script>
    const targetKelas = @json($assignedKelas ?? '');
    let santriKelas = @json($santris ?? []);
    const attendanceMap = @json($attendanceMap ?? []);

    function getTodayStr() {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    }

    document.getElementById('fTanggal').value = '{{ $tanggal ?? $tglInput ?? date("Y-m-d") }}' || getTodayStr();

    @if(isset($sesiFilter) && $sesiFilter)
        document.getElementById('fSesi').value = '{{ $sesiFilter }}';
    @endif

    const santriData = @json($santris ?? []);
    let trenChart = null;

    function applyFilters() {
        const periode = document.getElementById('fPeriode').value.toLowerCase();
        const tgl     = document.getElementById('fTanggal').value || getTodayStr();
        const sesi    = document.getElementById('fSesi').value;
        const search  = (document.getElementById('fSearch').value || '').toLowerCase();

        const currentPeriod = @json($period ?? 'harian');
        const currentTgl    = @json($tanggal ?? '');

        if (periode !== currentPeriod || (tgl && tgl !== currentTgl)) {
            const url = new URL(window.location.href);
            url.searchParams.set('period', periode);
            url.searchParams.set('tanggal', tgl);
            if (sesi && sesi !== 'Semua Sesi') url.searchParams.set('sesi', sesi);
            else url.searchParams.delete('sesi');
            window.location.href = url.toString();
            return;
        }

        const filteredSantri = santriData.filter(s => {
            return !search || (s.name && s.name.toLowerCase().includes(search)) || (s.nis && String(s.nis).includes(search));
        });

        filteredSantri.sort((a,b) => a.name.localeCompare(b.name));

        const attendanceStats = @json($attendanceStats ?? []);
        const globalStats     = @json($globalStats ?? []);

        if (currentPeriod !== 'harian') {
            document.getElementById('stHadir').textContent = globalStats.hadir || 0;
            document.getElementById('stIzin').textContent = globalStats.izin || 0;
            document.getElementById('stSakit').textContent = globalStats.sakit || 0;
            document.getElementById('stAlfa').textContent = globalStats.alfa || 0;
            const avgPct = globalStats.persentase || 0;
            document.getElementById('stAvg').textContent = `${avgPct}%`;
            if (document.getElementById('stAvgBar')) {
                document.getElementById('stAvgBar').style.width = `${avgPct}%`;
            }
        }

        renderTable(filteredSantri, tgl, sesi);
    }

    function renderTable(santriList, tgl, sesi) {
        const tbl = document.getElementById('tblLaporan');
        tbl.innerHTML = '';

        document.getElementById('pagInfoText').textContent = `Menampilkan seluruh ${santriList.length} santri dalam 1 halaman`;

        const currentPeriod   = @json($period ?? 'harian');
        const attendanceStats = @json($attendanceStats ?? []);
        const attendanceMap = @json($attendanceMap ?? []);

        if (currentPeriod !== 'harian') {
            let thead = `
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">NO</th>
                        <th class="px-4 py-3.5">NAMA SANTRI</th>
                        <th class="px-4 py-3.5 text-center">HADIR</th>
                        <th class="px-4 py-3.5 text-center">IZIN</th>
                        <th class="px-4 py-3.5 text-center">SAKIT</th>
                        <th class="px-4 py-3.5 text-center">ALFA</th>
                        <th class="px-4 py-3.5 text-center">LIBUR</th>
                        <th class="px-4 py-3.5 text-center">PERSENTASE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
            `;

            let tbodyHtml = '';
            santriList.forEach((s, idx) => {
                const st = attendanceStats[s.id] || { hadir: 0, izin: 0, sakit: 0, alfa: 0, libur: 0, persentase: 0 };
                tbodyHtml += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3.5 text-center font-medium text-slate-500">${idx + 1}</td>
                        <td class="px-4 py-3.5 font-bold text-slate-800">${s.name}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-emerald-600">${st.hadir}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-blue-600">${st.izin}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-amber-600">${st.sakit}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-red-600">${st.alfa}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-600">${st.libur}</td>
                        <td class="px-4 py-3.5 text-center font-extrabold text-slate-800">${st.persentase}%</td>
                    </tr>
                `;
            });

            tbl.innerHTML = thead + tbodyHtml + '</tbody>';
            return;
        }

        if(sesi === 'Semua Sesi') {
            let thead = `
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">NO</th>
                        <th class="px-4 py-3.5">NAMA SANTRI</th>
                        <th class="px-4 py-3.5 text-center">SUBUH</th>
                        <th class="px-4 py-3.5 text-center">DZUHUR</th>
                        <th class="px-4 py-3.5 text-center">ASHAR</th>
                        <th class="px-4 py-3.5 text-center">ISYA</th>
                        <th class="px-4 py-3.5 text-center">PERSENTASE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
            `;

            let tbodyHtml = '';

            santriList.forEach((s, idx) => {
                const num = idx + 1;
                const sessions = ['subuh', 'dzuhur', 'ashar', 'isya'];
                let hTotal = 0;
                let colsHtml = '';

                sessions.forEach(ses => {
                    const rec = attendanceMap[s.id]?.[tgl]?.[ses === 'ashar' ? 'asar' : ses];
                    if (rec) {
                        const st = (rec.status || '').toLowerCase();
                        if (st === 'libur') {
                            colsHtml += `<td class="px-4 py-3 text-center"><span class="bg-slate-200 text-slate-700 font-bold px-2 py-0.5 rounded text-xs border border-slate-300">Libur</span></td>`;
                        } else if (st === 'hadir') {
                            hTotal++;
                            colsHtml += `<td class="px-4 py-3 text-center"><span class="bg-emerald-100 text-emerald-700 font-bold w-6 h-6 rounded-full inline-flex items-center justify-center text-xs" title="Hadir (${rec.time})">✓</span></td>`;
                        } else if (st === 'izin') {
                            colsHtml += `<td class="px-4 py-3 text-center"><span class="bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded text-xs">Izin</span></td>`;
                        } else if (st === 'sakit') {
                            colsHtml += `<td class="px-4 py-3 text-center"><span class="bg-amber-100 text-amber-700 font-bold px-2 py-0.5 rounded text-xs">Sakit</span></td>`;
                        } else if (st === 'alfa') {
                            colsHtml += `<td class="px-4 py-3 text-center"><span class="bg-red-100 text-red-700 font-bold px-2 py-0.5 rounded text-xs">Alfa</span></td>`;
                        } else {
                            colsHtml += `<td class="px-4 py-3 text-center"><span class="text-slate-300 text-xs font-bold">-</span></td>`;
                        }
                    } else {
                        colsHtml += `<td class="px-4 py-3 text-center"><span class="text-slate-300 text-xs font-bold">-</span></td>`;
                    }
                });

                const pct = Math.round((hTotal / 4) * 100);
                const colorClass = pct > 0 ? 'text-emerald-600' : 'text-slate-400';

                tbodyHtml += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3.5 text-center font-medium text-slate-500">${num}</td>
                        <td class="px-4 py-3.5 font-bold text-slate-800">${s.name}</td>
                        ${colsHtml}
                        <td class="px-4 py-3.5 text-center font-extrabold ${colorClass}">${pct}%</td>
                    </tr>
                `;
            });

            tbl.innerHTML = thead + tbodyHtml + '</tbody>';

        } else {
            const sesKey = sesi.toLowerCase();
            let thead = `
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">NO</th>
                        <th class="px-4 py-3.5">NAMA SANTRI</th>
                        <th class="px-4 py-3.5 text-center">STATUS</th>
                        <th class="px-4 py-3.5">WAKTU</th>
                        <th class="px-4 py-3.5">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
            `;

            let tbodyHtml = '';

            santriList.forEach((s, idx) => {
                const num = idx + 1;
                const rec = attendanceMap[s.id]?.[tgl]?.[sesKey === 'ashar' ? 'asar' : sesKey];

                let statusBadge = '<span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-md font-bold text-xs">Belum Absen</span>';
                let waktuText = '-';
                let ketText   = 'Belum Terekam';

                if (rec) {
                    const st = (rec.status || '').toLowerCase();
                    waktuText = rec.time || '-';
                    ketText   = rec.notes || 'Tercatat di sistem';
                    if (st === 'libur') statusBadge = '<span class="px-2.5 py-1 bg-slate-200 text-slate-700 border border-slate-300 rounded-md font-bold text-xs">Libur</span>';
                    else if (st === 'hadir') statusBadge = '<span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-md font-bold text-xs">Hadir</span>';
                    else if (st === 'izin') statusBadge = '<span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-md font-bold text-xs">Izin</span>';
                    else if (st === 'sakit') statusBadge = '<span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-md font-bold text-xs">Sakit</span>';
                    else if (st === 'alfa') statusBadge = '<span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-md font-bold text-xs">Alfa</span>';
                }

                tbodyHtml += `
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3.5 text-center font-medium text-slate-500">${num}</td>
                        <td class="px-4 py-3.5 font-bold text-slate-800">${s.name}</td>
                        <td class="px-4 py-3.5 text-center">${statusBadge}</td>
                        <td class="px-4 py-3.5 text-xs font-semibold text-slate-500">${waktuText}</td>
                        <td class="px-4 py-3.5 text-xs text-slate-600">${ketText}</td>
                    </tr>
                `;
            });

            tbl.innerHTML = thead + tbodyHtml + '</tbody>';
        }
    }

    function renderRightCharts(santriList, tgl) {
        const ctx = document.getElementById('chartTrenMingguan').getContext('2d');
        if(trenChart) trenChart.destroy();

        // Calculate count of Hadir for each session on date tgl
        const sessions = ['subuh', 'dzuhur', 'ashar', 'isya'];
        const dataHadir = sessions.map(ses => {
            let cnt = 0;
            santriList.forEach(s => {
                if (attendanceMap[s.id]?.[tgl]?.[ses === 'ashar' ? 'asar' : ses]?.status?.toLowerCase() === 'hadir') cnt++;
            });
            return cnt;
        });

        trenChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Subuh', 'Dzuhur', 'Ashar', 'Isya'],
                datasets: [{
                    data: dataHadir,
                    backgroundColor: ['#1a4731', '#1a4731', '#1a4731', '#1a4731'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    applyFilters();
</script>
@endpush

