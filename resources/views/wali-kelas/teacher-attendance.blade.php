@extends('layouts.wali-kelas')

@section('title', 'Absensi Mengajar — Wali Kelas')
@section('breadcrumb', 'Absensi Mengajar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Absensi Mengajar</h1>
            <p class="text-sm text-slate-500 mt-1">
                Catat kehadiran mengajar harian untuk kelas wali Anda.
            </p>
        </div>
        {{-- Badge Kelas Wali --}}
        @if($kelasWali)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Kelas Wali: {{ $kelasWali->name }}
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
                    Sesi mengajar tidak aktif hari ini.
                </p>
            </div>
        </div>
    @endif

    {{-- Kelas Wali Belum Ditetapkan --}}
    @if(!$kelasWali)
        <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-6 flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-bold text-red-800">Kelas Wali Belum Ditetapkan</p>
                <p class="text-sm text-red-700">Akun Anda belum dihubungkan dengan kelas wali. Hubungi Admin untuk menetapkan kelas wali Anda di halaman <strong>Data Guru</strong>.</p>
            </div>
        </div>
    @else

    {{-- Info Banner Kelas Wali (READ ONLY) --}}
    <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] rounded-2xl p-5 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <span class="inline-block px-2.5 py-1 bg-white/10 rounded-full text-[11px] font-bold text-emerald-200 uppercase tracking-wider border border-white/10 mb-2">
                🔒 Kelas Wali — Terkunci Otomatis
            </span>
            <h2 class="text-xl sm:text-2xl font-extrabold text-white">{{ $kelasWali->name }}</h2>
            <p class="text-xs text-emerald-100/90 mt-1">Wali Kelas: <strong>{{ $guru->name }}</strong></p>
        </div>
        <div class="text-right hidden sm:block">
            <p class="text-[11px] text-emerald-200/70 uppercase tracking-wider">Absensi mengajar hari ini</p>
            <p class="text-2xl font-extrabold">{{ $attendancesToday->count() }} <span class="text-sm font-normal text-emerald-200">sesi</span></p>
        </div>
    </div>

    {{-- Form Absensi Mengajar --}}
    <form method="POST" action="{{ route('wali-kelas.teacher-attendance.store') }}"
          class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 space-y-5">
        @csrf

        {{-- Kelas Wali → hidden input (dikunci) --}}
        <input type="hidden" name="classroom_id" value="{{ $kelasWali->id }}">

        <div class="border-b border-slate-100 pb-3">
            <h2 class="text-base font-bold text-slate-800">Form Absensi Mengajar</h2>
            <p class="text-xs text-slate-500">Catat kehadiran mengajar, kitab, dan materi pembelajaran yang diajarkan.</p>
        </div>

        {{-- Read-only info kelas --}}
        <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
                <p class="text-xs text-slate-500 font-medium">Kelas yang Diajar (Kelas Wali)</p>
                <p class="text-sm font-bold text-slate-800">{{ $kelasWali->name }}</p>
            </div>
            <span class="ml-auto text-[11px] bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full">🔒 Otomatis</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal</label>
                <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Waktu Absensi</label>
                <input type="time" name="attendance_time" value="{{ old('attendance_time', now()->format('H:i')) }}" required
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sesi</label>
                <select name="session" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="">-- Pilih Sesi --</option>
                    @php $reqSession = $sessionAktif ?? ''; @endphp
                    <option value="Subuh"  @selected(old('session', $reqSession) === 'Subuh')>Subuh (04:00 – 08:00)</option>
                    <option value="Dzuhur" @selected(old('session', $reqSession) === 'Dzuhur')>Dzuhur (11:30 – 14:59)</option>
                    <option value="Ashar"  @selected(old('session', $reqSession) === 'Ashar')>Ashar (15:00 – 17:59)</option>
                    <option value="Isya"   @selected(old('session', $reqSession) === 'Isya')>Isya (18:00 – 23:59)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kitab Pelajaran <span class="text-red-500">*</span></label>
                <input type="text" name="kitab" value="{{ old('kitab') }}"
                    placeholder="Contoh: Fathul Qarib / Tadzhib" required maxlength="255"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Kehadiran <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="Hadir" @selected(old('status', 'Hadir') === 'Hadir')>Hadir</option>
                    <option value="Izin"  @selected(old('status') === 'Izin')>Izin</option>
                    <option value="Sakit" @selected(old('status') === 'Sakit')>Sakit</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Materi yang Diajarkan <span class="text-red-500">*</span></label>
            <textarea name="materi" rows="3" required
                placeholder="Contoh: Bab Thaharah - Pembahasan rukun wudhu dan syarat sah wudhu."
                class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">{{ old('materi') }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="w-full sm:w-auto px-6 py-3 rounded-xl bg-[#1a4731] hover:bg-[#143726] text-white font-bold text-sm shadow-sm transition">
                Simpan &amp; Lanjut ke Absensi Santri →
            </button>
        </div>
    </form>

    {{-- Riwayat Mengajar Hari Ini --}}
    @if($attendancesToday->isNotEmpty())
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Absensi Mengajar Hari Ini</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($attendancesToday as $att)
            <div class="px-6 py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-slate-700">{{ $att->attendance_time ? $att->attendance_time->format('H:i') : '-' }}</span>
                    @if($att->session)
                        <span class="text-xs px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-semibold">{{ $att->session }}</span>
                    @endif
                    <span class="text-xs text-slate-500">{{ $att->kitab }}</span>
                </div>
                <span class="text-xs font-bold px-2 py-1 rounded-full {{ $att->status === 'Hadir' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $att->status }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Riwayat Mengajar Semua --}}
    <section class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Riwayat Absensi Mengajar</h2>
            <span class="text-xs text-slate-400">Total: {{ $attendances->count() }} Data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Waktu / Sesi</th>
                        <th class="px-6 py-3.5">Kitab</th>
                        <th class="px-6 py-3.5">Materi Pembelajaran</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
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
                                    <span class="text-xs text-slate-400">({{ $att->session }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">{{ $att->kitab }}</td>
                            <td class="px-6 py-4 text-slate-600 min-w-[280px]">{{ $att->materi }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @php
                                $badgeClass = match($att->status) {
                                    'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Izin'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Alfa'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full border text-xs font-bold {{ $badgeClass }}">
                                    {{ $att->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600">Belum ada riwayat mengajar.</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan form di atas untuk mencatat kehadiran mengajar Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @endif {{-- end if $kelasWali --}}

</div>
@endsection
