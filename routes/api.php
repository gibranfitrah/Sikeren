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

// Mobile App REST API Routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);

    // Kegiatan & Daily Summary
    Route::get('/kegiatan', [\App\Http\Controllers\Api\KegiatanApiController::class, 'index']);
    Route::get('/kegiatan/{id}', [\App\Http\Controllers\Api\KegiatanApiController::class, 'show']);
    Route::post('/kegiatan/{id}/presensi', [\App\Http\Controllers\Api\PresensiMobileController::class, 'presensiKegiatan']);
    Route::get('/daily-summary', [\App\Http\Controllers\Api\KegiatanApiController::class, 'dailySummary']);

    // Presensi & QR Absensi
    Route::get('/presensi/today-status', [\App\Http\Controllers\Api\PresensiMobileController::class, 'todayStatus']);
    Route::post('/presensi/scan', [\App\Http\Controllers\Api\PresensiMobileController::class, 'submitScan']);
    Route::get('/presensi/active-qr', [\App\Http\Controllers\Api\PresensiMobileController::class, 'getActiveQr']);
    Route::get('/presensi/history', [\App\Http\Controllers\Api\PresensiMobileController::class, 'history']);

    // Notifications & Ruangan
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationApiController::class, 'index']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAllAsRead']);
    Route::post('/ruangan/lepas', [\App\Http\Controllers\Api\NotificationApiController::class, 'lepasRuangan']);

    // PST Duty Roster & Substitutions
    Route::get('/pst/jadwal', [\App\Http\Controllers\Api\PstApiController::class, 'getJadwal']);
    Route::post('/pst/berhalangan', [\App\Http\Controllers\Api\PstApiController::class, 'laporkanBerhalangan']);
    Route::post('/pst/gantikan', [\App\Http\Controllers\Api\PstApiController::class, 'ambilAlihShift']);
    Route::post('/pst/assign-pengganti', [\App\Http\Controllers\Api\PstApiController::class, 'assignPengganti']);
    Route::get('/pst/eligible-officers', [\App\Http\Controllers\Api\PstApiController::class, 'getEligibleOfficers']);
});
