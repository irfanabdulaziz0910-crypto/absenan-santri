@extends('layouts.wali-kelas')
@section('title', 'Data Santri Wali Kelas')

@section('content')
<div class="mb-6 flex flex-col md:flex-row justify-between md:items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Data Santri {{ $assignedKelas ? 'Kelas ' . $assignedKelas : 'Wali Kelas' }}</h1>
        <p class="text-slate-500 text-sm mt-1">Daftar seluruh {{ count($santris) }} santri yang menjadi tanggung jawab Anda dalam satu halaman.</p>
    </div>
    <div class="flex gap-3">
        <div class="relative w-full md:w-72">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" id="santriSearch" oninput="renderSantriTable()" placeholder="Cari nama, NIS, atau RFID..." class="w-full pl-9 pr-4 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#1a4731]/20 focus:border-[#1a4731] outline-none shadow-sm">
        </div>
        <button onclick="showToast('Fitur Tambah Santri dikelola oleh Administrator.', 'error')" class="w-10 h-10 bg-[#1a4731] text-white rounded-xl font-bold flex items-center justify-center shadow-md shadow-green-900/20 hover:bg-[#153c28] transition shrink-0 text-xl" title="Tambah Santri (Admin Only)">
            +
        </button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-4 w-12 text-center">NO</th>
                    <th class="px-5 py-4">SANTRI</th>
                    <th class="px-5 py-4">NIS</th>
                    <th class="px-5 py-4">RFID BARCODE</th>
                    <th class="px-5 py-4">KELAS</th>
                    <th class="px-5 py-4">STATUS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="santriTableBody">
                <!-- Data injected via JS -->
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <p id="santriCountLabel">Menampilkan {{ count($santris) }} santri (Satu Halaman)</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const targetKelas = @json($assignedKelas ?? '');
    let santriKelas = @json($santris ?? []);

    function renderSantriTable() {
        const query = (document.getElementById('santriSearch').value || '').toLowerCase();

        let filtered = santriKelas.filter(s => {
            return !query ||
                   (s.name && s.name.toLowerCase().includes(query)) ||
                   (s.nis && String(s.nis).toLowerCase().includes(query)) ||
                   (s.rfid_barcode && String(s.rfid_barcode).toLowerCase().includes(query));
        });

        filtered.sort((a,b) => a.name.localeCompare(b.name));

        const tbody = document.getElementById('santriTableBody');
        tbody.innerHTML = '';

        if(filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-8 text-center text-slate-400 text-xs">Tidak ada data santri yang ditemukan.</td></tr>`;
            document.getElementById('santriCountLabel').textContent = 'Menampilkan 0 santri';
            return;
        }

        document.getElementById('santriCountLabel').textContent = `Menampilkan seluruh ${filtered.length} santri dalam 1 halaman`;

        filtered.forEach((s, idx) => {
            const num = idx + 1;
            const bgAvatar = (s.jenis_kelamin === 'L' || !s.jenis_kelamin) ? 'e2f3e9' : 'fdf4ff';
            const colAvatar = (s.jenis_kelamin === 'L' || !s.jenis_kelamin) ? '1a4731' : '86198f';
            const genderStr = (s.jenis_kelamin === 'P') ? 'Perempuan' : 'Laki-laki';

            const rfidText = s.rfid_barcode ? `<span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">${s.rfid_barcode}</span>` : '<span class="text-slate-400 italic text-xs">Belum Aktif</span>';
            const statusBadge = `<span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-50 text-green-700 border border-green-200 inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif</span>`;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 transition';
            tr.innerHTML = `
                <td class="px-5 py-4 text-center font-medium text-slate-500">${String(num).padStart(2, '0')}</td>
                <td class="px-5 py-4 flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=${bgAvatar}&color=${colAvatar}&bold=true" class="w-8 h-8 rounded-full border border-slate-200 shrink-0">
                    <div>
                        <p class="font-bold text-slate-800">${s.name}</p>
                        <p class="text-[10px] text-slate-400 font-semibold">${genderStr}</p>
                    </div>
                </td>
                <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${s.nis}</td>
                <td class="px-5 py-4">${rfidText}</td>
                <td class="px-5 py-4 font-semibold text-slate-700 text-xs">${s.kelas || targetKelas}</td>
                <td class="px-5 py-4">${statusBadge}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    renderSantriTable();
</script>
@endpush

