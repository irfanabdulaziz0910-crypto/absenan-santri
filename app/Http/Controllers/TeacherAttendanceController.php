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
        
        // Data Guru untuk pilihan badal
        $allGurus = Guru::orderBy('name')->get();

        // Absensi Mengajar yang sudah dilakukan hari ini
        $attendancesToday = TeacherAttendance::with('classroom')
            ->where('guru_id', $guru->id)
            ->whereDate('date', $today)
            ->latest('attendance_time')
            ->get();

        $viewName = request()->routeIs('guru.*')
            ? 'guru.absensi-mengajar'
            : 'guru.absensi-mengajar';   // View yang sama, dibedakan oleh flag $isWaliKelas

        return view($viewName, [
            'guru'               => $guru,
            'classrooms'         => $allowedClassrooms,
            'allGurus'           => $allGurus,
            'isWaliKelas'        => $isWaliKelas,
            'kelasWali'          => ($isWaliKelas && $guru->classroom_id) ? Classroom::find($guru->classroom_id) : null,
            'attendances'        => TeacherAttendance::with(['classroom', 'replacedGuru'])
                ->where('guru_id', $guru->id)
                ->latest('date')->latest('attendance_time')->get(),
            'attendancesToday'   => $attendancesToday,
            'hariIni'            => $hariIni,
            'sessionAktif'       => $sessionAktif,
            'isHariLibur'        => $isHariLibur,
            'hariLibur'          => $hariLibur,
        ]);
    }

    public function store(Request $request)
    {
        $guru = $this->currentGuru();
        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $isWaliKelas = $this->isWaliKelas();

        $validated = $request->validate([
            'date'            => ['required', 'date'],
            'attendance_time' => ['required'],
            'session'         => ['nullable', 'string', 'max:50'],
            'classroom_id'    => ['required', 'exists:classrooms,id'],
            'kitab'           => ['required', 'string', 'max:255'],
            'materi'          => ['required', 'string'],
            'status'          => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alfa'])],
        ]);

        // Simpan "Kelas Aktif" ke Session agar Absensi Santri tidak perlu memilih kelas lagi
        session([
            'active_classroom_id' => $validated['classroom_id'],
            'active_date' => $validated['date'],
            'active_session' => $validated['session'] ?: 'Subuh',
        ]);

        $validated['attendance_time'] = substr((string) $validated['attendance_time'], 0, 5);

        // Tentukan apakah persetujuan diperlukan (otomatis APPROVED hanya jika mengajar di kelas induk sendiri)
        $isOwnClass = $guru->classroom_id && (int) $validated['classroom_id'] === (int) $guru->classroom_id;
        $approvalStatus = $isOwnClass ? 'approved' : 'pending';

        // Cek duplikasi absensi mengajar untuk kelas + sesi + tanggal yang sama
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
            // Jika update record yang sudah ada, set status persetujuan baru
            $existingRecord->update([
                'attendance_time' => $validated['attendance_time'],
                'kitab'           => $validated['kitab'],
                'materi'          => $validated['materi'],
                'status'          => $validated['status'],
                'approval_status' => $approvalStatus,
                'approved_by'     => $isOwnClass ? (Auth::guard('web')->id() ?: Auth::id()) : null,
                'approved_at'     => $isOwnClass ? now() : null,
            ]);

            $msg = $approvalStatus === 'approved' 
                ? 'Absensi Mengajar diperbarui. Absensi Santri otomatis terbuka!' 
                : 'Permintaan mengajar diperbarui dan telah dikirim. Menunggu persetujuan Admin atau Wali Kelas.';

            return $this->redirectAfterStore($isWaliKelas, $validated, $msg);
        }

        TeacherAttendance::create($validated + [
            'guru_id'         => $guru->id,
            'approval_status' => $approvalStatus,
            'approved_by'     => $isOwnClass ? (Auth::guard('web')->id() ?: Auth::id()) : null,
            'approved_at'     => $isOwnClass ? now() : null,
        ]);

        $msg = $approvalStatus === 'approved' 
            ? 'Absensi Mengajar berhasil disimpan. Absensi Santri otomatis terbuka!' 
            : 'Permintaan mengajar telah dikirim. Menunggu persetujuan Admin atau Wali Kelas.';

        return $this->redirectAfterStore($isWaliKelas, $validated, $msg);
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
