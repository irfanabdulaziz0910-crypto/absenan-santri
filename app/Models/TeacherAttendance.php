<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    protected $table = 'teacher_attendances';

    protected $fillable = [
        'guru_id',
        'classroom_id',
        'date',
        'attendance_time',
        'session',
        'kitab',
        'materi',
        'status',
        'status_mengajar',
        'replaced_guru_id',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function replacedGuru()
    {
        return $this->belongsTo(Guru::class, 'replaced_guru_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
