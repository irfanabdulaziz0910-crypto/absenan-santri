@extends('layouts.guru')

@section('title', 'Profil Saya')
@section('breadcrumb', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Profil Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data akun login dan keamanan password Anda.</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Info Ringkas Guru --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-center gap-5">
        <div class="w-20 h-20 rounded-full bg-[#1a4731] text-white font-extrabold text-3xl flex items-center justify-center shrink-0">
            {{ strtoupper(substr($user->name ?? 'G', 0, 1)) }}
        </div>
        <div class="text-center sm:text-left space-y-1">
            <h2 class="text-xl font-bold text-slate-800">{{ $guru->name ?? $user->name }}</h2>
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs">
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold rounded-lg">
                    Role: Guru Pengajar
                </span>
                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-semibold rounded-lg">
                    Kelas Induk: {{ $guru->classroom->name ?? ($guru->kelas ?? 'Ma\'had Ali') }}
                </span>
                @if($guru && $guru->nip)
                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 font-mono rounded-lg">
                    NIP: {{ $guru->nip }}
                </span>
                @endif
            </div>
            <p class="text-xs text-slate-400">Username Login: <strong class="text-slate-700 font-mono">{{ $user->username }}</strong></p>
        </div>
    </div>

    {{-- Grid Form Username & Password --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Form Ubah Username --}}
        <form method="POST" action="{{ route('profile.username') }}"
              class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
            @csrf
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-base">Ubah Username</h3>
                <p class="text-xs text-slate-400">Username digunakan untuk masuk ke dalam aplikasi.</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Username Baru <span class="text-red-500">*</span></label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-[#1a4731] text-white font-bold text-xs rounded-xl hover:bg-[#143726] transition">
                Simpan Username Baru
            </button>
        </form>

        {{-- Form Ubah Password --}}
        <form method="POST" action="{{ route('profile.password') }}"
              class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">
            @csrf
            <div class="border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-base">Ubah Password</h3>
                <p class="text-xs text-slate-400">Pastikan password baru minimal 6 karakter.</p>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi password baru"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-[#1a4731] text-white font-bold text-xs rounded-xl hover:bg-[#143726] transition">
                Perbarui Password
            </button>
        </form>

    </div>

</div>
@endsection
