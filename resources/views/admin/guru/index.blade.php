@extends('layouts.admin')
@section('title', 'Data Guru')
@section('breadcrumb', 'Data Guru')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Data Guru & Penugasan Mengajar</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola data tenaga pendidik dan penetapan daftar kelas yang dapat diajar.</p>
    </div>
    <div class="flex gap-4 items-center">
        <div class="relative w-64 hidden md:block">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" id="guruSearchInput" oninput="renderGuruTable()" placeholder="Cari nama atau NIP..." class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a4731]/20">
        </div>
        <button onclick="openModalTambah()" class="px-5 py-2.5 bg-[#1a4731] text-white rounded-xl font-semibold shadow-sm shadow-green-900/20 hover:bg-[#153c28] transition whitespace-nowrap">
            + Tambah Guru
        </button>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div class="text-right">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Guru Aktif</p>
            <p class="text-3xl font-extrabold text-slate-800" id="statGuruAktif">0</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div class="text-right">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kelas Terampu</p>
            <p class="text-3xl font-extrabold text-slate-800" id="statKelasTerampu">0</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
        <div class="w-14 h-14 bg-red-50 text-red-500 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="text-right">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Guru Cuti/Izin</p>
            <p class="text-3xl font-extrabold text-slate-800" id="statGuruCuti">0</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-slate-700 font-bold text-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Status
            </div>
            <div class="w-px h-5 bg-slate-200 hidden md:block"></div>
            <select id="guruStatusFilter" onchange="renderGuruTable()" class="border-none text-sm font-semibold text-slate-600 outline-none bg-transparent cursor-pointer">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
                <option value="cuti">Cuti/Izin</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 w-12 text-center">NO</th>
                    <th class="px-5 py-4">PROFIL GURU</th>
                    <th class="px-5 py-4">NIP / ID</th>
                    <th class="px-5 py-4">KELAS INDUK</th>
                    <th class="px-5 py-4">KELAS YANG DAPAT DIAJAR</th>
                    <th class="px-5 py-4">STATUS GURU</th>
                    <th class="px-5 py-4">STATUS AKUN</th>
                    <th class="px-5 py-4 text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="guruTableBody">
                <!-- Rows injected via JS -->
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500 gap-4" id="guruPaginationInfo">
        <!-- Pagination injected via JS -->
    </div>
</div>

<!-- Modal Form Tambah/Edit Guru -->
<div id="modalFormGuru" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalGuru()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-1.5rem)] sm:w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-2xl">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 sticky top-0 z-10">
            <h3 class="font-bold text-lg text-slate-800" id="modalGuruTitle">Tambah Guru</h3>
            <button onclick="closeModalGuru()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form id="formGuruAction" method="POST" action="{{ route('admin.guru.store') }}" class="p-6 space-y-4 pb-8">
            @csrf
            <input type="hidden" name="_method" id="frmGuruMethod" value="POST">
            <input type="hidden" name="id" id="frmGuruId">

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Lengkap Guru <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="frmGuruName" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">NIP / ID</label>
                    <input type="text" name="nip" id="frmGuruNip" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none transition" placeholder="Cth: 19850101">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Nomor HP</label>
                    <input type="text" name="nomor_hp" id="frmGuruHp" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none transition" placeholder="Cth: 08123456789">
                </div>
            </div>

            {{-- ROLE --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Role / Jabatan <span class="text-red-500">*</span></label>
                <select name="role" id="frmGuruRole" required onchange="onRoleChange()"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white font-semibold focus:ring-[#1a4731] outline-none transition cursor-pointer">
                    <option value="guru">🧑‍🏫 Guru Pendamping (Tanpa Kelas Induk)</option>
                    <option value="wali_kelas">🏫 Wali Kelas</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1" id="roleHelp">
                    Guru Pendamping tidak mempunyai kelas induk tetap. Guru memilih kelas ketika akan mengajar.
                </p>
            </div>

            {{-- KELAS WALI (hanya tampil untuk Wali Kelas) --}}
            <div id="blockKelasWali" class="hidden">
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Kelas Wali <span class="text-red-500">*</span></label>
                <select name="classroom_id" id="frmGuruClassroomId"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none transition bg-white cursor-pointer font-semibold">
                    <option value="">-- Pilih Kelas Wali --</option>
                    @foreach($kelasList as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Kelas utama yang menjadi tanggung jawab Wali Kelas ini.</p>
            </div>

            {{-- PENUGASAN: KELAS YANG DAPAT DIAJAR (hanya tampil untuk Guru Biasa) --}}
            <div id="blockKelasDiajar" class="space-y-1.5">
                <label class="block text-sm font-bold text-slate-700">Kelas yang Dapat Diajar (Penugasan):</label>
                <p class="text-[11px] text-slate-500 leading-tight">Centang kelas yang diperbolehkan diajar oleh guru ini saat mengisi Absensi Mengajar.</p>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($kelasList as $cls)
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer hover:text-emerald-800">
                            <input type="checkbox" name="allowed_classroom_ids[]" value="{{ $cls->id }}" id="chk_cls_{{ $cls->id }}" class="chkAllowedClass rounded text-[#1a4731] focus:ring-[#1a4731]">
                            {{ $cls->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Spesialisasi / Bidang Studi</label>
                <input type="text" name="spesialisasi" id="frmGuruSpesialisasi" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none transition" placeholder="Cth: Fiqih, Tahsin &amp; Tajwid">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Status Guru</label>
                <select name="status" id="frmGuruStatus" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] outline-none transition bg-white cursor-pointer font-semibold">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                    <option value="cuti">Cuti / Izin</option>
                </select>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                <p class="text-xs font-bold text-slate-700">Akun Login Aplikasi (Opsional):</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Username</label>
                        <input type="text" name="username" id="frmGuruUsername" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-[#1a4731] outline-none" placeholder="Opsional">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Password</label>
                        <input type="password" name="password" id="frmGuruPassword" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-[#1a4731] outline-none" placeholder="Min 6 karakter">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-600 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="frmGuruPasswordConfirmation" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-[#1a4731] outline-none" placeholder="Ulangi password">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-[#1a4731] text-white rounded-xl font-bold hover:bg-[#153c28] shadow-md shadow-green-900/20 transition">
                    Simpan Data Guru
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmation Hapus Guru -->
<div id="modalHapusGuru" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalHapus()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="font-bold text-lg text-slate-800 mb-1" id="delTitle">Hapus Data Guru?</h3>
        <p class="text-xs text-slate-500 mb-4">Tindakan ini akan menghapus data guru beserta akun login yang berelasi darinya.</p>

        <form id="formGuruDelete" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex gap-3">
                <button type="button" onclick="closeModalHapus()" class="flex-1 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-200 transition">
                    Batal
                </button>
                <button type="submit" id="btnConfirmDelete" class="flex-1 py-2.5 bg-red-600 text-white rounded-xl font-bold text-xs hover:bg-red-700 shadow-md shadow-red-900/20 transition">
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let dbGurus = @json($gurus ?? []);
    let guruData = Array.isArray(dbGurus) ? dbGurus : [];

    function updateStats() {
        const totalAktif = guruData.filter(g => g.status === 'aktif').length;
        const totalCuti = guruData.filter(g => g.status === 'cuti').length;

        let allAssignedClasses = new Set();
        guruData.forEach(g => {
            if(g.status === 'aktif') {
                if (g.classroom) allAssignedClasses.add(g.classroom.name);
                if (Array.isArray(g.classrooms)) {
                    g.classrooms.forEach(c => allAssignedClasses.add(c.name));
                }
            }
        });

        document.getElementById('statGuruAktif').textContent = totalAktif;
        document.getElementById('statGuruCuti').textContent = totalCuti;
        document.getElementById('statKelasTerampu').textContent = allAssignedClasses.size;
    }

    function renderGuruTable() {
        const query = (document.getElementById('guruSearchInput').value || '').toLowerCase();
        const fStatus = document.getElementById('guruStatusFilter').value;

        let filtered = guruData.filter(g => {
            const mQ = !query || (g.name && g.name.toLowerCase().includes(query)) || (g.nip && String(g.nip).toLowerCase().includes(query));
            const mS = !fStatus || g.status === fStatus;
            return mQ && mS;
        });

        filtered.sort((a,b) => a.name.localeCompare(b.name));

        const tbody = document.getElementById('guruTableBody');
        tbody.innerHTML = '';

        if(filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-slate-400 text-xs">Tidak ada data guru yang ditemukan. Database guru kosong.</td></tr>`;
            return;
        }

        filtered.forEach((g, idx) => {
            const num = idx + 1;
            const kelasIndukName = g.classroom ? g.classroom.name : (g.kelas || '-');

            // Render list allowed classrooms
            let allowedBadgesHtml = '';
            if (Array.isArray(g.classrooms) && g.classrooms.length > 0) {
                allowedBadgesHtml = g.classrooms.map(c => `<span class="px-2 py-0.5 bg-[#e6f4ec] text-[#1a4731] border border-green-200 rounded-md text-[11px] font-bold inline-block mr-1 mb-1">${c.name}</span>`).join('');
            } else if (g.classroom) {
                allowedBadgesHtml = `<span class="px-2 py-0.5 bg-[#e6f4ec] text-[#1a4731] border border-green-200 rounded-md text-[11px] font-bold inline-block mr-1 mb-1">${g.classroom.name}</span>`;
            } else {
                allowedBadgesHtml = '<span class="text-slate-400 italic text-xs">Semua Kelas</span>';
            }

            let statusHtml = '';
            if(g.status === 'aktif') statusHtml = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-green-700 bg-green-50 rounded-full border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif</span>`;
            else if(g.status === 'cuti') statusHtml = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-amber-700 bg-amber-50 rounded-full border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Cuti/Izin</span>`;
            else statusHtml = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold text-slate-500 bg-slate-100 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Nonaktif</span>`;

            let akunHtml = '';
            if(g.user) {
                const roleLabel = g.user.role === 'wali_kelas' ? 'Wali Kelas' : (g.user.role === 'supervisor' ? 'Supervisor' : 'Guru');
                akunHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200">🟢 Akun Aktif (${roleLabel})</span>`;
            } else {
                akunHtml = `<div class="flex items-center gap-1.5"><span class="px-2 py-0.5 text-[11px] font-medium text-slate-500 bg-slate-100 rounded-full">⚪ Belum ada akun</span><a href="{{ route("admin.setting-role") }}" class="text-[11px] font-bold text-[#1a4731] hover:underline">+ Akun</a></div>`;
            }

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition';
            tr.innerHTML = `
                <td class="px-5 py-4 text-center font-medium text-slate-500">${num}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(g.name)}&background=e2f3e9&color=1a4731&bold=true" class="w-9 h-9 rounded-full border border-slate-200 shrink-0">
                        <div>
                            <p class="font-bold text-slate-800">${g.name}</p>
                            <p class="text-[11px] text-slate-400 font-medium">HP: ${g.nomor_hp || '-'}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 text-xs font-mono text-slate-600">${g.nip || '-'}</td>
                <td class="px-5 py-4"><span class="font-bold text-slate-700">${kelasIndukName}</span></td>
                <td class="px-5 py-4">${allowedBadgesHtml}</td>
                <td class="px-5 py-4">${statusHtml}</td>
                <td class="px-5 py-4">${akunHtml}</td>
                <td class="px-5 py-4 text-center">
                    <button onclick="openModalEdit(${g.id})" class="text-slate-400 hover:text-blue-600 p-1 mr-1 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                    <button onclick="confirmHapusGuru(${g.id})" class="text-slate-400 hover:text-red-600 p-1 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function onRoleChange() {
        const role = document.getElementById('frmGuruRole').value;
        const blockKelasWali   = document.getElementById('blockKelasWali');
        const blockKelasDiajar = document.getElementById('blockKelasDiajar');
        const roleHelp         = document.getElementById('roleHelp');

        if (role === 'wali_kelas') {
            blockKelasWali.classList.remove('hidden');
            blockKelasDiajar.classList.add('hidden');
            roleHelp.textContent = 'Wali Kelas bertanggung jawab atas satu kelas utama.';
        } else {
            blockKelasWali.classList.add('hidden');
            blockKelasDiajar.classList.remove('hidden');
            roleHelp.textContent = 'Guru Biasa dapat mengajar beberapa kelas sesuai penugasan.';
        }
    }

    function openModalTambah() {
        document.getElementById('modalGuruTitle').textContent = 'Tambah Guru Baru';
        document.getElementById('formGuruAction').action = "{{ route('admin.guru.store') }}";
        document.getElementById('frmGuruMethod').value = 'POST';
        document.getElementById('frmGuruId').value = '';
        document.getElementById('frmGuruName').value = '';
        document.getElementById('frmGuruNip').value = '';
        document.getElementById('frmGuruHp').value = '';
        document.getElementById('frmGuruUsername').value = '';
        document.getElementById('frmGuruPassword').value = '';
        document.getElementById('frmGuruPasswordConfirmation').value = '';
        document.getElementById('frmGuruClassroomId').value = '';
        document.getElementById('frmGuruSpesialisasi').value = '';
        document.getElementById('frmGuruStatus').value = 'aktif';
        document.getElementById('frmGuruRole').value = 'guru';

        // Uncheck semua kelas
        document.querySelectorAll('.chkAllowedClass').forEach(c => c.checked = false);

        // Reset tampilan
        onRoleChange();

        document.getElementById('modalFormGuru').classList.remove('hidden');
    }

    function openModalEdit(id) {
        const g = guruData.find(x => x.id === id);
        if(!g) return;

        document.getElementById('modalGuruTitle').textContent = 'Edit Data Guru & Penugasan';
        document.getElementById('formGuruAction').action = "/admin/guru/" + g.id;
        document.getElementById('frmGuruMethod').value = 'PUT';
        document.getElementById('frmGuruId').value = g.id;
        document.getElementById('frmGuruName').value = g.name;
        document.getElementById('frmGuruNip').value = g.nip || '';
        document.getElementById('frmGuruHp').value = g.nomor_hp || '';
        document.getElementById('frmGuruUsername').value = g.user ? g.user.username : '';
        document.getElementById('frmGuruPassword').value = '';
        document.getElementById('frmGuruPasswordConfirmation').value = '';
        document.getElementById('frmGuruClassroomId').value = g.classroom_id || '';
        document.getElementById('frmGuruSpesialisasi').value = g.spesialisasi || '';
        document.getElementById('frmGuruStatus').value = g.status || 'aktif';

        // Set role berdasarkan akun user
        const userRole = g.user ? g.user.role : 'guru';
        document.getElementById('frmGuruRole').value = (userRole === 'wali_kelas') ? 'wali_kelas' : 'guru';

        // Check allowed checkboxes
        const allowedIds = Array.isArray(g.classrooms) ? g.classrooms.map(c => c.id) : [];
        document.querySelectorAll('.chkAllowedClass').forEach(c => {
            c.checked = allowedIds.includes(parseInt(c.value));
        });

        // Toggle tampilan berdasarkan role
        onRoleChange();

        document.getElementById('modalFormGuru').classList.remove('hidden');
    }

    function closeModalGuru() {
        document.getElementById('modalFormGuru').classList.add('hidden');
    }

    function confirmHapusGuru(id) {
        const g = guruData.find(x => x.id === id);
        if(!g) return;

        document.getElementById('delTitle').textContent = `Hapus ${g.name}?`;
        document.getElementById('formGuruDelete').action = "/admin/guru/" + g.id;

        document.getElementById('modalHapusGuru').classList.remove('hidden');
    }

    function closeModalHapus() {
        document.getElementById('modalHapusGuru').classList.add('hidden');
    }

    // Init
    onRoleChange();
    updateStats();
    renderGuruTable();
</script>
@endpush
