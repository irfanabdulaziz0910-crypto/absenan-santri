<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Double check column existence
        if (! Schema::hasColumn('users', 'guru_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('guru_id')->nullable()->after('role')->constrained('gurus')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('gurus', 'user_id')) {
            Schema::table('gurus', function (Blueprint $table): void {
                $table->foreignId('user_id')->nullable()->after('classroom_id')->constrained('users')->nullOnDelete();
            });
        }

        $hasNipColumn = Schema::hasColumn('gurus', 'nip');

        // 2. Safely link unlinked users to existing gurus
        $unlinkedUsers = DB::table('users')
            ->whereIn('role', ['wali_kelas', 'guru'])
            ->whereNull('guru_id')
            ->get();

        foreach ($unlinkedUsers as $user) {
            $guru = DB::table('gurus')
                ->where(function ($q) use ($user, $hasNipColumn) {
                    $q->where('user_id', $user->id)
                      ->orWhere('name', $user->name)
                      ->orWhere('kelas', $user->username);
                    if ($hasNipColumn) {
                        $q->orWhere('nip', $user->username);
                    }
                })
                ->first();

            if (! $guru) {
                // Try fuzzy name match (e.g. Irfan Abdul Aziz)
                $guru = DB::table('gurus')
                    ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) $user->name))])
                    ->first();
            }

            if ($guru) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['guru_id' => $guru->id]);

                DB::table('gurus')
                    ->where('id', $guru->id)
                    ->whereNull('user_id')
                    ->update(['user_id' => $user->id]);
            }
        }

        // 3. Ensure Guru ID 1 is linked to User ID 41 if both exist
        $user41 = DB::table('users')->where('id', 41)->first();
        $guru1 = DB::table('gurus')->where('id', 1)->first();

        if ($user41 && $guru1) {
            DB::table('users')->where('id', 41)->update(['guru_id' => 1]);
            DB::table('gurus')->where('id', 1)->update(['user_id' => 41]);
        }
    }

    public function down(): void
    {
        // Intentionally left empty to preserve database relations.
    }
};
