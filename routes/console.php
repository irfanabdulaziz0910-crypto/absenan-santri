<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pendaftaran Scheduler Auto-Alfa pada jam penutupan sesi
Schedule::command('attendance:auto-alfa')->at('08:01'); // Sesi Subuh
Schedule::command('attendance:auto-alfa')->at('15:00'); // Sesi Dzuhur
Schedule::command('attendance:auto-alfa')->at('18:00'); // Sesi Ashar
Schedule::command('attendance:auto-alfa')->at('00:00'); // Sesi Isya
