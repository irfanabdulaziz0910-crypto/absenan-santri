<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Santri extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'santris';

    protected $fillable = [
        'classroom_id',
        'rfid_barcode',
        'name',
        'nis',
        'jenis_kelamin',
        'barcode',
        'nama_santri',
        'kelas',
    ];

    protected $casts = [
        'jenis_kelamin' => 'string',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $santri): void {
            if (! $santri->exists && array_key_exists('barcode', $santri->getAttributes()) && ! $santri->getAttributes()['barcode']) {
                $santri->attributes['barcode'] = null;
            }

            if (! $santri->classroom_id) {
                $kelas = $santri->kelas ?? null;
                $santri->classroom_id = $kelas
                    ? Classroom::firstOrCreate(['name' => $kelas])->id
                    : Classroom::firstOrCreate(['name' => 'Umum'])->id;
            }

            if (! $santri->rfid_barcode) {
                $santri->rfid_barcode = $santri->barcode ?: 'AUTO-' . strtoupper(substr(uniqid('', true), -6));
            }

            if (! $santri->name) {
                $santri->name = $santri->nama_santri ?: 'Santri ' . $santri->rfid_barcode;
            }

            if (array_key_exists('barcode', $santri->getAttributes()) && ! array_key_exists('rfid_barcode', $santri->getAttributes())) {
                $santri->attributes['rfid_barcode'] = $santri->attributes['barcode'] ?? 'AUTO-' . strtoupper(substr(uniqid('', true), -6));
            }

            if (array_key_exists('nama_santri', $santri->getAttributes()) && ! array_key_exists('name', $santri->getAttributes())) {
                $santri->attributes['name'] = $santri->attributes['nama_santri'];
            }

            if (array_key_exists('kelas', $santri->getAttributes()) && ! array_key_exists('classroom_id', $santri->getAttributes())) {
                $santri->attributes['classroom_id'] = Classroom::firstOrCreate(['name' => $santri->attributes['kelas']])->id;
            }

            unset($santri->attributes['barcode']);
            unset($santri->attributes['nama_santri']);
            unset($santri->attributes['kelas']);
        });
    }

    public function getBarcodeAttribute(): ?string
    {
        return $this->attributes['rfid_barcode'] ?? null;
    }

    public function getNamaSantriAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function getKelasAttribute(): ?string
    {
        return $this->classroom?->name;
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function absensi()
    {
        return $this->hasMany(Attendance::class);
    }
}
