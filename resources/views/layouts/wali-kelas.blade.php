<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wali Kelas') — ABSENSI NGAJI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-manahijulhuda.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        html, body {
            background: #f5f7fa;
            overflow-x: hidden;
        }
        body { min-height: 100vh; }
        button, input, select, textarea {
            min-height: 44px;
            box-sizing: border-box;
        }
        .sidebar-link { transition: all 0.15s ease; }
        #toast { transition: all 0.3s ease; transform: translateX(120%); }
        #toast.show { transform: translateX(0); }
        @media (max-width: 991px) {
            #sidebar {
                transform: translateX(-110%);
                z-index: 50;
            }
            body.sidebar-open #sidebar {
                transform: translateX(0);
            }
            #sidebarBackdrop {
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease;
            }
            body.sidebar-open #sidebarBackdrop {
                opacity: 1;
                pointer-events: auto;
            }
            .wali-shell {
                margin-left: 0 !important;
            }
            .wali-header {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .wali-header .header-search,
            .wali-header .header-actions {
                display: none !important;
            }
            .wali-main {
                padding: 1rem !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen">
<div id="sidebarBackdrop" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-[2px] hidden lg:hidden"></div>
    {{-- SIDEBAR WALI KELAS --}}
    <aside id="sidebar" class="w-[220px] h-screen bg-white border-r border-slate-100 flex flex-col fixed left-0 top-0 z-50 shadow-sm transition-transform duration-300 lg:translate-x-0">
        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-slate-100 flex items-center gap-2.5">
            <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo Manahijul Huda" class="w-9 h-9 rounded-xl object-contain shrink-0">
            <div>
                <p class="text-[13px] font-extrabold text-slate-800 leading-tight tracking-tight">ABSENSI NGAJI</p>
                <p class="text-[10px] text-slate-400 font-medium leading-none mt-0.5">Manahijul Huda</p>
            </div>
        </div>

        {{-- Nav --}}
        @php
            $wkUser = Auth::guard('web')->user() ?: Auth::user();
            $wkGuru = $wkUser?->guru_id ? \App\Models\Guru::find($wkUser->guru_id) : \App\Models\Guru::where('user_id', $wkUser?->id)->first();
            $wkPendingCount = 0;
            if ($wkGuru && $wkGuru->classroom_id) {
                $wkPendingCount = \App\Models\TeacherAttendance::where('classroom_id', $wkGuru->classroom_id)
                    ->where('approval_status', 'pending')
                    ->count();
            }
        @endphp
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <a href="{{ route('wali-kelas.dashboard') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('wali-kelas.dashboard') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('wali-kelas.notifikasi') }}" class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('wali-kelas.notifikasi') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    🔔 Notifikasi
                </div>
                @if($wkPendingCount > 0)
                    <span class="px-2 py-0.5 text-[10px] font-extrabold bg-red-500 text-white rounded-full animate-pulse">
                        {{ $wkPendingCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('wali-kelas.santri') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('wali-kelas.santri') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Data Santri
            </a>
            <a href="{{ route('wali-kelas.absensi-manual') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('wali-kelas.absensi-manual') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Absensi Manual
            </a>
            <a href="{{ route('wali-kelas.teacher-attendance.index') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('wali-kelas.teacher-attendance.*') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>
                Absensi Mengajar
            </a>
            <a href="{{ route('wali-kelas.laporan') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('wali-kelas.laporan') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan
            </a>
        </nav>

        {{-- Profile --}}
        @php
            $authUser = Auth::guard('web')->user() ?: Auth::user();
            $userName = $authUser ? $authUser->name : 'Wali Kelas';
        @endphp
        <div class="p-3 border-t border-slate-100">
            <div class="flex items-center gap-3 px-2 py-2 mb-2">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=e2f3e9&color=1a4731&bold=true" class="w-8 h-8 rounded-full border border-slate-200 object-cover shrink-0">
                <div class="min-w-0">
                    <p class="text-[12px] font-bold text-slate-800 leading-tight truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-slate-400 font-bold leading-none mt-0.5 uppercase tracking-wide">WALI KELAS {{ $assignedKelas ?? '' }}</p>
                </div>
            </div>
            <div class="space-y-2">
                <a href="{{ route('profile') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-slate-600 border border-slate-200 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>
                    Profil Saya
                </a>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-red-600 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="wali-shell flex flex-col min-h-screen lg:ml-[220px]">
        {{-- TOPBAR --}}
        <header class="wali-header sticky top-0 z-30 bg-white border-b border-slate-100 px-4 py-3 sm:px-6 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-sidebar-toggle class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-700 shadow-sm lg:hidden" aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex items-center gap-2 text-sm text-slate-500 font-semibold min-w-0">
                    <svg class="w-4 h-4 text-slate-400 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="truncate">Wali Kelas — <strong class="text-slate-800">{{ $assignedKelas ?? 'Pengampu' }}</strong></span>
                </div>
            </div>

            <div class="header-actions flex items-center gap-3">
                <div class="header-search relative hidden sm:block">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <input type="text" placeholder="Cari santri..." class="pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl w-52 focus:outline-none focus:ring-2 focus:ring-green-200">
                </div>

                <a href="{{ route('wali-kelas.notifikasi') }}" title="Notifikasi Permintaan Mengajar Kelas Wali" class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($wkPendingCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white rounded-full text-[10px] font-extrabold flex items-center justify-center animate-bounce border border-white">
                            {{ $wkPendingCount }}
                        </span>
                    @endif
                </a>

                <img src="https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=1a4731&color=ffffff&bold=true" class="w-10 h-10 rounded-xl object-cover" title="{{ $userName }}">
            </div>
        </header>

        <main class="wali-main flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    {{-- Toast --}}
    <div id="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-white text-sm font-medium max-w-sm">
        <div id="toast-icon" class="w-5 h-5 shrink-0"></div>
        <span id="toast-msg"></span>
    </div>

    <script>
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const icon  = document.getElementById('toast-icon');
        const msgEl = document.getElementById('toast-msg');
        msgEl.textContent = msg;

        if (type === 'success') {
            toast.className = 'fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-white text-sm font-medium max-w-sm bg-[#1a4731] show';
            icon.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>`;
        } else {
            toast.className = 'fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-white text-sm font-medium max-w-sm bg-red-600 show';
            icon.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
        }
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    function toggleSidebar() {
        document.body.classList.toggle('sidebar-open');
    }

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', toggleSidebar);
    });

    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
    }

    document.querySelectorAll('#sidebar a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                document.body.classList.remove('sidebar-open');
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
