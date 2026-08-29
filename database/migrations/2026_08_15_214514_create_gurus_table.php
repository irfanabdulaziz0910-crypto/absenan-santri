<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->string('kelas')->nullable();
            $table->string('spesialisasi')->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'cuti'])->default('aktif');
            $table->string('keterangan_status')->nullable(); // e.g. "Cuti Umroh"
            $table->date('bergabung_at')->nullable();
            $table->integer('total_santri')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gurus');
    }
};
