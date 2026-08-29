@extends('layouts.guru')

@section('title', 'Jadwal Mengajar Saya')
@section('breadcrumb', 'Jadwal Mengajar')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Jadwal Mengajar Saya</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar jadwal mengajar master yang ditetapkan oleh Admin.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 border border-purple-200 rounded-xl text-xs font-semibold text-purple-800">
            Total {{ $schedules->count() }} Jadwal Mengajar
        </div>
    </div>

    {{-- Session Legend --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="border bg-purple-50 border-purple-200 text-purple-700 rounded-xl px-4 py-3 text-center">
            <p class="text-xs font-bold uppercase tracking-wider">Subuh</p>
            <p class="text-[11px] opacity-80 mt-0.5">04:00 – 08:00</p>
        </div>
        <div class="border bg-amber-50 border-amber-200 text-amber-700 rounded-xl px-4 py-3 text-center">
            <p class="text-xs font-bold uppercase tracking-wider">Dzuhur</p>
            <p class="text-[11px] opacity-80 mt-0.5">11:30 – 14:59</p>
        </div>
        <div class="border bg-orange-50 border-orange-200 text-orange-700 rounded-xl px-4 py-3 text-center">
            <p class="text-xs font-bold uppercase tracking-wider">Ashar</p>
            <p class="text-[11px] opacity-80 mt-0.5">15:00 – 17:59</p>
        </div>
        <div class="border bg-indigo-50 border-indigo-200 text-indigo-700 rounded-xl px-4 py-3 text-center">
            <p class="text-xs font-bold uppercase tracking-wider">Isya</p>
            <p class="text-[11px] opacity-80 mt-0.5">18:00 – 23:59</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Master Jadwal Mengajar</h2>
            <span class="text-xs text-slate-400">Total: {{ $schedules->count() }} Record</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Hari</th>
                        <th class="px-6 py-3.5">Sesi & Jam</th>
                        <th class="px-6 py-3.5">Kelas</th>
                        <th class="px-6 py-3.5">Kitab</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $s)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 font-bold text-slate-800">
                            <span class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-bold text-slate-700">
                                {{ $s->hari }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $sesiColor = match($s->session) {
                                'Subuh'  => 'bg-purple-50 border-purple-200 text-purple-700',
                                'Dzuhur' => 'bg-amber-50 border-amber-200 text-amber-700',
                                'Ashar'  => 'bg-orange-50 border-orange-200 text-orange-700',
                                'Isya'   => 'bg-indigo-50 border-indigo-200 text-indigo-700',
                                default  => 'bg-slate-50 border-slate-200 text-slate-700',
                            };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border text-xs font-bold {{ $sesiColor }}">
                                {{ $s->session }}
                            </span>
                            <span class="text-xs text-slate-500 ml-1.5">{{ $s->jam_mulai }} – {{ $s->jam_selesai }}</span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ $s->classroom->name ?? 'Tanpa Kelas' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-medium">
                            {{ $s->kitab ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200">
                                Aktif
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <p class="font-bold text-slate-600">Belum ada jadwal mengajar master.</p>
                            <p class="text-xs text-slate-400 mt-1">Admin belum membuatkan jadwal mengajar untuk Anda.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
