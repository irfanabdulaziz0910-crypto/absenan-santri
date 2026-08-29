<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;

class TeacherScheduleController extends Controller
{
    /**
     * Tampilkan Master Jadwal Mengajar Guru
     */
    public function index(Request $request)
    {
        $query = TeacherSchedule::with(['guru', 'classroom'])->where('status', 'aktif');

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }
        if ($request->filled('session')) {
            $query->where('session', $request->session);
        }
        if ($request->filled('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        $hariOrder = TeacherSchedule::hariOrder();
        $sessionOrder = array_keys(TeacherSchedule::sessionMap());

        $schedules = $query->get()->sortBy(function ($s) use ($hariOrder, $sessionOrder) {
            $hariNum    = $hariOrder[$s->hari] ?? 9;
            $sessionNum = array_search($s->session, $sessionOrder) !== false ? array_search($s->session, $sessionOrder) : 9;
            return [$hariNum, $sessionNum];
        })->values();

        $gurus      = Guru::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        $sessions   = array_keys(TeacherSchedule::sessionMap());
        $hariList   = array_keys(TeacherSchedule::hariOrder());

        return view('admin.teacher-schedule.index', compact(
            'schedules',
            'gurus',
            'classrooms',
            'sessions',
            'hariList'
        ));
    }

    /**
     * Simpan jadwal master baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id'      => 'required|exists:gurus,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'session'      => 'required|in:Subuh,Dzuhur,Ashar,Isya',
            'kitab'        => 'nullable|string|max:255',
        ]);

        // Cek duplikasi
        $duplicate = TeacherSchedule::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where('session', $validated['session'])
            ->where('classroom_id', $validated['classroom_id'] ?? null)
            ->where('status', 'aktif')
            ->exists();

        if ($duplicate) {
            return back()->withInput()
                ->with('error', 'Jadwal ini sudah terdaftar. Tidak dapat membuat jadwal duplikat untuk kombinasi Guru + Hari + Sesi + Kelas yang sama.');
        }

        // Isi jam otomatis dari session map
        $sessionMap = TeacherSchedule::sessionMap();
        $jamMulai   = $sessionMap[$validated['session']]['jam_mulai'] ?? null;
        $jamSelesai = $sessionMap[$validated['session']]['jam_selesai'] ?? null;

        TeacherSchedule::create([
            'guru_id'      => $validated['guru_id'],
            'classroom_id' => $validated['classroom_id'] ?? null,
            'hari'         => $validated['hari'],
            'session'      => $validated['session'],
            'jam_mulai'    => $jamMulai,
            'jam_selesai'  => $jamSelesai,
            'kitab'        => $validated['kitab'] ?? null,
            'status'       => 'aktif',
        ]);

        $guru = Guru::find($validated['guru_id']);
        return redirect()->route('admin.teacher-schedule.index')
            ->with('success', "Jadwal mengajar untuk {$guru->name} (Hari {$validated['hari']}, Sesi {$validated['session']}) berhasil ditambahkan.");
    }

    /**
     * Update jadwal master
     */
    public function update(Request $request, $id)
    {
        $schedule = TeacherSchedule::findOrFail($id);

        $validated = $request->validate([
            'guru_id'      => 'required|exists:gurus,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'hari'         => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'session'      => 'required|in:Subuh,Dzuhur,Ashar,Isya',
            'kitab'        => 'nullable|string|max:255',
        ]);

        // Cek duplikasi (kecuali record ini sendiri)
        $duplicate = TeacherSchedule::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where('session', $validated['session'])
            ->where('classroom_id', $validated['classroom_id'] ?? null)
            ->where('status', 'aktif')
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()
                ->with('error', 'Jadwal dengan kombinasi Guru + Hari + Sesi + Kelas yang sama sudah terdaftar.');
        }

        $sessionMap = TeacherSchedule::sessionMap();
        $jamMulai   = $sessionMap[$validated['session']]['jam_mulai'] ?? null;
        $jamSelesai = $sessionMap[$validated['session']]['jam_selesai'] ?? null;

        $schedule->update([
            'guru_id'      => $validated['guru_id'],
            'classroom_id' => $validated['classroom_id'] ?? null,
            'hari'         => $validated['hari'],
            'session'      => $validated['session'],
            'jam_mulai'    => $jamMulai,
            'jam_selesai'  => $jamSelesai,
            'kitab'        => $validated['kitab'] ?? null,
        ]);

        return redirect()->route('admin.teacher-schedule.index')
            ->with('success', 'Jadwal mengajar berhasil diperbarui.');
    }

    /**
     * Hapus jadwal master
     */
    public function destroy($id)
    {
        $schedule = TeacherSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.teacher-schedule.index')
            ->with('success', 'Jadwal mengajar berhasil dihapus.');
    }
}
