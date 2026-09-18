<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Classroom;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        $wkUser = User::where('role', 'wali_kelas')->with('guru')->first();
        return view('auth.login', compact('wkUser'));
    }

    public function debugLoginPage()
    {
        $admin = Admin::where('username', 'admin')->first();

        return view('auth.debug-login', compact('admin'));
    }

    public function debugLogin(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        $admin = Admin::where('username', $username)->first();
        $result = 'Admin tidak ditemukan';

        if ($admin) {
            $result = $admin->validatePassword($password)
                ? 'Akun ditemukan dan password cocok.'
                : 'Akun ditemukan tetapi password tidak cocok.';
        }

        return redirect()->route('admin.debug.login.page')->with('debug_result', $result);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $rawUsername = trim((string) $request->input('username'));
        $password    = (string) $request->input('password');

        $admin = Admin::where('username', $rawUsername)->first();
        if ($admin && $admin->validatePassword($password)) {
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        $user = $this->findUserForLogin($rawUsername);

        if ($user && $this->passwordMatches($user->password, $password)) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            session([
                'guru_id'      => $user->guru_id,
                'classroom_id' => $user->guru?->classroom_id,
            ]);

            // Redirect berdasarkan role
            return match ($user->role) {
                'guru'       => redirect()->route('guru.dashboard'),
                'supervisor' => redirect()->route('wali-kelas.dashboard'),
                default      => redirect()->route('wali-kelas.dashboard'), // wali_kelas & lainnya
            };
        }

        return back()->withErrors([
            'username' => 'Username atau password salah. Pastikan menggunakan username yang valid.',
        ])->withInput()->onlyInput('username');
    }

    protected function findUserForLogin(string $username): ?User
    {
        $candidate = trim($username);

        $user = User::where('username', $candidate)->first();
        if ($user) {
            return $user;
        }

        $user = User::where('email', $candidate)->first();
        if ($user) {
            return $user;
        }

        if ($candidate === '' || preg_match('/[\s\'\"]/', $candidate)) {
            return null;
        }

        if (! Schema::hasColumn('gurus', 'nip')) {
            return null;
        }

        $legacyNipPattern = '/^[A-Za-z0-9._-]+$/';
        if (! preg_match($legacyNipPattern, $candidate)) {
            return null;
        }

        return User::whereHas('guru', function ($query) use ($candidate) {
            $query->where('nip', $candidate);
        })->first();
    }

    protected function passwordMatches(string $storedPassword, string $inputPassword): bool
    {
        if (Hash::check($inputPassword, $storedPassword)) {
            return true;
        }

        return $storedPassword === $inputPassword;
    }

    /**
     * Tampilkan halaman pendaftaran akun guru mandiri
     */
    /**
     * Tampilkan halaman pendaftaran akun guru mandiri
     */
    public function showRegisterForm(Request $request)
    {
        $classrooms = Classroom::orderBy('name')->get();
        return view('auth.guru-register', compact('classrooms'));
    }

    /**
     * API AJAX Endpoint untuk verifikasi Nama Guru
     */
    public function checkTeacherName(Request $request)
    {
        $nameInput = trim((string) $request->input('name', ''));

        if ($nameInput === '') {
            return response()->json([
                'success' => false,
                'message' => 'Nama lengkap wajib diisi.',
            ], 422);
        }

        // Cari data Guru yang namanya cocok (case-insensitive, trim whitespace)
        $guru = Guru::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($nameInput)])->first();

        if (!$guru) {
            $guru = Guru::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($nameInput) . '%'])->first();
            if ($guru && strtolower(trim($guru->name)) !== strtolower($nameInput)) {
                $guru = null;
            }
        }

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Data Guru dengan nama tersebut belum terdaftar. Silakan hubungi Admin.',
            ], 404);
        }

        if ($guru->hasAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Guru "' . $guru->name . '" sudah memiliki akun login. Silakan gunakan halaman Login.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'guru'    => [
                'id'     => $guru->id,
                'name'   => $guru->name,
                'nip'    => $guru->nip,
                'status' => $guru->status_label,
            ],
            'message' => 'Data Guru ditemukan: ' . $guru->name,
        ]);
    }

    /**
     * Proses pendaftaran akun guru mandiri berdasarkan NAMA GURU & PENUGASAN
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'role'            => ['required', Rule::in(['wali_kelas', 'guru'])],
            'classroom_id'    => ['nullable', 'required_if:role,wali_kelas', 'exists:classrooms,id'],
            'classroom_ids'   => ['nullable', 'required_if:role,guru', 'array'],
            'classroom_ids.*' => ['exists:classrooms,id'],
            'username'        => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')],
            'password'        => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'             => 'Nama lengkap wajib diisi.',
            'role.required'             => 'Penugasan wajib dipilih.',
            'classroom_id.required_if'  => 'Wali Kelas wajib memilih Kelas Yang Diwali.',
            'classroom_ids.required_if' => 'Guru Pendamping wajib memilih minimal satu kelas.',
            'username.required'         => 'Username wajib diisi.',
            'username.alpha_dash'       => 'Username hanya boleh mengandung huruf, angka, tanda hubung, dan garis bawah.',
            'username.unique'           => 'Tidak dapat menggunakan username tersebut karena sudah digunakan.',
            'password.required'         => 'Password wajib diisi.',
            'password.min'              => 'Password minimal 6 karakter.',
            'password.confirmed'        => 'Tidak dapat membuat akun karena password tidak sama.',
        ]);

        $nameInput = trim((string) $request->input('name'));

        // Cari Data Guru di Master Data Guru
        $guru = Guru::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($nameInput)])->first();
        if (!$guru) {
            $guru = Guru::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($nameInput) . '%'])->first();
            if ($guru && strtolower(trim($guru->name)) !== strtolower($nameInput)) {
                $guru = null;
            }
        }

        if (!$guru) {
            return back()->withInput()->withErrors([
                'name' => 'Data Guru dengan nama tersebut belum terdaftar. Silakan hubungi Admin.',
            ]);
        }

        if ($guru->hasAccount()) {
            return back()->withInput()->withErrors([
                'name' => 'Guru "' . $guru->name . '" sudah memiliki akun login. Silakan gunakan halaman Login.',
            ]);
        }

        $role = $request->input('role'); // 'wali_kelas' atau 'guru'

        // Validasi Backend: 1 Kelas = Maksimal 1 Wali Kelas Aktif
        if ($role === 'wali_kelas') {
            $classroomId  = (int) $request->input('classroom_id');
            $existingWali = Guru::where('classroom_id', $classroomId)
                ->where('id', '!=', $guru->id)
                ->first();

            if ($existingWali) {
                return back()->withInput()->withErrors([
                    'classroom_id' => 'Kelas ini sudah memiliki Wali Kelas.',
                ]);
            }
        }

        $username = trim($request->username);
        $email    = preg_replace('/[^a-zA-Z0-9._@-]/', '', $username) . '@santri.com';

        // Eksekusi atomic pembuatan akun & hubungkan dengan Guru existing + simpan penugasan
        DB::transaction(function () use ($guru, $username, $email, $request, $role) {
            $user = User::create([
                'name'     => $guru->name,
                'username' => $username,
                'email'    => $email,
                'password' => Hash::make($request->password),
                'role'     => $role,
                'guru_id'  => $guru->id,
            ]);

            if ($role === 'wali_kelas') {
                $classroomId = (int) $request->classroom_id;
                $kelasName   = Classroom::where('id', $classroomId)->value('name');

                $guru->update([
                    'user_id'      => $user->id,
                    'classroom_id' => $classroomId,
                    'kelas'        => $kelasName,
                ]);

                $guru->classrooms()->sync([$classroomId]);
            } else {
                $guru->update([
                    'user_id'      => $user->id,
                    'classroom_id' => null,
                    'kelas'        => null,
                ]);

                $selectedClassroomIds = array_map('intval', (array) $request->input('classroom_ids', []));
                $guru->classrooms()->sync($selectedClassroomIds);
            }
        });

        return redirect()->route('admin.login')
            ->with('success', 'Akun guru berhasil dibuat untuk ' . $guru->name . '! Silakan login dengan username [' . $username . '] dan password Anda.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}

