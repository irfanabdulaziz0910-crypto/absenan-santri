<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}

