<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Guru') — ABSENSI NGAJI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-manahijulhuda.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        html, body {
            background: #f8fafc;
            overflow-x: hidden;
        }
        body { min-height: 100vh; }
        button, input, select, textarea {
            min-height: 44px;
            box-sizing: border-box;
        }
        .sidebar-link { transition: all 0.15s ease; }
        /* Must match Tailwind's lg: breakpoint (1024px) */
        @media (max-width: 1023px) {
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
            .guru-shell {
                margin-left: 0 !important;
            }
            .guru-header {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .guru-main {
                padding: 1rem !important;
            }
        }
        .guru-main {
            overflow-x: hidden;
            max-width: 100%;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen">
<div id="sidebarBackdrop" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-[2px] hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- SIDEBAR GURU BIASA --}}
    <aside id="sidebar" class="w-[230px] h-screen bg-white border-r border-slate-200/80 flex flex-col fixed left-0 top-0 z-50 shadow-sm transition-transform duration-300 lg:translate-x-0">
        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-slate-100 flex items-center gap-2.5">
            <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo Manahijul Huda" class="w-9 h-9 rounded-xl object-contain shrink-0">
            <div>
                <p class="text-[13px] font-extrabold text-slate-800 leading-tight tracking-tight">PANEL GURU</p>
                <p class="text-[10px] text-slate-400 font-medium leading-none mt-0.5">Manahijul Huda</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <a href="{{ route('guru.dashboard') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('guru.dashboard') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('guru.jadwal') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('guru.jadwal') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Jadwal Mengajar
            </a>
            <a href="{{ route('guru.absensi-mengajar') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('guru.absensi-mengajar') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Absensi Mengajar
            </a>
            <a href="{{ route('guru.absensi-santri') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('guru.absensi-santri') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Absensi Santri
            </a>
            <a href="{{ route('guru.riwayat-mengajar') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('guru.riwayat-mengajar') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>
                Riwayat Mengajar
            </a>
            <a href="{{ route('guru.profil') }}"
               class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs('guru.profil') ? 'bg-[#1a4731] text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profil Saya
            </a>
        </nav>

        {{-- Profile User Bottom --}}
        @php
            $authUser = Auth::guard('web')->user() ?: Auth::user();
        @endphp
        <div class="p-3 border-t border-slate-100">
            <div class="flex items-center gap-3 px-2 py-2 mb-2">
                <div class="w-9 h-9 rounded-full bg-[#1a4731] text-white flex items-center justify-center font-bold text-xs shrink-0">
                    {{ strtoupper(substr($authUser->name ?? 'G', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $authUser->name ?? 'Guru' }}</p>
                    <p class="text-[10px] text-emerald-700 font-semibold uppercase tracking-wider">Role: Guru Pengajar</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 text-xs font-bold transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar Aplikasi
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN CONTENT SHELL --}}
    <div class="guru-shell lg:ml-[230px] min-h-screen flex flex-col">
        {{-- Topbar --}}
        <header class="guru-header bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="text-xs text-slate-500 font-medium hidden sm:block">
                    <span>Panel Guru</span> &rsaquo; <span class="font-bold text-slate-800">@yield('breadcrumb', 'Dashboard')</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @php
                    $guruUser = Auth::guard('web')->user() ?: Auth::user();
                    $guruPending = 0;
                    if ($guruUser?->guru_id) {
                        $guruPending = \App\Models\TeacherAttendance::where('guru_id', $guruUser->guru_id)
                            ->where('approval_status', 'pending')->count();
                    }
                @endphp
                @if($guruPending > 0)
                    <a href="{{ route('guru.absensi-santri') }}" title="Permintaan Mengajar Menunggu Persetujuan" class="relative flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-800 text-xs font-bold rounded-xl hover:bg-amber-100 transition">
                        <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        {{ $guruPending }} Menunggu Persetujuan
                    </a>
                @endif
                <span class="text-xs font-semibold px-3 py-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl">
                    🟢 Guru Pengajar
                </span>
            </div>
        </header>

        {{-- Main Body --}}
        <main class="guru-main flex-1 p-6">
            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-open');
            document.getElementById('sidebarBackdrop').classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
