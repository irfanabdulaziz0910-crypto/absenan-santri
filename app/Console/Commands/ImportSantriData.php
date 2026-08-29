<?php

namespace App\Console\Commands;

use App\Models\Classroom;
use App\Models\Santri;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportSantriData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:santri-sql {file? : The path to the SQL file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import santri data from an SQL file containing INSERT INTO santris (rfid_barcode, name, kelas) VALUES ...';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!$filePath) {
            $filePath = $this->ask('Silakan masukkan path lengkap ke file SQL Anda (contoh: C:\data\santri.sql)');
        }

        if (!File::exists($filePath)) {
            $this->error("File tidak ditemukan pada path: {$filePath}");
            return;
        }

        $this->info("Membaca file: {$filePath}...");
        $content = File::get($filePath);

        // Cari semua data di dalam VALUES (...) menggunakan regex
        // Regex yang lebih robust untuk menangkap: ('barcode', 'name', 'kelas') dengan handle quote di dalam string (seperti Ma''had)
        preg_match_all('/\(\s*\'((?:[^\']|\'\')*)\'\s*,\s*\'((?:[^\']|\'\')*)\'\s*,\s*\'((?:[^\']|\'\')*)\'\s*\)/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            $this->error("Tidak dapat menemukan data dengan format ('rfid_barcode', 'name', 'kelas') di dalam file.");
            return;
        }

        $this->info(count($matches) . " baris data ditemukan. Memulai proses import...");

        DB::beginTransaction();
        try {
            $imported = 0;
            $skipped = 0;

            foreach ($matches as $match) {
                // Unescape quote mark pada values (seperti Ma''had menjadi Ma'had)
                $rfid_barcode = str_replace("''", "'", $match[1]);
                $name = str_replace("''", "'", $match[2]);
                $kelas = str_replace("''", "'", $match[3]);

                // Pastikan rfid tidak kosong
                if (empty($rfid_barcode) || empty($name)) {
                    continue;
                }

                // Cek apakah santri dengan barcode ini sudah ada
                if (Santri::where('rfid_barcode', $rfid_barcode)->exists()) {
                    $skipped++;
                    continue;
                }

                // Cari atau buat classroom
                $classroom = Classroom::firstOrCreate(['name' => $kelas ?: 'Umum']);

                // Insert santri
                Santri::create([
                    'classroom_id' => $classroom->id,
                    'rfid_barcode' => $rfid_barcode,
                    'name'         => $name,
                ]);

                $imported++;
            }

            DB::commit();
            $this->info("Import berhasil! Data yang masuk: {$imported} santri. Data yang dilewati (sudah ada): {$skipped} santri.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan saat import data: " . $e->getMessage());
        }
    }
}
