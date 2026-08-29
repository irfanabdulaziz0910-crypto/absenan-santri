<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $account = $this->currentAccount();

        if (!$account) {
            return redirect()->route('admin.login');
        }

        $profile = $this->buildProfile($account);
        $layout = $this->isAdminGuard() ? 'layouts.admin' : 'layouts.wali-kelas';

        return view('profile.index', [
            'profile' => $profile,
            'layout' => $layout,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $account = $this->currentAccount();

        if (!$account) {
            return redirect()->route('admin.login');
        }

        $account->name = trim((string) $request->name);
        $account->save();

        return redirect()->route('profile')->with('success', 'Perubahan profil berhasil disimpan.');
    }

    public function updateUsername(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[A-Za-z0-9._-]+$/',
            ],
        ]);

        $account = $this->currentAccount();

        if (!$account) {
            return redirect()->route('admin.login');
        }

        if (!$this->verifyPassword($account, (string) $request->current_password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
        }

        $username = trim((string) $request->username);
        $existing = $this->findDuplicateUsername($username, $account);

        if ($existing) {
            return back()->withErrors(['username' => 'Username sudah digunakan oleh akun lain.'])->withInput();
        }

        $account->username = $username;
        $account->save();

        return redirect()->route('profile')->with('success', 'Username berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $account = $this->currentAccount();

        if (!$account) {
            return redirect()->route('admin.login');
        }

        if (!$this->verifyPassword($account, (string) $request->current_password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
        }

        $account->password = Hash::make((string) $request->password);
        $account->save();

        return redirect()->route('profile')->with('success', 'Password berhasil diperbarui.');
    }

    protected function currentAccount()
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }

        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }

        return null;
    }

    protected function isAdminGuard(): bool
    {
        return Auth::guard('admin')->check();
    }

    protected function buildProfile($account): array
    {
        if ($account instanceof Admin) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'username' => $account->username,
                'role' => 'Administrator',
                'kelas' => 'Semua Kelas',
                'status' => 'Aktif',
                'type' => 'admin',
                'avatar' => $account->name ?: 'Admin',
            ];
        }

        $role = strtolower((string) ($account->role ?? ''));
        $label = match ($role) {
            'supervisor' => 'Supervisor',
            'wali_kelas' => 'Wali Kelas',
            'guru' => 'Guru',
            default => ucfirst($account->role ?? 'Pengguna'),
        };

        $guru = $account->guru;
        if (! $guru && !empty($account->guru_id)) {
            $guru = \App\Models\Guru::find($account->guru_id);
        }
        if (! $guru && !empty($account->id)) {
            $guru = \App\Models\Guru::where('user_id', $account->id)->first();
        }

        $kelas = 'Belum ditentukan';
        if ($guru) {
            $kelas = $guru->classroom?->name ?? ($guru->kelas ?? 'Ma\'had Ali');
        }

        $teacherAttendances = $guru ? TeacherAttendance::where('guru_id', $guru->id) : TeacherAttendance::whereRaw('1 = 0');

        return [
            'id' => $account->id,
            'name' => $account->name,
            'username' => $account->username,
            'role' => $label,
            'kelas' => $kelas,
            'status' => $guru ? ucfirst((string) ($guru->status ?: 'aktif')) : 'Aktif',
            'type' => 'user',
            'avatar' => $account->name ?: 'User',
            'nip' => $guru?->nip ?? '-',
            'nomor_hp' => $guru?->nomor_hp ?? '-',
            'total_mengajar' => (clone $teacherAttendances)->count(),
            'total_hadir' => (clone $teacherAttendances)->where('status', 'Hadir')->count(),
            'total_izin' => (clone $teacherAttendances)->where('status', 'Izin')->count(),
            'total_sakit' => (clone $teacherAttendances)->where('status', 'Sakit')->count(),
            'total_alfa' => (clone $teacherAttendances)->where('status', 'Alfa')->count(),
        ];
    }

    protected function verifyPassword($account, string $password): bool
    {
        if ($account instanceof Admin) {
            return $account->validatePassword($password);
        }

        $storedPassword = (string) ($account->password ?? '');

        return Hash::check($password, $storedPassword) || $storedPassword === $password;
    }

    protected function findDuplicateUsername(string $username, $currentAccount)
    {
        if ($currentAccount instanceof Admin) {
            return Admin::where('username', $username)
                ->whereKeyNot($currentAccount->getKey())
                ->first();
        }

        return User::where('username', $username)
            ->whereKeyNot($currentAccount->getKey())
            ->first();
    }
}
