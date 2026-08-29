<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Santri;
use Illuminate\Database\Seeder;

class SantriSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::orderBy('name')->get();
        if ($classrooms->isEmpty()) {
            return;
        }

        $clsId = $classrooms->first()->id;

        $santris = [
            ['classroom_id' => $clsId, 'rfid_barcode' => 'SAN001', 'name' => 'Ahmad Fauzi'],
            ['classroom_id' => $clsId, 'rfid_barcode' => 'SAN002', 'name' => 'Budi Santoso'],
        ];

        foreach ($santris as $santri) {
            Santri::updateOrCreate(['rfid_barcode' => $santri['rfid_barcode']], $santri);
        }
    }
}
