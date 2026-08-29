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
        if (!Schema::hasTable('guru_classrooms')) {
            Schema::create('guru_classrooms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('guru_id');
                $table->unsignedBigInteger('classroom_id');
                $table->timestamps();

                $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
                $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
                $table->unique(['guru_id', 'classroom_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe additive migration — down keeps existing tables
    }
};
