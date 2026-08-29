@extends('layouts.admin')
@section('title', 'Setting Role')
@section('breadcrumb', 'Setting Role')

@section('content')
<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Pengaturan Akun & Role</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola akses, peran pengguna, dan relasi akun dengan data guru existing.</p>
    </div>
    <div class="flex gap-3">
        <button onclick="openModal('add')" class="flex items-center gap-2 px-4 py-2 bg-[#1a4731] text-white rounded-xl font-semibold shadow-sm shadow-green-900/20 hover:bg-[#153c28] transition">
            + Tambah Akun
        </button>
    </div>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Administrator</p>
            <p class="text-3xl font-extrabold text-slate-800" id="countAdmin">0</p>
            <p class="text-xs text-slate-500 mt-1">Akses penuh sistem</p>
        </div>
        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Guru / Wali Kelas</p>
            <p class="text-3xl font-extrabold text-slate-800" id="countGuru">0</p>
            <p class="text-xs text-slate-500 mt-1">Mengelola absensi kelas & mengajar</p>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Supervisor</p>
            <p class="text-3xl font-extrabold text-slate-800" id="countSupervisor">0</p>
            <p class="text-xs text-slate-500 mt-1">Hanya melihat laporan</p>
        </div>
        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-5">
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative flex-1 md:w-80">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" id="searchInput" oninput="renderTable()" placeholder="Cari nama atau username..." class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#1a4731]/20 focus:border-[#1a4731] outline-none">
            </div>
            <button class="p-2 border border-slate-200 bg-slate-50 rounded-xl hover:bg-slate-100 text-slate-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg></button>
        </div>

        <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-x-auto">
            <button onclick="setFilterRole('Semua Role')" id="btnFilter-Semua Role" class="px-4 py-1.5 text-sm font-semibold rounded-lg bg-[#1a4731] text-white transition whitespace-nowrap">Semua Role</button>
            <button onclick="setFilterRole('Admin Utama')" id="btnFilter-Admin Utama" class="px-4 py-1.5 text-sm font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition whitespace-nowrap">Admin</button>
            <button onclick="setFilterRole('Guru')" id="btnFilter-Guru" class="px-4 py-1.5 text-sm font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition whitespace-nowrap">Guru</button>
            <button onclick="setFilterRole('Supervisor')" id="btnFilter-Supervisor" class="px-4 py-1.5 text-sm font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition whitespace-nowrap">Supervisor</button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-4">PENGGUNA</th>
                    <th class="px-5 py-4">ROLE</th>
                    <th class="px-5 py-4">KELAS INDUK / STATUS RELASI</th>
                    <th class="px-5 py-4">AKTIVITAS TERAKHIR</th>
                    <th class="px-5 py-4">STATUS AKUN</th>
                    <th class="px-5 py-4 text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="accountTableBody">
                <!-- Rows will be injected here via JS -->
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500 gap-4" id="paginationInfo">
        <!-- Pagination injected here -->
    </div>
</div>

<!-- Modal Form Tambah/Edit -->
<div id="modalForm" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 sticky top-0 bg-white z-10">
            <h3 class="font-bold text-lg text-slate-800" id="modalTitle">Tambah Akun</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form id="accountForm" class="p-6 space-y-4" onsubmit="saveAccount(event)">
            <input type="hidden" id="accountId">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select id="accRole" required onchange="handleRoleChange()" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer font-bold text-slate-700">
                        <option value="Admin Utama">Admin Utama</option>
                        <option value="Wali Kelas">Wali Kelas</option>
                        <option value="Guru">Guru (Tenaga Pendidik)</option>
                        <option value="Supervisor">Supervisor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Status Akun</label>
                    <select id="accStatus" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer font-bold text-slate-700">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div id="guruSelectContainer" class="hidden p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-200/80 space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-800">Pilih Data Guru Existing <span class="text-red-500">*</span></label>
                <select id="accGuruId" onchange="onGuruSelectChange()" class="w-full border border-emerald-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-[#1a4731] outline-none cursor-pointer">
                    <option value="">-- Pilih Guru dari Data Guru --</option>
                    @foreach ($gurus as $g)
                        <option value="{{ $g->id }}" data-name="{{ $g->name }}" data-has-class="{{ ($g->classroom_id || ($g->kelas && !in_array($g->kelas, ['Belum Assign', '']))) ? 'true' : 'false' }}" data-kelas="{{ $g->classroom?->name ?: ($g->kelas ?: 'Belum Assign') }}">
                            {{ $g->name }} (Kelas Induk: {{ $g->classroom?->name ?: ($g->kelas ?: 'Belum Assign') }})
                        </option>
                    @endforeach
                </select>
                <p id="guruNoticeText" class="text-[11px] text-emerald-700 leading-tight">Memilih guru dari data guru existing akan menghubungkan akun login ini ke data guru yang sudah ada tanpa membuat guru duplikat.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="accName" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Username <span class="text-red-500">*</span></label>
                <input type="text" id="accUsername" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password <span class="text-xs font-normal text-slate-400">(Kosongkan jika edit dan tidak diubah)</span></label>
                <input type="password" id="accPassword" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>

            <div id="kelasContainer" class="hidden">
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Kelas Induk / Penugasan</label>
                <input type="text" id="accKelas" placeholder="Contoh: Ma'had Ali" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-[#1a4731] text-white rounded-xl font-bold hover:bg-[#153c28] transition shadow-md shadow-green-900/20">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hubungkan Relasi Guru -->
<div id="modalLinkGuru" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeLinkModal()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="font-bold text-base text-slate-800">Hubungkan Akun ke Data Guru</h3>
                <p class="text-xs text-slate-500" id="linkAccountUserLabel">Pilih data guru existing untuk akun ini</p>
            </div>
            <button onclick="closeLinkModal()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form id="linkGuruForm" class="p-6 space-y-4" onsubmit="submitLinkGuru(event)">
            <input type="hidden" id="linkAccountId">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pilih Data Guru Existing <span class="text-red-500">*</span></label>
                <select id="linkGuruId" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-[#1a4731] outline-none">
                    <option value="">-- Pilih Guru Existing --</option>
                    @foreach ($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} (Kelas Induk: {{ $g->classroom?->name ?: ($g->kelas ?: 'Ma\'had Ali') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeLinkModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1a4731] text-white font-bold text-xs shadow-md">Simpan Relasi</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let accounts = @json($accounts ?? []);
    let gurusData = @json($gurus ?? []);
    let currentFilter = 'Semua Role';
    let currentPage = 1;
    const itemsPerPage = 10;

    function updateStats() {
        document.getElementById('countAdmin').textContent = accounts.filter(a => a.role === 'Admin Utama').length;
        document.getElementById('countGuru').textContent = accounts.filter(a => a.role === 'Guru').length;
        document.getElementById('countSupervisor').textContent = accounts.filter(a => a.role === 'Supervisor').length;
    }

    function setFilterRole(role) {
        currentFilter = role;
        currentPage = 1;

        const btns = ['Semua Role', 'Admin Utama', 'Guru', 'Supervisor'];
        btns.forEach(b => {
            const el = document.getElementById('btnFilter-' + b);
            if(b === role) {
                el.className = "px-4 py-1.5 text-sm font-semibold rounded-lg bg-[#1a4731] text-white transition whitespace-nowrap";
            } else {
                el.className = "px-4 py-1.5 text-sm font-semibold rounded-lg text-slate-500 hover:text-slate-700 transition whitespace-nowrap";
            }
        });

        renderTable();
    }

    function getFilteredData() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        return accounts.filter(acc => {
            const matchRole = currentFilter === 'Semua Role' || acc.role === currentFilter;
            const matchQuery = acc.name.toLowerCase().includes(query) || acc.username.toLowerCase().includes(query);
            return matchRole && matchQuery;
        });
    }

    function renderTable() {
        const filtered = getFilteredData();
        const totalItems = filtered.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginated = filtered.slice(start, end);

        const tbody = document.getElementById('accountTableBody');
        tbody.innerHTML = '';

        if (paginated.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Tidak ada data yang ditemukan.</td></tr>`;
        } else {
            paginated.forEach(acc => {
                let roleBadge = '';
                if(acc.role === 'Admin Utama') roleBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-md"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> Admin Utama</span>';
                if(acc.role === 'Guru') roleBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-green-700 bg-green-50 border border-green-200 rounded-md"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg> Guru</span>';
                if(acc.role === 'Supervisor') roleBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Supervisor</span>';

                let kelasBadges = '-';
                if (acc.role === 'Guru' || acc.role === 'Wali Kelas') {
                    if (acc.is_linked) {
                        const kText = (acc.kelas && acc.kelas.length > 0) ? acc.kelas.join(', ') : 'Belum Assign Kelas';
                        kelasBadges = `
                            <div class="flex flex-col items-start gap-1">
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded text-[11px] font-bold inline-flex items-center gap-1 whitespace-normal max-w-xs">
                                    ✓ ${kText}
                                </span>
                                <span class="text-[10px] text-slate-400 font-medium">${acc.role}: ${acc.guru_name || acc.name}</span>
                            </div>
                        `;
                    } else {
                        kelasBadges = `
                            <div class="flex flex-col items-start gap-1.5">
                                <span class="px-2 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded text-[10px] font-bold">
                                    ⚠️ Belum Terhubung Guru
                                </span>
                                <button type="button" onclick="openLinkModal('${acc.id}', '${acc.name.replace(/'/g, "\\'")}')" class="px-2.5 py-1 text-[11px] font-bold text-white bg-[#1a4731] hover:bg-[#143726] rounded-lg shadow-xs transition">
                                    🔗 Hubungkan Guru
                                </button>
                            </div>
                        `;
                    }
                } else if (acc.kelas && acc.kelas.length > 0) {
                    kelasBadges = acc.kelas.map(k => `<span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-[10px] font-bold mr-1 inline-block">${k}</span>`).join('');
                }

                let statusDot = acc.status === 'Aktif' ? '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>' : '<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>';
                let statusText = acc.status === 'Aktif' ? 'text-emerald-700' : 'text-red-600';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/50 transition';
                tr.innerHTML = `
                    <td class="px-5 py-4 flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(acc.name)}&background=e2f3e9&color=1a4731&bold=true" class="w-9 h-9 rounded-full object-cover">
                        <div>
                            <p class="font-bold text-slate-800">${acc.name}</p>
                            <p class="text-xs text-slate-400">${acc.username}</p>
                        </div>
                    </td>
                    <td class="px-5 py-4">${roleBadge}</td>
                    <td class="px-5 py-4">${kelasBadges}</td>
                    <td class="px-5 py-4 text-xs font-medium text-slate-500">${acc.last_active}</td>
                    <td class="px-5 py-4 font-semibold text-xs ${statusText} flex items-center gap-1.5 mt-2">${statusDot} ${acc.status}</td>
                    <td class="px-5 py-4 text-center">
                        <button onclick="openModal('edit', '${acc.id}')" title="Edit Akun" class="text-slate-400 hover:text-blue-500 mr-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                        <button onclick="deleteAccount('${acc.id}')" title="Hapus Akun" class="text-slate-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        updateStats();
        renderPagination(totalItems, start, end);
    }

    function renderPagination(total, start, end) {
        const pag = document.getElementById('paginationInfo');
        if (total === 0) {
            pag.innerHTML = '<p>Menampilkan 0 data</p>';
            return;
        }

        const totalPages = Math.ceil(total / itemsPerPage);
        let currentEnd = end > total ? total : end;

        let html = `<p>Menampilkan ${start + 1}-${currentEnd} dari ${total} akun</p><div class="flex gap-1">`;
        html += `<button onclick="goToPage(${currentPage - 1})" class="w-8 h-8 flex items-center justify-center rounded text-slate-400 hover:bg-slate-50" ${currentPage === 1 ? 'disabled' : ''}>&lsaquo;</button>`;

        for(let i=1; i<=totalPages; i++) {
            if (i === currentPage) {
                html += `<button class="w-8 h-8 flex items-center justify-center rounded bg-[#1a4731] text-white font-bold">${i}</button>`;
            } else {
                html += `<button onclick="goToPage(${i})" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-50 font-medium">${i}</button>`;
            }
        }

        html += `<button onclick="goToPage(${currentPage + 1})" class="w-8 h-8 flex items-center justify-center rounded text-slate-400 hover:bg-slate-50" ${currentPage === totalPages ? 'disabled' : ''}>&rsaquo;</button></div>`;
        pag.innerHTML = html;
    }

    function goToPage(page) {
        const total = getFilteredData().length;
        const totalPages = Math.ceil(total / itemsPerPage);
        if(page < 1 || page > totalPages) return;
        currentPage = page;
        renderTable();
    }

    function handleRoleChange() {
        const role = document.getElementById('accRole').value;
        const kelasContainer = document.getElementById('kelasContainer');
        const guruContainer = document.getElementById('guruSelectContainer');
        const guruNoticeText = document.getElementById('guruNoticeText');
        const inputKelas = document.getElementById('accKelas');

        if (role === 'Wali Kelas' || role === 'Guru') {
            guruContainer.classList.remove('hidden');
            kelasContainer.classList.remove('hidden');
            if (role === 'Wali Kelas') {
                if (guruNoticeText) guruNoticeText.textContent = "Role Wali Kelas HARUS terhubung dengan data guru yang sudah memiliki penugasan kelas.";
            } else {
                if (guruNoticeText) guruNoticeText.textContent = "Role Guru digunakan untuk fitur absensi mengajar, pencatatan kitab, materi, & riwayat pengajaran.";
            }
            if (inputKelas.value === 'Semua Kelas' || inputKelas.value === '-') inputKelas.value = '';
        } else if (role === 'Supervisor') {
            kelasContainer.classList.add('hidden');
            guruContainer.classList.add('hidden');
            inputKelas.value = 'Semua Kelas';
        } else {
            kelasContainer.classList.add('hidden');
            guruContainer.classList.add('hidden');
            inputKelas.value = '-';
        }
    }

    function onGuruSelectChange() {
        const select = document.getElementById('accGuruId');
        const selected = select.options[select.selectedIndex];
        if (selected && selected.value) {
            const name = selected.getAttribute('data-name');
            const kelas = selected.getAttribute('data-kelas');
            const hasClass = selected.getAttribute('data-has-class');
            const role = document.getElementById('accRole').value;

            if (name) document.getElementById('accName').value = name;
            if (kelas) document.getElementById('accKelas').value = kelas;

            if (role === 'Wali Kelas' && hasClass === 'false') {
                showToast('Peringatan: Guru ini belum memiliki kelas yang dapat digunakan sebagai Wali Kelas.', 'warning');
            }
        }
    }

    function openModal(mode, id = null) {
        const modal = document.getElementById('modalForm');
        const form = document.getElementById('accountForm');
        form.reset();

        if (mode === 'add') {
            document.getElementById('modalTitle').textContent = 'Tambah Akun Baru';
            document.getElementById('accountId').value = '';
            document.getElementById('accPassword').required = true;
            document.getElementById('accRole').value = 'Wali Kelas';
            handleRoleChange();
        } else {
            document.getElementById('modalTitle').textContent = 'Edit Akun';
            const acc = accounts.find(a => a.id === id);
            if (acc) {
                document.getElementById('accountId').value = acc.id;
                document.getElementById('accName').value = acc.name;
                document.getElementById('accUsername').value = acc.username;
                document.getElementById('accRole').value = acc.role;
                document.getElementById('accStatus').value = acc.status;
                document.getElementById('accKelas').value = acc.kelas.join(', ');
                document.getElementById('accPassword').required = false;
                if (acc.guru_id) {
                    document.getElementById('accGuruId').value = acc.guru_id;
                }
                handleRoleChange();
            }
        }
        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modalForm').classList.add('hidden');
    }

    function openLinkModal(accountId, userName) {
        document.getElementById('linkAccountId').value = accountId;
        document.getElementById('linkAccountUserLabel').textContent = 'Menghubungkan akun: ' + userName;
        document.getElementById('linkGuruId').value = '';
        document.getElementById('modalLinkGuru').classList.remove('hidden');
    }

    function closeLinkModal() {
        document.getElementById('modalLinkGuru').classList.add('hidden');
    }

    function submitLinkGuru(e) {
        e.preventDefault();
        const accountId = document.getElementById('linkAccountId').value;
        const guruId = document.getElementById('linkGuruId').value;
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!guruId) {
            showToast('Pilih guru terlebih dahulu', 'error');
            return;
        }

        const route = "{{ route('admin.setting-role.link-guru', ['account' => '__ACCOUNT__']) }}".replace('__ACCOUNT__', encodeURIComponent(accountId));

        fetch(route, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ guru_id: guruId })
        }).then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || 'Gagal menghubungkan guru.');
            }
            closeLinkModal();
            showToast(data.message || 'Akun berhasil terhubung dengan data guru.', 'success');
            setTimeout(() => window.location.reload(), 500);
        }).catch(err => {
            showToast(err.message || 'Gagal menghubungkan guru.', 'error');
        });
    }

    function saveAccount(e) {
        e.preventDefault();

        const id = document.getElementById('accountId').value;
        const name = document.getElementById('accName').value.trim();
        const username = document.getElementById('accUsername').value.trim();
        const role = document.getElementById('accRole').value;
        const status = document.getElementById('accStatus').value;
        const kelasStr = document.getElementById('accKelas').value.trim();
        const guruId = document.getElementById('accGuruId').value;
        const password = document.getElementById('accPassword').value;
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if ((role === 'Wali Kelas' || role === 'Guru') && !guruId) {
            showToast('Silakan pilih data guru yang sudah ada dari daftar.', 'error');
            return;
        }

        let kelas = [];
        if ((role === 'Wali Kelas' || role === 'Guru') && kelasStr) {
            kelas = kelasStr.split(',').map(s => s.trim()).filter(s => s);
        } else if (role === 'Supervisor') {
            kelas = ['Semua Kelas'];
        }

        const payload = {
            name,
            username,
            role,
            status,
            kelas: kelas.join(', '),
            guru_id: guruId || undefined,
            password: password || undefined,
        };

        const route = id
            ? "{{ route('admin.setting-role.update', ['account' => '__ACCOUNT__']) }}".replace('__ACCOUNT__', encodeURIComponent(id))
            : "{{ route('admin.setting-role.store') }}";

        fetch(route, {
            method: id ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = data.message || 'Gagal menyimpan akun.';
                throw new Error(message);
            }

            closeModal();
            showToast(id ? 'Akun berhasil diperbarui.' : 'Akun berhasil ditambahkan.', 'success');
            setTimeout(() => window.location.reload(), 500);
        }).catch(err => {
            showToast(err.message || 'Gagal menyimpan akun.', 'error');
        });
    }

    function deleteAccount(id) {
        const message = 'Apakah Anda yakin ingin menghapus akun ini?\n\nTindakan ini hanya akan menghapus akun yang dipilih dan tidak akan menghapus data guru, santri, kelas, maupun data absensi.';

        if (confirm(message)) {
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const route = "{{ route('admin.setting-role.destroy', ['account' => '__ACCOUNT__']) }}".replace('__ACCOUNT__', encodeURIComponent(id));

            fetch(route, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menghapus akun.');
                }
                showToast('Akun berhasil dihapus.', 'success');
                setTimeout(() => window.location.reload(), 500);
            }).catch(err => {
                showToast(err.message || 'Gagal menghapus akun.', 'error');
            });
        }
    }

    renderTable();
</script>
@endpush

