@extends('layouts.admin')

@section('title', 'Rekap Absensi Guru')
@section('breadcrumb', 'Rekap Absensi Guru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Rekap Absensi Guru</h1>
            <p class="text-sm text-slate-500 mt-1">Laporan & rekapitulasi kehadiran guru berdasarkan sesi, hari, minggu (Sabtu–Jumat), bulan, semester, dan tahun.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.teacher-attendance.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Absensi Mengajar
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.teacher-attendance.rekap') }}" id="rekapFilterForm" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mr-2">Pilih Jenis Periode:</span>
            @php
                $periodes = [
                    'sesi'     => 'Per Sesi',
                    'harian'   => 'Per Hari',
                    'mingguan' => 'Per Minggu',
                    'bulanan'  => 'Per Bulan',
                    'semester' => 'Per Semester',
                    'tahun'    => 'Per Tahun',
                ];
            @endphp
            @foreach ($periodes as $pKey => $pLabel)
                <button type="submit" name="jenis_periode" value="{{ $pKey }}" 
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-xl border transition {{ $jenisPeriode === $pKey ? 'bg-[#1a4731] text-white border-[#1a4731] shadow-sm' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                    {{ $pLabel }}
                </button>
            @endforeach
        </div>

        <input type="hidden" name="jenis_periode" id="inputJenisPeriode" value="{{ $jenisPeriode }}">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="filterFieldsContainer">
            <!-- Field Tanggal (Sesi, Harian, Mingguan) -->
            <div id="fieldTanggal" class="filter-field">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                    {{ $jenisPeriode === 'mingguan' ? 'Tanggal Pada Minggu Yg Dipilih' : 'Tanggal' }}
                </label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
                @if($jenisPeriode === 'mingguan')
                    <p class="text-[11px] text-emerald-700 font-medium mt-1">📌 Minggu dimulai dari hari <strong>Sabtu</strong> hingga <strong>Jumat</strong>.</p>
                @endif
            </div>

            <!-- Field Bulan (Bulanan) -->
            <div id="fieldBulan" class="filter-field hidden">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Bulan</label>
                <select name="bulan" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    @php
                        $namaBulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    @endphp
                    @foreach($namaBulan as $mNum => $mName)
                        <option value="{{ $mNum }}" @selected($bulanFilter == $mNum)>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Field Semester (Semester) -->
            <div id="fieldSemester" class="filter-field hidden">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Semester</label>
                <select name="semester" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="1" @selected($semesterFilter == 1)>Semester 1 (Ganjil: Juli - Desember)</option>
                    <option value="2" @selected($semesterFilter == 2)>Semester 2 (Genap: Januari - Juni)</option>
                </select>
            </div>

            <!-- Field Tahun (Bulanan, Semester, Tahun) -->
            <div id="fieldTahun" class="filter-field hidden">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tahun</label>
                <select name="tahun" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    @for($y = date('Y'); $y >= 2024; $y--)
                        <option value="{{ $y }}" @selected($tahunFilter == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Field Sesi (Opsional / Wajib untuk Sesi) -->
            <div id="fieldSesi" class="filter-field">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                    Sesi {{ $jenisPeriode === 'sesi' ? '(Wajib)' : '(Opsional)' }}
                </label>
                <select name="sesi" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    @if($jenisPeriode !== 'sesi')
                        <option value="">Semua Sesi</option>
                    @endif
                    @foreach($availableSessions as $ses)
                        <option value="{{ $ses }}" @selected($sesiFilter === $ses)>{{ $ses }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Field Guru (Opsional) -->
            <div id="fieldGuru" class="filter-field">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Guru (Opsional)</label>
                <select name="guru_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="">Semua Guru</option>
                    @foreach($allGurus as $g)
                        <option value="{{ $g->id }}" @selected($guruIdFilter == $g->id)>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
            <span class="text-xs text-slate-400">Periode Aktif: <strong>{{ strtoupper($jenisPeriode) }}</strong></span>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.teacher-attendance.rekap') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Reset Filter
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#1a4731] hover:bg-[#143726] text-white text-xs font-bold shadow-sm transition">
                    Tampilkan Rekap
                </button>
            </div>
        </div>
    </form>

    <!-- Ringkasan Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Guru</p>
            <p class="text-xl font-extrabold text-slate-800 mt-1">{{ $globalSummary['total_guru'] }}</p>
        </div>
        <div class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Hadir</p>
            <p class="text-xl font-extrabold text-emerald-800 mt-1">{{ $globalSummary['hadir'] }}</p>
        </div>
        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-700">Izin</p>
            <p class="text-xl font-extrabold text-blue-800 mt-1">{{ $globalSummary['izin'] }}</p>
        </div>
        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Sakit</p>
            <p class="text-xl font-extrabold text-amber-800 mt-1">{{ $globalSummary['sakit'] }}</p>
        </div>
        <div class="bg-rose-50 p-4 rounded-2xl border border-rose-100 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Alfa</p>
            <p class="text-xl font-extrabold text-rose-800 mt-1">{{ $globalSummary['alfa'] }}</p>
        </div>
        <div class="bg-[#1a4731] text-white p-4 rounded-2xl shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Kehadiran</p>
            <p class="text-xl font-extrabold mt-1">{{ $globalSummary['persentase'] }}%</p>
        </div>
    </div>

    <!-- Tabel Hasil Rekap -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">
                    Data Rekap Absensi Guru — <span class="capitalize">{{ $jenisPeriode }}</span>
                </h3>
                @if($jenisPeriode === 'mingguan' && $startDate && $endDate)
                    <p class="text-xs text-slate-500">Rentang: <strong>{{ $startDate->format('d M Y') }}</strong> s/d <strong>{{ $endDate->format('d M Y') }}</strong> (Sabtu–Jumat)</p>
                @endif
            </div>
            <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                Total Entry: {{ count($rekapData) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            @if($jenisPeriode === 'sesi')
                <!-- TABEL PER SESI -->
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Nama Guru</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Sesi</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Jam Absen</th>
                            <th class="px-5 py-3">Kelas Diajar</th>
                            <th class="px-5 py-3">Kitab &amp; Materi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rekapData as $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-800 whitespace-nowrap">
                                    {{ $row['guru']->name }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y') }}
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $row['sesi'] }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @include('admin.teacher-attendance.partials.badge-status', ['status' => $row['status']])
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                    {{ $row['jam_absen'] }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-medium">
                                        {{ $row['classroom_name'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    @if($row['kitab'] !== '-')
                                        <strong>Kitab:</strong> {{ $row['kitab'] }} <br>
                                        <span class="text-xs text-slate-400">Materi: {{ Str::limit($row['materi'], 50) }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-400 font-medium">Belum ada data absensi untuk sesi ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($jenisPeriode === 'harian')
                <!-- TABEL PER HARI -->
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Nama Guru</th>
                            @foreach($availableSessions as $ses)
                                <th class="px-5 py-3 text-center">{{ $ses }}</th>
                            @endforeach
                            <th class="px-5 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rekapData as $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-800 whitespace-nowrap">
                                    {{ $row['guru']->name }}
                                </td>
                                @foreach($availableSessions as $ses)
                                    @php $sInfo = $row['sessions'][$ses] ?? ['status' => '-']; @endphp
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        @include('admin.teacher-attendance.partials.badge-status', ['status' => $sInfo['status']])
                                        @if(($sInfo['time'] ?? '-') !== '-')
                                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $sInfo['time'] }}</div>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-3.5 text-xs text-slate-500">
                                    {{ $row['keterangan'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-slate-400 font-medium">Belum ada data absensi untuk tanggal ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            @elseif($jenisPeriode === 'mingguan')
                <!-- TABEL PER MINGGU (SABTU-JUMAT) -->
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Nama Guru</th>
                            @foreach($daysOfWeek as $d)
                                <th class="px-3 py-3 text-center" title="{{ $d['name'] }} ({{ $d['date'] }})">
                                    <div>{{ $d['short'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $d['formatted'] }}</div>
                                </th>
                            @endforeach
                            <th class="px-5 py-3 text-center">Total Hadir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rekapData as $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-800 whitespace-nowrap">
                                    {{ $row['guru']->name }}
                                </td>
                                @foreach($daysOfWeek as $d)
                                    @php $code = $row['daily_map'][$d['date']] ?? '-'; @endphp
                                    <td class="px-3 py-3.5 text-center font-extrabold whitespace-nowrap">
                                        @php
                                            $codeClass = match($code) {
                                                'H' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                                'I' => 'bg-blue-100 text-blue-800 border-blue-300',
                                                'S' => 'bg-amber-100 text-amber-800 border-amber-300',
                                                'A' => 'bg-rose-100 text-rose-800 border-rose-300',
                                                'L' => 'bg-slate-200 text-slate-700 border-slate-300',
                                                default => 'text-slate-300 font-normal',
                                            };
                                        @endphp
                                        @if($code !== '-')
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg border text-xs {{ $codeClass }}">
                                                {{ $code }}
                                            </span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-5 py-3.5 text-center whitespace-nowrap font-extrabold text-slate-800">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-xs">
                                        {{ $row['total_hadir'] }} Hari
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-10 text-center text-slate-400 font-medium">Belum ada data absensi untuk minggu ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex flex-wrap items-center gap-4">
                    <span class="font-bold uppercase tracking-wider text-slate-400">Keterangan Singkatan:</span>
                    <span><strong class="text-emerald-700">H</strong> = Hadir</span>
                    <span><strong class="text-blue-700">I</strong> = Izin</span>
                    <span><strong class="text-amber-700">S</strong> = Sakit</span>
                    <span><strong class="text-rose-700">A</strong> = Alfa</span>
                    <span><strong class="text-slate-600">L</strong> = Libur</span>
                </div>

            @else
                <!-- TABEL PER BULAN / PER SEMESTER / PER TAHUN -->
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Nama Guru</th>
                            <th class="px-5 py-3 text-right">Hadir</th>
                            <th class="px-5 py-3 text-right">Izin</th>
                            <th class="px-5 py-3 text-right">Sakit</th>
                            <th class="px-5 py-3 text-right">Alfa</th>
                            <th class="px-5 py-3 text-right">Total Sesi</th>
                            <th class="px-5 py-3 text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rekapData as $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-800 whitespace-nowrap">
                                    {{ $row['guru']->name }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-emerald-700">{{ $row['hadir'] }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-blue-700">{{ $row['izin'] }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-amber-700">{{ $row['sakit'] }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-rose-700">{{ $row['alfa'] }}</td>
                                <td class="px-5 py-3.5 text-right font-extrabold text-slate-800">{{ $row['total_sesi'] }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap font-extrabold">
                                    <span class="px-2.5 py-1 rounded-full text-xs {{ $row['persentase'] >= 85 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($row['persentase'] >= 70 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-rose-50 text-rose-800 border border-rose-200') }}">
                                        {{ number_format($row['persentase'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-slate-400 font-medium">Belum ada data absensi untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jenisPeriode = @json($jenisPeriode);

        const fieldTanggal  = document.getElementById('fieldTanggal');
        const fieldBulan    = document.getElementById('fieldBulan');
        const fieldSemester = document.getElementById('fieldSemester');
        const fieldTahun    = document.getElementById('fieldTahun');

        // Reset visibility
        fieldTanggal.classList.add('hidden');
        fieldBulan.classList.add('hidden');
        fieldSemester.classList.add('hidden');
        fieldTahun.classList.add('hidden');

        if (jenisPeriode === 'sesi' || jenisPeriode === 'harian' || jenisPeriode === 'mingguan') {
            fieldTanggal.classList.remove('hidden');
        } else if (jenisPeriode === 'bulanan') {
            fieldBulan.classList.remove('hidden');
            fieldTahun.classList.remove('hidden');
        } else if (jenisPeriode === 'semester') {
            fieldSemester.classList.remove('hidden');
            fieldTahun.classList.remove('hidden');
        } else if (jenisPeriode === 'tahun') {
            fieldTahun.classList.remove('hidden');
        }
    });
</script>
@endsection
