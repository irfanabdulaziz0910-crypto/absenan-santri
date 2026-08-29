<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('santri') || ! Schema::hasTable('santris')) {
            return;
        }

        $legacySantris = DB::table('santri')->get();
        if ($legacySantris->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($legacySantris) {
            foreach ($legacySantris as $legacy) {
                $kelas = trim((string) ($legacy->kelas ?? '')) ?: 'Umum';
                $classroomId = DB::table('classrooms')->where('name', $kelas)->value('id');

                if (! $classroomId) {
                    $classroomId = DB::table('classrooms')->insertGetId([
                        'name' => $kelas,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $rfidBarcode = trim((string) ($legacy->barcode ?? '')) ?: 'AUTO-' . strtoupper(substr(uniqid('', true), -6));

                if (DB::table('santris')->where('rfid_barcode', $rfidBarcode)->exists()) {
                    continue;
                }

                DB::table('santris')->insert([
                    'classroom_id' => $classroomId,
                    'rfid_barcode' => $rfidBarcode,
                    'name' => trim((string) ($legacy->nama_santri ?? 'Santri ' . $rfidBarcode)) ?: 'Santri ' . $rfidBarcode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('santri') || ! Schema::hasTable('santris')) {
            return;
        }

        $legacyBarcodes = DB::table('santri')->pluck('barcode')->filter()->unique()->values()->all();
        if (empty($legacyBarcodes)) {
            return;
        }

        DB::table('santris')->whereIn('rfid_barcode', $legacyBarcodes)->delete();
    }
};
