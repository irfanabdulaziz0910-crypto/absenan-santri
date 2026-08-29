@extends('layouts.admin')

@section('title', 'Absensi Mengajar Guru')
@section('breadcrumb', 'Absensi Mengajar Guru')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Absensi Mengajar Guru</h1>
            <p class="text-sm text-slate-500 mt-1">Monitoring & laporan aktivitas mengajar, kitab, dan jurnal materi seluruh guru.</p>
        </div>
        <div class="flex items-center gap-2">
            @php $totalPending = \App\Models\TeacherAttendance::where('approval_status','pending')->count(); @endphp
            @if($totalPending > 0)
                <span class="text-xs font-extrabold px-3 py-1.5 bg-amber-500 text-white rounded-xl animate-pulse flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    {{ $totalPending }} Menunggu Persetujuan
                </span>
            @endif
            <span class="text-xs font-semibold px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">
                Total Record: {{ $attendances->total() }}
            </span>
        </div>
    </div>

    {{-- PENDING APPROVAL BANNER --}}
    @php
        $pendingItems = \App\Models\TeacherAttendance::with(['guru','classroom'])
            ->where('approval_status','pending')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    @endphp
    @if($pendingItems->count() > 0)
    <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            </div>
            <div>
                <p class="font-extrabold text-amber-900 text-sm">🔔 Permintaan Mengajar — Menunggu Persetujuan Anda ({{ $pendingItems->count() }})</p>
                <p class="text-xs text-amber-700">Guru meminta izin mengajar di kelas di luar kelas induknya. Setujui atau tolak di bawah ini.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($pendingItems as $req)
            <div class="bg-white border border-amber-200 rounded-xl p-4 flex flex-col gap-3 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-bold text-slate-800 text-sm">{{ $req->guru->name ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Kelas: <strong class="text-slate-700">{{ $req->classroom->name ?? '-' }}</strong>
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $req->date?->format('d/m/Y') }} &bull; Sesi: <strong>{{ $req->session ?: '-' }}</strong>
                        </p>
                    </div>
                    <span class="shrink-0 text-[10px] bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full">PENDING</span>
                </div>
                <div class="bg-slate-50 rounded-lg border border-slate-100 px-3 py-2 text-xs text-slate-600">
                    <span class="font-semibold">Kitab:</span> {{ $req->kitab }}<br>
                    <span class="font-semibold">Materi:</span> {{ Str::limit($req->materi, 80) }}
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <form method="POST" action="{{ route('admin.teacher-attendance.approve', $req->id) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition">✓ SETUJUI</button>
                    </form>
                    <form method="POST" action="{{ route('admin.teacher-attendance.reject', $req->id) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-xs shadow-sm transition">✕ TOLAK</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filter Section -->
    <form method="GET" action="{{ route('admin.teacher-attendance.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 mr-2">Periode Laporan:</span>
            @foreach (['' => 'Semua', 'harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'semester' => 'Semester'] as $pKey => $pLabel)
                <button type="submit" name="period" value="{{ $pKey }}" class="px-3 py-1.5 text-xs font-semibold rounded-xl border transition {{ ($filters['period'] ?? '') === $pKey ? 'bg-[#1a4731] text-white border-[#1a4731]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                    {{ $pLabel }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Guru</label>
                <select name="guru_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="">Semua Guru</option>
                    @foreach ($gurus as $guru)
                        <option value="{{ $guru->id }}" @selected(($filters['guru_id'] ?? '') == $guru->id)>{{ $guru->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Kelas Diajar</label>
                <select name="classroom_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected(($filters['classroom_id'] ?? '') == $classroom->id)>{{ $classroom->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Kitab</label>
                <input type="search" name="kitab" value="{{ $filters['kitab'] ?? '' }}" placeholder="Nama kitab..." class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a4731] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-[#1a4731] outline-none">
                    <option value="">Semua Status</option>
                    @foreach (['Hadir','Izin','Sakit','Alfa'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-1">
            <a href="{{ route('admin.teacher-attendance.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                Reset Filter
            </a>
            <button type="submit" class="px-5 py-2 rounded-xl bg-[#1a4731] hover:bg-[#143726] text-white text-xs font-bold shadow-sm transition">
                Terapkan Filter
            </button>
        </div>
    </form>

    <!-- Table Section -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Nama Guru</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Waktu / Sesi</th>
                        <th class="px-5 py-3">Kelas Diajar</th>
                        <th class="px-5 py-3">Kitab Pelajaran</th>
                        <th class="px-5 py-3">Materi Pembelajaran</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-center">Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($attendances as $attendance)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-800 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-[#1a4731]/10 text-[#1a4731] flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($attendance->guru->name ?? 'G', 0, 2)) }}
                                    </div>
                                    <span>{{ $attendance->guru->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                {{ $attendance->date ? $attendance->date->format('d-m-Y') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                {{ $attendance->attendance_time ? $attendance->attendance_time->format('H:i') : '-' }}
                                @if ($attendance->session)
                                    <span class="text-xs text-slate-400">({{ $attendance->session }})</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-medium whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700">
                                    {{ $attendance->classroom->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $attendance->kitab }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 min-w-[260px]">
                                <div class="line-clamp-2" id="materi-preview-{{ $attendance->id }}">
                                    {{ $attendance->materi }}
                                </div>
                                @if (strlen($attendance->materi) > 80)
                                    <button type="button" onclick="showMateriModal('{{ e($attendance->guru->name) }}', '{{ e($attendance->kitab) }}', '{{ e($attendance->materi) }}')" class="text-xs text-[#1a4731] font-bold hover:underline mt-1 block">
                                        Lihat Detail Materi →
                                    </button>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @php
                                    $badgeClass = match ($attendance->status) {
                                        'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Izin'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Alfa'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full border text-xs font-bold {{ $badgeClass }}">
                                    {{ $attendance->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                @php $appStatus = $attendance->approval_status ?? 'approved'; @endphp
                                @if ($appStatus === 'pending')
                                    <div class="flex items-center justify-center gap-1.5">
                                        <form method="POST" action="{{ route('admin.teacher-attendance.approve', $attendance->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                                SETUJUI
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.teacher-attendance.reject', $attendance->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-xs shadow-sm transition">
                                                TOLAK
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($appStatus === 'approved')
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-extrabold">
                                            ✓ DISETUJUI
                                        </span>
                                        @if($attendance->approvedBy)
                                            <span class="text-[10px] text-slate-400 font-medium">Oleh: {{ $attendance->approvedBy->name }}</span>
                                        @endif
                                    </div>
                                @elseif ($appStatus === 'rejected')
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-extrabold">
                                            ✕ DITOLAK
                                        </span>
                                        @if($attendance->approvedBy)
                                            <span class="text-[10px] text-slate-400 font-medium">Oleh: {{ $attendance->approvedBy->name }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                                        {{ strtoupper($appStatus) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                                <p class="font-medium">Belum ada data absensi mengajar guru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $attendances->links() }}
        </div>
    </div>
</div>

<!-- Modal Detail Materi -->
<div id="materiModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeMateriModal()"></div>
    <div class="absolute left-1/2 top-1/2 w-[92%] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800" id="modalGuruName">Detail Materi Mengajar</h3>
                <p class="text-xs text-slate-400" id="modalKitabName">Kitab</p>
            </div>
            <button type="button" onclick="closeMateriModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <div class="p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Materi Pembelajaran:</p>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 whitespace-pre-line leading-relaxed" id="modalMateriText">
            </div>
        </div>
        <div class="flex justify-end px-5 py-3 border-t border-slate-100 bg-slate-50">
            <button type="button" onclick="closeMateriModal()" class="px-4 py-2 rounded-xl bg-[#1a4731] text-white text-xs font-bold">Tutup</button>
        </div>
    </div>
</div>

<script>
    function showMateriModal(guru, kitab, materi) {
        document.getElementById('modalGuruName').textContent = 'Guru: ' + guru;
        document.getElementById('modalKitabName').textContent = 'Kitab: ' + kitab;
        document.getElementById('modalMateriText').textContent = materi;
        document.getElementById('materiModal').classList.remove('hidden');
    }

    function closeMateriModal() {
        document.getElementById('materiModal').classList.add('hidden');
    }
</script>
@endsection
