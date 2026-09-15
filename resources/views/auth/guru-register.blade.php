<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Akun Guru — Sistem Absensi Ngaji</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: #f0f7f4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(26,71,49,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(26,71,49,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 0;
        }
        .deco-circle {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.12;
            z-index: 0;
        }
        .card {
            box-shadow: 0 20px 60px rgba(26,71,49,0.12), 0 4px 20px rgba(0,0,0,0.06);
        }
        .inp-field:focus {
            outline: none;
            border-color: #1a4731;
            box-shadow: 0 0 0 3px rgba(26,71,49,0.1);
        }
        .btn-primary:hover {
            background: #153c28;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(26,71,49,0.35);
        }
        .btn-primary { transition: all 0.2s ease; }
    </style>
</head>
<body>
    <!-- Decorative background -->
    <div class="deco-circle w-96 h-96 bg-green-400" style="top:-80px;left:-80px;"></div>
    <div class="deco-circle w-64 h-64 bg-teal-400" style="bottom:-50px;right:-50px;"></div>
    <div class="deco-circle w-48 h-48 bg-emerald-500" style="bottom:30%;left:10%;"></div>

    <!-- Card -->
    <div class="card relative z-10 bg-white rounded-2xl w-[calc(100%-1.5rem)] max-w-md mx-4 overflow-hidden">

        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] px-6 py-6 text-white text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center mx-auto mb-3 overflow-hidden p-1.5">
                <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-xl font-extrabold tracking-tight">Buat Akun Guru</h1>
            <p class="text-xs text-emerald-100/90 mt-1">Pesantren Manahijul Huda — Sistem Absensi Ngaji</p>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">

            {{-- Info Box --}}
            <div class="flex items-start gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl">
                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
                <p class="text-xs text-blue-800 leading-relaxed">
                    Nama lengkap yang Anda masukkan harus <strong>sama persis</strong> dengan data yang sudah didaftarkan oleh Admin. Jika nama Anda belum terdaftar, hubungi Admin terlebih dahulu.
                </p>
            </div>

            {{-- Error Alerts --}}
            @if($errors->any())
                <div class="rounded-xl bg-red-50 border border-red-200 p-4 text-xs font-medium text-red-800 space-y-1">
                    @foreach($errors->all() as $err)
                        <p class="flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ $err }}
                        </p>
                    @endforeach
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('guru.register.post') }}" class="space-y-4">
                @csrf

                {{-- Nama Lengkap --}}
                <div>
                    <label for="reg-name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" name="name" id="reg-name"
                            value="{{ old('name') }}"
                            placeholder="Masukkan nama lengkap sesuai data Admin"
                            class="inp-field w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                            required autocomplete="name">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Harus sama persis dengan nama yang didaftarkan Admin.</p>
                </div>

                {{-- Username --}}
                <div>
                    <label for="reg-username" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Username Login <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="username" id="reg-username"
                            value="{{ old('username') }}"
                            placeholder="Contoh: ustadz_irfan"
                            class="inp-field w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                            required autocomplete="username">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Digunakan untuk masuk ke aplikasi panel guru. Hanya huruf, angka, _ dan -.</p>
                </div>

                {{-- Password --}}
                <div>
                    <label for="reg-password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Buat Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" name="password" id="reg-password"
                            placeholder="Minimal 6 karakter"
                            class="inp-field w-full pl-10 pr-11 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                            required minlength="6" autocomplete="new-password">
                        <button type="button" onclick="togglePass('reg-password', 'eye1')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg id="eye1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="reg-password-confirm" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                        </div>
                        <input type="password" name="password_confirmation" id="reg-password-confirm"
                            placeholder="Ulangi password"
                            class="inp-field w-full pl-10 pr-11 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                            required minlength="6" autocomplete="new-password">
                        <button type="button" onclick="togglePass('reg-password-confirm', 'eye2')"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                            <svg id="eye2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- PENUGASAN GURU --}}
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-[#1a4731]">
                        PENUGASAN GURU
                    </p>

                    {{-- Peran / Penugasan --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Peran / Penugasan <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-[#1a4731] has-[:checked]:border-[#1a4731] has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="wali_kelas" {{ old('role', 'wali_kelas') === 'wali_kelas' ? 'checked' : '' }} onchange="toggleRoleForm()" class="text-[#1a4731] focus:ring-[#1a4731]">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">🏫 Wali Kelas</p>
                                    <p class="text-[10px] text-slate-500">Wajib pilih 1 kelas wali</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-[#1a4731] has-[:checked]:border-[#1a4731] has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="guru" {{ old('role') === 'guru' ? 'checked' : '' }} onchange="toggleRoleForm()" class="text-[#1a4731] focus:ring-[#1a4731]">
                                <div>
                                    <p class="text-xs font-bold text-slate-800">🧑‍🏫 Guru Pendamping</p>
                                    <p class="text-[10px] text-slate-500">Bisa memilih 1 atau lebih kelas</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Kelas Wali (Single Select Dropdown) --}}
                    <div id="blockWaliKelas">
                        <label for="selectWaliKelas" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Kelas Wali <span class="text-red-500">*</span>
                        </label>
                        <select name="classroom_id" id="selectWaliKelas" class="inp-field w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 font-semibold cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}" {{ old('classroom_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Hanya 1 Wali Kelas aktif per kelas.</p>
                    </div>

                    {{-- Kelas Guru Pendamping (Multi Select Checkboxes) --}}
                    <div id="blockGuruPendamping" class="hidden">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Pilih Kelas Pendamping <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($classrooms as $cls)
                                <label class="flex items-center gap-2 p-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 cursor-pointer hover:border-[#1a4731] transition">
                                    <input type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}" {{ is_array(old('classroom_ids')) && in_array($cls->id, old('classroom_ids')) ? 'checked' : '' }} class="text-[#1a4731] focus:ring-[#1a4731] rounded">
                                    <span>{{ $cls->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Guru Pendamping dapat memilih satu atau beberapa kelas.</p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                        class="btn-primary w-full py-3 bg-[#1a4731] text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-green-950/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        BUAT AKUN GURU
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah punya akun?
                <a href="{{ route('admin.login') }}" class="font-bold text-[#1a4731] hover:underline">Masuk di Sini</a>
            </p>
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const inp  = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isHidden = inp.type === 'password';
            inp.type = isHidden ? 'text' : 'password';
            icon.innerHTML = isHidden
                ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`
                : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        }

        function toggleRoleForm() {
            const role = document.querySelector('input[name="role"]:checked')?.value || 'wali_kelas';
            const blockWali = document.getElementById('blockWaliKelas');
            const blockPendamping = document.getElementById('blockGuruPendamping');
            const selectWali = document.getElementById('selectWaliKelas');

            if (role === 'wali_kelas') {
                blockWali.classList.remove('hidden');
                blockPendamping.classList.add('hidden');
                selectWali.disabled = false;
            } else {
                blockWali.classList.add('hidden');
                blockPendamping.classList.remove('hidden');
                selectWali.disabled = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleRoleForm();
        });
    </script>
</body>
</html>
