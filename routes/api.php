<?php

use Illuminate\Http\Request;
use \App\Http\Controllers\GanttController;
use \App\Http\Controllers\SiseraBeritaController;
use \App\Http\Controllers\PresensiApiController;
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


Route::resource('task', 'TaskController');
Route::resource('link', 'LinkController');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
