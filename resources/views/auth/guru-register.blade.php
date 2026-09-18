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
    <div class="card relative z-10 bg-white rounded-2xl w-[calc(100%-1.5rem)] max-w-md mx-4 overflow-hidden my-6">

        {{-- Header Banner --}}
        <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] px-6 py-6 text-white text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center mx-auto mb-3 overflow-hidden p-1.5">
                <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-xl font-extrabold tracking-tight">BUAT AKUN GURU</h1>
            <p class="text-xs text-emerald-100/90 mt-1">Pesantren Manahijul Huda — Sistem Absensi Ngaji</p>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">

            {{-- Info Box --}}
            <div class="flex items-start gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
                <p class="text-xs text-emerald-900 leading-relaxed">
                    Nama harus sesuai dengan nama yang telah didaftarkan Admin. Masukkan nama lengkap Anda lalu klik <strong>Cek Nama</strong>.
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
            <form method="POST" action="{{ route('guru.register.post') }}" class="space-y-4" id="regForm">
                @csrf

                {{-- Nama Lengkap --}}
                <div>
                    <label for="reg-name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        NAMA LENGKAP <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
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
                        <button type="button" onclick="verifyName()" id="btnVerifyName"
                            class="px-4 py-3 bg-[#1a4731] hover:bg-[#153c28] text-white font-bold text-xs rounded-xl shadow-sm transition shrink-0 flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Cek Nama
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Harus sesuai dengan nama yang telah didaftarkan Admin.</p>
                </div>

                {{-- Status Verifikasi Data Guru --}}
                <div id="guruVerifiedBox" class="hidden px-4 py-3.5 bg-emerald-50 border-2 border-emerald-300 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-600 text-white flex items-center justify-center font-extrabold text-sm shrink-0">
                            ✓
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-emerald-600">Data Guru Ditemukan</span>
                            <h4 class="text-sm font-extrabold text-slate-800" id="txtGuruName"></h4>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800" id="txtGuruStatus">
                        Aktif
                    </span>
                </div>

                <div id="guruErrorBox" class="hidden px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-semibold flex items-start gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    <span id="txtGuruError"></span>
                </div>

                {{-- Form Fields (Penugasan & Credentials) --}}
                <div id="registrationFields" class="space-y-4 pt-2">

                    {{-- Penugasan --}}
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                            PENUGASAN <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-[#1a4731] has-[:checked]:border-[#1a4731] has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="wali_kelas" onchange="onRoleToggle()" {{ old('role') === 'wali_kelas' ? 'checked' : '' }} class="w-4 h-4 text-[#1a4731] accent-[#1a4731]">
                                <span class="text-xs font-bold text-slate-800">Wali Kelas</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-[#1a4731] has-[:checked]:border-[#1a4731] has-[:checked]:bg-emerald-50/40 transition">
                                <input type="radio" name="role" value="guru" onchange="onRoleToggle()" {{ old('role', 'guru') === 'guru' ? 'checked' : '' }} class="w-4 h-4 text-[#1a4731] accent-[#1a4731]">
                                <span class="text-xs font-bold text-slate-800">Guru Pendamping</span>
                            </label>
                        </div>
                    </div>

                    {{-- Block Kelas Wali --}}
                    <div id="blockWaliKelas" class="hidden">
                        <label for="reg-classroom" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            KELAS YANG DIWALI <span class="text-red-500">*</span>
                        </label>
                        <select name="classroom_id" id="reg-classroom" class="inp-field w-full px-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 font-semibold">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}" {{ old('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-amber-700 mt-1">⚠️ Setiap kelas hanya boleh memiliki 1 Wali Kelas aktif.</p>
                    </div>

                    {{-- Block Kelas Pendamping --}}
                    <div id="blockPendamping">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            KELAS PENDAMPING <span class="text-red-500">*</span>
                        </label>
                        <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-1">
                            @foreach($classrooms as $cls)
                                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer hover:bg-slate-100 p-1.5 rounded-lg">
                                    <input type="checkbox" name="classroom_ids[]" value="{{ $cls->id }}"
                                        {{ is_array(old('classroom_ids')) && in_array($cls->id, old('classroom_ids')) ? 'checked' : '' }}
                                        class="w-4 h-4 text-[#1a4731] rounded accent-[#1a4731]">
                                    <span class="font-semibold">{{ $cls->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Pilih satu atau beberapa kelas yang Anda dampingi/ajar.</p>
                    </div>

                    {{-- Username --}}
                    <div class="pt-2 border-t border-slate-100">
                        <label for="reg-username" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            USERNAME LOGIN <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="username" id="reg-username"
                                value="{{ old('username') }}"
                                placeholder="Buat username login"
                                class="inp-field w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                                required autocomplete="username">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Hanya huruf, angka, _ dan -.</p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="reg-password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            BUAT PASSWORD <span class="text-red-500">*</span>
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
                            KONFIRMASI PASSWORD <span class="text-red-500">*</span>
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

                    {{-- Submit Button --}}
                    <button type="submit" class="btn-primary w-full py-3.5 bg-[#1a4731] text-white text-sm font-bold rounded-xl flex items-center justify-center gap-2 shadow-md cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        ✓ BUAT AKUN GURU
                    </button>
                </div>

            </form>

            {{-- Link Kembali ke Login --}}
            <div class="pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Sudah memiliki akun login?
                    <a href="{{ route('admin.login') }}" class="font-bold text-[#1a4731] hover:underline">
                        Masuk Sekarang
                    </a>
                </p>
            </div>

        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
            } else {
                input.type = 'password';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
            }
        }

        function onRoleToggle() {
            const role = document.querySelector('input[name="role"]:checked')?.value || 'guru';
            const bWali = document.getElementById('blockWaliKelas');
            const bPend = document.getElementById('blockPendamping');

            if (role === 'wali_kelas') {
                bWali.classList.remove('hidden');
                bPend.classList.add('hidden');
            } else {
                bWali.classList.add('hidden');
                bPend.classList.remove('hidden');
            }
        }

        async function verifyName() {
            const nameInput = document.getElementById('reg-name');
            const name = nameInput.value.trim();
            const vBox = document.getElementById('guruVerifiedBox');
            const eBox = document.getElementById('guruErrorBox');
            const txtName = document.getElementById('txtGuruName');
            const txtStatus = document.getElementById('txtGuruStatus');
            const txtErr  = document.getElementById('txtGuruError');

            if (!name) {
                eBox.classList.remove('hidden');
                vBox.classList.add('hidden');
                txtErr.innerText = 'Nama lengkap wajib diisi.';
                return;
            }

            try {
                const res = await fetch("{{ route('guru.check-nama') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ name: name })
                });
                const data = await res.json();

                if (data.success) {
                    vBox.classList.remove('hidden');
                    eBox.classList.add('hidden');
                    txtName.innerText = data.guru.name;
                    txtStatus.innerText = data.guru.status || 'Aktif';
                } else {
                    vBox.classList.add('hidden');
                    eBox.classList.remove('hidden');
                    txtErr.innerText = data.message || 'Data Guru tidak ditemukan.';
                }
            } catch (err) {
                vBox.classList.add('hidden');
                eBox.classList.remove('hidden');
                txtErr.innerText = 'Terjadi kesalahan saat verifikasi nama. Silakan coba lagi.';
            }
        }

        // Init
        onRoleToggle();
        if (document.getElementById('reg-name').value.trim() !== '') {
            verifyName();
        }
    </script>
</body>
</html>
