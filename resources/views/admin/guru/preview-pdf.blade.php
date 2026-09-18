@extends('layouts.admin')

@section('title', 'Preview & Verifikasi Ekstraksi PDF Guru')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Verifikasi & Koreksi Data Pengajar (PDF)</h1>
            <p class="text-slate-500 text-sm mt-1">Admin dapat mengedit nama, memilih data yang akan dimasukkan, atau menghapus baris sebelum disimpan ke Master Data Guru.</p>
        </div>
        <a href="{{ route('admin.guru.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold rounded-xl text-sm inline-flex items-center gap-2">
            ← Kembali ke Master Data Guru
        </a>
    </div>

    {{-- Info Rules Box --}}
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-sm shrink-0">!</div>
        <div class="text-xs text-amber-900 space-y-1 leading-relaxed">
            <p class="font-bold text-sm">Petunjuk Verifikasi Admin:</p>
            <p>1. <strong>Edit Nama</strong>: Ubah teks nama di dalam kotak jika terdapat kesalahan pembacaan PDF (misal: "Sudaryanto" diubah menjadi "Ust. Sudaryanto").</p>
            <p>2. <strong>Potensi Duplikat</strong>: Sistem menandai nama yang mirip dengan Data Guru existing. Centang hanya jika orang tersebut benar-benar guru baru.</p>
            <p>3. <strong>Pilih Data</strong>: Berikan tanda centang pada nama yang valid untuk dimasukkan ke Master Data Guru.</p>
        </div>
    </div>

    <form action="{{ route('admin.guru.import-pdf') }}" method="POST">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Total Ekstraksi: {{ count($previewData) }} Pengajar</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="selectAll(true)" class="text-xs font-bold text-[#1a4731] hover:underline">Pilih Semua Siap Import</button>
                    <span class="text-slate-300">|</span>
                    <button type="button" onclick="selectAll(false)" class="text-xs font-bold text-slate-500 hover:underline">Batalkan Semua</button>
                </div>
            </div>

            <div class="divide-y divide-slate-100" id="previewRowsContainer">
                @foreach($previewData as $idx => $item)
                    <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50 transition preview-row" id="row_{{ $idx }}">
                        <div class="flex items-center gap-3 flex-1">
                            <input type="checkbox" name="names[]" value="{{ $item['original'] }}" id="chk_{{ $idx }}"
                                {{ $item['status'] === 'aman' ? 'checked' : '' }}
                                class="row-checkbox w-5 h-5 text-[#1a4731] rounded border-slate-300 focus:ring-[#1a4731] accent-[#1a4731] shrink-0">
                            
                            <div class="flex-1 space-y-1">
                                <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Koreksi Nama Guru (Bisa Diedit):</label>
                                <input type="text" class="row-name-input w-full px-3 py-1.5 bg-slate-50 border border-slate-200 focus:border-[#1a4731] focus:bg-white rounded-xl text-sm font-bold text-slate-800"
                                    value="{{ $item['original'] }}"
                                    onchange="updateCheckboxValue({{ $idx }}, this.value)">
                                <p class="text-xs text-slate-500">{{ $item['note'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 justify-between md:justify-end">
                            @if($item['status'] === 'duplikat')
                                <span class="px-3 py-1.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                    ⚠️ Potensi Duplikat
                                </span>
                            @elseif($item['status'] === 'verifikasi')
                                <span class="px-3 py-1.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-900 border border-blue-300">
                                    🔍 Perlu Verifikasi
                                </span>
                            @else
                                <span class="px-3 py-1.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    ✓ Siap Ditambahkan
                                </span>
                            @endif

                            <button type="button" onclick="removeRow('row_{{ $idx }}')" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Abaikan / Hapus Baris Ini">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="{{ route('admin.guru.index') }}" class="text-xs font-bold text-slate-500 hover:underline">
                    ← Batalkan Import & Kembali
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-[#1a4731] hover:bg-[#153c28] text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    SIMPAN DATA DIPILIH KE MASTER DATA GURU
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function updateCheckboxValue(idx, newValue) {
        const chk = document.getElementById('chk_' + idx);
        if (chk) {
            chk.value = newValue;
        }
    }

    function removeRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    }

    function selectAll(checked) {
        document.querySelectorAll('.row-checkbox').forEach(el => {
            el.checked = checked;
        });
    }
</script>
@endsection
