<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'username')) {
                    $table->string('username')->unique()->nullable()->after('name');
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('wali_kelas')->after('email');
                }
                if (!Schema::hasColumn('users', 'guru_id')) {
                    $table->unsignedBigInteger('guru_id')->nullable()->after('role');
                }
            });
        }

        if (Schema::hasTable('gurus')) {
            Schema::table('gurus', function (Blueprint $table) {
                if (!Schema::hasColumn('gurus', 'classroom_id')) {
                    $table->unsignedBigInteger('classroom_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('gurus', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('classroom_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'guru_id')) {
                    $table->dropColumn('guru_id');
                }
                if (Schema::hasColumn('users', 'username')) {
                    $table->dropColumn('username');
                }
                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }

        if (Schema::hasTable('gurus')) {
            Schema::table('gurus', function (Blueprint $table) {
                if (Schema::hasColumn('gurus', 'user_id')) {
                    $table->dropColumn('user_id');
                }
                if (Schema::hasColumn('gurus', 'classroom_id')) {
                    $table->dropColumn('classroom_id');
                }
            });
        }
    }
};
