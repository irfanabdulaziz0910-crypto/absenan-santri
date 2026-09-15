<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Link Undangan Tidak Valid' }} — ABSENSI NGAJI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-manahijulhuda.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden text-center p-6 space-y-5">
        <div class="w-16 h-16 rounded-full bg-amber-100 text-amber-600 mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>

        <div>
            <h1 class="text-xl font-extrabold text-slate-800">{{ $title ?? 'Link Undangan Tidak Valid' }}</h1>
            <p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ $message }}</p>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2">
            <a href="{{ route('admin.login') }}"
                class="w-full inline-flex items-center justify-center gap-2 py-3 bg-[#1a4731] hover:bg-[#143726] text-white font-bold text-sm rounded-xl transition shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Halaman Login Guru
            </a>
        </div>
    </div>
</body>
</html>
