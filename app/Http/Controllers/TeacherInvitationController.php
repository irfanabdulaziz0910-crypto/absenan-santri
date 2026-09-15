<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\TeacherInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherInvitationController extends Controller
{
    /**
     * Tampilkan form pendaftaran mandiri guru via token invitation
     */
    public function show(string $token)
    {
        $invitation = TeacherInvitation::with('guru')->where('token', $token)->first();

        if (!$invitation) {
            return view('auth.teacher-invite-invalid', [
                'status'  => 'invalid',
                'title'   => 'Link Undangan Tidak Valid',
                'message' => 'Link undangan yang Anda buka tidak ditemukan. Silakan minta admin untuk mengirimkan link undangan resmi.',
            ]);
        }

        if ($invitation->isUsed()) {
            return view('auth.teacher-invite-invalid', [
                'status'  => 'used',
                'title'   => 'Link Undangan Sudah Digunakan',
                'message' => 'Link undangan ini sudah berhasil digunakan sebelumnya. Silakan login menggunakan akun yang sudah Anda buat.',
                'invitation' => $invitation,
            ]);
        }

        if ($invitation->isExpired()) {
            return view('auth.teacher-invite-invalid', [
                'status'  => 'expired',
                'title'   => 'Link Undangan Kedaluwarsa',
                'message' => 'Masa berlaku link undangan ini sudah berakhir pada ' . $invitation->expires_at->format('d M Y H:i') . '. Silakan hubungi admin untuk mendapatkan link undangan baru.',
            ]);
        }

        // Cek jika guru_id terhubung tetapi guru tersebut ternyata sudah punya akun login
        if ($invitation->guru_id) {
            $existingUser = User::where('guru_id', $invitation->guru_id)->first();
            if ($existingUser) {
                return view('auth.teacher-invite-invalid', [
                    'status'  => 'already_has_account',
                    'title'   => 'Guru Sudah Memiliki Akun',
                    'message' => 'Data guru "' . ($invitation->guru->name ?? 'ini') . '" sudah terhubung dengan akun login. Silakan login menggunakan akun yang ada.',
                ]);
            }
        }

        return view('auth.teacher-invite', [
            'invitation' => $invitation,
            'presetName' => $invitation->guru ? $invitation->guru->name : ($invitation->name ?: ''),
        ]);
    }

    /**
     * Proses pembuatan akun guru mandiri
     */
    public function store(Request $request, string $token)
    {
        $invitation = TeacherInvitation::with('guru')->where('token', $token)->first();

        if (!$invitation || !$invitation->isValid()) {
            return back()->withInput()->withErrors([
                'invitation' => 'Link undangan ini sudah tidak berlaku atau sudah digunakan.',
            ]);
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username ini sudah digunakan oleh pengguna lain. Silakan pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);

        $name     = trim($request->name);
        $username = trim($request->username);
        $email    = $username . '@santri.com';

        // 1. Tentukan atau cari Data Guru
        $guru = null;
        if ($invitation->guru_id) {
            $guru = Guru::find($invitation->guru_id);
        }

        if (!$guru) {
            // Cari apakah sudah ada data Guru dengan nama persis tanpa akun
            $guru = Guru::where('name', $name)->whereNull('user_id')->first();
        }

        if (!$guru) {
            // Buat record Guru baru jika belum ada
            $guru = Guru::create([
                'name'         => $name,
                'status'       => 'aktif',
                'bergabung_at' => now()->toDateString(),
            ]);
        } else {
            // Update nama jika diperlukan
            $guru->update(['name' => $name]);
        }

        // Cek kembali pencegahan akun ganda
        $existingAccount = User::where('guru_id', $guru->id)->first();
        if ($existingAccount) {
            return back()->withInput()->withErrors([
                'username' => 'Guru "' . $guru->name . '" sudah memiliki akun login. Silakan login dengan akun yang ada.',
            ]);
        }

        // 2. Buat Kredensial Login di tabel users
        $role = $invitation->role ?: ($guru->classroom_id ? 'wali_kelas' : 'guru');

        $user = User::create([
            'name'     => $guru->name,
            'username' => $username,
            'email'    => $email,
            'password' => Hash::make($request->password),
            'role'     => $role,
            'guru_id'  => $guru->id,
        ]);

        // Link kan user_id ke model Guru
        $guru->update(['user_id' => $user->id]);

        // Mark invitation as used
        $invitation->update([
            'used_at'         => now(),
            'used_by_user_id' => $user->id,
        ]);

        return redirect()->route('admin.login')
            ->with('success', "Akun guru berhasil dibuat! Silakan login dengan username [{$username}] dan password Anda.");
    }
}
