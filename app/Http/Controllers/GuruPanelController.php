<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\Santri;
use App\Models\TeacherAttendance;
use App\Models\TeacherSchedule;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruPanelController extends Controller
{
    /**
     * Helper untuk mendapatkan data Guru dari user yang sedang login
     */
    protected function currentGuru(): ?Guru
    {
        $user = Auth::guard('web')->user() ?: Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->guru_id) {
            $guru = Guru::with('classroom')->find($user->guru_id);
            if ($guru) {
                return $guru;
            }
        }

        return Guru::with('classroom')->where('user_id', $user->id)->first();
    }

    /**
     * Dashboard Guru — Menampilkan Jadwal Mengajar Hari Ini & Tombol Mulai Absen
     */
    public function dashboard()
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $today        = now()->toDateString();
        $hariIni      = TeacherSchedule::currentHari();
        $sessionAktif = TeacherSchedule::currentSession();
        $hariLibur    = HariLibur::whereDate('tanggal', $today)->first();

        // Jadwal master hari ini milik guru
        $jadwalHariIni = TeacherSchedule::with('classroom')
            ->where('guru_id', $guru->id)
            ->where('hari', $hariIni)
            ->where('status', 'aktif')
            ->get();

        // Absensi mengajar yang sudah dilakukan hari ini
        $absensiHariIni = TeacherAttendance::where('guru_id', $guru->id)
            ->whereDate('date', $today)
            ->pluck('classroom_id')
            ->toArray();

        // Total riwayat mengajar & total jadwal
        $totalJadwal    = TeacherSchedule::where('guru_id', $guru->id)->where('status', 'aktif')->count();
        $totalRiwayat   = TeacherAttendance::where('guru_id', $guru->id)->count();

        return view('guru.dashboard', compact(
            'guru',
            'today',
            'hariIni',
            'sessionAktif',
            'hariLibur',
            'jadwalHariIni',
            'absensiHariIni',
            'totalJadwal',
            'totalRiwayat'
        ));
    }

    /**
     * Halaman Jadwal Mengajar Guru
     */
    public function jadwal()
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $hariOrder    = TeacherSchedule::hariOrder();
        $sessionOrder = array_keys(TeacherSchedule::sessionMap());

        $schedules = TeacherSchedule::with('classroom')
            ->where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(function ($s) use ($hariOrder, $sessionOrder) {
                $hariNum    = $hariOrder[$s->hari] ?? 9;
                $sessionNum = array_search($s->session, $sessionOrder) !== false ? array_search($s->session, $sessionOrder) : 9;
                return [$hariNum, $sessionNum];
            })->values();

        return view('guru.jadwal', compact('guru', 'schedules'));
    }

    /**
     * Halaman Absensi Santri — TERBUKA OTOMATIS SETELAH GURU ABSEN MENGAJAR
     */
    public function absensiSantri(Request $request)
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $today        = now()->toDateString();
        $date         = $request->input('date', $today);
        $hariIni      = TeacherSchedule::currentHari();
        $sessionAktif = ucfirst(strtolower($request->input('session', TeacherSchedule::currentSession() ?: 'Subuh')));

        // 1. Cari rekaman Absensi Mengajar yang sudah dilakukan Guru ini untuk tanggal terpilih
        $teacherAttendancesToday = TeacherAttendance::with('classroom')
            ->where('guru_id', $guru->id)
            ->whereDate('date', $date)
            ->get();

        // Tentukan classroom_id yang dicari (input request atau session)
        $requestedClassroomId = $request->input('classroom_id') ?: session('active_classroom_id');

        $activeTeacherAttendance = null;
        if ($requestedClassroomId) {
            // Jika ada kelas tertentu yang diminta, HANYA ambil rekaman absensi mengajar untuk kelas tersebut (TIDAK BOLEH fallback ke kelas lain)
            $activeTeacherAttendance = $teacherAttendancesToday->firstWhere('classroom_id', (int) $requestedClassroomId);
        } else {
            // Jika tidak ada kelas spesifik yang diparameterkan, cari berdasarkan sesi aktif atau yang terbaru
            $activeTeacherAttendance = $teacherAttendancesToday->firstWhere('session', $sessionAktif)
                ?: $teacherAttendancesToday->first();
        }

        // Cek status persetujuan secara MUTLAK KETAT (HANYA 'approved' YANG DIIZINKAN)
        $approvalStatus     = $activeTeacherAttendance?->approval_status ?? null;
        $isApproved         = $activeTeacherAttendance !== null && $approvalStatus === 'approved';
        $hasAbsenMengajar   = $isApproved;
        
        $selectedClassroom  = $activeTeacherAttendance?->classroom;
        $kitabAktif         = $isApproved ? $activeTeacherAttendance?->kitab : null;
        $materiAktif        = $isApproved ? $activeTeacherAttendance?->materi : null;
        $waktuAbsenMengajar = ($isApproved && $activeTeacherAttendance?->attendance_time) 
            ? $activeTeacherAttendance->attendance_time->format('H:i') 
            : null;

        // Query santri HANYA DAN HANYA JIKA $hasAbsenMengajar === true (DISETUJUI / APPROVED)
        $santris = ($hasAbsenMengajar && $selectedClassroom)
            ? Santri::where('classroom_id', $selectedClassroom->id)->orderBy('name')->get()
            : collect();

        $attRecords = ($hasAbsenMengajar && $selectedClassroom)
            ? Attendance::whereDate('date', $date)
                ->whereRaw('LOWER(session) = ?', [strtolower($sessionAktif)])
                ->whereHas('santri', fn($q) => $q->where('classroom_id', $selectedClassroom->id))
                ->get()
                ->keyBy('santri_id')
            : collect();

        // Cek Libur
        $liburRecord = HariLibur::whereDate('tanggal', $date)->first();
        $attendanceService = app(AttendanceService::class);
        $isLibur = false;
        if ($liburRecord && $attendanceService->isHolidayForSession($liburRecord->keterangan, $sessionAktif)) {
            $isLibur = true;
        }

        // Hitung statistik
        $totalSantri = $santris->count();
        $hadirCount  = $attRecords->filter(fn($a) => strtolower($a->status) === 'hadir')->count();
        $izinCount   = $attRecords->filter(fn($a) => strtolower($a->status) === 'izin')->count();
        $sakitCount  = $attRecords->filter(fn($a) => strtolower($a->status) === 'sakit')->count();
        $alfaCount   = $attRecords->filter(fn($a) => strtolower($a->status) === 'alfa')->count();

        return view('guru.absensi-santri', compact(
            'guru',
            'teacherAttendancesToday',
            'activeTeacherAttendance',
            'approvalStatus',
            'hasAbsenMengajar',
            'selectedClassroom',
            'kitabAktif',
            'materiAktif',
            'waktuAbsenMengajar',
            'date',
            'hariIni',
            'sessionAktif',
            'isLibur',
            'liburRecord',
            'santris',
            'attRecords',
            'totalSantri',
            'hadirCount',
            'izinCount',
            'sakitCount',
            'alfaCount'
        ));
    }

    /**
     * Simpan / Batch Update Absensi Santri Manual oleh Guru
     */
    public function saveAbsensiSantri(Request $request)
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $attendanceService = app(AttendanceService::class);
        $validated = $request->validate([
            'date'                   => ['required', 'date'],
            'session'                => ['required', 'in:Subuh,Dzuhur,Ashar,Isya'],
            'classroom_id'           => ['required', 'exists:classrooms,id'],
            'attendance'             => ['required', 'array'],
            'attendance.*.santri_id' => ['required', 'exists:santris,id'],
            'attendance.*.status'    => ['required', 'in:Hadir,Izin,Sakit,Alfa,Belum Absen'],
            'attendance.*.notes'     => ['nullable', 'string'],
        ]);

        $tanggal = $validated['date'];
        $sesi    = $attendanceService->normalizeSession($validated['session']);

        // STRICT AUTHORIZATION CHECK: Guru WAJIB sudah melakukan Absensi Mengajar & DISETUJUI (APPROVED) untuk kelas + tanggal ini!
        $hasApprovedTeacherAttendance = TeacherAttendance::where('guru_id', $guru->id)
            ->whereDate('date', $tanggal)
            ->where('classroom_id', $validated['classroom_id'])
            ->where('approval_status', 'approved')
            ->exists();

        if (!$hasApprovedTeacherAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan mengajar Anda untuk kelas ini belum disetujui oleh Admin atau Wali Kelas. Anda tidak dapat mengisi absensi santri.'
            ], 403);
        }

        // Cek Libur
        $liburRecord = HariLibur::whereDate('tanggal', $tanggal)->first();
        if ($liburRecord && $attendanceService->isHolidayForSession($liburRecord->keterangan, $sesi)) {
            return response()->json([
                'success' => false,
                'message' => "Sesi {$sesi} pada tanggal {$tanggal} sedang LIBUR. Absensi manual ditutup."
            ], 422);
        }

        foreach ($validated['attendance'] as $item) {
            $santriId = $item['santri_id'];
            $status   = $item['status'];
            $notes    = trim($item['notes'] ?? '');

            if ($status === 'Belum Absen') {
                continue;
            }

            // Lock jika sudah ada absensi Hadir dari RFID Tap
            $existingRfid = Attendance::where('santri_id', $santriId)
                ->whereDate('date', $tanggal)
                ->where('session', $sesi)
                ->whereRaw('LOWER(status) = ?', ['hadir'])
                ->whereRaw('LOWER(COALESCE(notes, \'\')) LIKE ?', ['%rfid%'])
                ->exists();

            if ($existingRfid) {
                continue;
            }

            // Keterangan wajib untuk Sakit & Izin
            if (in_array($status, ['Sakit', 'Izin'], true) && empty($notes)) {
                return response()->json([
                    'success' => false,
                    'message' => "Keterangan/Alasan wajib diisi untuk status {$status}.",
                ], 422);
            }

            if (empty($notes)) {
                $notes = 'Absensi oleh Guru ' . $guru->name;
            }

            Attendance::updateOrCreate(
                [
                    'santri_id' => $santriId,
                    'date'      => $tanggal,
                    'session'   => $sesi,
                ],
                [
                    'status'    => $status,
                    'notes'     => $notes,
                    'scan_time' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Data absensi santri berhasil disimpan ke database oleh Guru!'
        ]);
    }

    /**
     * Halaman Riwayat Mengajar Guru
     */
    public function riwayatMengajar()
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $attendances = TeacherAttendance::with('classroom')
            ->where('guru_id', $guru->id)
            ->latest('date')
            ->latest('attendance_time')
            ->get();

        return view('guru.riwayat-mengajar', compact('guru', 'attendances'));
    }

    /**
     * Halaman Profil Guru
     */
    public function profil()
    {
        $guru = $this->currentGuru();
        $user = Auth::guard('web')->user() ?: Auth::user();

        return view('guru.profil', compact('guru', 'user'));
    }
}
