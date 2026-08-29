@extends('layouts.app')

@section('title', 'Input Manual Absensi')

@section('content')
<main class="mx-auto max-w-5xl px-4 py-8">

    <!-- Breadcrumb / Back Link -->
    <div class="mb-6">
        <a href="{{ route('attendance.daily.report') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Laporan Harian <span class="text-slate-400">/ Input Manual Absensi & Keterangan</span>
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-slate-900">Input Manual Absensi & Keterangan</h1>
        <p class="mt-2 text-sm text-slate-600">Kelola data kehadiran santri secara manual berdasarkan sesi waktu sholat</p>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700">{{ $errors->first() }}</div>
    @endif

    <!-- Filter Card -->
    <div class="mb-6 rounded-lg bg-white p-5 shadow-sm border border-slate-200">
        <form action="{{ route('attendance.manual.page') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="flex-1 w-full relative">
                <label class="block text-sm font-semibold text-slate-800 mb-2">Pilih Kelas:</label>
                <div class="relative">
                    <select name="classroom_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-500 focus:outline-none">
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ $classroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
            <div class="flex-1 w-full relative">
                <label class="block text-sm font-semibold text-slate-800 mb-2">Pilih Sesi Waktu (Otomatis / Manual):</label>
                <div class="relative">
                    <select name="session" class="w-full appearance-none rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 focus:border-violet-500 focus:outline-none">
                        @foreach($sessions as $sesiOption)
                            <option value="{{ $sesiOption }}" {{ $session === $sesiOption ? 'selected' : '' }}>{{ $sesiOption }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
            <div class="w-full sm:w-auto">
                <button type="submit" class="w-full sm:w-auto rounded-lg bg-[#5b21b6] px-8 py-3 text-sm font-medium text-white hover:bg-[#4c1d95] transition">Tampilkan Data</button>
            </div>
        </form>
    </div>

    <!-- Table Form -->
    <form action="{{ route('attendance.manual.post') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
        <input type="hidden" name="session" value="{{ $session }}">

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-[#f8fafc] font-bold text-slate-700 uppercase text-xs border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">NO</th>
                            <th class="px-6 py-4">NAMA SANTRI</th>
                            <th class="px-6 py-4">KELAS</th>
                            <th class="px-6 py-4">STATUS (SESI: {{ strtoupper($session) }})</th>
                            <th class="px-6 py-4">KETERANGAN / ALASAN *(WAJIB JIKA SAKIT/IZIN)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($santriList as $index => $santri)
                            @php
                                $attendance = $attendanceBySantri->get($santri->id);
                                $alreadyAttended = (bool) $attendance;
                                $status = $attendance?->status ?? old("attendance.$index.status", 'Alfa');
                                $notes = $attendance?->notes ?? old("attendance.$index.notes");
                            @endphp
                            <tr class="hover:bg-slate-50/50 {{ $alreadyAttended ? 'bg-slate-50' : '' }}">
                                <td class="px-6 py-4">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $santri->name }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-[#e0f2fe] text-[#0284c7]">
                                        {{ $santri->classroom?->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($alreadyAttended)
                                        @php
                                            $badge = match($attendance->status) {
                                                'Hadir' => 'bg-emerald-100 text-emerald-700',
                                                'Sakit' => 'bg-orange-100 text-orange-700',
                                                'Izin' => 'bg-yellow-100 text-yellow-700',
                                                'Alfa', 'Belum Absen' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-100 text-slate-700',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $attendance->status }}</span>
                                    @else
                                        <input type="hidden" name="attendance[{{ $index }}][santri_id]" value="{{ $santri->id }}">
                                        <div class="relative w-full min-w-[120px]">
                                            <select name="attendance[{{ $index }}][status]" class="w-full appearance-none rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-violet-500 focus:outline-none">
                                                @foreach(['Hadir', 'Sakit', 'Izin', 'Alfa'] as $option)
                                                    <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </div>
                                        <input type="hidden" name="attendance[{{ $index }}][scan_time]" value="{{ now()->format('Y-m-d H:i:s') }}">
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($alreadyAttended)
                                        {{ $attendance->notes ?: '-' }}
                                    @else
                                        <input type="text" name="attendance[{{ $index }}][notes]" value="{{ $notes }}" class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-violet-500 focus:outline-none placeholder:text-slate-400" placeholder="Tulis alasan jika Sakit/Izin...">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="w-full rounded-lg bg-[#5b21b6] px-6 py-4 text-sm font-semibold text-white hover:bg-[#4c1d95] transition">Simpan Semua Absen Sesi {{ $session }}</button>
    </form>
</main>
@endsection
