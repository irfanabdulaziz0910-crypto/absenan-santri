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
        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->enum('status_mengajar', ['normal', 'badal'])->default('normal')->after('status');
            $table->unsignedBigInteger('replaced_guru_id')->nullable()->after('status_mengajar');
            
            $table->foreign('replaced_guru_id')->references('id')->on('gurus')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->dropForeign(['replaced_guru_id']);
            $table->dropColumn(['status_mengajar', 'replaced_guru_id']);
        });
    }
};
