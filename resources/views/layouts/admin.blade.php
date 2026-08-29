<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — ABSENSI NGAJI</title>
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
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .nav-active { position: relative; }
        .nav-active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 60%;
            background: #1a4731;
            border-radius: 0 4px 4px 0;
        }
        .sidebar-link { transition: all 0.15s ease; }
        #toast {
            transition: all 0.3s ease;
            transform: translateX(120%);
        }
        #toast.show {
            transform: translateX(0);
        }
        .modal-backdrop {
            backdrop-filter: blur(4px);
            background: rgba(0,0,0,0.3);
        }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c8d5c0; border-radius: 10px; }
        .responsive-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .responsive-mobile-card {
            display: block;
        }
        @media (min-width: 640px) {
            .responsive-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .responsive-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 991px) {
            #sidebar {
                transform: translateX(-110%);
                z-index: 50;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.2);
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
            .admin-shell {
                margin-left: 0 !important;
            }
            .admin-header {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            .admin-header .header-search,
            .admin-header .header-actions {
                display: none !important;
            }
            .admin-main {
                padding: 1rem !important;
            }
            .page-header {
                align-items: flex-start !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen">
<div id="sidebarBackdrop" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-[2px] hidden lg:hidden"></div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- SIDEBAR --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<aside id="sidebar" class="w-[220px] h-screen bg-white border-r border-slate-100 flex flex-col fixed left-0 top-0 z-50 shadow-sm transition-transform duration-300 lg:translate-x-0">

    {{-- Logo --}}
    <div class="px-5 py-5 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/logo-manahijulhuda.png') }}" alt="Logo Manahijul Huda" class="w-9 h-9 rounded-xl object-contain flex-shrink-0">
            <div>
                <p class="text-[13px] font-extrabold text-slate-800 leading-tight tracking-tight">ABSENSI NGAJI</p>
                <p class="text-[10px] text-slate-400 font-medium leading-none mt-0.5">Manahijul Huda</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5 hide-scrollbar">
        @php
            $navItems = [
                ['route' => 'admin.dashboard',             'label' => 'Dashboard',          'icon' => 'dashboard'],
                ['route' => 'admin.santri.index',          'label' => 'Data Santri',        'icon' => 'santri'],
                ['route' => 'admin.guru.index',            'label' => 'Data Guru',          'icon' => 'guru'],
                ['route' => 'admin.jadwal.index',          'label' => 'Kelola Jadwal',      'icon' => 'jadwal'],
                ['route' => 'admin.rfid',                  'label' => 'Absensi RFID',       'icon' => 'rfid'],
                ['route' => 'admin.setting-role',          'label' => 'Manajemen Akun',     'icon' => 'role'],
                ['route' => 'admin.teacher-schedule.index','label' => 'Jadwal Mengajar',    'icon' => 'jadwal'],
                ['route' => 'admin.laporan',               'label' => 'Laporan',            'icon' => 'laporan'],
                ['route' => 'admin.teacher-attendance.index', 'label' => 'Absensi Guru', 'icon' => 'laporan'],
            ];
        @endphp

        @foreach ($navItems as $item)
            @php
                $isActive = request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}"
               class="sidebar-link nav-{{ $isActive ? 'active' : 'inactive' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium mb-0.5
                      {{ $isActive
                            ? 'bg-[#e6f4ec] text-[#1a4731] font-semibold nav-active'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                <span class="{{ $isActive ? 'text-[#1a4731]' : 'text-slate-400' }}">
                    @include('admin.partials.nav-icon', ['icon' => $item['icon'], 'active' => $isActive])
                </span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Profile & Logout --}}
    <div class="p-3 border-t border-slate-100">
        <div class="flex items-center gap-3 px-2 py-2 mb-2">
            <div class="w-8 h-8 rounded-full bg-[#1a4731] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'AU', 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="text-[13px] font-bold text-slate-800 leading-tight truncate">{{ Auth::guard('admin')->user()->name ?? 'Admin Utama' }}</p>
                <p class="text-[11px] text-slate-400 font-medium leading-none mt-0.5">Administrator</p>
            </div>
        </div>
        <div class="space-y-2">
            <a href="{{ route('profile') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z"/></svg>
                Profil Saya
            </a>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-red-600 hover:border-red-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MAIN CONTENT --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="admin-shell flex flex-col min-h-screen lg:ml-[220px]">

    {{-- TOPBAR --}}
    <header class="admin-header sticky top-0 z-30 bg-white border-b border-slate-100 px-4 py-3 sm:px-6 flex items-center justify-between gap-3 shadow-sm">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" data-sidebar-toggle class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-700 shadow-sm lg:hidden" aria-label="Buka menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="flex items-center gap-2 text-sm text-slate-400 min-w-0">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700 font-medium hidden sm:inline">Beranda</a>
                <svg class="w-3.5 h-3.5 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-slate-700 font-semibold truncate">@yield('breadcrumb', 'Dashboard')</span>
            </div>
        </div>

        <div class="header-actions flex items-center gap-3">
            <div class="header-search relative hidden sm:block">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" placeholder="Cari data..."
                    class="pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl w-52 focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-400 transition">
            </div>

            @php
                $pendingCount = \App\Models\TeacherAttendance::where('approval_status', 'pending')->count();
            @endphp
            <a href="{{ route('admin.teacher-attendance.index') }}" title="Notifikasi Permintaan Mengajar" class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 hover:bg-slate-100 transition">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($pendingCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white rounded-full text-[10px] font-extrabold flex items-center justify-center animate-bounce border border-white">
                        {{ $pendingCount }}
                    </span>
                @endif
            </a>

            <div class="w-10 h-10 rounded-xl bg-[#1a4731] flex items-center justify-center text-white text-xs font-bold cursor-pointer">
                AU
            </div>
        </div>
    </header>

    <main class="admin-main flex-1 p-4 sm:p-6">
        @yield('content')
    </main>
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- TOAST NOTIFICATION --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-white text-sm font-medium max-w-sm">
    <div id="toast-icon" class="w-5 h-5 flex-shrink-0"></div>
    <span id="toast-msg"></span>
</div>

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        showToast('{{ session('success') }}', 'success');
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        showToast('{{ session('error') }}', 'error');
    });
</script>
@endif

<script>
function showToast(msg, type = 'success') {
    const toast = document.getElementById('toast');
    const icon  = document.getElementById('toast-icon');
    const msgEl = document.getElementById('toast-msg');

    msgEl.textContent = msg;

    if (type === 'success') {
        toast.className = toast.className.replace(/bg-\S+/, '');
        toast.classList.add('bg-[#1a4731]');
        icon.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
        </svg>`;
    } else {
        toast.classList.add('bg-red-600');
        icon.innerHTML = `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>`;
    }

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
}

function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
}

const sidebarToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
sidebarToggleButtons.forEach((button) => {
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
