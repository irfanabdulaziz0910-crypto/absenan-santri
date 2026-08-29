<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Utama',
                'password' => 'admin123',
            ]
        );

        Admin::updateOrCreate(
            ['username' => 'walikelas'],
            [
                'name' => 'Ustadz Ahmad Fauzi',
                'password' => 'admin123',
            ]
        );
    }
}
