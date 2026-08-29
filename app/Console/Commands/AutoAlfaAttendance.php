<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Santri;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoAlfaAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-alfa {--session= : Sesi absensi opsional (Subuh/Dzuhur/Ashar/Isya)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mengisi status Alfa untuk santri yang belum melakukan absensi setelah sesi ditutup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sessionParam = $this->option('session');

        if ($sessionParam) {
            $targetSession = ucfirst(strtolower($sessionParam));
        } else {
            // Logika mapping sesi berdasarkan jam penutupan
            $now  = Carbon::now();
            $hour = $now->hour;

            if ($hour >= 8 && $hour < 11) {
                $targetSession = 'Subuh';
            } elseif ($hour >= 15 && $hour < 18) {
                $targetSession = 'Dzuhur';
            } elseif ($hour >= 18 && $hour < 23) {
                $targetSession = 'Ashar';
            } else {
                $targetSession = 'Isya';
            }
        }

        $today = Carbon::now()->toDateString();
        $this->info("Menjalankan Auto-Alfa untuk Sesi {$targetSession} pada tanggal {$today}...");

        // 0. Cek apakah Tanggal hari ini atau Sesi Target berstatus Libur / Nonaktif
        $liburToday = \App\Models\HariLibur::whereDate('tanggal', $today)->first();
        $isLibur = false;

        if ($liburToday) {
            if (app(\App\Services\AttendanceService::class)->isHolidayForSession($liburToday->keterangan, $targetSession)) {
                $isLibur = true;
            }
        }

        $jadwalTarget = \App\Models\Jadwal::whereRaw('LOWER(nama_kegiatan) = ?', [strtolower($targetSession)])->first();
        if ($jadwalTarget && $jadwalTarget->status === 'nonaktif') {
            $isLibur = true;
        }

        if ($isLibur) {
            $this->info("Sesi {$targetSession} pada tanggal {$today} berstatus LIBUR / Nonaktif. Auto-Alfa dibatalkan.");
            return Command::SUCCESS;
        }

        // 1. Ambil seluruh ID Santri aktif
        $allSantriIds = Santri::pluck('id');

        // 2. Ambil ID Santri yang sudah memiliki absensi pada hari ini & sesi target
        $absenSantriIds = Attendance::whereDate('date', $today)
            ->whereRaw('LOWER(session) = ?', [strtolower($targetSession)])
            ->pluck('santri_id')
            ->toArray();

        // 3. Filter santri yang BELUM memiliki absensi
        $missingSantriIds = $allSantriIds->reject(fn ($id) => in_array($id, $absenSantriIds));

        if ($missingSantriIds->isEmpty()) {
            $this->info("Seluruh santri sudah memiliki catatan absensi untuk sesi {$targetSession}.");
            return Command::SUCCESS;
        }

        // 4. Bulk insert status Alfa
        $nowTimestamp = now();
        $bulkInsertData = [];

        foreach ($missingSantriIds as $santriId) {
            $bulkInsertData[] = [
                'santri_id'  => $santriId,
                'date'       => $today,
                'session'    => $targetSession,
                'status'     => 'Alfa',
                'notes'      => 'Auto-Alfa (Sistem Cron Job)',
                'scan_time'  => null,
                'created_at' => $nowTimestamp,
                'updated_at' => $nowTimestamp,
            ];
        }

        Attendance::insert($bulkInsertData);

        $insertedCount = count($bulkInsertData);
        $this->info("Berhasil melakukan Bulk Insert {$insertedCount} record 'Alfa' untuk Sesi {$targetSession}.");

        return Command::SUCCESS;
    }
}
