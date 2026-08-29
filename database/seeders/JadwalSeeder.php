<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use Illuminate\Database\Seeder;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        $jadwals = [
            [
                'nama_kegiatan' => 'Pengajian Subuh',
                'hari'          => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
                'jam_mulai'     => '04:30:00',
                'jam_selesai'   => '06:00:00',
                'status'        => 'aktif',
                'urutan'        => 1,
            ],
            [
                'nama_kegiatan' => 'Pengajian Dzuhur',
                'hari'          => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
                'jam_mulai'     => '12:00:00',
                'jam_selesai'   => '13:30:00',
                'status'        => 'aktif',
                'urutan'        => 2,
            ],
            [
                'nama_kegiatan' => 'Pengajian Ashar',
                'hari'          => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'],
                'jam_mulai'     => '15:00:00',
                'jam_selesai'   => '16:30:00',
                'status'        => 'aktif',
                'urutan'        => 3,
            ],
            [
                'nama_kegiatan' => 'Pengajian Isya',
                'hari'          => ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'],
                'jam_mulai'     => '19:00:00',
                'jam_selesai'   => '20:30:00',
                'status'        => 'aktif',
                'urutan'        => 4,
            ],
        ];

        foreach ($jadwals as $jadwal) {
            Jadwal::updateOrCreate(
                ['nama_kegiatan' => $jadwal['nama_kegiatan']],
                $jadwal
            );
        }
    }
}
