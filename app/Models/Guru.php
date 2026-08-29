<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'name',
        'nip',
        'nomor_hp',
        'classroom_id',
        'user_id',
        'kelas',
        'spesialisasi',
        'avatar',
        'status',
        'keterangan_status',
        'bergabung_at',
        'total_santri',
    ];

    protected $casts = [
        'bergabung_at' => 'date',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault(function () {
            return User::where('guru_id', $this->id)->first();
        });
    }

    public function getTotalSantriCountAttribute(): int
    {
        if ($this->classroom_id) {
            return Santri::where('classroom_id', $this->classroom_id)->count();
        }
        if ($this->kelas) {
            $cls = Classroom::where('name', $this->kelas)->first();
            return $cls ? Santri::where('classroom_id', $cls->id)->count() : 0;
        }
        return 0;
    }

    public function teacherAttendances()
    {
        return $this->hasMany(TeacherAttendance::class, 'guru_id');
    }

    public function teacherSchedules()
    {
        return $this->hasMany(\App\Models\TeacherSchedule::class, 'guru_id');
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'guru_classrooms', 'guru_id', 'classroom_id');
    }

    public function getAllowedClassrooms()
    {
        $assigned = $this->classrooms()->orderBy('name')->get();
        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        if ($this->classroom_id) {
            return Classroom::where('id', $this->classroom_id)->get();
        }

        return Classroom::orderBy('name')->get();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif'    => 'Aktif',
            'nonaktif' => 'Nonaktif',
            'cuti'     => $this->keterangan_status ?: 'Cuti',
            default    => 'Aktif',
        };
    }
}

