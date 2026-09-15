<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TeacherInvitation extends Model
{
    use HasFactory;

    protected $table = 'teacher_invitations';

    protected $fillable = [
        'token',
        'guru_id',
        'name',
        'role',
        'expires_at',
        'used_at',
        'used_by_user_id',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function usedByUser()
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isUsed()) {
            return 'Digunakan';
        }

        if ($this->isExpired()) {
            return 'Kedaluwarsa';
        }

        return 'Menunggu';
    }

    public function getInviteUrlAttribute(): string
    {
        return url('/invite/guru/' . $this->token);
    }
}
