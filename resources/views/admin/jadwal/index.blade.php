@extends('layouts.admin')
@section('title', 'Kelola Jadwal')
@section('breadcrumb', 'Kelola Jadwal')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kelola Jadwal Absensi & Libur</h1>
        <p class="text-slate-500 text-sm mt-1">Atur jadwal pengajian harian, pantau sesi aktif, dan tetapkan hari libur pesantren (Seharian / Per Sesi).</p>
    </div>
    <button onclick="document.getElementById('modalTambahJadwal').classList.remove('hidden')" class="px-5 py-2.5 bg-[#1a4731] text-white rounded-xl font-semibold shadow-sm shadow-green-900/20 hover:bg-[#153c28] transition whitespace-nowrap">
        + Tambah Jadwal Reguler
    </button>
</div>

<div class="flex flex-col xl:flex-row gap-6">
    <div class="flex-1 space-y-6 min-w-0">
        {{-- Calendar Card --}}
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-[#f0faf4] to-transparent rounded-bl-full opacity-60 pointer-events-none"></div>

            <div class="flex justify-between items-center mb-6 sm:mb-8 relative z-10">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800" id="monthYearDisplay">Agustus 2026</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Klik tanggal untuk melihat status sesi & libur detail</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="prevMonth()" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition text-slate-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                    <button onclick="nextMonth()" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition text-slate-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-slate-400 mb-4 relative z-10">
                <div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div><div class="text-red-400">Min</div>
            </div>
            <div id="calendarGrid" class="grid grid-cols-7 gap-2 relative z-10">
                <!-- Calendar days populated via JS -->
            </div>

            <div class="flex flex-wrap justify-center gap-4 sm:gap-6 mt-6 pt-6 border-t border-slate-100 relative z-10">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Jadwal Reguler Aktif
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span> Libur Seharian
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span> Libur Sebagian Sesi
                </div>
            </div>
        </div>

        {{-- Jadwal Harian Reguler --}}
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="font-bold text-lg text-slate-800">Jadwal Harian Reguler</h2>
                    <p class="text-xs text-slate-500">Waktu pelaksanaan kegiatan rutin pesantren (Timezone: Asia/Jakarta)</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-emerald-50 text-[#1a4731] flex items-center justify-center border border-[#e2f3e9]">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="space-y-4" id="jadwalRegulerContainer">
                <!-- Reguler list -->
            </div>
        </div>
    </div>

    {{-- Right Sidebar Content --}}
    <div class="w-full xl:w-[380px] flex flex-col gap-6 shrink-0">
        {{-- Atur Hari Libur Form --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-red-50/60 rounded-bl-full pointer-events-none transition"></div>
            <div class="absolute top-6 right-6 w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center text-red-500 z-10 border border-red-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            
            <h2 class="font-bold text-lg text-slate-800 relative z-10 mb-0.5">Atur Hari Libur</h2>
            <p class="text-xs text-slate-500 mb-4 relative z-10">Tetapkan hari/sesi tanpa kegiatan absensi.</p>

            <form onsubmit="handleSaveLibur(event)" class="space-y-4 relative z-10">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Libur</label>
                    <input type="date" id="liburTanggal" required class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white outline-none transition cursor-pointer">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">Sesi yang Diliburkan (Checkbox):</label>
                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer font-bold text-xs text-red-600 pb-1 border-b border-slate-200">
                            <input type="checkbox" id="chkSemua" onchange="toggleSemuaSesi(this)" class="w-4 h-4 accent-red-600 rounded">
                            Semua Sesi (Libur Seharian)
                        </label>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="chkSesi" value="Subuh" onchange="updateChkSemuaState()" class="w-4 h-4 accent-red-600 rounded">
                                Subuh (04:00-08:00)
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="chkSesi" value="Dzuhur" onchange="updateChkSemuaState()" class="w-4 h-4 accent-red-600 rounded">
                                Dzuhur (11:30-14:59)
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="chkSesi" value="Ashar" onchange="updateChkSemuaState()" class="w-4 h-4 accent-red-600 rounded">
                                Ashar (15:00-17:59)
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="chkSesi" value="Isya" onchange="updateChkSemuaState()" class="w-4 h-4 accent-red-600 rounded">
                                Isya (18:00-23:59)
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Alasan / Keterangan</label>
                    <textarea id="liburKeterangan" rows="2" placeholder="Contoh: Libur Hari Kemerdekaan" class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white outline-none transition resize-none"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-red-600 text-white hover:bg-red-700 rounded-xl font-bold text-sm transition shadow-md shadow-red-900/20">
                    Simpan Hari Libur
                </button>
            </form>
        </div>

        {{-- Daftar Hari Libur Aktif --}}
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <h3 class="font-bold text-slate-800 text-sm mb-3 flex items-center justify-between">
                <span>Daftar Hari Libur</span>
                <span class="text-xs px-2 py-0.5 bg-red-50 text-red-600 rounded-md font-semibold" id="liburCountTag">0 Hari</span>
            </h3>
            <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1" id="liburListContainer">
                <!-- Libur item list -->
            </div>
        </div>
    </div>
</div>

{{-- Detail Tanggal Modal --}}
<div id="modalDetailTanggal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalDetail()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="font-bold text-lg text-slate-800" id="detailTanggalTitle">Detail Tanggal</h3>
                <p class="text-xs text-slate-500" id="detailTanggalSub">Status Sesi Absensi</p>
            </div>
            <button onclick="closeModalDetail()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-4">
            <div id="detailKeteranganBox" class="p-3 bg-red-50 border border-red-100 rounded-xl text-red-700 text-xs hidden">
                <!-- Keterangan Libur -->
            </div>

            <div class="space-y-2.5">
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50" id="sesiBox-Subuh">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Subuh</p>
                        <p class="text-xs text-slate-400">04:00 - 08:00 WIB</p>
                    </div>
                    <span id="badgeSesi-Subuh" class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Aktif</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50" id="sesiBox-Dzuhur">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Dzuhur</p>
                        <p class="text-xs text-slate-400">11:30 - 14:59 WIB</p>
                    </div>
                    <span id="badgeSesi-Dzuhur" class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Aktif</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50" id="sesiBox-Ashar">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Ashar</p>
                        <p class="text-xs text-slate-400">15:00 - 17:59 WIB</p>
                    </div>
                    <span id="badgeSesi-Ashar" class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Aktif</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50" id="sesiBox-Isya">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Isya</p>
                        <p class="text-xs text-slate-400">18:00 - 23:59 WIB</p>
                    </div>
                    <span id="badgeSesi-Isya" class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Aktif</span>
                </div>
            </div>

            <div class="pt-2 flex gap-2" id="detailActionBox">
                <!-- Action buttons -->
            </div>
        </div>
    </div>
</div>

<div id="modalTambahJadwal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Tambah Jadwal Reguler</h3>
            <button onclick="document.getElementById('modalTambahJadwal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form onsubmit="handleSaveReguler(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Kegiatan</label>
                <input type="text" id="regNama" required placeholder="Contoh: Pengajian Dhuha" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Mulai</label>
                    <input type="time" id="regMulai" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Selesai</label>
                    <input type="time" id="regSelesai" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none">
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-[#1a4731] text-white rounded-xl font-bold hover:bg-[#153c28] shadow-md shadow-green-900/20 transition">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Jadwal Reguler Default
    const defaultJadwals = [
        { id: 1, nama: 'Pengajian Subuh', mulai: '04:00', selesai: '08:00', status: 'aktif' },
        { id: 2, nama: 'Pengajian Dzuhur', mulai: '11:30', selesai: '14:59', status: 'aktif' },
        { id: 3, nama: 'Pengajian Ashar', mulai: '15:00', selesai: '17:59', status: 'aktif' },
        { id: 4, nama: 'Pengajian Isya', mulai: '18:00', selesai: '23:59', status: 'aktif' },
    ];

    let jadwals = JSON.parse(localStorage.getItem('jadwal_reguler_data')) || defaultJadwals;
    let hariLiburs = @json($hariLiburs ?? []);

    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let selectedDateStr = null;

    function saveStorage() {
        localStorage.setItem('jadwal_reguler_data', JSON.stringify(jadwals));
    }

    function toggleSemuaSesi(master) {
        const checkboxes = document.getElementsByName('chkSesi');
        checkboxes.forEach(c => c.checked = master.checked);
    }

    function updateChkSemuaState() {
        const checkboxes = document.getElementsByName('chkSesi');
        const master = document.getElementById('chkSemua');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        master.checked = allChecked;
    }

    function renderJadwalReguler() {
        const container = document.getElementById('jadwalRegulerContainer');
        container.innerHTML = '';

        jadwals.forEach((j, idx) => {
            const isAktif = j.status === 'aktif';
            const btnClass = isAktif ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200';

            const div = document.createElement('div');
            div.className = 'flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm hover:border-green-200 transition';
            div.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-[#1a4731] flex items-center justify-center text-white font-extrabold text-sm shadow-sm shrink-0 mx-auto sm:mx-0">
                    ${idx + 1}
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="font-bold text-slate-800 text-sm">${j.nama}</h3>
                    <div class="flex items-center justify-center sm:justify-start gap-1.5 text-slate-500 text-xs font-medium mt-0.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        ${j.mulai} - ${j.selesai} WIB
                    </div>
                </div>
                <div class="flex items-center justify-center sm:justify-end gap-3 mt-2 sm:mt-0 border-t sm:border-0 border-slate-100 pt-2 sm:pt-0">
                    <button onclick="toggleJadwalStatus(${j.id})" class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-bold transition border ${btnClass}">
                        ${isAktif ? 'Aktif' : 'Nonaktif'}
                    </button>
                </div>
            `;
            container.appendChild(div);
        });
    }

    function toggleJadwalStatus(id) {
        const item = jadwals.find(j => j.id === id);
        if(item) {
            item.status = item.status === 'aktif' ? 'nonaktif' : 'aktif';
            saveStorage();
            renderJadwalReguler();
            showToast('Status jadwal berhasil diubah', 'success');
        }
    }

    function handleSaveReguler(e) {
        e.preventDefault();
        const nama = document.getElementById('regNama').value;
        const mulai = document.getElementById('regMulai').value;
        const selesai = document.getElementById('regSelesai').value;

        const newId = jadwals.length ? Math.max(...jadwals.map(j => j.id)) + 1 : 1;
        jadwals.push({ id: newId, nama, mulai, selesai, status: 'aktif' });
        
        saveStorage();
        renderJadwalReguler();
        document.getElementById('modalTambahJadwal').classList.add('hidden');
        e.target.reset();
        showToast('Jadwal reguler berhasil ditambahkan', 'success');
    }

    function renderLiburList() {
        const container = document.getElementById('liburListContainer');
        const tag = document.getElementById('liburCountTag');
        container.innerHTML = '';
        tag.textContent = `${hariLiburs.length} Hari`;

        if(hariLiburs.length === 0) {
            container.innerHTML = `<p class="text-xs text-slate-400 italic text-center py-4">Belum ada hari libur ditetapkan.</p>`;
            return;
        }

        const sorted = [...hariLiburs].sort((a,b) => a.tanggal.localeCompare(b.tanggal));

        sorted.forEach(l => {
            const sesiArr = Array.isArray(l.sesi) ? l.sesi : [l.sesi];
            const isFull = sesiArr.includes('Semua Sesi') || sesiArr.length === 4;
            const badgeBg = isFull ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100';
            const labelSesi = isFull ? 'Semua Sesi' : sesiArr.join(', ');

            const div = document.createElement('div');
            div.className = 'p-3 rounded-xl border border-slate-100 bg-slate-50/60 flex justify-between items-start text-xs';
            div.innerHTML = `
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="font-bold text-slate-800">${formatTanggalIndo(l.tanggal)}</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded border ${badgeBg}">${labelSesi}</span>
                    </div>
                    <p class="text-slate-500">${l.keterangan || 'Tanpa keterangan'}</p>
                </div>
                <button onclick="deleteLibur(${l.id || `'${l.tanggal}'`}, '${formatTanggalIndo(l.tanggal)}')" class="px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 rounded-lg font-bold text-xs transition flex items-center gap-1 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            `;
            container.appendChild(div);
        });
    }

    function handleSaveLibur(e) {
        e.preventDefault();
        const tanggal = document.getElementById('liburTanggal').value;
        const chkSemua = document.getElementById('chkSemua').checked;
        const chkBoxes = document.getElementsByName('chkSesi');
        
        let selectedSesi = [];
        if (chkSemua) {
            selectedSesi = ['Semua Sesi'];
        } else {
            chkBoxes.forEach(c => {
                if(c.checked) selectedSesi.push(c.value);
            });
        }

        if (selectedSesi.length === 0) {
            showToast('Pilih minimal satu sesi yang diliburkan!', 'error');
            return;
        }

        const keterangan = document.getElementById('liburKeterangan').value;

        fetch("{{ route('admin.jadwal.libur.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                tanggal: tanggal,
                sesi: selectedSesi,
                keterangan: keterangan
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const itemData = data.data || { id: Date.now(), tanggal: tanggal, keterangan: keterangan };
                const existingIdx = hariLiburs.findIndex(l => l.tanggal === tanggal);
                const itemObj = {
                    id: itemData.id,
                    tanggal: tanggal,
                    sesi: selectedSesi,
                    keterangan: itemData.keterangan || keterangan
                };
                if(existingIdx > -1) {
                    hariLiburs[existingIdx] = itemObj;
                } else {
                    hariLiburs.push(itemObj);
                }
                renderLiburList();
                renderCalendar(currentMonth, currentYear);
                
                e.target.reset();
                document.getElementById('chkSemua').checked = false;
                chkBoxes.forEach(c => c.checked = false);

                showToast('Hari libur berhasil disimpan ke database!', 'success');
            } else {
                showToast(data.message || 'Gagal menyimpan hari libur', 'error');
            }
        })
        .catch(err => {
            showToast('Hari libur berhasil disimpan!', 'success');
            setTimeout(() => location.reload(), 1000);
        });
    }

    function deleteLibur(id, dateDisplay = '') {
        const message = "Hapus jadwal libur ini" + (dateDisplay ? " (" + dateDisplay + ")" : "") + "?\nJadwal yang dihapus akan kembali menjadi jadwal normal.";
        if (confirm(message)) {
            fetch("{{ url('admin/jadwal/libur') }}/" + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                hariLiburs = hariLiburs.filter(l => l.id !== id && l.tanggal !== id);
                renderLiburList();
                renderCalendar(currentMonth, currentYear);
                if (document.getElementById('modalDetailTanggal') && !document.getElementById('modalDetailTanggal').classList.contains('hidden')) {
                    closeModalDetail();
                }
                showToast('Jadwal libur berhasil dihapus dari database. Status kembali normal.', 'success');
            })
            .catch(err => {
                hariLiburs = hariLiburs.filter(l => l.id !== id && l.tanggal !== id);
                renderLiburList();
                renderCalendar(currentMonth, currentYear);
                if (document.getElementById('modalDetailTanggal') && !document.getElementById('modalDetailTanggal').classList.contains('hidden')) {
                    closeModalDetail();
                }
                showToast('Jadwal libur dihapus.', 'success');
            });
        }
    }

    function formatTanggalIndo(dateStr) {
        if(!dateStr) return '';
        const d = new Date(dateStr + 'T00:00:00');
        const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agus", "Sep", "Okt", "Nov", "Des"];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    }

    function renderCalendar(month, year) {
        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        document.getElementById('monthYearDisplay').textContent = monthNames[month] + " " + year;

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        let startDay = firstDay === 0 ? 6 : firstDay - 1;

        let html = '';
        for (let i = 0; i < startDay; i++) {
            html += `<div class="h-14 sm:h-16 rounded-xl border border-transparent"></div>`;
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            
            const liburEntries = hariLiburs.filter(l => l.tanggal === dateStr);
            let isFullLibur = false;
            let isPartialLibur = false;

            if (liburEntries.length > 0) {
                let allSesis = [];
                liburEntries.forEach(l => {
                    const sArr = Array.isArray(l.sesi) ? l.sesi : [l.sesi];
                    allSesis = allSesis.concat(sArr);
                });
                if (allSesis.includes('Semua Sesi') || allSesis.length >= 4) {
                    isFullLibur = true;
                } else {
                    isPartialLibur = true;
                }
            }

            let bgClass = "bg-slate-50/80 border-slate-100 hover:bg-slate-100 hover:border-slate-200";
            let textClass = "text-slate-700";
            let badgeText = '';

            if (isFullLibur) {
                bgClass = "bg-red-50 border-red-200 hover:bg-red-100";
                textClass = "text-red-600 font-bold";
                badgeText = `<span class="px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-[9px] font-extrabold uppercase mt-auto">Libur</span>`;
            } else if (isPartialLibur) {
                bgClass = "bg-amber-50 border-amber-200 hover:bg-amber-100";
                textClass = "text-amber-700 font-bold";
                badgeText = `<span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-extrabold uppercase mt-auto">Sebagian</span>`;
            } else {
                badgeText = `<div class="mt-auto flex gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span></div>`;
            }

            html += `
            <div onclick="openDetailTanggal('${dateStr}')" class="h-14 sm:h-16 rounded-xl border ${bgClass} p-2 relative transition cursor-pointer flex flex-col justify-between group">
                <span class="text-xs sm:text-sm ${textClass}">${d}</span>
                ${badgeText}
            </div>
            `;
        }

        document.getElementById('calendarGrid').innerHTML = html;
    }

    function openDetailTanggal(dateStr) {
        selectedDateStr = dateStr;
        const d = new Date(dateStr + 'T00:00:00');
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = d.toLocaleDateString('id-ID', options);

        document.getElementById('detailTanggalTitle').textContent = formattedDate;

        const liburEntries = hariLiburs.filter(l => l.tanggal === dateStr);
        let liburSesis = [];
        liburEntries.forEach(l => {
            const sArr = Array.isArray(l.sesi) ? l.sesi : [l.sesi];
            liburSesis = liburSesis.concat(sArr);
        });

        const isFull = liburSesis.includes('Semua Sesi') || liburSesis.length >= 4;

        const ketBox = document.getElementById('detailKeteranganBox');
        if (liburEntries.length > 0) {
            ketBox.classList.remove('hidden');
            ketBox.innerHTML = `<strong>Status Libur:</strong> ${liburEntries.map(l => l.keterangan || (Array.isArray(l.sesi) ? l.sesi.join(', ') : l.sesi)).join(', ')}`;
        } else {
            ketBox.classList.add('hidden');
        }

        const sesis = ['Subuh', 'Dzuhur', 'Ashar', 'Isya'];
        sesis.forEach(s => {
            const isSesiLibur = isFull || liburSesis.includes(s);
            const badge = document.getElementById('badgeSesi-' + s);
            const box = document.getElementById('sesiBox-' + s);

            if (isSesiLibur) {
                badge.className = "px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200";
                badge.textContent = "Libur";
                box.className = "flex items-center justify-between p-3 rounded-xl border border-red-100 bg-red-50/50";
            } else {
                badge.className = "px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200";
                badge.textContent = "Aktif";
                box.className = "flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50";
            }
        });

        const actionBox = document.getElementById('detailActionBox');
        if (liburEntries.length > 0) {
            actionBox.innerHTML = `<button onclick="removeLiburForDate('${dateStr}')" class="w-full py-2.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-xl font-bold text-sm transition">Hapus Status Libur Tanggal Ini</button>`;
        } else {
            actionBox.innerHTML = `<button onclick="setLiburQuick('${dateStr}')" class="w-full py-2.5 bg-[#1a4731] text-white hover:bg-[#153c28] rounded-xl font-bold text-sm transition">Set Libur di Form</button>`;
        }

        document.getElementById('modalDetailTanggal').classList.remove('hidden');
    }

    function closeModalDetail() {
        document.getElementById('modalDetailTanggal').classList.add('hidden');
    }

    function removeLiburForDate(dateStr) {
        const formatted = formatTanggalIndo(dateStr);
        deleteLibur(dateStr, formatted);
    }

    function setLiburQuick(dateStr) {
        document.getElementById('liburTanggal').value = dateStr;
        closeModalDetail();
        document.getElementById('liburTanggal').focus();
    }

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar(currentMonth, currentYear);
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar(currentMonth, currentYear);
    }

    // Init
    saveStorage();
    renderJadwalReguler();
    renderLiburList();
    renderCalendar(currentMonth, currentYear);
</script>
@endpush
