@extends('layouts.wali-kelas')

@section('title', 'Notifikasi Permintaan Mengajar')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <span>🔔 Notifikasi Permintaan Mengajar</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Persetujuan guru yang meminta izin mengajar untuk kelas <strong>{{ $assignedClassroom->name ?? 'Wali Kelas' }}</strong>.
            </p>
        </div>
        @if($assignedClassroom)
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 shrink-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Kelas Wali: {{ $assignedClassroom->name }}
            </div>
        @endif
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-medium text-red-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Check if Wali Kelas assigned --}}
    @if(!$assignedClassroom)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="font-bold text-amber-800">Kelas Wali Belum Ditetapkan</p>
                <p class="text-sm text-amber-700">Akun Anda belum terhubung dengan kelas wali. Hubungi Admin untuk menetapkan kelas wali Anda.</p>
            </div>
        </div>
    @else

        {{-- SECTION 1: PERMINTAAN PENDING --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">
                        {{ $pendingRequests->count() }}
                    </div>
                    <h2 class="font-bold text-slate-800 text-base">Permintaan Mengajar Menunggu Persetujuan</h2>
                </div>
                <span class="text-xs text-slate-400 font-medium">Memerlukan Tindakan Wali Kelas / Admin</span>
            </div>

            @if($pendingRequests->count() > 0)
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($pendingRequests as $req)
                        <div class="bg-white border-2 border-amber-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
                            <div>
                                <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-full bg-[#1a4731] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($req->guru->name ?? 'G', 0, 2)) }}
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-800 text-sm leading-tight">{{ $req->guru->name ?? '-' }}</h3>
                                            <p class="text-xs text-slate-400 mt-0.5">Pemohon Izin Mengajar</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 text-[10px] font-extrabold text-amber-800 bg-amber-100 rounded-full border border-amber-300 animate-pulse">
                                        PENDING
                                    </span>
                                </div>

                                <div class="mt-3 space-y-2 text-xs">
                                    <div class="flex justify-between text-slate-600">
                                        <span class="text-slate-400">Kelas Tujuan:</span>
                                        <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ $req->classroom->name ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600">
                                        <span class="text-slate-400">Tanggal:</span>
                                        <span class="font-semibold text-slate-700">{{ $req->date ? $req->date->format('d/m/Y') : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600">
                                        <span class="text-slate-400">Waktu / Sesi:</span>
                                        <span class="font-semibold text-slate-700">{{ $req->attendance_time ? $req->attendance_time->format('H:i') : '-' }} ({{ $req->session ?: '-' }})</span>
                                    </div>
                                    <div class="flex justify-between text-slate-600">
                                        <span class="text-slate-400">Kitab:</span>
                                        <span class="font-bold text-slate-800">{{ $req->kitab }}</span>
                                    </div>
                                </div>

                                <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600">
                                    <span class="font-semibold block text-slate-500 mb-1">Materi Pembelajaran:</span>
                                    <p class="italic leading-relaxed">"{{ $req->materi }}"</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                                <form method="POST" action="{{ route('wali-kelas.teacher-attendance.approve', $req->id) }}" class="flex-1">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-1 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        SETUJUI
                                    </button>
                                </form>
                                <button type="button" onclick="openRejectModal('{{ $req->id }}', '{{ e($req->guru->name ?? 'Guru') }}')"
                                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-1 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    TOLAK
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center text-slate-400">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="font-bold text-slate-600">Tidak ada permintaan mengajar yang pending.</p>
                    <p class="text-xs text-slate-400 mt-1">Semua permintaan izin mengajar untuk kelas wali Anda telah diproses.</p>
                </div>
            @endif
        </div>

        {{-- SECTION 2: RIWAYAT PERMINTAAN --}}
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800 text-base">Riwayat Persetujuan & Penolakan Mengajar</h2>
                <span class="text-xs text-slate-400">Terbaru (Maks. 30 Record)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3.5 whitespace-nowrap">Nama Guru</th>
                            <th class="px-6 py-3.5 whitespace-nowrap">Tanggal & Sesi</th>
                            <th class="px-6 py-3.5 whitespace-nowrap">Kitab</th>
                            <th class="px-6 py-3.5">Materi</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap">Status</th>
                            <th class="px-6 py-3.5 whitespace-nowrap">Keterangan / Diproses Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($historyRequests as $hist)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-bold text-slate-800 whitespace-nowrap">
                                    {{ $hist->guru->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                    {{ $hist->date ? $hist->date->format('d/m/Y') : '-' }}
                                    <span class="text-xs text-slate-400 ml-1">({{ $hist->session ?: '-' }})</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $hist->kitab }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 min-w-[200px]">
                                    <p class="line-clamp-2">{{ $hist->materi }}</p>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if($hist->approval_status === 'approved')
                                        <span class="px-3 py-1 text-xs font-extrabold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200">
                                            ✓ DISETUJUI
                                        </span>
                                    @elseif($hist->approval_status === 'rejected')
                                        <span class="px-3 py-1 text-xs font-extrabold text-rose-700 bg-rose-50 rounded-full border border-rose-200">
                                            ✕ DITOLAK
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-bold text-slate-600 bg-slate-100 rounded-full">
                                            {{ strtoupper($hist->approval_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                                    @if($hist->approvedBy)
                                        <span class="font-semibold text-slate-700">Oleh: {{ $hist->approvedBy->name }}</span>
                                    @else
                                        <span class="italic text-slate-400">Oleh Admin / System</span>
                                    @endif
                                    @if($hist->approval_status === 'rejected' && $hist->rejection_reason)
                                        <br><span class="text-rose-600 italic">"{{ $hist->rejection_reason }}"</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                    Belum ada riwayat persetujuan permintaan mengajar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif

</div>

{{-- MODAL REJECT --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeRejectModal()"></div>
    <div class="absolute left-1/2 top-1/2 w-[92%] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-2xl overflow-hidden p-6 space-y-4">
        <h3 class="text-base font-bold text-slate-800">Tolak Permintaan Mengajar</h3>
        <p class="text-xs text-slate-500">Berikan alasan penolakan permintaan mengajar dari <strong id="rejectGuruName" class="text-slate-800"></strong>.</p>
        
        <form id="rejectForm" method="POST" action="">
            @csrf @method('PATCH')
            <div class="mb-4">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Alasan Penolakan</label>
                <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Jadwal bentrok dengan kegiatan kelas."
                    class="w-full border border-slate-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition">Konfirmasi Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(id, guruName) {
        const actionUrl = "{{ url('/wali-kelas/absensi-mengajar') }}/" + id + "/reject";
        document.getElementById('rejectForm').action = actionUrl;
        document.getElementById('rejectGuruName').textContent = guruName;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
