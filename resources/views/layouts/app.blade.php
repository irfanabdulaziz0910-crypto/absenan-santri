<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Absensi Santri')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Menyembunyikan scrollbar bawaan browser pada sidebar agar terlihat bersih */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen bg-[#f8f9fa] text-slate-800">

    <!-- SIDEBAR NAV (FIXED KIRI) -->
    <aside class="w-64 h-screen bg-white border-r border-slate-100 flex flex-col fixed left-0 top-0 z-50 shadow-sm">
        
        <!-- Logo Area -->
        <div class="h-24 flex items-center px-8 border-b border-transparent">
            <div class="flex items-center gap-3">
                <div class="bg-[#0f4a30] p-2.5 rounded-xl shadow-sm">
                    <!-- Icon Buku / Quran -->
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <span class="text-xl font-extrabold text-slate-800 tracking-tight">Absen Ngaji</span>
            </div>
        </div>

        <!-- Menu Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-8 hide-scrollbar">
            
            <!-- SECTION: MANAGEMENT -->
            <div>
                <h3 class="px-4 text-[10px] font-bold text-slate-400 tracking-widest uppercase mb-3">Management</h3>
                <ul class="space-y-1">
                    <!-- Menu: Laporan Harian (Dijadikan Dashboard) -->
                    <li>
                        <a href="{{ route('attendance.daily.report') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all text-sm {{ request()->routeIs('attendance.daily.report') ? 'bg-[#e2f3e9] text-[#0f4a30] font-bold shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 font-medium' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('attendance.daily.report') ? 'text-[#0f4a30]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard Harian
                        </a>
                    </li>
                    <!-- Visual Dummy Items (Agar sama persis dengan gambar) -->
                    <li>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors font-medium text-sm">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Data Santri
                        </a>
                    </li>
                    <li>
                        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors font-medium text-sm">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Data Guru
                        </a>
                    </li>
                </ul>
            </div>

            <!-- SECTION: OPERATIONS -->
            <div>
                <h3 class="px-4 text-[10px] font-bold text-slate-400 tracking-widest uppercase mb-3">Operations</h3>
                <ul class="space-y-1">
                    <!-- Menu: Scan Barcode -->
                    <li>
                        <a href="{{ route('attendance.scan.page') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all text-sm {{ request()->routeIs('attendance.scan.page') ? 'bg-[#e2f3e9] text-[#0f4a30] font-bold shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 font-medium' }}">
                            <!-- Icon Barcode/Scan -->
                            <svg class="w-5 h-5 {{ request()->routeIs('attendance.scan.page') ? 'text-[#0f4a30]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            Scan Kehadiran
                        </a>
                    </li>
                    <!-- Menu: Jurnal Bulanan -->
                    <li>
                        <a href="{{ route('attendance.monthly.journal') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all text-sm {{ request()->routeIs('attendance.monthly.journal') ? 'bg-[#e2f3e9] text-[#0f4a30] font-bold shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700 font-medium' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('attendance.monthly.journal') ? 'text-[#0f4a30]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Jurnal Bulanan
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Bottom Profile Area -->
        <div class="p-6 mt-auto border-t border-slate-100">
            <div class="bg-[#f4f7fb] rounded-2xl p-3 flex items-center justify-between border border-slate-100 hover:border-slate-200 transition-colors cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#0f4a30] flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-bold text-slate-800 leading-none">Admin Pusat</h4>
                        <p class="text-[11px] font-medium text-slate-500 mt-1">Administrator</p>
                    </div>
                </div>
                <button class="text-slate-400 hover:text-slate-700 transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- WRAPPER KONTEN UTAMA (Kanan) -->
    <!-- ml-64 digunakan untuk memberi ruang pada sidebar sebesar 16rem agar tidak tertimpa -->
    <div class="ml-64 flex flex-col min-h-screen">
        
        <!-- HEADER ATAS (Tempat Action Button) -->
        <header class="h-24 px-8 flex items-center justify-end gap-4 bg-transparent w-full">
            
            <!-- Logika Button Export Persis Milik Anda (Hanya beda di class Tailwind) -->
            @if(request()->routeIs('attendance.daily.report'))
                <a href="{{ route('attendance.daily.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Harian (CSV)
                </a>
            @elseif(request()->routeIs('attendance.monthly.journal'))
                <a href="{{ route('attendance.monthly.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Bulanan (CSV)
                </a>
            @else
                <a href="{{ route('attendance.daily.report') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    Lihat Laporan Harian
                </a>
            @endif

            <!-- Button Input Manual (Mengambil Route milik Anda, tampilan mengikuti gambar) -->
            <a href="{{ route('attendance.manual.page') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#0f4a30] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#0b3824] transition-all shadow-sm {{ request()->routeIs('attendance.manual.page') || request()->routeIs('attendance.manual.page.legacy') ? 'ring-4 ring-emerald-500/30' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Input Manual
            </a>
            
        </header>

        <!-- AREA KONTEN (Dashboard/Halaman Lain akan dimuat di sini) -->
        <main>
            @yield('content')
        </main>
        
    </div>

</body>
</html>