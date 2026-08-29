<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table): void {
            if (! Schema::hasColumn('santris', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P'])->default('L')->after('name');
            }

            if (! Schema::hasColumn('santris', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table): void {
            if (Schema::hasColumn('santris', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            if (Schema::hasColumn('santris', 'jenis_kelamin')) {
                $table->dropColumn('jenis_kelamin');
            }
        });
    }
};
