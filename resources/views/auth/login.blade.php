<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Absensi Ngaji</title>
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
        /* Geometric background pattern */
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
        /* Floating decorative circles */
        .deco-circle {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.12;
            z-index: 0;
        }
        /* Card shadow */
        .login-card {
            box-shadow: 0 20px 60px rgba(26,71,49,0.12), 0 4px 20px rgba(0,0,0,0.06);
        }
        /* Input focus ring */
        .inp-field:focus {
            outline: none;
            border-color: #1a4731;
            box-shadow: 0 0 0 3px rgba(26,71,49,0.1);
        }
        /* Button hover */
        .btn-login:hover {
            background: #153c28;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(26,71,49,0.35);
        }
        .btn-login {
            transition: all 0.2s ease;
        }
    </style>
</head>
<body>
    <!-- Decorative background elements -->
    <div class="deco-circle w-96 h-96 bg-green-400" style="top:-80px;left:-80px;"></div>
    <div class="deco-circle w-64 h-64 bg-teal-400" style="bottom:-50px;right:-50px;"></div>
    <div class="deco-circle w-48 h-48 bg-emerald-500" style="bottom:30%;left:10%;"></div>

    <!-- Login Card -->
    <div class="login-card relative z-10 bg-white rounded-2xl w-[calc(100%-1.5rem)] max-w-sm mx-4 p-5 sm:p-8">

        <!-- Logo -->
        <div class="flex flex-col items-center mb-6">
            <div class="w-20 h-20 rounded-full border-2 border-[#1a4731] flex items-center justify-center mb-4 bg-white shadow-sm overflow-hidden p-1">
                <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo Manahijul Huda" class="w-full h-full object-contain">
            </div>

            <h1 class="text-xl font-bold text-slate-800 mb-1">Sistem Absensi Ngaji</h1>
            <p class="text-sm text-slate-400 font-medium text-center">Akses dashboard administrasi pesantren</p>
        </div>

        <!-- Error alert -->
        @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
            Username atau password salah.
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Username -->
            <div class="relative">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Username"
                    class="inp-field w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                    required
                    autocomplete="username"
                >
            </div>

            <!-- Password -->
            <div class="relative">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input
                    type="password"
                    name="password"
                    id="passwordInput"
                    placeholder="Password"
                    class="inp-field w-full pl-11 pr-11 py-3 border border-slate-200 rounded-xl text-sm bg-slate-50 text-slate-800 placeholder-slate-400"
                    required
                    autocomplete="current-password"
                >
                <button type="button" onclick="togglePassword()"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>

            <!-- Ingat Saya -->
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                    class="w-4 h-4 rounded border-slate-300 text-[#1a4731] focus:ring-[#1a4731] cursor-pointer accent-[#1a4731]">
                <label for="remember" class="text-sm text-slate-500 cursor-pointer select-none">Ingat saya</label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login w-full py-3 bg-[#1a4731] text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 mt-2">
                Masuk
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <!-- Quick Login Helper Links -->
        <div class="mt-6 pt-4 border-t border-slate-100 space-y-2">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center mb-2">Pilihan Akun Demo Login:</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillLogin('admin', 'admin')" class="px-3 py-2 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-xl text-xs text-slate-700 font-semibold text-center transition">
                    <span class="block font-bold text-[#1a4731]">Admin Utama</span>
                    <span class="text-[10px] text-slate-400">admin / admin</span>
                </button>
                @php
                    $wkUsername = isset($wkUser) && $wkUser ? $wkUser->username : 'username_wali_kelas';
                @endphp
                <button type="button" onclick="fillLogin('{{ $wkUsername }}', '')" class="px-3 py-2 bg-[#e6f4ec] border border-green-200 hover:bg-[#d6ebd9] rounded-xl text-xs text-[#1a4731] font-semibold text-center transition">
                    <span class="block font-bold">Wali Kelas</span>
                    <span class="text-[10px] text-[#1a4731]/70">{{ $wkUsername }} / gunakan password akun</span>
                </button>
            </div>
        </div>

        <!-- Footer text -->
        <p class="text-center text-[10px] font-bold tracking-[0.25em] text-slate-400 uppercase mt-4">
            SISTEM INFORMASI TERPADU
        </p>
    </div>

    <script>
        function fillLogin(u, p) {
            document.querySelector('input[name="username"]').value = u;
            document.getElementById('passwordInput').value = p;
        }
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon  = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
            } else {
                input.type = 'password';
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
            }
        }
    </script>
</body>
</html>
