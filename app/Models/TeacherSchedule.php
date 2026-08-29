<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSchedule extends Model
{
    use HasFactory;

    protected $table = 'teacher_schedules';

    protected $fillable = [
        'guru_id',
        'classroom_id',
        'hari',
        'session',
        'jam_mulai',
        'jam_selesai',
        'kitab',
        'status',
    ];

    /**
     * Relasi ke Guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * Relasi ke Classroom
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Urutan hari untuk sorting
     */
    public static function hariOrder(): array
    {
        return ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
    }

    /**
     * Daftar sesi dan jam terkait (sesuai sistem existing)
     */
    public static function sessionMap(): array
    {
        return [
            'Subuh'  => ['jam_mulai' => '04:00', 'jam_selesai' => '08:00'],
            'Dzuhur' => ['jam_mulai' => '11:30', 'jam_selesai' => '14:59'],
            'Ashar'  => ['jam_mulai' => '15:00', 'jam_selesai' => '17:59'],
            'Isya'   => ['jam_mulai' => '18:00', 'jam_selesai' => '23:59'],
        ];
    }

    /**
     * Deteksi sesi aktif berdasarkan jam saat ini
     */
    public static function currentSession(): ?string
    {
        $hour   = (int) now()->format('H');
        $minute = (int) now()->format('i');
        $time   = $hour * 60 + $minute;

        if ($time >= 4 * 60 && $time <= 8 * 60)   return 'Subuh';
        if ($time >= 11 * 60 + 30 && $time <= 14 * 60 + 59) return 'Dzuhur';
        if ($time >= 15 * 60 && $time <= 17 * 60 + 59) return 'Ashar';
        if ($time >= 18 * 60 && $time <= 23 * 60 + 59) return 'Isya';

        return null;
    }

    /**
     * Mendapatkan nama hari Indonesia saat ini
     */
    public static function currentHari(): string
    {
        $days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        return $days[now()->format('l')];
    }
}
