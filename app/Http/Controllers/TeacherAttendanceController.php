<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\TeacherAttendance;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TeacherAttendanceController extends Controller
{
    /**
     * Helper untuk mendapatkan data Guru dari user login
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
     * Cek apakah user login adalah Wali Kelas
     */
    protected function isWaliKelas(): bool
    {
        $user = Auth::guard('web')->user() ?: Auth::user();
        if (!$user) return false;
        return in_array($user->role, ['wali_kelas', 'supervisor'], true);
    }

    public function index()
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $today        = now()->toDateString();
        $hariIni      = TeacherSchedule::currentHari();
        $sessionAktif = TeacherSchedule::currentSession() ?: 'Subuh';

        // Cek apakah hari ini adalah hari libur
        $hariLibur   = HariLibur::whereDate('tanggal', $today)->first();
        $isHariLibur = $hariLibur !== null;

        // Deteksi role
        $isWaliKelas = $this->isWaliKelas();

        // Hapus batasan kelas, semua guru (termasuk Wali Kelas) bisa melihat SEMUA kelas
        $allowedClassrooms = Classroom::orderBy('name')->get();
        
        // Data Guru & Penugasan Kelas untuk fitur Badal
        $allGurus = Guru::orderBy('name')->get();
        $assignedClassIds = collect([$guru->classroom_id])
            ->merge($guru->classrooms->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $classroomsWithGurus = [];
        foreach ($allowedClassrooms as $cls) {
            $mainGurus = $allGurus->filter(function ($g) use ($cls) {
                return (int) $g->classroom_id === (int) $cls->id || $g->classrooms->contains('id', $cls->id);
            })->values();

            $classroomsWithGurus[$cls->id] = $mainGurus->map(fn($g) => [
                'id'   => $g->id,
                'name' => $g->name,
            ])->toArray();
        }

        // Absensi Mengajar yang sudah dilakukan hari ini
        $attendancesToday = TeacherAttendance::with(['classroom', 'replacedGuru'])
            ->where('guru_id', $guru->id)
            ->whereDate('date', $today)
            ->latest('attendance_time')
            ->get();

        $viewName = request()->routeIs('wali-kelas.*')
            ? 'wali-kelas.teacher-attendance'
            : 'guru.absensi-mengajar';

        return view($viewName, [
            'guru'                => $guru,
            'classrooms'          => $allowedClassrooms,
            'allGurus'            => $allGurus,
            'assignedClassIds'    => $assignedClassIds,
            'classroomsWithGurus' => $classroomsWithGurus,
            'isWaliKelas'         => $isWaliKelas,
            'kelasWali'           => ($isWaliKelas && $guru->classroom_id) ? Classroom::find($guru->classroom_id) : null,
            'attendances'         => TeacherAttendance::with(['classroom', 'replacedGuru'])
                ->where('guru_id', $guru->id)
                ->latest('date')->latest('attendance_time')->get(),
            'attendancesToday'    => $attendancesToday,
            'hariIni'             => $hariIni,
            'sessionAktif'        => $sessionAktif,
            'isHariLibur'         => $isHariLibur,
            'hariLibur'           => $hariLibur,
        ]);
    }

    public function store(Request $request)
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $isWaliKelas = $this->isWaliKelas();

        $assignedClassIds = collect([$guru->classroom_id])
            ->merge($guru->classrooms->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $selectedClassroom = (int) $request->input('classroom_id');
        $isOwnClass        = in_array($selectedClassroom, $assignedClassIds, true);

        $validated = $request->validate([
            'date'               => ['required', 'date'],
            'attendance_time'    => ['required'],
            'session'            => ['nullable', 'string', 'max:50'],
            'classroom_id'       => ['required', 'exists:classrooms,id'],
            'kitab'              => ['required', 'string', 'max:255'],
            'materi'             => ['required', 'string'],
            'status'             => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alfa'])],
            'replaced_guru_id'   => [$isOwnClass ? 'nullable' : 'required', 'nullable', 'exists:gurus,id'],
            'alasan_tidak_masuk' => ['nullable', 'string', 'max:255'],
            'status_guru_utama'  => ['nullable', Rule::in(['Sakit', 'Izin', 'Alfa'])],
        ]);

        // Simpan "Kelas Aktif" ke Session agar Absensi Santri tidak perlu memilih kelas lagi
        session([
            'active_classroom_id' => $validated['classroom_id'],
            'active_date'         => $validated['date'],
            'active_session'      => $validated['session'] ?: 'Subuh',
        ]);

        $validated['attendance_time'] = substr((string) $validated['attendance_time'], 0, 5);
        $approvalStatus = $isOwnClass ? 'approved' : 'pending';

        $statusMengajar  = $isOwnClass ? 'normal' : 'badal';
        $replacedGuruId  = $isOwnClass ? null : ($validated['replaced_guru_id'] ?? null);
        $alasanTidakMasuk = $request->input('alasan_tidak_masuk');

        $finalMateri = $validated['materi'];
        if (!$isOwnClass && $alasanTidakMasuk) {
            $finalMateri = "[Alasan Guru Utama: {$alasanTidakMasuk}] " . $validated['materi'];
        }

        // Cek duplikasi absensi mengajar untuk kelas + sesi + tanggal yang sama untuk guru ini
        $duplicateQuery = TeacherAttendance::where('guru_id', $guru->id)
            ->whereDate('date', $validated['date'])
            ->where('classroom_id', $validated['classroom_id']);

        if (!empty($validated['session'])) {
            $duplicateQuery->where(function ($q) use ($validated) {
                $q->where('session', $validated['session'])
                  ->orWhere('attendance_time', $validated['attendance_time']);
            });
        } else {
            $duplicateQuery->where('attendance_time', $validated['attendance_time']);
        }

        $existingRecord = $duplicateQuery->first();
        if ($existingRecord) {
            $existingRecord->update([
                'attendance_time'  => $validated['attendance_time'],
                'kitab'            => $validated['kitab'],
                'materi'           => $finalMateri,
                'status'           => $validated['status'],
                'status_mengajar'  => $statusMengajar,
                'replaced_guru_id' => $replacedGuruId,
                'approval_status'  => $approvalStatus,
                'approved_by'      => $isOwnClass ? (Auth::guard('web')->id() ?: Auth::id()) : null,
                'approved_at'      => $isOwnClass ? now() : null,
            ]);

            $msg = $approvalStatus === 'approved' 
                ? 'Absensi Mengajar diperbarui. Absensi Santri otomatis terbuka!' 
                : 'Permintaan mengajar diperbarui dan telah dikirim. Menunggu persetujuan Admin atau Wali Kelas.';

            $this->recordReplacedGuruAbsence($validated, $replacedGuruId, $request->status_guru_utama, $alasanTidakMasuk, $guru);

            return $this->redirectAfterStore($isWaliKelas, $validated, $msg);
        }

        TeacherAttendance::create([
            'guru_id'          => $guru->id,
            'classroom_id'     => $validated['classroom_id'],
            'date'             => $validated['date'],
            'attendance_time'  => $validated['attendance_time'],
            'session'          => $validated['session'],
            'kitab'            => $validated['kitab'],
            'materi'           => $finalMateri,
            'status'           => $validated['status'],
            'status_mengajar'  => $statusMengajar,
            'replaced_guru_id' => $replacedGuruId,
            'approval_status'  => $approvalStatus,
            'approved_by'      => $isOwnClass ? (Auth::guard('web')->id() ?: Auth::id()) : null,
            'approved_at'      => $isOwnClass ? now() : null,
        ]);

        $this->recordReplacedGuruAbsence($validated, $replacedGuruId, $request->status_guru_utama, $alasanTidakMasuk, $guru);

        $msg = $approvalStatus === 'approved' 
            ? 'Absensi Mengajar berhasil disimpan. Absensi Santri otomatis terbuka!' 
            : 'Permintaan mengajar (Badal) telah dikirim. Menunggu persetujuan Admin atau Wali Kelas.';

        return $this->redirectAfterStore($isWaliKelas, $validated, $msg);
    }

    /**
     * Catat absensi ketidakhadiran guru utama (jika diisi / dipilih saat badal)
     */
    private function recordReplacedGuruAbsence(array $validated, ?int $replacedGuruId, ?string $statusUtama, ?string $alasan, Guru $guruAktual)
    {
        if (!$replacedGuruId || (int) $replacedGuruId === (int) $guruAktual->id) {
            return;
        }

        $statusUtama = $statusUtama ?: 'Sakit';
        $sessionStr  = $validated['session'] ?: 'Subuh';

        $existingUtama = TeacherAttendance::where('guru_id', $replacedGuruId)
            ->whereDate('date', $validated['date'])
            ->where('classroom_id', $validated['classroom_id'])
            ->where('session', $sessionStr)
            ->first();

        $materiNotes = "Digantikan oleh Guru " . $guruAktual->name . ($alasan ? " (Alasan: {$alasan})" : "");

        if ($existingUtama) {
            // Hanya update jika statusnya bukan Hadir
            if ($existingUtama->status !== 'Hadir') {
                $existingUtama->update([
                    'status'          => $statusUtama,
                    'materi'          => $existingUtama->materi ?: $materiNotes,
                    'status_mengajar' => 'normal',
                ]);
            }
        } else {
            TeacherAttendance::create([
                'guru_id'          => $replacedGuruId,
                'classroom_id'     => $validated['classroom_id'],
                'date'             => $validated['date'],
                'attendance_time'  => $validated['attendance_time'],
                'session'          => $sessionStr,
                'kitab'            => $validated['kitab'],
                'materi'           => $materiNotes,
                'status'           => $statusUtama,
                'status_mengajar'  => 'normal',
                'replaced_guru_id' => null,
                'approval_status'  => 'approved',
                'approved_by'      => null,
                'approved_at'      => now(),
            ]);
        }
    }

    /**
     * Redirect setelah simpan — Wali Kelas ke absensi-manual, Guru ke absensi-santri
     */
    private function redirectAfterStore(bool $isWaliKelas, array $validated, string $message)
    {
        if ($isWaliKelas) {
            return redirect()->route('wali-kelas.absensi-manual', [
                'date'    => $validated['date'],
                'session' => $validated['session'] ?: 'Subuh',
            ])->with('success', $message);
        }

        return redirect()->route('guru.absensi-santri', [
            'classroom_id' => $validated['classroom_id'],
            'date'         => $validated['date'],
            'session'      => $validated['session'] ?: 'Subuh',
        ])->with('success', $message);
    }
}
