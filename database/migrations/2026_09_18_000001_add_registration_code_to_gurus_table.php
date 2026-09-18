<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('gurus')) {
            Schema::table('gurus', function (Blueprint $table) {
                if (!Schema::hasColumn('gurus', 'registration_code')) {
                    $table->string('registration_code', 50)->nullable()->unique()->after('nip');
                }
                if (!Schema::hasColumn('gurus', 'registration_code_expires_at')) {
                    $table->timestamp('registration_code_expires_at')->nullable()->after('registration_code');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gurus')) {
            Schema::table('gurus', function (Blueprint $table) {
                if (Schema::hasColumn('gurus', 'registration_code_expires_at')) {
                    $table->dropColumn('registration_code_expires_at');
                }
                if (Schema::hasColumn('gurus', 'registration_code')) {
                    $table->dropColumn('registration_code');
                }
            });
        }
    }
};
