<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('santris', 'nis')) {
            Schema::table('santris', function (Blueprint $table): void {
                $table->string('nis', 50)->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('santris', 'nis')) {
            Schema::table('santris', function (Blueprint $table): void {
                $table->dropColumn('nis');
            });
        }
    }
};
