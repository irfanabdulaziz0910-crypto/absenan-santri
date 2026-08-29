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
        if (!Schema::hasColumn('gurus', 'nip')) {
            Schema::table('gurus', function (Blueprint $table) {
                $table->string('nip', 50)->nullable()->after('name');
            });
        }

        if (!Schema::hasTable('teacher_schedules')) {
            Schema::create('teacher_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
                $table->string('hari', 20);
                $table->string('session', 30);
                $table->string('jam_mulai', 10)->nullable();
                $table->string('jam_selesai', 10)->nullable();
                $table->string('kitab', 255)->nullable();
                $table->string('status', 20)->default('aktif');
                $table->timestamps();

                $table->index(['guru_id', 'hari', 'session']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_schedules');

        if (Schema::hasColumn('gurus', 'nip')) {
            Schema::table('gurus', function (Blueprint $table) {
                $table->dropColumn('nip');
            });
        }
    }
};
