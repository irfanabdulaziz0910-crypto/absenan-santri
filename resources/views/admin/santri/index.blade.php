@extends('layouts.admin')
@section('title', 'Data Santri')
@section('breadcrumb', 'Data Santri')

@section('content')
<div class="mb-6 flex flex-col gap-4 md:flex-row md:justify-between md:items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Data Santri</h1>
        <p class="text-slate-500 text-sm mt-1">Seluruh data santri terintegrasi langsung dengan database.</p>
    </div>
    <div class="flex w-full flex-col gap-3 sm:flex-row md:w-auto">
        <button class="flex items-center justify-center gap-2 px-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-700 font-semibold shadow-sm hover:bg-slate-50 text-sm sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Export Data
        </button>
        <button onclick="openModalTambah()" class="flex items-center justify-center gap-2 px-4 py-3 bg-[#1a4731] text-white rounded-xl font-semibold shadow-sm hover:bg-[#153c28] text-sm sm:py-2.5">
            + Tambah Santri
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-5">
    {{-- Search & Filter Controls --}}
    <div class="flex flex-col gap-4 mb-5 md:flex-row">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" id="searchInput" oninput="handleSearch()" placeholder="Cari nama, NIS, atau RFID..." class="w-full pl-9 pr-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#1a4731]/20 focus:border-[#1a4731] outline-none transition sm:py-2.5">
        </div>
        <div class="flex flex-wrap gap-2 md:flex-nowrap">
            <select id="kelasFilter" class="min-h-[44px] w-full border border-slate-200 bg-slate-50 rounded-xl px-3 py-3 text-sm text-slate-600 outline-none cursor-pointer transition focus:border-[#1a4731] md:w-auto md:py-2.5" onchange="handleFilter()">
                <option value="Semua Kelas">Semua Kelas</option>
            </select>
            <select id="statusFilter" class="min-h-[44px] w-full border border-slate-200 bg-slate-50 rounded-xl px-3 py-3 text-sm text-slate-600 outline-none cursor-pointer transition focus:border-[#1a4731] md:w-auto md:py-2.5" onchange="handleFilter()">
                <option value="Semua Status">Semua Status</option>
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
            <button onclick="resetFilters()" class="min-h-[44px] w-[44px] border border-slate-200 bg-slate-50 rounded-xl hover:bg-slate-100 transition" title="Reset Filter">
                <svg class="w-4 h-4 text-slate-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    {{-- Main Santri Table (SATU HALAMAN TANPA PAGINATION) --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-slate-50 border-y border-slate-100 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                <tr>
                    <th class="px-5 py-3 text-center">No</th>
                    <th class="px-5 py-3">Santri</th>
                    <th class="px-5 py-3">NIS</th>
                    <th class="px-5 py-3">ID RFID</th>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3">Jenis Kelamin</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="santriTableBody">
                <!-- Dynamic Santri Rows Injected via JS -->
            </tbody>
        </table>
    </div>

    {{-- Total Counter Info (TANPA TOMBOL PAGINATION) --}}
    <div class="mt-4 flex flex-col md:flex-row justify-between items-center text-sm text-slate-500 gap-4" id="paginationInfo">
        <!-- Total count info injected via JS -->
    </div>
</div>

{{-- MODAL TAMBAH SANTRI --}}
<div id="modalTambah" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalTambah()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Tambah Santri</h3>
            <button type="button" onclick="closeModalTambah()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form onsubmit="saveSantri(event)" class="p-4 space-y-4 sm:p-6">
            @csrf
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Santri</label>
                <input type="text" id="frmName" required placeholder="Masukkan nama santri" class="w-full min-h-[44px] border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">NIS</label>
                    <input type="text" id="frmNis" required placeholder="Contoh: 230001" class="w-full min-h-[44px] border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jenis Kelamin</label>
                    <select id="frmJk" required class="w-full min-h-[44px] border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Kelas</label>
                <select id="frmKelas" required class="w-full min-h-[44px] border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer">
                    <!-- Options injected via JS -->
                </select>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">ID RFID</label>
                    <input type="text" id="frmRfid" required placeholder="Contoh: RF001" class="w-full min-h-[44px] border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Status</label>
                    <select id="frmStatus" required class="w-full min-h-[44px] border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full min-h-[48px] py-2.5 bg-[#1a4731] text-white rounded-xl font-bold mt-2 hover:bg-[#153c28] transition shadow-md shadow-green-900/20">Simpan Data Santri</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT SANTRI --}}
<div id="modalEdit" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModalEdit()"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-lg text-slate-800">Edit Santri</h3>
            <button type="button" onclick="closeModalEdit()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <form onsubmit="updateSantri(event)" class="p-6 space-y-4">
            <input type="hidden" id="editSantriId">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama Santri</label>
                <input type="text" id="editName" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">NIS</label>
                    <input type="text" id="editNis" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jenis Kelamin</label>
                    <select id="editJk" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Kelas</label>
                <select id="editKelas" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer">
                    <!-- Options injected via JS -->
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">ID RFID</label>
                    <input type="text" id="editRfid" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Status</label>
                    <select id="editStatus" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-white cursor-pointer">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-[#1a4731] text-white rounded-xl font-bold mt-2 hover:bg-[#153c28] transition shadow-md shadow-green-900/20">Perbarui Data Santri</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // DYNAMIC CLASSROOM STORE FROM DATABASE
    function getDynamicClassrooms() {
        const dbClasses = @json($kelasList ?? []);
        if (Array.isArray(dbClasses) && dbClasses.length > 0) {
            return dbClasses.map(c => ({ id: c.id, name: c.name }));
        }
        return [];
    }

    // 100% PURE DATABASE SANTRI SOURCE (SEMUA SANTRI TANPA LIMIT / PAGINATION)
    let dbSantris = @json($santris ?? []);
    let santriData = Array.isArray(dbSantris) ? dbSantris : [];

    function populateClassDropdowns() {
        const classrooms = getDynamicClassrooms();

        // Filter Kelas Dropdown
        const selectFilter = document.getElementById('kelasFilter');
        const currentFilterVal = selectFilter.value;
        selectFilter.innerHTML = '<option value="Semua Kelas">Semua Kelas</option>';
        classrooms.forEach(c => {
            selectFilter.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        });
        if (currentFilterVal && Array.from(selectFilter.options).some(o => o.value === currentFilterVal)) {
            selectFilter.value = currentFilterVal;
        }

        // Form Tambah Kelas Dropdown
        const selectFrm = document.getElementById('frmKelas');
        selectFrm.innerHTML = '';
        if (classrooms.length === 0) {
            selectFrm.innerHTML = '<option value="">Belum ada data kelas</option>';
        } else {
            classrooms.forEach(c => {
                selectFrm.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        }

        // Form Edit Kelas Dropdown
        const selectEdit = document.getElementById('editKelas');
        selectEdit.innerHTML = '';
        if (classrooms.length === 0) {
            selectEdit.innerHTML = '<option value="">Belum ada data kelas</option>';
        } else {
            classrooms.forEach(c => {
                selectEdit.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        }
    }

    function renderAllSantriData() {
        const q = document.getElementById('searchInput').value.trim().toLowerCase();
        const fKelas = document.getElementById('kelasFilter').value;
        const fStatus = document.getElementById('statusFilter').value;

        let filtered = santriData.filter(s => {
            let matchKelas = (fKelas === 'Semua Kelas' || String(s.classroom_id) === String(fKelas) || s.kelas === fKelas);
            let matchStatus = (fStatus === 'Semua Status' || s.status.toLowerCase() === fStatus.toLowerCase());
            let matchQ = q === '' ||
                         s.name.toLowerCase().includes(q) ||
                         (s.nis && String(s.nis).toLowerCase().includes(q)) ||
                         (s.rfid_barcode && String(s.rfid_barcode).toLowerCase().includes(q));

            return matchKelas && matchStatus && matchQ;
        });

        filtered.sort((a,b) => a.name.localeCompare(b.name));

        renderTable(filtered, fKelas);
        renderCounterInfo(filtered.length);
    }

    function renderTable(data, selectedKelasFilter) {
        const tbody = document.getElementById('santriTableBody');
        tbody.innerHTML = '';

        if (santriData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-slate-400 font-medium">Belum ada data santri</td></tr>`;
            return;
        }

        if (data.length === 0) {
            const emptyMsg = selectedKelasFilter !== 'Semua Kelas'
                ? 'Tidak ada santri pada kelas ini'
                : 'Tidak ada santri yang sesuai filter.';
            tbody.innerHTML = `<tr><td colspan="8" class="px-5 py-8 text-center text-slate-400 font-medium">${emptyMsg}</td></tr>`;
            return;
        }

        // TAMPILKAN SELURUH DATA SANTRI DALAM SATU HALAMAN
        data.forEach((s, i) => {
            const num = i + 1;
            const bgAvatar = s.jenis_kelamin === 'L' ? 'e2f3e9' : 'fdf4ff';
            const colAvatar = s.jenis_kelamin === 'L' ? '1a4731' : '86198f';
            const genderStr = s.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';

            const rfidTpl = s.rfid_barcode
                ? `<span class="inline-flex items-center gap-1.5 text-slate-600 font-semibold text-xs font-mono"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>${s.rfid_barcode}</span>`
                : `<span class="text-slate-400 italic text-xs flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>Belum Set</span>`;

            const statTpl = (s.status || 'Aktif').toLowerCase() === 'aktif'
                ? `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-green-200 text-green-700 bg-green-50"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif</span>`
                : `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-red-200 text-red-600 bg-red-50"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Nonaktif</span>`;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition';
            tr.innerHTML = `
                <td class="px-5 py-4 font-medium text-slate-500 text-center">${num}</td>
                <td class="px-5 py-4 flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=${bgAvatar}&color=${colAvatar}&bold=true" class="w-9 h-9 rounded-full object-cover shrink-0 border border-slate-200">
                    <div>
                        <p class="font-bold text-slate-800">${s.name}</p>
                        <p class="text-[10px] uppercase font-bold tracking-wide text-slate-400 mt-0.5">${genderStr}</p>
                    </div>
                </td>
                <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${s.nis}</td>
                <td class="px-5 py-4">${rfidTpl}</td>
                <td class="px-5 py-4"><span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold border border-slate-200 shadow-sm">${s.kelas}</span></td>
                <td class="px-5 py-4 text-xs font-semibold text-slate-600">${genderStr}</td>
                <td class="px-5 py-4">${statTpl}</td>
                <td class="px-5 py-4 text-center flex justify-center gap-2">
                    <button onclick="openModalEdit(${s.id})" class="p-1 text-slate-400 hover:text-blue-600 transition" title="Edit Santri">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                    <form action="/admin/santri/${s.id}" method="POST" onsubmit="return confirm('Yakin ingin menghapus santri ini?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-slate-400 hover:text-red-600 transition" title="Hapus Santri">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderCounterInfo(count) {
        const wrap = document.getElementById('paginationInfo');
        if (count === 0) {
            wrap.innerHTML = `<p class="text-xs text-slate-400 font-medium">Menampilkan 0 santri</p>`;
        } else {
            wrap.innerHTML = `<p class="text-xs text-slate-500 font-medium">Menampilkan 1–${count} dari ${count} santri</p>`;
        }
    }

    function handleSearch() {
        renderAllSantriData();
    }

    function handleFilter() {
        renderAllSantriData();
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('kelasFilter').value = 'Semua Kelas';
        document.getElementById('statusFilter').value = 'Semua Status';
        renderAllSantriData();
    }

    function openModalTambah() {
        populateClassDropdowns();
        document.getElementById('modalTambah').classList.remove('hidden');
        document.getElementById('frmName').focus();
    }

    function closeModalTambah() {
        document.getElementById('modalTambah').classList.add('hidden');
    }

    function saveSantri(e) {
        e.preventDefault();
        const classrooms = getDynamicClassrooms();
        const clsId = document.getElementById('frmKelas').value;
        const clsObj = classrooms.find(c => String(c.id) === String(clsId));

        const payload = {
            name: document.getElementById('frmName').value,
            nis: document.getElementById('frmNis').value,
            jenis_kelamin: document.getElementById('frmJk').value,
            classroom_id: clsId,
            rfid_barcode: document.getElementById('frmRfid').value,
            status: document.getElementById('frmStatus').value
        };

        fetch("{{ route('admin.santri.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Data santri gagal disimpan.');
            return data;
        })
        .then(data => {
            const newId = data.id || (santriData.length ? Math.max(...santriData.map(s => s.id)) + 1 : 1);
            const newS = {
                id: newId,
                name: payload.name,
                nis: payload.nis,
                jenis_kelamin: payload.jenis_kelamin,
                classroom_id: clsId,
                kelas: clsObj ? clsObj.name : 'Tanpa Kelas',
                rfid_barcode: payload.rfid_barcode,
                status: payload.status
            };
            santriData.unshift(newS);
            closeModalTambah();
            e.target.reset();
            showToast('Santri berhasil disimpan ke database!', 'success');
            renderAllSantriData();
        })
        .catch(() => {
            closeModalTambah();
            location.reload();
        });
    }

    function openModalEdit(id) {
        populateClassDropdowns();
        const s = santriData.find(item => item.id === id);
        if (!s) return;

        document.getElementById('editSantriId').value = s.id;
        document.getElementById('editName').value = s.name;
        document.getElementById('editNis').value = s.nis || '';
        document.getElementById('editJk').value = s.jenis_kelamin || 'L';
        document.getElementById('editRfid').value = s.rfid_barcode || '';
        document.getElementById('editStatus').value = s.status || 'Aktif';

        const selectEdit = document.getElementById('editKelas');
        if (s.classroom_id) {
            selectEdit.value = s.classroom_id;
        }

        document.getElementById('modalEdit').classList.remove('hidden');
    }

    function closeModalEdit() {
        document.getElementById('modalEdit').classList.add('hidden');
    }

    function updateSantri(e) {
        e.preventDefault();
        const id = parseInt(document.getElementById('editSantriId').value);
        const sIdx = santriData.findIndex(item => item.id === id);
        if (sIdx === -1) return;

        const classrooms = getDynamicClassrooms();
        const clsId = document.getElementById('editKelas').value;
        const clsObj = classrooms.find(c => String(c.id) === String(clsId));

        const updatedData = {
            name: document.getElementById('editName').value,
            nis: document.getElementById('editNis').value,
            jenis_kelamin: document.getElementById('editJk').value,
            classroom_id: clsId,
            rfid_barcode: document.getElementById('editRfid').value,
            status: document.getElementById('editStatus').value
        };

        const updateUrl = "{{ route('admin.santri.update', ':id') }}".replace(':id', id);

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'PUT',
                'Accept': 'application/json'
            },
            body: JSON.stringify(updatedData)
        })
        .then(async res => {
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                throw new Error(data.message || 'Data santri gagal diperbarui.');
            }
            return res;
        })
        .then(() => {
            santriData[sIdx].name = updatedData.name;
            santriData[sIdx].nis = updatedData.nis;
            santriData[sIdx].jenis_kelamin = updatedData.jenis_kelamin;
            santriData[sIdx].classroom_id = clsId;
            santriData[sIdx].kelas = clsObj ? clsObj.name : 'Tanpa Kelas';
            santriData[sIdx].rfid_barcode = updatedData.rfid_barcode;
            santriData[sIdx].status = updatedData.status;

            closeModalEdit();
            showToast('Data santri berhasil diperbarui ke database!', 'success');
            renderAllSantriData();
        })
        .catch(() => {
            closeModalEdit();
            location.reload();
        });
    }

    // Initial Render
    populateClassDropdowns();
    renderAllSantriData();
</script>
@endpush
