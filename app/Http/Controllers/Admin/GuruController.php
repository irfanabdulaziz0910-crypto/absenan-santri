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

        $invitations   = \App\Models\TeacherInvitation::with(['guru', 'usedByUser'])->latest()->get();
        $unlinkedGurus = Guru::where(function ($q) {
            $q->whereNull('user_id')
              ->orWhereDoesntHave('user');
        })->orderBy('name')->get();

        return view('admin.guru.index', compact(
            'gurus',
            'totalAktif',
            'totalCuti',
            'kelasAktif',
            'kelasList',
            'invitations',
            'unlinkedGurus',
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

        // Validasi 1 Wali Kelas per Kelas
        if ($role === 'wali_kelas' && $classroomId) {
            $existingWali = Guru::where('classroom_id', $classroomId)->first();
            if ($existingWali) {
                $clsName = Classroom::where('id', $classroomId)->value('name');
                return back()->withInput()->withErrors([
                    'classroom_id' => "Kelas {$clsName} sudah memiliki Wali Kelas yaitu {$existingWali->name}. Satu kelas hanya dapat memiliki satu Wali Kelas aktif.",
                ]);
            }
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
                'role'     => $role,
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

        // Validasi 1 Wali Kelas per Kelas
        if ($role === 'wali_kelas' && $classroomId) {
            $existingWali = Guru::where('classroom_id', $classroomId)->where('id', '!=', $guru->id)->first();
            if ($existingWali) {
                $clsName = Classroom::where('id', $classroomId)->value('name');
                return back()->withInput()->withErrors([
                    'classroom_id' => "Kelas {$clsName} sudah memiliki Wali Kelas yaitu {$existingWali->name}. Satu kelas hanya dapat memiliki satu Wali Kelas aktif.",
                ]);
            }
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
                $updateData = [
                    'name'     => $guru->name,
                    'username' => $username,
                    'email'    => $email,
                    'role'     => $role,
                ];
                if ($request->filled('password')) {
                    $updateData['password'] = Hash::make($request->password);
                }
                $user->update($updateData);
            } elseif ($request->filled('password')) {
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
            $updateData = ['name' => $guru->name];
            if ($request->filled('role')) {
                $updateData['role'] = $role;
            }
            $user->update($updateData);
        }

        return redirect()->route('admin.guru.index')
            ->with('success', "Data guru '{$guru->name}' berhasil diperbarui.");
    }

    /**
     * Buat Kode Pendaftaran Unik untuk Guru
     */
    public function generateRegistrationCode(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        if ($guru->hasAccount()) {
            return back()->with('error', "Guru '{$guru->name}' sudah memiliki akun login terdaftar.");
        }

        $code = strtoupper(\Illuminate\Support\Str::random(3)) . '-' . rand(1000, 9999);
        while (Guru::where('registration_code', $code)->exists()) {
            $code = strtoupper(\Illuminate\Support\Str::random(3)) . '-' . rand(1000, 9999);
        }

        $guru->update([
            'registration_code'            => $code,
            'registration_code_expires_at' => now()->addDays(30),
        ]);

        return back()->with('success', "Kode Pendaftaran untuk '{$guru->name}' berhasil dibuat: [{$code}]. Minta guru menggunakan kode ini saat registrasi.");
    }

    /**
     * Preview & Deteksi Duplikat Nama dari File PDF yang diupload
     */
    public function previewPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'pdf_file.required' => 'File PDF wajib dipilih.',
            'pdf_file.mimes'    => 'File harus berformat PDF (.pdf).',
            'pdf_file.max'      => 'Ukuran file PDF maksimal 10MB.',
        ]);

        $file = $request->file('pdf_file');
        $fileName = $file->getClientOriginalName();
        $extractedText = $this->extractTextFromPdfFile($file->getPathname());

        $teacherNames = $this->parseTeacherNamesFromText($extractedText);

        if (empty($teacherNames)) {
            return back()->with('error', "Gagal membaca daftar nama pengajar dari file '{$fileName}'. Pastikan file PDF berisi teks pengajar yang valid.");
        }

        $existingGurus = Guru::all();
        $previewData = [];

        foreach ($teacherNames as $name) {
            $norm = $this->normalizeName($name);

            $exactMatch = $existingGurus->first(function ($g) use ($name, $norm) {
                return strtolower(trim($g->name)) === strtolower(trim($name)) || $this->normalizeName($g->name) === $norm;
            });

            if ($exactMatch) {
                $previewData[] = [
                    'original'  => $name,
                    'status'    => 'duplikat',
                    'note'      => 'Potensi Data Duplikat dengan Guru ID ' . $exactMatch->id . ' (' . $exactMatch->name . ')',
                    'guru_id'   => $exactMatch->id,
                ];
            } else {
                $partialMatch = $existingGurus->first(function ($g) use ($norm) {
                    return str_contains($norm, $this->normalizeName($g->name)) || str_contains($this->normalizeName($g->name), $norm);
                });

                if ($partialMatch) {
                    $previewData[] = [
                        'original' => $name,
                        'status'   => 'verifikasi',
                        'note'     => 'Perlu Verifikasi Admin (Kemungkinan mirip dengan Guru: ' . $partialMatch->name . ')',
                        'guru_id'  => null,
                    ];
                } else {
                    $previewData[] = [
                        'original' => $name,
                        'status'   => 'aman',
                        'note'     => 'Siap Ditambahkan',
                        'guru_id'  => null,
                    ];
                }
            }
        }

        return view('admin.guru.preview-pdf', compact('previewData', 'fileName'));
    }

    /**
     * Helper Ekstraksi Teks dari File PDF
     */
    protected function extractTextFromPdfFile(string $filePath): string
    {
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
                if (trim($text) !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                // Fallback to native stream parsing
            }
        }

        $content = file_get_contents($filePath);
        if (!$content) return '';

        preg_match_all('/BT[\s\S]*?ET/s', $content, $matches);
        $text = '';
        if (!empty($matches[0])) {
            foreach ($matches[0] as $block) {
                preg_match_all('/\((.*?)\)\s*T[jJ]/s', $block, $strMatches);
                if (!empty($strMatches[1])) {
                    $text .= implode(" ", $strMatches[1]) . "\n";
                } else {
                    preg_match_all('/\((.*?)\)/s', $block, $rawStrs);
                    if (!empty($rawStrs[1])) {
                        $text .= implode(" ", $rawStrs[1]) . "\n";
                    }
                }
            }
        }

        if (trim($text) === '') {
            $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $content);
        }

        return $text;
    }

    /**
     * Extract unique teacher names from PDF extracted text
     */
    protected function parseTeacherNamesFromText(string $rawText): array
    {
        $names = [];

        $pattern = '/\b(?:Ust\.|Ustdz\.|Ust|Ustdz|Syaikhuna)\s+[A-Za-z0-9\.\,\'\s\\\]+/i';
        preg_match_all($pattern, $rawText, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $clean = trim($match);
                // Filter out non-teacher text words if any
                $clean = preg_replace('/\s+(KHULASOH|JURUMIYAH|TASRIFAN|SAFINAH|NADZOMAN|HISNUL|ALFIYYAH|DUHUR|SUBUH|ASAR|MALAM|WAKTU|MATA|PELAJARAN|PENGAJAR|KELAS|IBTIDA|TSANAWI).*$/i', '', $clean);
                $clean = trim($clean);

                if (strlen($clean) >= 4 && strlen($clean) <= 60) {
                    $names[] = $clean;
                }
            }
        }

        $lines = explode("\n", $rawText);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '' && (preg_match('/^(Ust\.|Ustdz\.|Ust|Syaikhuna)/i', $line))) {
                $clean = preg_replace('/\s+(KHULASOH|JURUMIYAH|TASRIFAN|SAFINAH|NADZOMAN|HISNUL|ALFIYYAH).*$/i', '', $line);
                $names[] = trim($clean);
            }
        }

        return array_values(array_unique(array_filter($names)));
    }

    /**
     * Simpan Data Guru dari Verifikasi Admin PDF
     */
    public function importPdf(Request $request)
    {
        $selectedNames = (array) $request->input('names', []);

        if (empty($selectedNames)) {
            return redirect()->route('admin.guru.index')->with('error', 'Tidak ada nama yang dipilih untuk dimasukkan.');
        }

        $count = 0;
        foreach ($selectedNames as $name) {
            $cleanName = trim($name);
            if ($cleanName === '') continue;

            $exists = Guru::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])->exists();
            if (!$exists) {
                Guru::create([
                    'name'         => $cleanName,
                    'status'       => 'aktif',
                    'bergabung_at' => now()->toDateString(),
                ]);
                $count++;
            }
        }

        return redirect()->route('admin.guru.index')
            ->with('success', "Berhasil menambahkan {$count} data Guru baru ke Master Data Guru setelah verifikasi Admin.");
    }

    protected function normalizeName(string $name): string
    {
        $name = preg_replace('/^(ust\.|ustdz\.|ustadz|ustadzah|syaikhuna)\s+/i', '', trim($name));
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
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

    /**
     * Buat Link Undangan Guru oleh Admin
     */
    public function storeInvitation(Request $request)
    {
        $request->validate([
            'guru_id'      => ['nullable', 'exists:gurus,id'],
            'name'         => ['nullable', 'string', 'max:255'],
            'expires_days' => ['required', 'integer', 'min:1', 'max:90'],
            'role'         => ['nullable', 'in:guru,wali_kelas'],
        ]);

        $guru = null;
        if ($request->filled('guru_id')) {
            $guru = Guru::find($request->guru_id);

            if ($guru) {
                $existingAccount = User::where('guru_id', $guru->id)->first();
                if ($existingAccount) {
                    return back()->with('error', "Guru '{$guru->name}' sudah memiliki akun login (username: {$existingAccount->username}).");
                }
            }
        }

        // Tentukan role: prioritaskan input form, fallback auto-detect dari classroom guru
        $roleFromInput = $request->input('role');
        if ($roleFromInput && in_array($roleFromInput, ['guru', 'wali_kelas'])) {
            $finalRole = $roleFromInput;
        } else {
            $finalRole = ($guru && $guru->classroom_id) ? 'wali_kelas' : 'guru';
        }

        $token     = \Illuminate\Support\Str::random(40);
        $expiresAt = now()->addDays((int) $request->expires_days);

        $invitation = \App\Models\TeacherInvitation::create([
            'token'      => $token,
            'guru_id'    => $guru?->id,
            'name'       => $guru ? $guru->name : ($request->name ? trim($request->name) : null),
            'role'       => $finalRole,
            'expires_at' => $expiresAt,
            'created_by' => auth()->guard('admin')->id(),
        ]);

        return back()->with('success', "Link undangan berhasil dibuat untuk " . ($invitation->name ?: 'Guru') . "! Link: " . $invitation->invite_url);
    }

    /**
     * Hapus Link Undangan Guru
     */
    public function destroyInvitation($id)
    {
        $invitation = \App\Models\TeacherInvitation::findOrFail($id);
        $invitation->delete();

        return back()->with('success', 'Link undangan berhasil dihapus.');
    }
}
