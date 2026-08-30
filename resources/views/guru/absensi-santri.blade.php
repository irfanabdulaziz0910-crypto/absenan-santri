@extends('layouts.guru')

@section('title', 'Absensi Santri')
@section('breadcrumb', 'Absensi Santri')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Absensi Santri</h1>
            <p class="text-slate-500 text-sm mt-1">Absensi santri otomatis terbuka setelah Anda melakukan Absensi Mengajar.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-2 shadow-sm min-h-[44px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL</span>
                <input type="date" id="selTanggal" value="{{ $date }}" onchange="updateFilters()"
                    class="text-xs font-semibold text-slate-700 outline-none bg-transparent cursor-pointer">
            </div>
            <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-2 shadow-sm min-h-[44px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SESI NGAJI</span>
                <select id="selSesi" onchange="updateFilters()"
                    class="text-xs font-bold text-slate-800 outline-none bg-transparent cursor-pointer">
                    <option value="Subuh"  @selected($sessionAktif === 'Subuh')>Subuh</option>
                    <option value="Dzuhur" @selected($sessionAktif === 'Dzuhur')>Dzuhur</option>
                    <option value="Ashar"  @selected($sessionAktif === 'Ashar')>Ashar</option>
                    <option value="Isya"   @selected($sessionAktif === 'Isya')>Isya</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Toast Notification --}}
    <div id="toastAlert" class="hidden rounded-xl border px-4 py-3 text-sm font-medium flex items-center justify-between transition-all duration-300">
        <div class="flex items-center gap-2">
            <svg id="toastIcon" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
            <span id="toastMessage"></span>
        </div>
        <button onclick="hideToast()" class="text-xs font-bold opacity-70 hover:opacity-100">&times;</button>
    </div>

    @if(!$hasAbsenMengajar)
        {{-- BLOCKED ALERT CARD: GURU BELUM ABSEN MENGAJAR / PENDING / REJECTED --}}
        <div class="bg-white border border-amber-200/80 rounded-2xl p-8 shadow-sm text-center max-w-2xl mx-auto space-y-5 my-6">
            @if(($approvalStatus ?? null) === 'pending')
                <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto shrink-0 border border-amber-200 animate-pulse">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold uppercase tracking-wider mb-2">Menunggu Persetujuan</span>
                    <h2 class="text-xl font-extrabold text-slate-800">Permintaan Mengajar Belum Disetujui</h2>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Permintaan izin mengajar kelas <strong>{{ $activeTeacherAttendance?->classroom?->name ?? 'pilihan' }}</strong> sesi <strong class="text-slate-700">{{ $sessionAktif }}</strong> telah dikirim. Membutuhkan persetujuan dari <strong>Admin</strong> atau <strong>Wali Kelas</strong> sebelum Absensi Santri dapat dibuka.
                    </p>
                </div>
            @elseif(($approvalStatus ?? null) === 'rejected')
                <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto shrink-0 border border-rose-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <span class="inline-block px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold uppercase tracking-wider mb-2">Permintaan Ditolak</span>
                    <h2 class="text-xl font-extrabold text-slate-800">Permintaan Mengajar Ditolak</h2>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Permintaan izin mengajar kelas <strong>{{ $activeTeacherAttendance?->classroom?->name ?? 'pilihan' }}</strong> ditolak oleh Admin/Wali Kelas.
                        @if($activeTeacherAttendance?->rejection_reason)
                            <br><span class="text-rose-600 italic font-semibold mt-1 inline-block">"Catatan Penolakan: {{ $activeTeacherAttendance->rejection_reason }}"</span>
                        @endif
                    </p>
                </div>
            @else
                <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mx-auto shrink-0 border border-amber-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="space-y-1">
                    <h2 class="text-xl font-extrabold text-slate-800">Anda Belum Melakukan Absensi Mengajar</h2>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Untuk mengakses dan mengisi absensi santri sesi <strong class="text-slate-700">{{ $sessionAktif }}</strong> ({{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}), Anda wajib mengajukan <strong>Absensi Mengajar</strong> terlebih dahulu.
                    </p>
                </div>
            @endif
            <div class="pt-2">
                <a href="{{ route('guru.absensi-mengajar', ['session' => $sessionAktif]) }}"
                   class="inline-flex items-center gap-2.5 px-6 py-3 rounded-xl bg-[#1a4731] hover:bg-[#143726] text-white font-bold text-xs shadow-md shadow-green-900/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    [ KE ABSENSI MENGAJAR ]
                </a>
            </div>
        </div>
    @else
        {{-- INFO BANNER KELAS AKTIF (SUMBER DARI ABSENSI MENGAJAR) --}}
        <div class="bg-gradient-to-r from-[#1a4731] to-[#256343] rounded-2xl p-5 sm:p-6 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-2.5 py-1 bg-white/10 backdrop-blur-md rounded-full text-[11px] font-bold text-emerald-200 uppercase tracking-wider border border-white/10">
                    ✓ Sudah Absen Mengajar ({{ $waktuAbsenMengajar ?: '-' }})
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">
                    Kelas {{ $selectedClassroom->name ?? '-' }}
                </h2>
                <p class="text-xs text-emerald-100/90 mt-1">
                    Guru Pengajar: <strong>{{ $guru->name }}</strong>
                    @if($kitabAktif)
                        &bull; Kitab: <strong>{{ $kitabAktif }}</strong>
                    @endif
                    @if($materiAktif)
                        &bull; Materi: <strong>{{ $materiAktif }}</strong>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3 self-start md:self-auto">
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl px-4 py-2.5 text-right text-xs">
                    <p class="text-[10px] uppercase font-bold text-emerald-200">HARI & TANGGAL</p>
                    <p class="font-bold text-white">{{ $hariIni }}, {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-xl px-4 py-2.5 text-right text-xs">
                    <p class="text-[10px] uppercase font-bold text-emerald-200">SESI NGAJI</p>
                    <p class="font-bold text-white">{{ $sessionAktif }}</p>
                </div>
            </div>
        </div>

        {{-- Switcher if teacher did multiple teaching attendances today --}}
        @if($teacherAttendancesToday->count() > 1)
            <div class="bg-white border border-slate-200/80 rounded-2xl p-3.5 shadow-sm flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 mr-2">Absensi Mengajar Anda Hari Ini:</span>
                @foreach($teacherAttendancesToday as $ta)
                    <a href="{{ route('guru.absensi-santri', ['classroom_id' => $ta->classroom_id, 'date' => $date, 'session' => $ta->session]) }}"
                       class="px-3.5 py-2 rounded-xl text-xs font-bold border transition flex items-center gap-2 {{ $activeTeacherAttendance->id == $ta->id ? 'bg-[#1a4731] text-white border-[#1a4731] shadow-sm' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                        <span class="w-2 h-2 rounded-full {{ $activeTeacherAttendance->id == $ta->id ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                        {{ $ta->session }}: {{ $ta->classroom->name ?? 'Kelas' }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Info Hari Libur --}}
        @if($isLibur)
            <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                <div>
                    <p class="font-bold text-amber-800">Hari Libur Pesantren</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        Tanggal {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }} Sesi {{ $sessionAktif }} ditetapkan sebagai hari libur:
                        <strong>{{ $liburRecord->keterangan ?? 'Hari Libur Pesantren' }}</strong>.
                        Absensi manual ditutup.
                    </p>
                </div>
            </div>
        @endif

        {{-- Ringkasan Statistik Kelas --}}
        @if($selectedClassroom)
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 text-center">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Santri</p>
                <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ $totalSantri }}</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center">
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Hadir</p>
                <p class="text-2xl font-extrabold text-emerald-800 mt-1">{{ $hadirCount }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
                <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Izin</p>
                <p class="text-2xl font-extrabold text-blue-800 mt-1">{{ $izinCount }}</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Sakit</p>
                <p class="text-2xl font-extrabold text-amber-800 mt-1">{{ $sakitCount }}</p>
            </div>
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-center col-span-2 sm:col-span-1">
                <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Alfa</p>
                <p class="text-2xl font-extrabold text-rose-800 mt-1">{{ $alfaCount }}</p>
            </div>
        </div>
        @endif

        {{-- Tabel Absensi Santri --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-slate-800">
                    Daftar Kehadiran Santri {{ $selectedClassroom ? "— Kelas {$selectedClassroom->name}" : '' }}
                </h2>
                <span class="text-xs text-slate-400">
                    Sesi {{ $sessionAktif }} &bull; {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 w-12 text-center">NO</th>
                            <th class="px-5 py-4">SANTRI</th>
                            <th class="px-5 py-4">NIS / RFID</th>
                            <th class="px-5 py-4">STATUS REKAMAN</th>
                            <th class="px-5 py-4 text-center">KEHADIRAN MANUAL</th>
                            <th class="px-5 py-4">KETERANGAN / ALASAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="manualTableBody">
                        @forelse($santris as $idx => $santri)
                        @php
                            $attObj = $attRecords->get($santri->id);
                            $statusRaw = $attObj ? strtolower($attObj->status) : '';
                            $notesRaw  = $attObj ? strtolower($attObj->notes ?? '') : '';

                            // Absensi Hadir dari RFID/Tap Machine maupun record Hadir yang sudah tercatat
                            $isRfid  = $attObj && $statusRaw === 'hadir';
                            $isLiburStatus = $isLibur;

                            $currentStatus = $attObj ? ucfirst($statusRaw) : 'Alfa';
                            $currentNotes  = $attObj ? ($attObj->notes ?? '') : '';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4 text-center text-xs text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-4 font-bold text-slate-800">{{ $santri->name }}</td>
                            <td class="px-5 py-4 text-xs font-mono text-slate-500">
                                {{ $santri->rfid_barcode ?: ($santri->nis ?: '-') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($isRfid)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        ✓ Sudah Tap RFID
                                    </span>
                                @elseif($isLiburStatus)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold border border-slate-300">
                                        Libur Pesantren
                                    </span>
                                @elseif($attObj)
                                    @php
                                    $bClass = match(ucfirst($statusRaw)) {
                                        'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Izin'  => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Alfa'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold border {{ $bClass }}">
                                        {{ ucfirst($statusRaw) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">- Belum Tap -</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($isRfid)
                                    <div class="text-xs text-slate-400 font-semibold flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Terkunci (RFID)
                                    </div>
                                @elseif($isLiburStatus)
                                    <div class="text-xs text-red-600 font-extrabold flex items-center justify-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Terkunci (Hari Libur)
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-1" id="btnGroup_{{ $santri->id }}">
                                        <button type="button" onclick="setSantriStatus({{ $santri->id }}, 'Hadir')"
                                            id="btn_Hadir_{{ $santri->id }}"
                                            class="status-btn px-2.5 py-1 text-xs font-bold rounded-lg border transition {{ $currentStatus === 'Hadir' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                            Hadir
                                        </button>
                                        <button type="button" onclick="setSantriStatus({{ $santri->id }}, 'Izin')"
                                            id="btn_Izin_{{ $santri->id }}"
                                            class="status-btn px-2.5 py-1 text-xs font-bold rounded-lg border transition {{ $currentStatus === 'Izin' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                            Izin
                                        </button>
                                        <button type="button" onclick="setSantriStatus({{ $santri->id }}, 'Sakit')"
                                            id="btn_Sakit_{{ $santri->id }}"
                                            class="status-btn px-2.5 py-1 text-xs font-bold rounded-lg border transition {{ $currentStatus === 'Sakit' ? 'bg-amber-600 text-white border-amber-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                            Sakit
                                        </button>
                                        <button type="button" onclick="setSantriStatus({{ $santri->id }}, 'Alfa')"
                                            id="btn_Alfa_{{ $santri->id }}"
                                            class="status-btn px-2.5 py-1 text-xs font-bold rounded-lg border transition {{ $currentStatus === 'Alfa' ? 'bg-rose-600 text-white border-rose-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                            Alfa
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($isRfid)
                                    <span class="text-xs text-slate-400 font-semibold">{{ $attObj->notes ?: 'Tercatat di sistem' }}</span>
                                @elseif($isLiburStatus)
                                    <span class="text-xs text-slate-600 font-bold">{{ $liburRecord->keterangan ?? 'Jadwal Libur' }}</span>
                                @else
                                    <input type="text" id="ket_{{ $santri->id }}" value="{{ $currentNotes }}"
                                        onchange="setSantriNotes({{ $santri->id }}, this.value)"
                                        placeholder="Keterangan / alasan..."
                                        class="w-full max-w-xs border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-[#1a4731] outline-none">
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600">Tidak ada santri di kelas ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($selectedClassroom && $santris->isNotEmpty() && !$isLibur)
            <div class="px-5 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs font-semibold text-slate-500">
                    Pilih status kehadiran untuk santri di atas lalu tekan tombol di samping untuk menyimpan ke database.
                </p>
                <button onclick="saveGuruAbsensiBatch()" id="btnSaveAbsensi"
                    class="w-full sm:w-auto px-6 py-3 bg-[#1a4731] text-white font-bold text-xs rounded-xl shadow-md shadow-green-900/20 hover:bg-[#153c28] transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    [ SIMPAN ABSENSI SANTRI ]
                </button>
            </div>
            @endif
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    let draftData = {};

    @if($hasAbsenMengajar)
        @foreach($santris as $s)
            @php
                $attObj = $attRecords->get($s->id);
                $statusRaw = $attObj ? strtolower($attObj->status) : '';
                $isRfid    = $attObj && $statusRaw === 'hadir';
            @endphp
            @if(!$isRfid && !$isLibur)
                draftData[{{ $s->id }}] = {
                    status: '{{ $attObj ? ucfirst($statusRaw) : "Alfa" }}',
                    notes: '{{ addslashes($attObj ? ($attObj->notes ?? "") : "") }}'
                };
            @endif
        @endforeach
    @endif

    function updateFilters() {
        const clsId = "{{ $selectedClassroom?->id }}";
        const date  = document.getElementById('selTanggal').value;
        const session = document.getElementById('selSesi').value;

        window.location.href = `{{ route('guru.absensi-santri') }}?classroom_id=${clsId}&date=${date}&session=${session}`;
    }

    function setSantriStatus(santriId, status) {
        if (!draftData[santriId]) {
            draftData[santriId] = { status: 'Alfa', notes: '' };
        }
        draftData[santriId].status = status;

        ['Hadir', 'Izin', 'Sakit', 'Alfa'].forEach(st => {
            const btn = document.getElementById(`btn_${st}_${santriId}`);
            if (btn) {
                if (st === status) {
                    const bgClass = st === 'Hadir' ? 'bg-emerald-600 text-white border-emerald-600' :
                                   (st === 'Izin' ? 'bg-blue-600 text-white border-blue-600' :
                                   (st === 'Sakit' ? 'bg-amber-600 text-white border-amber-600' : 'bg-rose-600 text-white border-rose-600'));
                    btn.className = `status-btn px-2.5 py-1 text-xs font-bold rounded-lg border transition ${bgClass}`;
                } else {
                    btn.className = 'status-btn px-2.5 py-1 text-xs font-bold rounded-lg border transition bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100';
                }
            }
        });
    }

    function setSantriNotes(santriId, notesVal) {
        if (!draftData[santriId]) {
            draftData[santriId] = { status: 'Alfa', notes: '' };
        }
        draftData[santriId].notes = notesVal;
    }

    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toastAlert');
        const icon = document.getElementById('toastIcon');
        const msg  = document.getElementById('toastMessage');

        toast.className = `rounded-xl border px-4 py-3 text-sm font-medium flex items-center justify-between transition-all duration-300 ${
            isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'
        }`;

        icon.setAttribute('class', `w-5 h-5 shrink-0 ${isSuccess ? 'text-emerald-600' : 'text-red-600'}`);
        icon.innerHTML = isSuccess
            ? `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>`
            : `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>`;

        msg.textContent = message;
        toast.classList.remove('hidden');

        setTimeout(() => hideToast(), 5000);
    }

    function hideToast() {
        document.getElementById('toastAlert').classList.add('hidden');
    }

    async function saveGuruAbsensiBatch() {
        const clsId   = "{{ $selectedClassroom?->id }}";
        const date    = document.getElementById('selTanggal').value;
        const session = document.getElementById('selSesi').value;
        const btn     = document.getElementById('btnSaveAbsensi');

        if (!clsId) {
            showToast('Tidak ada kelas aktif yang dipilih.', false);
            return;
        }

        const payload = [];
        for (const sId in draftData) {
            payload.push({
                santri_id: parseInt(sId),
                status: draftData[sId].status,
                notes: draftData[sId].notes || ''
            });
        }

        if (payload.length === 0) {
            showToast('Tidak ada santri yang perlu diabsen.', false);
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Menyimpan...`;

        try {
            const res = await fetch("{{ route('guru.absensi-santri.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    date: date,
                    session: session,
                    classroom_id: parseInt(clsId),
                    attendance: payload
                })
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast(data.message || 'Absensi santri berhasil disimpan!');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showToast(data.message || 'Gagal menyimpan absensi.', false);
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan atau server.', false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> [ SIMPAN ABSENSI SANTRI ]`;
        }
    }
</script>
@endpush
