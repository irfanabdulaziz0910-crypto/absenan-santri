@extends('layouts.guru')

@section('title', 'Dashboard Guru')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome Card --}}
    <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 opacity-10 flex items-center pr-6 pointer-events-none">
            <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99z"/></svg>
        </div>
        <div class="relative z-10 space-y-2">
            <span class="px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-xs font-semibold text-emerald-200 border border-white/10">
                Selamat Datang
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $guru->name }}</h1>
            <p class="text-sm text-emerald-100/90 max-w-xl">
                Status: <span class="font-bold text-white">Guru Pengajar</span> &bull;
                Kelas Induk: <span class="font-bold text-white">{{ $guru->classroom->name ?? ($guru->kelas ?? 'Ma\'had Ali') }}</span>
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Jadwal Master</p>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $totalJadwal }} Jadwal</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat Mengajar</p>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $totalRiwayat }} Sesi</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            </div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sesi Aktif Saat Ini</p>
                <p class="text-base font-extrabold text-slate-800 mt-1">
                    {{ $sessionAktif ?: 'Tidak Ada Sesi' }}
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            </div>
        </div>
    </div>

    {{-- Info Hari Libur --}}
    @if($hariLibur)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            <div>
                <p class="font-bold text-amber-800">Hari Ini Libur</p>
                <p class="text-xs text-amber-700 mt-0.5">
                    Tanggal {{ now()->isoFormat('D MMMM Y') }} ditetapkan sebagai HARI LIBUR:
                    <strong>{{ $hariLibur->keterangan ?? 'Hari Libur' }}</strong>.
                    Sesi mengajar tidak aktif hari ini.
                </p>
            </div>
        </div>
    @endif

    {{-- Section Jadwal Mengajar Hari Ini --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-800 text-lg">Jadwal Mengajar Hari Ini</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $hariIni }}, {{ now()->isoFormat('D MMMM Y') }}</p>
            </div>
            @if($sessionAktif && !$hariLibur)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full text-xs font-bold text-emerald-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sesi Aktif: {{ $sessionAktif }}
                </span>
            @endif
        </div>

        @if($jadwalHariIni->isEmpty())
            <div class="px-6 py-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto opacity-30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="font-bold text-slate-600 text-base">Tidak ada jadwal mengajar hari ini.</p>
                <p class="text-xs text-slate-400 mt-1">Hari ini ({{ $hariIni }}) Anda tidak memiliki jadwal mengajar master.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($jadwalHariIni as $jadwal)
                @php
                    $isAktifSekarang = $sessionAktif === $jadwal->session && !$hariLibur;
                    $sudahAbsen = in_array($jadwal->classroom_id, $absensiHariIni);
                @endphp
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 {{ $isAktifSekarang ? 'bg-emerald-50/50' : '' }}">
                    <div class="flex items-center gap-4">
                        @php
                        $sesiColor = match($jadwal->session) {
                            'Subuh'  => 'bg-purple-100 text-purple-700',
                            'Dzuhur' => 'bg-amber-100 text-amber-700',
                            'Ashar'  => 'bg-orange-100 text-orange-700',
                            'Isya'   => 'bg-indigo-100 text-indigo-700',
                            default  => 'bg-slate-100 text-slate-700',
                        };
                        @endphp
                        <div class="w-14 h-14 rounded-2xl {{ $sesiColor }} flex flex-col items-center justify-center text-xs font-bold text-center shrink-0">
                            <span>{{ $jadwal->session }}</span>
                            <span class="text-[9px] opacity-70 font-normal">{{ $jadwal->jam_mulai }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 text-base">{{ $jadwal->classroom->name ?? 'Tanpa Kelas' }}</h3>
                                @if($isAktifSekarang)
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-full">Sesi Saat Ini</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kitab: <span class="font-semibold text-slate-700">{{ $jadwal->kitab ?: 'Belum ditentukan' }}</span> &bull;
                                Jam: <span class="font-semibold text-slate-700">{{ $jadwal->jam_mulai }} – {{ $jadwal->jam_selesai }}</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        @if($sudahAbsen)
                            <span class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Sudah Absen Mengajar
                            </span>
                        @elseif($isAktifSekarang)
                            <a href="{{ route('guru.absensi-mengajar', ['classroom_id' => $jadwal->classroom_id, 'session' => $jadwal->session, 'kitab' => $jadwal->kitab]) }}"
                               class="px-5 py-2.5 bg-[#1a4731] hover:bg-[#143726] text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                [ MULAI ABSEN MENGAJAR ]
                            </a>
                        @else
                            <span class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-50 border border-slate-200 text-slate-400">
                                Belum Waktunya Sesi
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
