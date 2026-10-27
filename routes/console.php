<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('sikeren:cek-pakaian-dinas', function () {
    $this->info('Memeriksa ketentuan pakaian dinas dan pengingat KORPRI tanggal 17...');
    $count = \App\Services\PakaianDinasService::broadcastPakaianKorpri();
    $this->info("Berhasil mengirim notifikasi ketentuan pakaian dinas ke {$count} pegawai.");
})->describe('Periksa dan kirim notifikasi ketentuan pakaian dinas pegawai (misal Seragam Korpri tanggal 17)');
