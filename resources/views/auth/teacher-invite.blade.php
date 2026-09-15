<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Akun Guru — ABSENSI NGAJI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-manahijulhuda.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] p-6 text-white text-center relative">
            <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo" class="w-14 h-14 mx-auto rounded-2xl bg-white p-1 shadow-md mb-3 object-contain">
            <h1 class="text-xl font-extrabold tracking-tight">Pendaftaran Akun Guru</h1>
            <p class="text-xs text-emerald-100/90 mt-1">Undangan Pendaftaran Mandiri — Pesantren Manahijul Huda</p>
        </div>

        {{-- Form Body --}}
        <div class="p-6 space-y-5">
            @if($errors->any())
                <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-xs font-medium text-red-800 space-y-1">
                    @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('teacher.invite.store', $invitation->token) }}" class="space-y-4">
                @csrf

                {{-- Nama Lengkap --}}
                <div>
                    <label for="inp-name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="inp-name" value="{{ old('name', $presetName) }}" required
                        placeholder="Nama lengkap guru..."
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-slate-50 focus:bg-white">
                    @if($invitation->guru)
                        <p class="text-[11px] text-emerald-700 font-medium mt-1">✓ Terhubung dengan Data Guru: <strong>{{ $invitation->guru->name }}</strong></p>
                    @endif
                </div>

                {{-- Username --}}
                <div>
                    <label for="inp-username" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Username Login <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="username" id="inp-username" value="{{ old('username') }}" required
                        placeholder="Contoh: ustadz_irfan"
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-slate-50 focus:bg-white">
                    <p class="text-[11px] text-slate-400 mt-1">Digunakan untuk masuk/login ke aplikasi panel guru.</p>
                </div>

                {{-- Password --}}
                <div>
                    <label for="inp-password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Buat Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="inp-password" required minlength="6"
                        placeholder="Minimal 6 karakter"
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-slate-50 focus:bg-white">
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="inp-password-confirm" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="inp-password-confirm" required minlength="6"
                        placeholder="Ulangi password..."
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition bg-slate-50 focus:bg-white">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 bg-[#1a4731] hover:bg-[#143726] text-white font-bold text-sm rounded-xl shadow-lg shadow-green-950/20 transition duration-200 cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        BUAT AKUN GURU
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah punya akun? <a href="{{ route('admin.login') }}" class="font-bold text-[#1a4731] hover:underline">Masuk di Sini</a>
            </p>
        </div>
    </div>
</body>
</html>
