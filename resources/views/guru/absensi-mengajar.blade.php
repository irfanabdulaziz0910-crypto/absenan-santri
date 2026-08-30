@extends('layouts.guru')

@section('title', $isWaliKelas ? 'Absensi Mengajar — Wali Kelas' : 'Absensi Mengajar Guru')
@section('breadcrumb', 'Absensi Mengajar')

@section('content')
<div class="space-y-6 max-w-full overflow-hidden">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-slate-800">Absensi Mengajar</h1>
            <p class="text-sm text-slate-500 mt-1">
                @if($isWaliKelas)
                    Catat kehadiran mengajar harian Wali Kelas untuk kelas <strong>{{ $kelasWali?->name ?? '-' }}</strong>.
                @else
                    Pencatatan kehadiran mengajar, kitab, dan materi pembelajaran harian.
                @endif
            </p>
        </div>
        {{-- Badge Kelas --}}
        @if($isWaliKelas && $kelasWali)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Kelas Wali: {{ $kelasWali->name }}
            </div>
        @elseif(!$isWaliKelas)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Kelas Induk: {{ $guru->classroom->name ?? ($guru->kelas ?? 'Ma\'had Ali') }}
            </div>
        @endif
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Info Hari Libur --}}
    @if($isHariLibur)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            <div>
                <p class="font-bold text-amber-800">Hari Libur</p>
                <p class="text-sm text-amber-700">Hari ini ({{ now()->isoFormat('dddd, D MMMM Y') }}) ditetapkan sebagai hari libur:
                    <strong>{{ $hariLibur->keterangan ?? 'Hari Libur' }}</strong>.
                    Sesi mengajar tidak aktif hari ini. Master jadwal tetap tersimpan.
                </p>
            </div>
        </div>
    @endif

    {{-- INFO BANNER KHUSUS WALI KELAS (kelas terkunci, tidak bisa pilih) --}}
    @if($isWaliKelas && $kelasWali)
        <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] rounded-2xl p-5 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-block px-2.5 py-1 bg-white/10 rounded-full text-[11px] font-bold text-emerald-200 uppercase tracking-wider border border-white/10 mb-2">
                    🔒 Kelas Wali — Terkunci Otomatis
                </span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white">{{ $kelasWali->name }}</h2>
                <p class="text-xs text-emerald-100/90 mt-1">Wali Kelas: <strong>{{ $guru->name }}</strong></p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-[11px] text-emerald-200/70 uppercase tracking-wider">Absensi hari ini</p>
                <p class="text-2xl font-extrabold">{{ $attendancesToday->count() }} <span class="text-sm font-normal text-emerald-200">sesi</span></p>
            </div>
        </div>

        {{-- Alert jika Wali Kelas tidak punya kelas --}}
    @elseif($isWaliKelas && !$kelasWali)
        <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-bold text-red-800">Kelas Wali Belum Ditetapkan</p>
                <p class="text-sm text-red-700">Akun Anda belum dihubungkan dengan kelas wali. Hubungi Admin untuk menetapkan kelas wali Anda di halaman Data Guru.</p>
            </div>
        </div>
    @endif

    {{-- Form Absensi Mengajar --}}
    @if(!$isWaliKelas || $kelasWali)
    <form method="POST"
          action="{{ $isWaliKelas ? route('wali-kelas.teacher-attendance.store') : route('guru.absensi-mengajar.store') }}"
          class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden" id="form-absensi">
        @csrf

        {{-- Form Header --}}
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-800">Form Absensi Mengajar</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catat kehadiran mengajar, kitab, dan materi pembelajaran yang diajarkan.</p>
        </div>

        {{-- Form Body --}}
        <div class="p-6 space-y-5">

            {{-- Row 1: Tanggal, Waktu, Sesi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="inp-date" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal</label>
                    <input type="date" name="date" id="inp-date" value="{{ old('date', now()->toDateString()) }}" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div>
                    <label for="inp-time" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Waktu Absensi</label>
                    <input type="time" name="attendance_time" id="inp-time" value="{{ old('attendance_time', now()->format('H:i')) }}" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="inp-session" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sesi</label>
                    <select name="session" id="inp-session" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                        <option value="">-- Pilih Sesi --</option>
                        @php $reqSession = request('session', $sessionAktif); @endphp
                        <option value="Subuh"  @selected(old('session', $reqSession) === 'Subuh')>Subuh (04:00 – 08:00)</option>
                        <option value="Dzuhur" @selected(old('session', $reqSession) === 'Dzuhur')>Dzuhur (11:30 – 14:59)</option>
                        <option value="Ashar"  @selected(old('session', $reqSession) === 'Ashar')>Ashar (15:00 – 17:59)</option>
                        <option value="Isya"   @selected(old('session', $reqSession) === 'Isya')>Isya (18:00 – 23:59)</option>
                    </select>
                </div>
            </div>

            {{-- Row 2: Kelas --}}
            <div>
                <label for="inp-classroom" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kelas yang Diajar <span class="text-red-500">*</span></label>
                <select name="classroom_id" id="inp-classroom" required
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white font-semibold focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                    <option value="">-- Pilih Kelas yang Diajar --</option>
                    @php 
                        $reqClassroom = request('classroom_id'); 
                        $defaultClassroom = $isWaliKelas && $kelasWali ? $kelasWali->id : old('classroom_id', $reqClassroom);
                    @endphp
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected($defaultClassroom == $classroom->id)>{{ $classroom->name }}</option>
                    @endforeach
                </select>
                @if($isWaliKelas && $kelasWali)
                    <p class="text-xs text-slate-400 mt-1.5">Kelas Wali Anda adalah: <strong>{{ $kelasWali->name }}</strong>, tapi Anda tetap bebas memilih kelas lain jika perlu.</p>
                @endif
            </div>

            {{-- Row 3: Kitab & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="inp-kitab" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kitab Pelajaran <span class="text-red-500">*</span></label>
                    <input type="text" name="kitab" id="inp-kitab" value="{{ old('kitab', request('kitab')) }}"
                        placeholder="Contoh: Fathul Qarib / Tadzhib" required maxlength="255"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                </div>
                <div>
                    <label for="inp-status" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Kehadiran <span class="text-red-500">*</span></label>
                    <select name="status" id="inp-status" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                        <option value="Hadir" @selected(old('status', 'Hadir') === 'Hadir')>Hadir</option>
                        <option value="Izin"  @selected(old('status') === 'Izin')>Izin</option>
                        <option value="Sakit" @selected(old('status') === 'Sakit')>Sakit</option>
                    </select>
                </div>
            </div>

            {{-- Row 4: Materi --}}
            <div>
                <label for="inp-materi" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Materi yang Diajarkan <span class="text-red-500">*</span></label>
                <textarea name="materi" id="inp-materi" rows="3" required
                    placeholder="Contoh: Bab Thaharah - Pembahasan rukun wudhu dan syarat sah wudhu."
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition resize-y">{{ old('materi') }}</textarea>
            </div>
        </div>

        {{-- Form Footer --}}
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-end">
            <button type="submit"
                class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#1a4731] hover:bg-[#143726] text-white font-bold text-sm shadow-md shadow-green-900/20 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer hover:shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                MULAI MENGAJAR
            </button>
        </div>
    </form>
    @endif

    {{-- Riwayat Mengajar --}}
    <section class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Riwayat Absensi Mengajar Saya</h2>
            <span class="text-xs text-slate-400">Total: {{ $attendances->count() }} Data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-3.5 whitespace-nowrap">Waktu / Sesi</th>
                        <th class="px-6 py-3.5 whitespace-nowrap">Kelas</th>
                        <th class="px-6 py-3.5 whitespace-nowrap">Kitab</th>
                        <th class="px-6 py-3.5">Materi Pembelajaran</th>
                        <th class="px-6 py-3.5 text-center whitespace-nowrap">Status Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">
                                {{ $att->date ? $att->date->format('d-m-Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                {{ $att->attendance_time ? $att->attendance_time->format('H:i') : '-' }}
                                @if($att->session)
                                    @php
                                    $sesiColor = match($att->session) {
                                        'Subuh'  => 'bg-purple-50 border-purple-200 text-purple-700',
                                        'Dzuhur' => 'bg-amber-50 border-amber-200 text-amber-700',
                                        'Ashar'  => 'bg-orange-50 border-orange-200 text-orange-700',
                                        'Isya'   => 'bg-indigo-50 border-indigo-200 text-indigo-700',
                                        default  => 'bg-slate-50 border-slate-200 text-slate-700',
                                    };
                                    @endphp
                                    <span class="ml-1 px-2 py-0.5 rounded-full border text-[10px] font-bold {{ $sesiColor }}">{{ $att->session }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                                    {{ $att->classroom->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">{{ $att->kitab }}</td>
                            <td class="px-6 py-4 text-slate-600 min-w-[200px] max-w-[320px]">
                                <p class="line-clamp-2">{{ $att->materi }}</p>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @php
                                $statusApp = strtolower($att->approval_status ?? $att->status ?? 'approved');
                                $badgeClass = match($statusApp) {
                                    'approved', 'disetujui', 'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'pending', 'menunggu'           => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse',
                                    'rejected', 'ditolak'          => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'expired', 'kadaluarsa'        => 'bg-slate-100 text-slate-500 border-slate-200',
                                    'izin'                         => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'sakit'                        => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'alfa'                         => 'bg-red-50 text-red-700 border-red-200',
                                    default                        => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                                $statusLabel = match($statusApp) {
                                    'approved', 'disetujui' => 'DISETUJUI',
                                    'pending', 'menunggu'   => 'MENUNGGU PERSETUJUAN',
                                    'rejected', 'ditolak'  => 'DITOLAK',
                                    'expired', 'kadaluarsa' => 'KADALUARSA',
                                    'hadir'                 => 'HADIR',
                                    'izin'                  => 'IZIN',
                                    'sakit'                 => 'SAKIT',
                                    'alfa'                  => 'ALFA',
                                    default                 => strtoupper($statusApp ?: 'DISETUJUI'),
                                };
                                @endphp
                                <div class="flex flex-col items-center gap-1">
                                    <span class="px-3 py-1 rounded-full border text-[11px] font-extrabold tracking-wide {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if($statusApp === 'approved' || $statusApp === 'disetujui' || $statusApp === 'hadir')
                                        <a href="{{ route('guru.absensi-santri', ['classroom_id' => $att->classroom_id, 'date' => $att->date?->toDateString(), 'session' => $att->session]) }}"
                                           class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-[#1a4731] hover:underline">
                                            Buka Absensi Santri &rarr;
                                        </a>
                                    @elseif($statusApp === 'pending' || $statusApp === 'menunggu')
                                        <span class="mt-1 text-[10px] text-amber-700 font-medium italic">
                                            Menunggu persetujuan Admin atau Wali Kelas
                                        </span>
                                        <span class="text-[10px] font-bold text-slate-400 cursor-not-allowed bg-slate-100 px-2 py-0.5 rounded">
                                            🔒 Absensi Santri Dikunci
                                        </span>
                                    @elseif($statusApp === 'rejected' || $statusApp === 'ditolak')
                                        <span class="mt-1 text-[10px] text-rose-600 font-bold">
                                            Permintaan mengajar ditolak
                                        </span>
                                        @if($att->rejection_reason)
                                            <span class="text-[10px] text-rose-500 italic">"{{ $att->rejection_reason }}"</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>
                                    <p class="font-bold text-slate-600">Belum ada riwayat mengajar.</p>
                                    <p class="text-xs text-slate-400">Gunakan form di atas untuk mencatat kehadiran mengajar Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
