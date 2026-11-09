<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Blueprint V2 — tugas harian.
Schedule::command('loket:arsipkan-tiket')->dailyAt('00:05');
Schedule::command('loket:pengingat-harian')->dailyAt('07:00');
Schedule::command('loket:bersihkan-notifikasi')->dailyAt('01:00');
Schedule::command('loket:bersihkan-log')->dailyAt('01:30');
