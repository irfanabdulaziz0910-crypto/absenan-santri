<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->input('search', '');
        $statusFilter = $request->input('status', '');

        $query = Guru::with(['classroom' => function ($q) {
            $q->withCount('santris');
        }, 'classrooms', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");

                if (Schema::hasColumn('gurus', 'nip')) {
                    $q->orWhere('nip', 'like', "%{$search}%");
                }
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $gurus        = $query->orderBy('name')->get();
        $totalAktif   = Guru::where('status', 'aktif')->count();
        $totalCuti    = Guru::where('status', 'cuti')->count();
        $kelasAktif   = Guru::whereNotNull('classroom_id')->where('status', 'aktif')->count();
        $kelasList    = Classroom::orderBy('name')->get();

        return view('admin.guru.index', compact(
            'gurus',
            'totalAktif',
            'totalCuti',
            'kelasAktif',
            'kelasList',
            'search',
            'statusFilter'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:255',
            'nip'                     => 'nullable|string|max:50',
            'nomor_hp'                => 'nullable|string|max:20',
            'role'                    => 'nullable|in:guru,wali_kelas,supervisor',
            'classroom_id'            => 'nullable|exists:classrooms,id',
            'allowed_classroom_ids'   => 'nullable|array',
            'allowed_classroom_ids.*' => 'exists:classrooms,id',
            'kelas'                   => 'nullable|string',
            'spesialisasi'            => 'nullable|string|max:100',
            'status'                  => 'required|in:aktif,nonaktif,cuti',
            'bergabung_at'            => 'nullable|date',
            'username'                => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'password'                => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Tentukan role dari input admin — EKSPLISIT, bukan auto-detect
        $role = $request->input('role', 'guru');

        // Untuk Wali Kelas, classroom_id adalah kelas wali (wajib). Untuk Guru Pendamping, classroom_id = NULL
        if ($role === 'guru') {
            $classroomId = null;
            $kelasName   = null;
        } else {
            $classroomId = $request->classroom_id;
            if (!$classroomId && $request->kelas) {
                $cls = Classroom::where('name', $request->kelas)->first();
                if ($cls) {
                    $classroomId = $cls->id;
                }
            }
        }

        // Jika Wali Kelas harus punya kelas
        if ($role === 'wali_kelas' && !$classroomId) {
            return back()->withInput()->withErrors([
                'classroom_id' => 'Wali Kelas harus memiliki Kelas Wali yang dipilih.',
            ]);
        }

        $kelasName = null;
        if ($classroomId) {
            $kelasName = Classroom::where('id', $classroomId)->value('name');
        }

        $guruData = [
            'name'         => $request->name,
            'nomor_hp'     => $request->nomor_hp,
            'classroom_id' => $classroomId,
            'kelas'        => $kelasName,
            'spesialisasi' => $request->spesialisasi,
            'status'       => $request->status,
            'bergabung_at' => $request->bergabung_at ?: now()->toDateString(),
        ];

        if (Schema::hasColumn('gurus', 'nip')) {
            $guruData['nip'] = $request->nip;
        }

        $guru = Guru::create($guruData);

        // Sync penugasan kelas yang dapat diajar (untuk Guru Biasa & Wali Kelas)
        if ($role === 'wali_kelas' && $classroomId) {
            $guru->classrooms()->sync([$classroomId]);
        } elseif ($request->has('allowed_classroom_ids') && is_array($request->allowed_classroom_ids)) {
            $guru->classrooms()->sync($request->input('allowed_classroom_ids', []));
        }

        // Buat akun login jika username & password diisi
        if ($request->filled('username') && $request->filled('password')) {
            $username = trim((string) $request->username);
            $email    = $this->buildEmailFromUsername($username);

            $user = User::create([
                'name'     => $guru->name,
                'username' => $username,
                'email'    => $email,
                'password' => Hash::make($request->password),
                'role'     => $role,   // ← Gunakan role dari input Admin
                'guru_id'  => $guru->id,
            ]);

            $guru->update(['user_id' => $user->id]);

            return redirect()->route('admin.guru.index')
                ->with('success', "Data guru berhasil ditambahkan. Akun login dibuat dengan username [{$username}] dan role [" . ($role === 'wali_kelas' ? 'Wali Kelas' : 'Guru') . "].");
        }

        return redirect()->route('admin.guru.index')
            ->with('success', "Data guru '{$guru->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        $user = User::where('guru_id', $guru->id)->first()
            ?: ($guru->user_id ? User::find($guru->user_id) : null);

        $request->validate([
            'name'                    => 'required|string|max:255',
            'nip'                     => 'nullable|string|max:50',
            'nomor_hp'                => 'nullable|string|max:20',
            'role'                    => 'nullable|in:guru,wali_kelas,supervisor',
            'classroom_id'            => 'nullable|exists:classrooms,id',
            'allowed_classroom_ids'   => 'nullable|array',
            'allowed_classroom_ids.*' => 'exists:classrooms,id',
            'kelas'                   => 'nullable|string',
            'spesialisasi'            => 'nullable|string|max:100',
            'status'                  => 'required|in:aktif,nonaktif,cuti',
            'username'                => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user?->id ?? 0)],
            'password'                => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Tentukan role — prioritaskan input admin, fallback ke role existing user
        $role = $request->input('role');
        if (!$role && $user) {
            $role = $user->role;
        }
        $role = $role ?: 'guru';

        // Untuk Wali Kelas, classroom_id adalah kelas wali (wajib). Untuk Guru Pendamping, classroom_id = NULL
        if ($role === 'guru') {
            $classroomId = null;
            $kelasName   = null;
        } else {
            $classroomId = $request->classroom_id;
            if (!$classroomId && $request->kelas) {
                $cls = Classroom::where('name', $request->kelas)->first();
                if ($cls) {
                    $classroomId = $cls->id;
                }
            }
        }

        // Jika Wali Kelas harus punya kelas
        if ($role === 'wali_kelas' && !$classroomId) {
            return back()->withInput()->withErrors([
                'classroom_id' => 'Wali Kelas harus memiliki Kelas Wali yang dipilih.',
            ]);
        }

        $kelasName = null;
        if ($classroomId) {
            $kelasName = Classroom::where('id', $classroomId)->value('name');
        }

        $guruData = [
            'name'         => $request->name,
            'nomor_hp'     => $request->nomor_hp,
            'classroom_id' => $classroomId,
            'kelas'        => $kelasName,
            'spesialisasi' => $request->spesialisasi,
            'status'       => $request->status,
        ];

        if (Schema::hasColumn('gurus', 'nip')) {
            $guruData['nip'] = $request->nip;
        }

        $guru->update($guruData);

        // Sync penugasan kelas yang dapat diajar / Wali Kelas
        if ($role === 'wali_kelas' && $classroomId) {
            $guru->classrooms()->sync([$classroomId]);
        } elseif ($request->has('allowed_classroom_ids') && is_array($request->allowed_classroom_ids)) {
            $guru->classrooms()->sync($request->input('allowed_classroom_ids', []));
        } else {
            $guru->classrooms()->sync([]);
        }

        // Update atau buat akun user
        if ($request->filled('username')) {
            $username = trim((string) $request->username);
            $email    = $this->buildEmailFromUsername($username);

            if ($user) {
                // Update akun existing — UPDATE ROLE sesuai pilihan admin
                $updateData = [
                    'name'     => $guru->name,
                    'username' => $username,
                    'email'    => $email,
                    'role'     => $role,   // ← Update role sesuai input admin
                ];
                if ($request->filled('password')) {
                    $updateData['password'] = Hash::make($request->password);
                }
                $user->update($updateData);
            } elseif ($request->filled('password')) {
                // Buat akun baru jika belum ada
                $user = User::create([
                    'name'     => $guru->name,
                    'username' => $username,
                    'email'    => $email,
                    'password' => Hash::make($request->password),
                    'role'     => $role,
                    'guru_id'  => $guru->id,
                ]);
                $guru->update(['user_id' => $user->id]);
            }
        } elseif ($user) {
            // Update nama dan role saja meski username tidak diisi
            $updateData = ['name' => $guru->name];
            if ($request->filled('role')) {
                $updateData['role'] = $role;
            }
            $user->update($updateData);
        }

        return redirect()->route('admin.guru.index')
            ->with('success', "Data guru '{$guru->name}' berhasil diperbarui.");
    }

    public function destroy(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $hasUser     = User::where('guru_id', $guru->id)->exists()
                       || ($guru->user_id && User::where('id', $guru->user_id)->exists());
        $hasAbsensi  = \App\Models\TeacherAttendance::where('guru_id', $guru->id)->exists();
        $hasSchedule = \App\Models\TeacherSchedule::where('guru_id', $guru->id)->exists();

        if ($hasUser || $hasAbsensi || $hasSchedule) {
            $relasi = [];
            if ($hasUser)     $relasi[] = 'akun login';
            if ($hasAbsensi)  $relasi[] = 'riwayat absensi mengajar';
            if ($hasSchedule) $relasi[] = 'jadwal mengajar';

            return redirect()->route('admin.guru.index')
                ->with('error', "Guru '{$guru->name}' masih memiliki " . implode(', ', $relasi) . ". Hapus atau pindahkan relasi tersebut terlebih dahulu sebelum menghapus data guru ini.");
        }

        $guru->classrooms()->detach();
        $guru->delete();

        return redirect()->route('admin.guru.index')
            ->with('success', "Data guru '{$guru->name}' berhasil dihapus.");
    }

    protected function buildEmailFromUsername(string $username): string
    {
        $base = trim($username);

        if ($base === '') {
            return 'guru@santri.local';
        }

        return preg_replace('/[^a-zA-Z0-9._@-]/', '', $base) . '@santri.com';
    }
}
