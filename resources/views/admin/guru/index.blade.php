@extends('layouts.admin')
@section('title', 'Data Guru')
@section('breadcrumb', 'Data Guru')

@section('content')
@if(session('success'))
<div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between shadow-sm">
    <span>✅ {{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">✕</button>
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs font-semibold flex items-center justify-between shadow-sm">
    <span>⚠️ {{ session('error') }}</span>
    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 font-bold">✕</button>
</div>
@endif

<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Master Data Guru & Penugasan Mengajar</h1>
        <p class="text-slate-500 text-sm mt-1">Pusat pengelolaan identitas guru, penugasan kelas, dan pembuat kode pendaftaran akun.</p>
    </div>
    <div class="flex gap-2 items-center flex-wrap">
        <div class="relative w-64 hidden md:block">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" id="guruSearchInput" oninput="renderGuruTable()" placeholder="Cari nama atau NIP..." class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-slate-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1a4731]/20">
        </div>
        <button onclick="openModalImportPdf()" class="px-4 py-2.5 bg-amber-600 text-white rounded-xl font-semibold text-xs shadow-sm hover:bg-amber-700 transition whitespace-nowrap cursor-pointer flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
            Import / Preview PDF
        </button>
        <button onclick="openModalTambah()" class="px-4 py-2.5 bg-[#1a4731] text-white rounded-xl font-semibold text-xs shadow-sm hover:bg-[#153c28] transition whitespace-nowrap cursor-pointer">
            + Tambah Guru Manual
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
        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
        </div>
        <div class="text-right">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Akun Terdaftar</p>
            <p class="text-3xl font-extrabold text-slate-800" id="statAkunTerdaftar">0</p>
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
                    <th class="px-5 py-4">NAMA GURU</th>
                    <th class="px-5 py-4">ID / KODE GURU</th>
                    <th class="px-5 py-4">STATUS AKUN</th>
                    <th class="px-5 py-4">PENUGASAN</th>
                    <th class="px-5 py-4">KELAS</th>
                    <th class="px-5 py-4 text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="guruTableBody">
                <!-- Rows injected via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Import & Preview PDF -->
<div id="modalImportPdf" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalImportPdf()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-1.5rem)] sm:w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-lg text-slate-800">Import & Verifikasi PDF Pengajar</h3>
            <button onclick="closeModalImportPdf()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.guru.preview-pdf') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Pilih File PDF Daftar Pengajar <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center bg-slate-50 hover:bg-slate-100 transition cursor-pointer relative">
                    <input type="file" name="pdf_file" id="pdfFileInput" accept=".pdf,application/pdf"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required onchange="displaySelectedPdfName(this)">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-2">
                            📄
                        </div>
                        <p class="font-bold text-slate-700 text-sm" id="txtPdfFileName">📁 PILIH FILE PDF</p>
                        <p class="text-xs text-slate-400 mt-1">Format: .pdf (Maksimal 10MB)</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModalImportPdf()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#1a4731] text-white rounded-xl text-xs font-bold shadow-md hover:bg-[#153c28] flex items-center gap-2">
                    Upload & Preview Hasil →
                </button>
            </div>
        </form>
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
            <input type="hidden" name="guru_id" id="frmGuruId" value="">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Lengkap Guru <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="frmGuruName" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#1a4731]">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">NIP / ID Guru</label>
                    <input type="text" name="nip" id="frmGuruNip" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#1a4731]">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor HP / WhatsApp</label>
                    <input type="text" name="nomor_hp" id="frmGuruHp" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#1a4731]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Keaktifan <span class="text-red-500">*</span></label>
                <select name="status" id="frmGuruStatus" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                    <option value="cuti">Cuti / Izin</option>
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100">
                <label class="block text-xs font-bold uppercase tracking-wider text-[#1a4731] mb-2">Penugasan & Hak Akses Admin</label>
                <select name="role" id="frmGuruRole" onchange="onRoleChange()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800">
                    <option value="guru">Guru Pendamping</option>
                    <option value="wali_kelas">Wali Kelas</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1" id="roleHelp"></p>
            </div>

            <div id="blockKelasWali" class="hidden">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pilih Kelas Wali <span class="text-red-500">*</span></label>
                <select name="classroom_id" id="frmGuruClassroomId" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700">
                    <option value="">-- Pilih Kelas Wali --</option>
                    @foreach($kelasList as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-amber-600 mt-1">⚠️ Setiap kelas hanya boleh memiliki 1 Wali Kelas aktif.</p>
            </div>

            <div id="blockKelasDiajar">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pilih Kelas yang Dapat Diajar (Pendamping)</label>
                <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-1">
                    @foreach($kelasList as $c)
                        <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer hover:bg-slate-100 p-1 rounded">
                            <input type="checkbox" name="allowed_classroom_ids[]" value="{{ $c->id }}" class="chkAllowedClass w-4 h-4 text-[#1a4731] rounded accent-[#1a4731]">
                            <span>{{ $c->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModalGuru()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#1a4731] text-white rounded-xl text-xs font-bold shadow-md hover:bg-[#153c28]">Simpan Data Guru</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Guru -->
<div id="modalHapusGuru" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalHapus()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100%-1.5rem)] sm:w-full max-w-sm bg-white rounded-2xl p-6 text-center shadow-2xl">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="font-bold text-slate-800 text-base" id="delTitle">Hapus Guru?</h3>
        <p class="text-xs text-slate-500 mt-1 mb-4">Tindakan ini tidak dapat dibatalkan jika guru tidak memiliki relasi absensi/jadwal.</p>
        <form id="formGuruDelete" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex gap-2">
                <button type="button" onclick="closeModalHapus()" class="w-1/2 py-2.5 bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl">Batal</button>
                <button type="submit" class="w-1/2 py-2.5 bg-red-600 text-white font-bold text-xs rounded-xl hover:bg-red-700">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let dbGurus = @json($gurus ?? []);
    let guruData = Array.isArray(dbGurus) ? dbGurus : [];

    function updateStats() {
        const totalAktif = guruData.filter(g => g.status === 'aktif').length;
        const totalAkun = guruData.filter(g => g.user || (g.user_id && g.user)).length;

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
        document.getElementById('statAkunTerdaftar').textContent = totalAkun;
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
            tbody.innerHTML = `<tr><td colspan="7" class="px-5 py-8 text-center text-slate-400 text-xs">Tidak ada data guru yang ditemukan.</td></tr>`;
            return;
        }

        filtered.forEach((g, idx) => {
            const num = idx + 1;

            let penugasanBadge = '';
            let kelasInfo = '';

            if (g.classroom) {
                penugasanBadge = `<span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[11px] font-bold rounded-full border border-emerald-200">Wali Kelas</span>`;
                kelasInfo = `<span class="font-bold text-slate-800">${g.classroom.name}</span>`;
            } else {
                penugasanBadge = `<span class="px-2.5 py-1 bg-blue-100 text-blue-800 text-[11px] font-bold rounded-full border border-blue-200">Pendamping</span>`;
                if (Array.isArray(g.classrooms) && g.classrooms.length > 0) {
                    kelasInfo = g.classrooms.map(c => `<span class="px-2 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-semibold rounded mr-1 mb-1 inline-block">${c.name}</span>`).join('');
                } else {
                    kelasInfo = `<span class="text-slate-400 italic text-xs">Bebas Kelas</span>`;
                }
            }

            let akunHtml = '';
            const userAccount = g.user || (g.user_id ? { username: 'Terdaftar' } : null);

            if (userAccount) {
                akunHtml = `<div>
                    <span class="px-2.5 py-1 text-xs font-bold text-emerald-800 bg-emerald-50 rounded-full border border-emerald-200 block w-max mb-1">
                        Sudah Terdaftar
                    </span>
                    <span class="text-[10px] font-mono text-slate-500">User: ${userAccount.username || '-'}</span>
                </div>`;
            } else {
                let codeBtnHtml = '';
                if (g.registration_code) {
                    codeBtnHtml = `<div class="mt-1">
                        <span class="font-mono text-xs font-bold px-2 py-1 bg-amber-50 text-amber-800 border border-amber-300 rounded inline-block">
                            Kode: ${g.registration_code}
                        </span>
                    </div>`;
                } else {
                    codeBtnHtml = `<form action="/admin/guru/${g.id}/generate-code" method="POST" class="mt-1 inline-block">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 font-bold text-[10px] rounded-lg transition shadow-2xs">
                            + BUAT KODE PENDAFTARAN
                        </button>
                    </form>`;
                }

                akunHtml = `<div>
                    <span class="px-2.5 py-1 text-xs font-semibold text-slate-500 bg-slate-100 rounded-full inline-block">
                        Belum Terdaftar
                    </span>
                    ${codeBtnHtml}
                </div>`;
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
                <td class="px-5 py-4 text-xs font-mono text-slate-600">${g.nip || ('ID-' + g.id)}</td>
                <td class="px-5 py-4">${akunHtml}</td>
                <td class="px-5 py-4">${penugasanBadge}</td>
                <td class="px-5 py-4">${kelasInfo}</td>
                <td class="px-5 py-4 text-center">
                    <button onclick="openModalEdit(${g.id})" class="text-slate-400 hover:text-blue-600 p-1 mr-1 transition" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                    <button onclick="confirmHapusGuru(${g.id})" class="text-slate-400 hover:text-red-600 p-1 transition" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
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
            roleHelp.textContent = 'Wali Kelas bertanggung jawab atas satu kelas utama (Maksimal 1 per kelas).';
        } else {
            blockKelasWali.classList.add('hidden');
            blockKelasDiajar.classList.remove('hidden');
            roleHelp.textContent = 'Guru Pendamping dapat mengajar beberapa kelas sesuai penugasan Admin.';
        }
    }

    function openModalImportPdf() {
        document.getElementById('modalImportPdf').classList.remove('hidden');
    }

    function closeModalImportPdf() {
        document.getElementById('modalImportPdf').classList.add('hidden');
    }

    function openModalTambah() {
        document.getElementById('modalGuruTitle').textContent = 'Tambah Guru Baru';
        document.getElementById('formGuruAction').action = "{{ route('admin.guru.store') }}";
        document.getElementById('frmGuruMethod').value = 'POST';
        document.getElementById('frmGuruId').value = '';
        document.getElementById('frmGuruName').value = '';
        document.getElementById('frmGuruNip').value = '';
        document.getElementById('frmGuruHp').value = '';
        document.getElementById('frmGuruClassroomId').value = '';
        document.getElementById('frmGuruStatus').value = 'aktif';
        document.getElementById('frmGuruRole').value = 'guru';

        document.querySelectorAll('.chkAllowedClass').forEach(c => c.checked = false);
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
        document.getElementById('frmGuruClassroomId').value = g.classroom_id || '';
        document.getElementById('frmGuruStatus').value = g.status || 'aktif';

        const isWali = g.classroom_id ? true : false;
        document.getElementById('frmGuruRole').value = isWali ? 'wali_kelas' : 'guru';

        const allowedIds = Array.isArray(g.classrooms) ? g.classrooms.map(c => c.id) : [];
        document.querySelectorAll('.chkAllowedClass').forEach(c => {
            c.checked = allowedIds.includes(parseInt(c.value));
        });

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

    function displaySelectedPdfName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('txtPdfFileName').textContent = '📄 ' + input.files[0].name;
        }
    }

    // Init
    onRoleChange();
    updateStats();
    renderGuruTable();
</script>
@endpush
@endsection
