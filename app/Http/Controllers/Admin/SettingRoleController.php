<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingRoleController extends Controller
{
    public function index()
    {
        $accounts = $this->getAccounts();
        $gurus = Guru::with('classroom')->orderBy('name')->get();

        return view('admin.setting-role.index', compact('accounts', 'gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'in:Admin Utama,Wali Kelas,Guru,Supervisor'],
            'status' => ['nullable', 'string'],
            'kelas' => ['nullable', 'string'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
        ]);

        if ($request->role === 'Admin Utama') {
            $admin = Admin::updateOrCreate(
                ['username' => trim($request->username)],
                [
                    'name' => trim($request->name),
                    'password' => trim((string) $request->password),
                ]
            );

            return response()->json(['success' => true, 'account' => $this->normalizeAdmin($admin)]);
        }

        $normalizedRole = match ($request->role) {
            'Wali Kelas' => 'wali_kelas',
            'Guru' => 'guru',
            default => 'supervisor',
        };

        if (in_array($normalizedRole, ['wali_kelas', 'guru'])) {
            if (!$request->filled('guru_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan pilih data guru yang sudah ada dari daftar.'
                ], 422);
            }

            $guru = Guru::find($request->guru_id);
            if (!$guru) {
                return response()->json(['success' => false, 'message' => 'Data guru tidak ditemukan.'], 422);
            }

            // PENCEGAHAN AKUN GANDA
            $existingAccount = User::where('guru_id', $guru->id);
            if ($guru->user_id) {
                $existingAccount->orWhere('id', $guru->user_id);
            }
            $existingAccount = $existingAccount->first();

            if ($existingAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru ini sudah memiliki akun.'
                ], 422);
            }

            // ATURAN KHUSUS WALI KELAS
            if ($normalizedRole === 'wali_kelas') {
                $hasClass = !empty($guru->classroom_id);
                if (!$hasClass) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guru ini belum memiliki kelas yang dapat digunakan sebagai Wali Kelas.'
                    ], 422);
                }
            }
        }

        $user = User::firstOrNew(['username' => trim($request->username)]);
        $user->name = trim($request->name);
        $user->email = $user->email ?: (trim($request->username) . '@santri.com');
        $user->role = $normalizedRole;
        $user->password = Hash::make(trim((string) $request->password));

        if (isset($guru) && $guru) {
            $user->guru_id = $guru->id;
            $user->save();

            if (!$guru->user_id) {
                $guru->user_id = $user->id;
                $guru->save();
            }
        } else {
            $user->save();
        }

        return response()->json(['success' => true, 'account' => $this->normalizeUser($user->fresh())]);
    }

    public function update(Request $request, string $accountId)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:4'],
            'role' => ['required', 'in:Admin Utama,Wali Kelas,Guru,Supervisor'],
            'status' => ['nullable', 'string'],
            'kelas' => ['nullable', 'string'],
            'guru_id' => ['nullable', 'exists:gurus,id'],
        ]);

        $parsedAccountId = $this->parseAccountId($accountId);

        if ($parsedAccountId['type'] === 'admin') {
            $admin = Admin::findOrFail($parsedAccountId['id']);
            $admin->name = trim($request->name);
            $admin->username = trim($request->username);

            if ($request->filled('password')) {
                $admin->password = trim((string) $request->password);
            }

            $admin->save();

            return response()->json(['success' => true, 'account' => $this->normalizeAdmin($admin)]);
        }

        $user = User::findOrFail($parsedAccountId['id']);
        $normalizedRole = match ($request->role) {
            'Wali Kelas' => 'wali_kelas',
            'Guru' => 'guru',
            default => 'supervisor',
        };

        if (in_array($normalizedRole, ['wali_kelas', 'guru'])) {
            $guruId = $request->guru_id ?: $user->guru_id;
            if (!$guruId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan pilih data guru yang sudah ada.'
                ], 422);
            }

            $guru = Guru::find($guruId);
            if (!$guru) {
                return response()->json(['success' => false, 'message' => 'Data guru tidak ditemukan.'], 422);
            }

            // PENCEGAHAN AKUN GANDA (jika memilih guru lain yang sudah berakun)
            $existingAccount = User::where('guru_id', $guru->id)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru ini sudah memiliki akun.'
                ], 422);
            }

            // ATURAN KHUSUS WALI KELAS
            if ($normalizedRole === 'wali_kelas') {
                $hasClass = !empty($guru->classroom_id);
                if (!$hasClass) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guru ini belum memiliki kelas yang dapat digunakan sebagai Wali Kelas.'
                    ], 422);
                }
            }
        }

        $user->name = trim($request->name);
        $user->username = trim($request->username);
        $user->email = $user->email ?: (trim($request->username) . '@santri.com');

        if ($request->filled('password')) {
            $user->password = Hash::make(trim((string) $request->password));
        }

        $user->role = $normalizedRole;

        if (isset($guru) && $guru) {
            $user->guru_id = $guru->id;
            $user->save();

            if (!$guru->user_id) {
                $guru->user_id = $user->id;
                $guru->save();
            }
        } else {
            $user->save();
        }

        return response()->json(['success' => true, 'account' => $this->normalizeUser($user->fresh())]);
    }

    public function linkGuru(Request $request, string $accountId)
    {
        $request->validate([
            'guru_id' => ['required', 'exists:gurus,id'],
        ]);

        $parsedAccountId = $this->parseAccountId($accountId);
        if ($parsedAccountId['type'] !== 'user') {
            return response()->json(['success' => false, 'message' => 'Tipe akun tidak valid untuk dihubungkan ke guru.'], 422);
        }

        $user = User::findOrFail($parsedAccountId['id']);
        $guru = Guru::findOrFail($request->guru_id);

        // PENCEGAHAN AKUN GANDA
        $existingAccount = User::where('guru_id', $guru->id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingAccount) {
            return response()->json(['success' => false, 'message' => 'Guru ini sudah memiliki akun.'], 422);
        }

        $user->guru_id = $guru->id;
        if ($user->role !== 'guru' && $user->role !== 'wali_kelas') {
            $user->role = $guru->classroom_id ? 'wali_kelas' : 'guru';
        }
        $user->save();

        $guru->user_id = $user->id;
        $guru->save();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil terhubung dengan Guru ' . $guru->name,
            'account' => $this->normalizeUser($user->fresh()),
        ]);
    }

    public function destroy(Request $request, string $accountId)
    {
        $parsedAccountId = $this->parseAccountId($accountId);

        if ($parsedAccountId['type'] === 'admin') {
            Admin::findOrFail($parsedAccountId['id'])->delete();

            return response()->json(['success' => true]);
        }

        $user = User::findOrFail($parsedAccountId['id']);

        if ($user->guru) {
            $user->guru->update(['user_id' => null]);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }

    protected function getAccounts(): \Illuminate\Support\Collection
    {
        $admins = Admin::query()
            ->select(['id', 'name', 'username'])
            ->get()
            ->map(fn ($admin) => $this->normalizeAdmin($admin));

        $users = User::query()
            ->with('guru.classroom')
            ->whereIn('role', ['wali_kelas', 'guru', 'supervisor'])
            ->get()
            ->map(fn ($user) => $this->normalizeUser($user));

        return $admins->concat($users)->values();
    }

    protected function normalizeAdmin(Admin $admin): array
    {
        return [
            'id' => 'admin_' . $admin->id,
            'db_id' => $admin->id,
            'type' => 'admin',
            'name' => $admin->name ?: 'Administrator Utama',
            'username' => $admin->username,
            'role' => 'Admin Utama',
            'kelas' => ['Semua Kelas'],
            'status' => 'Aktif',
            'last_active' => 'Aktif Sekarang',
            'is_linked' => true,
            'guru_id' => null,
            'guru_name' => null,
        ];
    }

    protected function normalizeUser(User $user): array
    {
        $roleLabel = match ($user->role) {
            'wali_kelas' => 'Wali Kelas',
            'guru' => 'Guru',
            'supervisor' => 'Supervisor',
            default => ucfirst((string) $user->role),
        };

        $guru = $user->guru;
        $kelas = ['Belum Terhubung Data Guru'];
        $status = 'Aktif';
        $isLinked = (bool) $guru;

        if ($guru) {
            $status = ucfirst((string) ($guru->status ?: 'aktif'));
            
            if ($user->role === 'wali_kelas') {
                // Wali Kelas harus punya kelas utama
                $kelas = $guru->classroom_id && $guru->classroom ? [$guru->classroom->name] : ['Belum ada Kelas Wali'];
            } else {
                // Guru Biasa: baca dari tabel pivot
                $pivotClassrooms = $guru->classrooms()->orderBy('name')->pluck('name')->toArray();
                if (!empty($pivotClassrooms)) {
                    $kelas = $pivotClassrooms;
                } elseif ($guru->classroom_id && $guru->classroom) {
                    $kelas = [$guru->classroom->name];
                } else {
                    $kelas = ['Belum ada kelas yang ditugaskan'];
                }
            }
        } elseif ($roleLabel === 'Supervisor') {
            $kelas = ['Semua Kelas'];
            $isLinked = true;
        }

        return [
            'id' => 'user_' . $user->id,
            'db_id' => $user->id,
            'type' => 'user',
            'name' => $user->name,
            'username' => $user->username,
            'role' => $roleLabel,
            'kelas' => $kelas,
            'status' => $status,
            'last_active' => 'Terdaftar di Sistem',
            'is_linked' => $isLinked,
            'guru_id' => $guru?->id,
            'guru_name' => $guru?->name,
        ];
    }

    protected function parseAccountId(string $accountId): array
    {
        if (str_starts_with($accountId, 'admin_')) {
            return ['type' => 'admin', 'id' => (int) str_replace('admin_', '', $accountId)];
        }

        return ['type' => 'user', 'id' => (int) str_replace('user_', '', $accountId)];
    }
}
