@extends('layouts.admin')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] text-center bg-white rounded-3xl border border-slate-100 shadow-sm p-12">
    <div class="w-24 h-24 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-300 mb-6 shadow-inner">
        @include('admin.partials.nav-icon', ['icon' => $icon ?? 'dashboard', 'active' => false])
    </div>
    <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $title ?? 'Halaman' }}</h1>
    <p class="text-slate-500 max-w-sm leading-relaxed">Halaman ini sedang dalam tahap pengembangan dan akan segera tersedia pada versi berikutnya.</p>
    <a href="{{ route('admin.dashboard') }}" class="mt-8 px-6 py-2.5 bg-slate-100 text-slate-600 rounded-full text-sm font-bold hover:bg-slate-200 transition">Kembali ke Dashboard</a>
</div>
@endsection
