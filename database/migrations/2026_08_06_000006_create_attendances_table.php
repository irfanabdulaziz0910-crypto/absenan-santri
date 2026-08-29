<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->enum('session', ['Subuh', 'Dzuhur', 'Asar', 'Isya']);
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alfa', 'Belum Absen']);
            $table->dateTime('scan_time')->nullable();
            $table->string('notes')->nullable();
            $table->date('date');
            $table->timestamps();
            $table->unique(['santri_id', 'session', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
