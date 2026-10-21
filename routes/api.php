<?php

use Illuminate\Http\Request;
use \App\Http\Controllers\GanttController;
use \App\Http\Controllers\SiseraBeritaController;
use \App\Http\Controllers\PresensiApiController;
use \App\Http\Controllers\StarlaApiController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/data',[GanttController::class, 'get'])->name('data');
Route::get('/berita-sisera', [SiseraBeritaController::class, 'index']);
Route::get('/berita-sisera/{id}', [SiseraBeritaController::class, 'show']);

Route::get('/presensis-today', [PresensiApiController::class, 'today']);
Route::get('/presensis-full-today', [PresensiApiController::class, 'fullToday']);
Route::post('/presensi-create', [PresensiApiController::class, 'store']);
Route::post('/generate-qr', [PresensiApiController::class, 'generate']);
Route::get('/running-text', [PresensiApiController::class, 'index_running']);

/*
|--------------------------------------------------------------------------
| STARLA API Integration - Jadwal Petugas PST
|--------------------------------------------------------------------------
| Endpoint untuk pertukaran data jadwal Petugas PST dengan aplikasi STARLA:
| - GET  /api/starla/pst-jadwal (Tarik jadwal Petugas PST: Tanggal, Sesi, Anggota/NIP Lama)
| - POST /api/starla/pst-jadwal (Kirim/Sync jadwal Petugas PST dari STARLA ke SIKEREN)
*/
Route::prefix('starla')->group(function () {
    Route::get('/pst-jadwal', [StarlaApiController::class, 'getJadwalPst'])->name('api.starla.pst.index');
    Route::post('/pst-jadwal', [StarlaApiController::class, 'storeJadwalPst'])->name('api.starla.pst.store');
    Route::get('/petugas-hari-ini', [StarlaApiController::class, 'getPetugasHariIni'])->name('api.starla.petugas.hari_ini');
    Route::get('/ketersediaan', [StarlaApiController::class, 'getKetersediaanLive'])->name('api.starla.ketersediaan');
});

Route::resource('task', 'TaskController');
Route::resource('link', 'LinkController');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
