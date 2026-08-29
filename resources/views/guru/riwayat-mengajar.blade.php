@extends('layouts.guru')

@section('title', 'Riwayat Mengajar')
@section('breadcrumb', 'Riwayat Mengajar')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Riwayat Mengajar Saya</h1>
            <p class="text-sm text-slate-500 mt-1">Catatan riwayat kehadiran mengajar, kitab, dan materi pembelajaran yang pernah diisi.</p>
        </div>
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800">
            Total {{ $attendances->count() }} Record Mengajar
        </div>
    </div>

    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-800">Riwayat Pembelajaran</h2>
            <span class="text-xs text-slate-400">Total: {{ $attendances->count() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Waktu / Sesi</th>
                        <th class="px-6 py-3.5">Kelas</th>
                        <th class="px-6 py-3.5">Kitab</th>
                        <th class="px-6 py-3.5">Materi Pembelajaran</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $att)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">
                            {{ $att->date ? $att->date->format('d-m-Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                            {{ $att->attendance_time ? $att->attendance_time->format('H:i') : '-' }}
                            @if($att->session)
                                <span class="text-xs text-slate-400">({{ $att->session }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-700 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                                {{ $att->classroom->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">
                            {{ $att->kitab }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 min-w-[280px]">
                            {{ $att->materi }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @php
                            $badgeClass = match($att->status) {
                                'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'Izin'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'Alfa'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border text-xs font-bold {{ $badgeClass }}">
                                {{ $att->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <p class="font-bold text-slate-600">Belum ada riwayat mengajar.</p>
                            <p class="text-xs text-slate-400 mt-1">Absensi mengajar yang Anda simpan akan secara otomatis tercatat di sini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
