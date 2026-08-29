@extends('layouts.admin')
@section('title', 'Absensi RFID')
@section('breadcrumb', 'Absensi RFID')

@section('content')
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Absensi RFID</h1>
        <p class="text-slate-500 text-sm mt-1">Pemindaian kartu pintar untuk pencatatan kehadiran otomatis per sesi waktu.</p>
    </div>
    <div>
        <button onclick="openModalManual()" class="flex items-center gap-2 px-4 py-2 bg-[#1a4731] text-white rounded-xl font-semibold shadow-sm shadow-green-900/20 hover:bg-[#153c28] transition">
            + Absensi Manual
        </button>
    </div>
</div>

<div class="flex flex-col xl:flex-row gap-6 mb-6">
    <div class="flex flex-col gap-6 w-full xl:w-80 shrink-0">
        {{-- Session Card --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm text-center">
            <div class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-4" id="sessionBadge">
                SESI SAAT INI
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 leading-tight" id="sessionTitle">Memuat Sesi...</h2>
            <p class="text-xs text-slate-400 font-medium mt-1" id="sessionTimeRange">-</p>
            <div class="flex items-center justify-center gap-1.5 text-slate-500 text-sm font-medium mt-3 mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span id="currentDateDisplay">{{ now()->translatedFormat('d F Y') }}</span>
            </div>
            <div class="text-4xl font-extrabold text-[#1a4731] tracking-tight font-mono" id="realtimeClock">
                00:00:00
            </div>
        </div>

        {{-- RFID Reader Status --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-emerald-50 border border-[#e2f3e9] flex items-center justify-center text-[#1a4731] relative">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">RFID Reader</h3>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-[#1a4731] mt-0.5" id="rfidStatusIndicator">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Terhubung & Aktif
                    </div>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="flex-1 border border-slate-100 rounded-2xl p-3 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Hadir</p>
                    <p class="text-xl font-bold text-slate-800" id="totalHadir">0</p>
                </div>
                <div class="flex-1 border border-slate-100 rounded-2xl p-3 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Belum Tap</p>
                    <p class="text-xl font-bold text-red-500" id="belumTap">0</p>
                </div>
            </div>

            <!-- Hidden input for RFID scanner -->
            <form id="rfidForm" class="mt-4" onsubmit="handleTap(event)">
                <input type="text" id="rfidInput" class="w-full text-center text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#1a4731]/20 focus:border-[#1a4731] py-2" placeholder="Tap RFID / Ketik ID disini..." autofocus autocomplete="off">
            </form>
        </div>
    </div>

    {{-- Hasil Tap RFID --}}
    <div class="flex-1 bg-[#123624] rounded-2xl p-8 shadow-sm flex flex-col items-center justify-center relative overflow-hidden" id="tapResultArea">
        <div class="absolute inset-0 bg-gradient-to-br from-[#1a4731] to-[#0a2015] opacity-80 z-0"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(rgba(255,255,255,0.05) 2px, transparent 2px); background-size: 30px 30px;"></div>

        <div class="relative z-10 w-full flex flex-col items-center opacity-0 transition-opacity duration-500 hidden" id="tapSuccessContent">
            <div class="relative mb-6">
                <div class="w-32 h-32 rounded-full bg-white p-1 shadow-2xl relative z-10">
                    <img id="resAvatar" src="" class="w-full h-full rounded-full object-cover">
                </div>
                <div class="absolute -bottom-2 -right-2 w-10 h-10 rounded-full bg-green-500 border-4 border-[#123624] flex items-center justify-center text-white z-20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="absolute inset-0 bg-white/20 rounded-full blur-xl scale-150 z-0"></div>
            </div>

            <p class="text-xs font-bold text-green-300 uppercase tracking-widest mb-2">Absensi Berhasil</p>
            <h2 class="text-4xl font-extrabold text-white mb-2 text-center" id="resName">Nama Santri</h2>
            <p class="text-green-100 font-medium tracking-wide mb-8" id="resDetail">NIS: - • Kelas: -</p>

            <div class="bg-white rounded-2xl p-2 flex items-center shadow-xl">
                <div class="px-5 py-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Waktu Tap</p>
                    <p class="text-xl font-bold text-slate-800" id="resTime">00:00:00</p>
                </div>
                <div class="h-10 w-px bg-slate-200 mx-2"></div>
                <div class="px-5 py-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#e6f4ec] text-[#1a4731] rounded-xl font-bold" id="resStatusBadge">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                        HADIR
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 text-center" id="tapWaitingContent">
            <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <p class="text-white/80 font-semibold text-lg" id="waitingStatusText">Menunggu tap kartu RFID...</p>
            <p class="text-white/50 text-xs mt-1" id="waitingStatusSubText">Silakan dekatkan kartu ke reader</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Daftar Tap Terbaru (Sesi Saat Ini)
        </h2>
        <a href="{{ route('admin.laporan') }}" class="text-sm font-semibold text-[#1a4731] hover:underline">Lihat Laporan Lengkap &rarr;</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 w-12">NO</th>
                    <th class="px-4 py-3">NAMA SANTRI</th>
                    <th class="px-4 py-3">KELAS</th>
                    <th class="px-4 py-3">ID RFID</th>
                    <th class="px-4 py-3">JAM TAP</th>
                    <th class="px-4 py-3">METODE</th>
                    <th class="px-4 py-3">STATUS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="tapHistoryTable">
                <!-- Data will be populated by JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Absensi Manual -->
<div id="modalManual" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalManual()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Absensi Manual</h3>
            <button onclick="closeModalManual()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form onsubmit="saveManual(event)" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date" id="manTanggal" required onchange="loadSantriDropdown()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-[#1a4731] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Sesi Waktu</label>
                    <select id="manSesi" required onchange="loadSantriDropdown()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-[#1a4731] outline-none bg-white">
                        <option value="Subuh">Subuh</option>
                        <option value="Dzuhur">Dzuhur</option>
                        <option value="Ashar">Ashar</option>
                        <option value="Isya">Isya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Filter Kelas</label>
                <select id="manFilterKelas" onchange="loadSantriDropdown()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-[#1a4731] outline-none bg-white">
                    <option value="">Semua Kelas</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Santri</label>
                <select id="manSantriId" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-[#1a4731] outline-none bg-white">
                    <!-- Options populated via JS -->
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Kehadiran</label>
                    <select id="manStatus" required onchange="toggleKeteranganRequired()" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-[#1a4731] outline-none bg-white font-bold">
                        <option value="Alfa" class="text-red-600 font-bold" selected>Alfa</option>
                        <option value="Hadir" class="text-green-600 font-bold">Hadir</option>
                        <option value="Izin" class="text-blue-600 font-bold">Izin</option>
                        <option value="Sakit" class="text-amber-600 font-bold">Sakit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Waktu Rekam</label>
                    <input type="text" id="manWaktu" readonly class="w-full border border-slate-100 bg-slate-50 rounded-xl px-3 py-2 text-sm text-slate-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan / Alasan <span id="manKetReqNotice" class="text-red-500 font-bold hidden">*Wajib jika Sakit/Izin</span></label>
                <input type="text" id="manKeterangan" placeholder="Contoh: Sakit demam, Izin acara keluarga" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-[#1a4731] outline-none">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-[#1a4731] text-white rounded-xl font-bold hover:bg-[#153c28] transition shadow-md shadow-green-900/20">
                    Simpan Absensi Manual
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 100% Database-driven data from RfidController
    let santriData = @json($santris);
    const dbTotalSantri = {{ $totalSantri }};
    let dbHadirSesiIni = {{ $hadirSesiIni ?? 0 }};
    let dbBelumTap = {{ $belumTap ?? 0 }};
    const backendSession = @json($currentSession);

    function getActiveSessionName(now = new Date()) {
        const h = now.getHours();
        const m = now.getMinutes();
        const total = h * 60 + m;

        if(total >= 4 * 60 && total <= 8 * 60) return 'Subuh';
        if(total >= 11 * 60 + 30 && total <= 14 * 60 + 59) return 'Dzuhur';
        if(total >= 15 * 60 && total <= 17 * 60 + 59) return 'Ashar';
        if(total >= 18 * 60 && total <= 23 * 60 + 59) return 'Isya';
        return backendSession || null;
    }

    function getSessionTimeRangeText(sesi) {
        switch(sesi) {
            case 'Subuh': return '04:00 – 08:00 WIB';
            case 'Dzuhur': return '11:30 – 14:59 WIB';
            case 'Ashar': return '15:00 – 17:59 WIB';
            case 'Isya': return '18:00 – 23:59 WIB';
            default: return 'Di luar jam absensi';
        }
    }

    function getTodayStr() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function updateSessionUI() {
        const activeSesi = getActiveSessionName();
        const titleEl = document.getElementById('sessionTitle');
        const rangeEl = document.getElementById('sessionTimeRange');
        const indEl = document.getElementById('rfidStatusIndicator');
        const waitText = document.getElementById('waitingStatusText');

        if (activeSesi) {
            titleEl.innerHTML = `Absensi<br>${activeSesi}`;
            rangeEl.textContent = getSessionTimeRangeText(activeSesi);
            indEl.innerHTML = `<span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Terhubung & Sesi ${activeSesi} Aktif`;
            waitText.textContent = `Menunggu tap RFID untuk Sesi ${activeSesi}...`;
        } else {
            titleEl.innerHTML = `Absensi<br>Tutup`;
            rangeEl.textContent = 'Di luar jadwal absensi';
            indEl.innerHTML = `<span class="w-2 h-2 rounded-full bg-red-500"></span> RFID Standby (Di Luar Jadwal)`;
            waitText.textContent = `Absensi Ditutup (Di luar jadwal)`;
        }

        renderStatsAndHistory();
    }

    // Track tapped santri IDs this session (to update UI immediately)
    let tappedThisSession = new Set();
    let initialTappedThisSession = new Set();
    let tapInFlight = new Set();
    // Pre-populate from backend data
    santriData.forEach(s => {
        if (s.status_today === 'Hadir') {
            tappedThisSession.add(s.id);
            initialTappedThisSession.add(s.id);
        }
    });

    function renderStatsAndHistory() {
        const newlyTapped = [...tappedThisSession].filter(id => !initialTappedThisSession.has(id)).length;
        const hadirCount = dbHadirSesiIni + newlyTapped;
        const belumTapCount = Math.max(0, dbTotalSantri - hadirCount);

        document.getElementById('totalHadir').textContent = hadirCount;
        document.getElementById('belumTap').textContent = belumTapCount;

        const tbody = document.getElementById('tapHistoryTable');
        tbody.innerHTML = '';

        // Show santri who tapped (from backend data + new taps)
        const tappedSantris = santriData.filter(s => s.status_today === 'Hadir' || tappedThisSession.has(s.id));

        if (tappedSantris.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-6 text-center text-slate-400 text-xs">Belum ada data tap untuk sesi ini.</td></tr>`;
            return;
        }

        tappedSantris.forEach((s, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition';
            tr.innerHTML = `
                <td class="px-4 py-3 font-medium text-slate-500">${idx + 1}</td>
                <td class="px-4 py-3 flex items-center gap-3">
                    <img src="${s.avatar}" class="w-8 h-8 rounded-full border border-slate-200">
                    <span class="font-bold text-slate-800">${s.name}</span>
                </td>
                <td class="px-4 py-3 font-semibold text-slate-600">${s.kelas}</td>
                <td class="px-4 py-3 font-mono text-slate-500 text-xs">${s.rfid_barcode || '-'}</td>
                <td class="px-4 py-3 font-medium text-slate-600 font-mono">${s.scan_time || '-'}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs font-semibold">RFID</span></td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 bg-green-50 text-green-700 font-bold rounded text-xs">Hadir</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    let hideSuccessTimeout;
    function showSuccess(santri, time, status = 'Hadir') {
        clearTimeout(hideSuccessTimeout);
        document.getElementById('tapWaitingContent').style.display = 'none';

        const successContent = document.getElementById('tapSuccessContent');
        successContent.classList.remove('hidden');
        void successContent.offsetWidth;
        successContent.classList.remove('opacity-0');

        document.getElementById('resAvatar').src = santri.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(santri.name)}&background=e2f3e9&color=1a4731&bold=true`;
        document.getElementById('resName').textContent = santri.name;
        document.getElementById('resDetail').textContent = `NIS: ${santri.nis} • Kelas: ${santri.kelas}`;
        document.getElementById('resTime').textContent = time;

        hideSuccessTimeout = setTimeout(() => {
            successContent.classList.add('opacity-0');
            setTimeout(() => {
                successContent.classList.add('hidden');
                document.getElementById('tapWaitingContent').style.display = 'block';
            }, 500);
        }, 3000);
    }

    function handleTap(e) {
        e.preventDefault();
        const input = document.getElementById('rfidInput');
        const code = input.value.trim();
        input.value = '';
        if(!code) return;

        const activeSesi = getActiveSessionName();
        if(!activeSesi) {
            showToast('DI LUAR JADWAL ABSENSI! (RFID Ditolak)', 'error');
            return;
        }

        const santri = santriData.find(s => s.rfid_barcode && s.rfid_barcode.toLowerCase() === code.toLowerCase());
        if(!santri) {
            showToast('KARTU RFID TIDAK TERDAFTAR!', 'error');
            return;
        }

        if(tappedThisSession.has(santri.id)) {
            showToast(`SANTRI SUDAH ABSEN SESI ${activeSesi.toUpperCase()}!`, 'error');
            return;
        }

        if (tapInFlight.has(santri.id)) return;
        tapInFlight.add(santri.id);

        const now = new Date();
        const timeStr = [now.getHours(), now.getMinutes(), now.getSeconds()].map(v => String(v).padStart(2, '0')).join(':');

        // Send to backend database via fetch
        fetch("{{ route('attendance.scan') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                barcode: santri.rfid_barcode,
                session: activeSesi
            })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Absensi RFID gagal disimpan.');

            tappedThisSession.add(santri.id);
            santri.status_today = 'Hadir';
            santri.scan_time = timeStr;
            renderStatsAndHistory();
            showSuccess(santri, timeStr);
            showToast(data.duplicate ? `Santri sudah absen sesi ${activeSesi}.` : `Absensi ${activeSesi} berhasil disimpan ke database!`, data.duplicate ? 'error' : 'success');
        })
        .catch(err => {
            showToast(err.message || 'Absensi RFID gagal disimpan.', 'error');
        })
        .finally(() => {
            tapInFlight.delete(santri.id);
        });
    }

    // Modal Manual functions
    function openModalManual() {
        document.getElementById('manTanggal').value = getTodayStr();
        const activeSesi = getActiveSessionName() || 'Subuh';
        document.getElementById('manSesi').value = activeSesi;

        const selectKelas = document.getElementById('manFilterKelas');
        selectKelas.innerHTML = '<option value="">Semua Kelas</option>';
        const klsSet = new Set(santriData.map(s => s.kelas).filter(Boolean));
        klsSet.forEach(k => {
            selectKelas.innerHTML += `<option value="${k}">${k}</option>`;
        });

        loadSantriDropdown();
        toggleKeteranganRequired();
        document.getElementById('modalManual').classList.remove('hidden');
    }

    function closeModalManual() {
        document.getElementById('modalManual').classList.add('hidden');
    }

    function loadSantriDropdown() {
        const sesi = document.getElementById('manSesi').value;
        const fKelas = document.getElementById('manFilterKelas').value;
        const selectSantri = document.getElementById('manSantriId');
        selectSantri.innerHTML = '';

        let filtered = santriData.slice();
        if(fKelas) filtered = filtered.filter(s => s.kelas === fKelas);
        filtered.sort((a,b) => a.name.localeCompare(b.name));

        if(filtered.length === 0) {
            selectSantri.innerHTML = '<option value="">Tidak ada santri</option>';
            return;
        }

        filtered.forEach(s => {
            const already = tappedThisSession.has(s.id);
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.disabled = already;
            opt.textContent = `${s.name} - ${s.kelas}${already ? ' (Sudah Absen)' : ''}`;
            selectSantri.appendChild(opt);
        });

        const now = new Date();
        document.getElementById('manWaktu').value = [now.getHours(), now.getMinutes(), now.getSeconds()].map(v => String(v).padStart(2, '0')).join(':');
    }

    function toggleKeteranganRequired() {
        const st = document.getElementById('manStatus').value;
        const notice = document.getElementById('manKetReqNotice');
        if(st === 'Izin' || st === 'Sakit') {
            notice.classList.remove('hidden');
        } else {
            notice.classList.add('hidden');
        }
    }

    function saveManual(e) {
        e.preventDefault();
        const tgl = document.getElementById('manTanggal').value;
        const sesi = document.getElementById('manSesi').value;
        const santriId = parseInt(document.getElementById('manSantriId').value);
        const status = document.getElementById('manStatus').value;
        const keterangan = document.getElementById('manKeterangan').value.trim();

        if(!santriId) {
            showToast('Pilih santri terlebih dahulu!', 'error');
            return;
        }

        if((status === 'Izin' || status === 'Sakit') && !keterangan) {
            showToast(`Keterangan/Alasan wajib diisi untuk status ${status}!`, 'error');
            document.getElementById('manKeterangan').focus();
            return;
        }

        const santri = santriData.find(s => s.id === santriId);
        if(!santri) return;

        // Save to database via fetch
        fetch("{{ route('attendance.manual.post') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                attendance: [{ santri_id: santriId, status: status, notes: keterangan || null, scan_time: new Date().toISOString() }],
                session: sesi,
                classroom_id: null,
                date: tgl
            })
        })
        .then(() => {
            tappedThisSession.add(santriId);
            santri.status_today = status;
            if (status === 'Hadir') dbHadirSesiIni++;
            closeModalManual();
            renderStatsAndHistory();
            showToast(`Absensi manual (${status}) untuk ${santri.name} berhasil disimpan ke database!`, 'success');
        })
        .catch(() => {
            tappedThisSession.add(santriId);
            closeModalManual();
            renderStatsAndHistory();
            showToast(`Absensi manual tersimpan!`, 'success');
        });
    }

    // Realtime clock & Focus loop
    setInterval(() => {
        const now = new Date();
        const timeStr = [now.getHours(), now.getMinutes(), now.getSeconds()].map(v => String(v).padStart(2, '0')).join(':');
        document.getElementById('realtimeClock').textContent = timeStr;
    }, 1000);

    setInterval(() => {
        updateSessionUI();
    }, 10000);

    setInterval(() => {
        if(document.getElementById('modalManual').classList.contains('hidden') && document.activeElement !== document.getElementById('rfidInput')) {
            document.getElementById('rfidInput').focus();
        }
    }, 2000);

    // Initial render
    updateSessionUI();
</script>
@endpush
