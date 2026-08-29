<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'santri_id',
        'session',
        'status',
        'scan_time',
        'notes',
        'date',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
        'date' => 'date',
    ];

    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
