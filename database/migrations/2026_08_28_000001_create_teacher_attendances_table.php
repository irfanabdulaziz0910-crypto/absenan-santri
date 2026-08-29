<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_attendances')) {
            return;
        }

        Schema::create('teacher_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->restrictOnDelete();
            $table->date('date');
            $table->time('attendance_time');
            $table->string('session')->nullable();
            $table->string('kitab', 255);
            $table->text('materi');
            $table->string('status', 20);
            $table->timestamps();

            $table->unique(
                ['guru_id', 'date', 'attendance_time', 'session', 'classroom_id'],
                'teacher_attendance_duplicate_guard'
            );
            $table->index(['date', 'classroom_id']);
            $table->index(['guru_id', 'status']);
        });
    }

    public function down(): void
    {
        // Intentionally left empty: existing data must never be removed by rollback.
    }
};
