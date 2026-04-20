<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Batalkan otomatis reservasi yang tanggalnya sudah lewat, setiap hari tengah malam
Schedule::command('reservasi:auto-batal')->everyMinute();
