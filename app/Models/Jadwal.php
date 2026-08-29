<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwals';

    protected $fillable = [
        'nama_kegiatan',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'status',
        'urutan',
    ];

    protected $casts = [
        'hari' => 'array',
    ];
}
