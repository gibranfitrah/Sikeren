<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LihatKegiatanController;
use App\Http\Controllers\BMNController;
use App\Http\Controllers\RapatController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DetailKegiatanController;
use App\Http\Controllers\KetuaTimController;
use App\Http\Controllers\SubKegiatanController;
use App\Http\Controllers\TugasSayaController;
use App\Http\Controllers\TimeScheduleController;
use App\Http\Controllers\BookingRuanganController;
use App\kegiatan;
use App\penugasan;
use App\User;
use App\Task;
use App\SubKegiatan;
use App\group;
use App\master_group;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('gantt', function () {

    $id = task::latest()->first()->id;
    $peserta = User::all();
    $groups = group::join('users', 'users.niplama', 'groups.niplama')->get();
    $master_groups = master_group::all();
    

    return view('gantt',compact('id','peserta','groups','master_groups'));
});


Route::get('/absen-kantor', [PresensiController::class, 'showForm'])->name('absen.form');
Route::post('/absen-kantor/submit', [PresensiController::class, 'submitPresensi'])->name('absen.submit');

Route::get('/check-new-qr', [PresensiController::class, 'checkForNewQRCode'])->name('qr.check');

Route::post('/update-jam/{id}', [PresensiController::class, 'updateJam'])->name('update.jam');

Route::get('rekap', [PresensiController::class, 'index_rekap'])->name('rekap');

Route::get('qr', [PresensiController::class, 'index'])->name('qr');
Route::get('report', [PresensiController::class, 'index_report'])->name('report');
Route::post('/scan/submit', [PresensiController::class, 'submitScan'])->name('submit.scan');
Route::post('/presensi/import', [PresensiController::class, 'import'])->name('presensi.import');

Route::post('/store_surat', [TaskController::class, 'store_surat']);

Route::post('/setuju_rapat', [LihatKegiatanController::class, 'setuju_rapat']);

Route::get('/', [LoginController::class, 'login'])->name('login');
Route::post('/actionlogin', [LoginController::class, 'actionlogin'])->name('/actionlogin');

Route::get('/color6/{id}/edit6/',  [BMNController::class, 'update6'])->name('color.update6');
Route::post('/color6/{id}/',  [BMNController::class, 'edit6'])->name('color.edit6');


Route::get('daftarhadir/{id}', [KegiatanController::class, 'daftarHadir'])->where('id','(.*)');
Route::post('daftarhadir/submit', [KegiatanController::class, 'submitDaftarHadir'])->name('daftarhadir.submit');
Route::get('/api/presensi-rapat/{id}', [KegiatanController::class, 'apiStatusPresensi'])->name('api.presensi.status');

Route::group(['middleware' => 'auth'], function () {
    
Route::get('dashboard', [DashboardController::class, 'index']);

Route::get('/notification/{id}', [NotificationController::class, 'read'])
    ->name('notification.read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
    ->name('notification.markAllRead');

Route::get('kegiatan', [KegiatanController::class, 'index'])->name('kegiatan');
Route::get('/penugasan', [KegiatanController::class, 'index_penugasan'])->name('penugasan');
Route::get('autocomplete', [KegiatanController::class, 'search'])->name('autocomplete');
Route::get('actionlogout', [LoginController::class, 'actionlogout'])->name('actionlogout');


Route::get('password', [PresensiController::class, 'showPasswordForm'])->name('password');
Route::post('/user/password', [PresensiController::class, 'updatePassword'])->name('password.update');



Route::delete('postssss/{post}',  [BMNController::class, 'destroy5'])->name('postssss.destroy5');

Route::post('post.store', [KegiatanController::class, 'store'])->name('post.store');
Route::post('/store_penugasan', [KegiatanController::class, 'store_penugasan'])->name('post.store_penugasan');
Route::post('post.tambah_penugasan', [LihatKegiatanController::class, 'tambah_penugasan'])->name('post.tambah_penugasan');

Route::post('post.store_perbaikan', [BMNController::class, 'store_perbaikan'])->name('post.store_perbaikan');

Route::get('/daftar_kegiatan', [KegiatanController::class, 'index2'])->name('kegiatan.daftar');
Route::get('/kelola-kegiatan', [KegiatanController::class, 'index2'])->name('kegiatan.kelola');
Route::get('/notulis', [KegiatanController::class, 'index3']);

// Sub Kegiatan Routes
Route::get('/sub-kegiatan', [SubKegiatanController::class, 'index'])->name('sub-kegiatan.index');
Route::post('/sub-kegiatan', [SubKegiatanController::class, 'store'])->name('sub-kegiatan.store');
Route::put('/sub-kegiatan/{id}', [SubKegiatanController::class, 'update'])->name('sub-kegiatan.update');
Route::delete('/sub-kegiatan/{id}', [SubKegiatanController::class, 'destroy'])->name('sub-kegiatan.destroy');
Route::post('/sub-kegiatan/{id}/progress', [SubKegiatanController::class, 'updateProgress'])->name('sub-kegiatan.progress');
Route::get('/api/sub-kegiatan/task/{id}', [SubKegiatanController::class, 'getByTask'])->name('sub-kegiatan.byTask');

// Kegiatan Saya Routes
Route::get('/kegiatan-saya', [TugasSayaController::class, 'index'])->name('kegiatan-saya.index');
Route::get('/tugas-saya', [TugasSayaController::class, 'index'])->name('tugas-saya.index');

// Time Schedule / Kalender Anggota Routes
Route::get('/time-schedule', [TimeScheduleController::class, 'index'])->name('time-schedule.index');
Route::get('/api/time-schedule/events', [TimeScheduleController::class, 'events'])->name('time-schedule.events');

// Booking & Jadwal Ruangan Rapat
Route::get('/booking-ruangan', [BookingRuanganController::class, 'index'])->name('booking-ruangan.index');
Route::post('/booking-ruangan', [BookingRuanganController::class, 'store'])->name('booking-ruangan.store');
Route::delete('/booking-ruangan/{id}', [BookingRuanganController::class, 'destroy'])->name('booking-ruangan.destroy');
Route::get('/api/booking-ruangan/schedule', [BookingRuanganController::class, 'apiSchedule'])->name('booking-ruangan.apiSchedule');

Route::get('lihatkegiatan/{id}', [KegiatanController::class, 'displayKegiatan'])->where('id','(.*)');
Route::post('/notulen', [KegiatanController::class, 'upload_notulen'])->name('notulen.upload_notulen');
Route::post('/materi', [KegiatanController::class, 'upload_materi'])->name('materi.upload_materi');

Route::post('/update_notulen', [LihatKegiatanController::class, 'updateNotulen'])
    ->name('update_notulen');

Route::get(
    '/detail_kegiatan/{id}',
    'DetailKegiatanController@show'
)->name('detail.kegiatan');

Route::delete('/postss/{id}',  [LihatKegiatanController::class, 'destroy'])->name('postss.destroy')->where('id', '(.*)');
Route::get('daftarkegiatan/{id}', [LihatKegiatanController::class, 'lihatKegiatan'])->where('id','(.*)');


Route::post('/store_notulen', [KegiatanController::class, 'store_notulen']);

Route::get('/bmn', [BMNController::class, 'index']);
Route::get('/pemeliharaan_bmn', [BMNController::class, 'index_pemeliharaan']);

Route::get('/employee/pdf_kegiatan/{id}', [KegiatanController::class, 'createPDF']);
Route::get('/kegiatan/{id}/download-word', [KegiatanController::class, 'downloadWord'])->name('kegiatan.downloadWord');
Route::get('/rapat/{id}/download-word', [KegiatanController::class, 'downloadWord'])->name('rapat.downloadWord');
Route::get('/agenda/download-word', [KegiatanController::class, 'downloadAgendaWord'])->name('agenda.downloadWord');


Route::get('fullcalender', [LihatKegiatanController::class, 'index']);
Route::post('fullcalenderAjax', [LihatKegiatanController::class, 'ajax']);


Route::get('getPegawai',[KegiatanController::class, 'getPegawai'])->name('getPegawai');
Route::get('qrcode/{id}', [KegiatanController::class, 'generate'])->name('generate');

Route::get('rapat', [RapatController::class, 'index'])->name('rapat');
Route::get('getPegawai',[RapatController::class, 'getPegawai'])->name('getPegawai');
Route::post('rapat.store', [RapatController::class, 'store_rapat'])->name('rapat.store');

Route::get('/surat-masuk', [\App\Http\Controllers\SuratMasukController::class, 'index'])->name('surat-masuk.index');
Route::get('/surat-masuk/create', [\App\Http\Controllers\SuratMasukController::class, 'create'])->name('surat-masuk.create');
Route::post('/surat-masuk', [\App\Http\Controllers\SuratMasukController::class, 'store'])->name('surat-masuk.store');

Route::middleware(['auth'])->group(function () {
    Route::get('/ketua-tim', [KetuaTimController::class, 'index'])->name('ketua-tim.index');
    Route::get('/ketua-tim/create', [KetuaTimController::class, 'create'])->name('ketua-tim.create');
    Route::post('/ketua-tim/store', [KetuaTimController::class, 'store'])->name('ketua-tim.store');
    Route::get('/ketua-tim/{id}', [KetuaTimController::class, 'show'])->name('ketua-tim.show');
    Route::delete('/ketua-tim/{id}', [KetuaTimController::class, 'destroy'])->name('ketua-tim.destroy');
    Route::get('/ketua-tim/{id}/edit', [KetuaTimController::class, 'edit'])->name('ketua-tim.edit');
    Route::put('/ketua-tim/{id}', [KetuaTimController::class, 'update'])->name('ketua-tim.update');
});

Route::get('/disposisi/{id}/show', [\App\Http\Controllers\DisposisiController::class, 'show'])->name('disposisi.show');
Route::get('/disposisi/create/{surat_masuk}', [\App\Http\Controllers\DisposisiController::class, 'create'])->name('disposisi.create');
Route::post('/disposisi', [\App\Http\Controllers\DisposisiController::class, 'store'])->name('disposisi.store');

Route::post('/kegiatan/{id}/approve', [\App\Http\Controllers\KegiatanController::class, 'approve'])->name('kegiatan.approve');
Route::post('/kegiatan/{id}/reject', [\App\Http\Controllers\KegiatanController::class, 'reject'])->name('kegiatan.reject');
Route::post('/kegiatan/{id}/status', [\App\Http\Controllers\KegiatanController::class, 'updateStatus'])->name('kegiatan.updateStatus');

});