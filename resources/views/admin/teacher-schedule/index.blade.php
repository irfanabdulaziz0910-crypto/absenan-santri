@extends('layouts.admin')

@section('title', 'Jadwal Mengajar Guru')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Jadwal Mengajar Guru</h1>
            <p class="text-sm text-slate-500 mt-1">Master jadwal mengajar harian yang menjadi acuan otomatis absensi guru setiap minggunya.</p>
        </div>
        <button onclick="openModal('modal-tambah')"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#1a4731] hover:bg-[#143726] text-white text-sm font-bold rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Tambah Jadwal
        </button>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.teacher-schedule.index') }}"
          class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Guru</label>
            <select name="guru_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none">
                <option value="">Semua Guru</option>
                @foreach($gurus as $g)
                    <option value="{{ $g->id }}" @selected(request('guru_id') == $g->id)>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Hari</label>
            <select name="hari" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none">
                <option value="">Semua Hari</option>
                @foreach($hariList as $hari)
                    <option value="{{ $hari }}" @selected(request('hari') === $hari)>{{ $hari }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Sesi</label>
            <select name="session" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none">
                <option value="">Semua Sesi</option>
                @foreach($sessions as $s)
                    <option value="{{ $s }}" @selected(request('session') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-[#1a4731] text-white text-sm font-bold rounded-xl hover:bg-[#143726] transition">Filter</button>
            <a href="{{ route('admin.teacher-schedule.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-200 transition">Reset</a>
        </div>
    </form>

    {{-- Informasi Sesi --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
        $sessionInfo = [
            'Subuh'  => ['jam' => '04:00 – 08:00', 'color' => 'bg-purple-50 border-purple-200 text-purple-700'],
            'Dzuhur' => ['jam' => '11:30 – 14:59', 'color' => 'bg-amber-50 border-amber-200 text-amber-700'],
            'Ashar'  => ['jam' => '15:00 – 17:59', 'color' => 'bg-orange-50 border-orange-200 text-orange-700'],
            'Isya'   => ['jam' => '18:00 – 23:59', 'color' => 'bg-indigo-50 border-indigo-200 text-indigo-700'],
        ];
        @endphp
        @foreach($sessionInfo as $ses => $info)
        <div class="border {{ $info['color'] }} rounded-xl px-4 py-3 text-center">
            <p class="text-xs font-bold uppercase tracking-wider">{{ $ses }}</p>
            <p class="text-[11px] mt-0.5 opacity-80">{{ $info['jam'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Tabel Jadwal --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Daftar Master Jadwal Mengajar</h2>
            <span class="text-xs text-slate-400 font-medium">Total: {{ $schedules->count() }} Jadwal</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Guru</th>
                        <th class="px-5 py-3">Hari</th>
                        <th class="px-5 py-3">Sesi & Jam</th>
                        <th class="px-5 py-3">Kelas</th>
                        <th class="px-5 py-3">Kitab</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $schedule->guru->name ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                                {{ $schedule->hari }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                            $sesiColor = match($schedule->session) {
                                'Subuh'  => 'bg-purple-50 border-purple-200 text-purple-700',
                                'Dzuhur' => 'bg-amber-50 border-amber-200 text-amber-700',
                                'Ashar'  => 'bg-orange-50 border-orange-200 text-orange-700',
                                'Isya'   => 'bg-indigo-50 border-indigo-200 text-indigo-700',
                                default  => 'bg-slate-50 border-slate-200 text-slate-700',
                            };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border text-xs font-bold {{ $sesiColor }}">
                                {{ $schedule->session }}
                            </span>
                            <span class="text-xs text-slate-400 ml-1">{{ $schedule->jam_mulai }} – {{ $schedule->jam_selesai }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $schedule->classroom->name ?? 'Tidak ditentukan' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $schedule->kitab ?: '-' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                                <button onclick='editSchedule(@json($schedule))'
                                    class="px-3 py-1.5 text-xs font-bold text-[#1a4731] bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.teacher-schedule.destroy', $schedule->id) }}"
                                      onsubmit="return confirm('Hapus jadwal ini? Jadwal master akan dihapus tetapi riwayat absensi tetap utuh.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="font-semibold text-slate-500">Belum ada jadwal mengajar</p>
                                <p class="text-xs">Klik "Tambah Jadwal" untuk membuat master jadwal mengajar guru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Jadwal --}}
<div id="modal-tambah" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('modal-tambah')"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base">Tambah Jadwal Mengajar</h3>
            <button onclick="closeModal('modal-tambah')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.teacher-schedule.store') }}">
            @csrf
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Guru <span class="text-red-500">*</span></label>
                    <select name="guru_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Hari <span class="text-red-500">*</span></label>
                        <select name="hari" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                            <option value="">-- Pilih Hari --</option>
                            @foreach($hariList as $hari)
                                <option value="{{ $hari }}">{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sesi <span class="text-red-500">*</span></label>
                        <select name="session" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                            <option value="">-- Pilih Sesi --</option>
                            @foreach($sessions as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kelas (Opsional)</label>
                    <select name="classroom_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                        <option value="">-- Tidak Ditentukan --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kitab (Opsional)</label>
                    <input type="text" name="kitab" placeholder="Contoh: Tadzhib, Aqidatul Awam..." maxlength="255"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
                </div>
                <p class="text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5 inline-block mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    Jam otomatis disesuaikan: Subuh (04:00–08:00), Dzuhur (11:30–14:59), Ashar (15:00–17:59), Isya (18:00–23:59)
                </p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-tambah')"
                    class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-[#1a4731] rounded-xl hover:bg-[#143726] transition">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Jadwal --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('modal-edit')"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg z-10 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base">Edit Jadwal Mengajar</h3>
            <button onclick="closeModal('modal-edit')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>
        <form method="POST" id="form-edit">
            @csrf
            @method('PUT')
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Guru <span class="text-red-500">*</span></label>
                    <select name="guru_id" id="edit-guru_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Hari <span class="text-red-500">*</span></label>
                        <select name="hari" id="edit-hari" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                            @foreach($hariList as $hari)
                                <option value="{{ $hari }}">{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sesi <span class="text-red-500">*</span></label>
                        <select name="session" id="edit-session" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                            @foreach($sessions as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kelas (Opsional)</label>
                    <select name="classroom_id" id="edit-classroom_id" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                        <option value="">-- Tidak Ditentukan --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kitab (Opsional)</label>
                    <input type="text" name="kitab" id="edit-kitab" maxlength="255"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-edit')"
                    class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-[#1a4731] rounded-xl hover:bg-[#143726] transition">Perbarui Jadwal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
function editSchedule(data) {
    const baseUrl = "{{ route('admin.teacher-schedule.update', '__ID__') }}".replace('__ID__', data.id);
    document.getElementById('form-edit').action = baseUrl;
    document.getElementById('edit-guru_id').value      = data.guru_id;
    document.getElementById('edit-hari').value         = data.hari;
    document.getElementById('edit-session').value      = data.session;
    document.getElementById('edit-classroom_id').value = data.classroom_id ?? '';
    document.getElementById('edit-kitab').value        = data.kitab ?? '';
    openModal('modal-edit');
}
</script>
@endsection
