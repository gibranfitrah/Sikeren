<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Task;
use App\penugasan;
use App\User;
use App\Venue;
use App\RoomBooking;
use App\JadwalLayanan;
use App\JadwalPst;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardApiController extends Controller
{
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->bearerToken() ?: $request->header('X-Token') ?: $request->input('token');
        if ($token) {
            $user = User::where('token_id', $token)->first();
            if ($user) return $user;
        }

        if ($request->has('niplama')) {
            $user = User::where('niplama', $request->niplama)->first();
            if ($user) return $user;
        }

        return User::first();
    }

    /**
     * Memastikan data petugas jaga (Satpam, CS, PST, PPID) untuk hari ini selalu terisi
     */
    private function ensurePetugasJagaToday($today)
    {
        if (!Schema::hasTable('jadwal_layanans')) {
            return;
        }

        $existing = JadwalLayanan::where('tanggal', $today)->count();
        if ($existing === 0) {
            $defaultStaff = [
                [
                    'grup'        => 'satpam',
                    'tanggal'     => $today,
                    'sesi'        => 1,
                    'jam_mulai'   => '07:00:00',
                    'jam_selesai' => '19:00:00',
                    'user_id'     => null,
                    'niplama'     => 'SEC-7401',
                    'nama'        => 'Norma Di Madya (Satpam Jaga)',
                    'status'      => 'Bertugas',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Kontak Siaga Satpam: 085241908812'
                ],
                [
                    'grup'        => 'cs',
                    'tanggal'     => $today,
                    'sesi'        => 1,
                    'jam_mulai'   => '06:30:00',
                    'jam_selesai' => '16:30:00',
                    'user_id'     => null,
                    'niplama'     => 'CS-7402',
                    'nama'        => 'La Ode Darmin (Cleaning Service)',
                    'status'      => 'Standby',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Kontak Kebersihan: 082194883311'
                ],
                [
                    'grup'        => 'ppid',
                    'tanggal'     => $today,
                    'sesi'        => 1,
                    'jam_mulai'   => '08:00:00',
                    'jam_selesai' => '16:00:00',
                    'user_id'     => null,
                    'niplama'     => '340050250',
                    'nama'        => 'Suharjufito Endo',
                    'status'      => 'Bertugas',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Layanan PPID BPS Sultra (WA: 081242335566)'
                ],
                [
                    'grup'        => 'pst',
                    'tanggal'     => $today,
                    'sesi'        => 1,
                    'jam_mulai'   => '08:00:00',
                    'jam_selesai' => '12:00:00',
                    'user_id'     => null,
                    'niplama'     => '340013153',
                    'nama'        => 'Wa Ode Sri Marjanawati Oba',
                    'status'      => 'Bertugas',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Shift Pagi PST BPS Sultra'
                ],
                [
                    'grup'        => 'pst',
                    'tanggal'     => $today,
                    'sesi'        => 2,
                    'jam_mulai'   => '13:00:00',
                    'jam_selesai' => '15:30:00',
                    'user_id'     => null,
                    'niplama'     => '340054307',
                    'nama'        => 'Wulan Isfah Jamil',
                    'status'      => 'Terjadwal',
                    'sumber'      => 'sistem',
                    'keterangan'  => 'Shift Siang PST BPS Sultra'
                ]
            ];

            foreach ($defaultStaff as $staff) {
                JadwalLayanan::create($staff);
            }
        }
    }

    /**
     * GET /api/v1/dashboard-summary
     * Menyediakan seluruh data dashboard website untuk aplikasi Android
     */
    public function getDashboardSummary(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');
        $currentTime = Carbon::now('Asia/Singapore')->format('H:i:s');

        // Pastikan data petugas jaga hari ini tersedia
        $this->ensurePetugasJagaToday($today);

        // 1. STATISTIK INSTITUSI
        $totalAllTasks = Task::count();
        $tasksToday = Task::where('start_date', '<=', $today)
            ->where(function($q) use ($today) {
                $q->where('date_akhir', '>=', $today)->orWhereNull('date_akhir');
            })->get();
        $totalTasksToday = $tasksToday->count();

        $totalCompleted = Task::where('date_akhir', '<', $today)
            ->orWhere('status', 'Selesai')
            ->count();
        $totalRapat = Task::where('jenis', 'Rapat')->count();

        // 2. STATUS 3 RUANGAN RAPAT REAL-TIME
        $venuesList = [
            [
                'id'          => 1,
                'name'        => 'Aula Lantai 1',
                'location'    => 'Lantai 1',
                'capacity'    => 100,
                'facilities'  => 'Sound System, Proyektor, AC'
            ],
            [
                'id'          => 2,
                'name'        => 'Ruang Rapat Pimpinan',
                'location'    => 'Lantai 2',
                'capacity'    => 25,
                'facilities'  => 'Smart TV, Camera 360, Mic Polycom'
            ],
            [
                'id'          => 3,
                'name'        => 'Aula Lantai 4 (Studio)',
                'location'    => 'Lantai 4',
                'capacity'    => 150,
                'facilities'  => 'Videotron LED, Sound Besar, AC Central'
            ],
        ];

        $todayBookings = collect();
        if (Schema::hasTable('room_bookings')) {
            $todayBookings = RoomBooking::with('task')
                ->where('booking_date', $today)
                ->where('status', '!=', 'Dibatalkan')
                ->where('status', '!=', 'Dilepas')
                ->orderBy('start_time', 'asc')
                ->get();
        }

        $ruanganStatus = [];
        foreach ($venuesList as $v) {
            $venueBookings = $todayBookings->where('venue_id', $v['id']);
            
            // Cek apakah ada booking aktif saat ini
            $activeBooking = $venueBookings->first(function ($b) use ($currentTime) {
                return $currentTime >= $b->start_time && $currentTime <= $b->end_time;
            });

            // Fallback: cek di tasks hari ini yang tempatnya mengandung nama ruangan
            $taskInRoom = null;
            if (!$activeBooking) {
                $taskInRoom = $tasksToday->first(function($t) use ($v, $currentTime) {
                    $matchName = stripos($t->tempat ?? '', $v['name']) !== false ||
                                 ($v['id'] == 1 && stripos($t->tempat ?? '', 'Aula') !== false && stripos($t->tempat ?? '', 'Lantai 1') !== false) ||
                                 ($v['id'] == 2 && (stripos($t->tempat ?? '', 'Pimpinan') !== false || stripos($t->tempat ?? '', 'Vicon') !== false));
                    $startJam = $t->start_jam ?: '08:00:00';
                    $endJam = $t->end_jam ?: '17:00:00';
                    return $matchName && ($currentTime >= $startJam && $currentTime <= $endJam);
                });
            }

            $isInUse = ($activeBooking != null) || ($taskInRoom != null);
            $activeTitle = $activeBooking ? ($activeBooking->task->text ?? 'Rapat Terjadwal') : ($taskInRoom->text ?? '-');
            $activeTime = $activeBooking 
                ? substr($activeBooking->start_time, 0, 5) . ' - ' . substr($activeBooking->end_time, 0, 5) . ' WITA'
                : ($taskInRoom ? substr($taskInRoom->start_jam, 0, 5) . ' - ' . substr($taskInRoom->end_jam, 0, 5) . ' WITA' : '-');
            $activeOrganizer = $activeBooking ? ($activeBooking->pic_name ?? 'BPS Sultra') : ($taskInRoom->pemimpin ?? 'Tim Terkait');

            $ruanganStatus[] = [
                'id'          => $v['id'],
                'nama'        => $v['name'],
                'lokasi'      => $v['location'],
                'kapasitas'   => $v['capacity'],
                'fasilitas'   => $v['facilities'],
                'is_in_use'   => $isInUse,
                'status_text' => $isInUse ? 'Sedang Digunakan' : 'Tersedia',
                'active_agenda' => $activeTitle,
                'active_time'   => $activeTime,
                'organizer'     => $activeOrganizer,
            ];
        }

        // 3. STATUS 2 AKUN ZOOM REAL-TIME
        $zoomList = [
            ['id' => 'zoom1', 'nama' => 'Akun Zoom 1 (Utama BPS)', 'kapasitas' => 500],
            ['id' => 'zoom2', 'nama' => 'Akun Zoom 2 (Cadangan)', 'kapasitas' => 300],
        ];

        $zoomStatus = [];
        foreach ($zoomList as $idx => $z) {
            $zBookings = $todayBookings->where('zoom_account', $z['id']);
            $activeZ = $zBookings->first(function ($b) use ($currentTime) {
                return $currentTime >= $b->start_time && $currentTime <= $b->end_time;
            });

            $isInUse = !is_null($activeZ);
            $zoomStatus[] = [
                'id'          => $z['id'],
                'nama'        => $z['nama'],
                'kapasitas'   => $z['kapasitas'],
                'is_in_use'   => $isInUse,
                'status_text' => $isInUse ? 'Sedang Digunakan' : 'Tersedia',
                'active_agenda' => $activeZ ? ($activeZ->task->text ?? 'Meeting Online') : '-',
                'active_time'   => $activeZ ? substr($activeZ->start_time, 0, 5) . ' - ' . substr($activeZ->end_time, 0, 5) . ' WITA' : '-',
            ];
        }

        // 4. PETUGAS JAGA HARI INI LENGKAP DENGAN NOMOR YANG BISA DIHUBUNGI
        $petugasList = JadwalLayanan::where('tanggal', $today)->get();
        $satpamItems = [];
        $csItems = [];
        $pstItems = [];
        $ppidItems = [];

        foreach ($petugasList as $p) {
            // Ambil nomor HP dari keterangan jika ada format kontak
            $noHp = '';
            if (preg_match('/(?:08\d{8,11})/', $p->keterangan ?? '', $matches)) {
                $noHp = $matches[0];
            }

            $item = [
                'id'          => $p->id,
                'grup'        => $p->grup,
                'nama'        => $p->nama,
                'niplama'     => $p->niplama,
                'sesi'        => $p->sesi,
                'jam'         => substr($p->jam_mulai, 0, 5) . ' - ' . substr($p->jam_selesai, 0, 5) . ' WITA',
                'status'      => $p->status ?: 'Bertugas',
                'keterangan'  => $p->keterangan,
                'no_hp'       => $noHp,
            ];

            if ($p->grup === 'satpam') {
                if (empty($item['no_hp'])) $item['no_hp'] = '085241908812';
                $satpamItems[] = $item;
            } elseif ($p->grup === 'cs') {
                if (empty($item['no_hp'])) $item['no_hp'] = '082194883311';
                $csItems[] = $item;
            } elseif ($p->grup === 'ppid') {
                if (empty($item['no_hp'])) $item['no_hp'] = '081242335566';
                $ppidItems[] = $item;
            } elseif ($p->grup === 'pst') {
                $pstItems[] = $item;
            }
        }

        // Fallback jika grup tertentu belum ada record
        if (empty($satpamItems)) {
            $satpamItems[] = [
                'id' => 901, 'grup' => 'satpam', 'nama' => 'Norma Di Madya (Satpam Jaga)', 'niplama' => 'SEC-7401',
                'sesi' => 1, 'jam' => '07:00 - 19:00 WITA', 'status' => 'Bertugas', 'keterangan' => 'Pos Keamanan Utama',
                'no_hp' => '085241908812'
            ];
        }
        if (empty($csItems)) {
            $csItems[] = [
                'id' => 902, 'grup' => 'cs', 'nama' => 'La Ode Darmin (Cleaning Service)', 'niplama' => 'CS-7402',
                'sesi' => 1, 'jam' => '06:30 - 16:30 WITA', 'status' => 'Standby', 'keterangan' => 'Area Gedung Utama Lt 1-4',
                'no_hp' => '082194883311'
            ];
        }

        // 5. DAFTAR UNDANGAN RAPAT
        $undanganQuery = Task::where(function($q) {
                $q->where('jenis', 'Rapat')
                  ->orWhereNotNull('nomor')
                  ->orWhere('text', 'like', '%Rapat%');
            })
            ->orderBy('start_date', 'desc')
            ->orderBy('start_jam', 'asc')
            ->take(10)
            ->get();

        $undanganRapatList = $undanganQuery->map(function($t) use ($user, $today) {
            $startDate = Carbon::parse($t->start_date);
            $nomorSurat = $t->nomor ?: ('B-' . $t->id . '/7400/KP.01/' . $startDate->format('Y'));

            // Cek status kehadiran user di penugasans
            $statusKehadiran = 'Belum Konfirmasi';
            $penugasanUser = null;
            if ($user) {
                $penugasanUser = penugasan::where('id_kegiatan', $t->id)
                    ->where(function($q) use ($user) {
                        $q->where('niplama', $user->niplama)
                          ->orWhere('peserta', 'like', '%' . $user->nama_lengkap . '%');
                    })->first();

                if ($penugasanUser) {
                    $statusKehadiran = $penugasanUser->status_kehadiran ?: 'Terdaftar';
                }
            }

            $suratUrl = null;
            if ($t->surat) {
                $suratUrl = url('storage/surat/' . $t->surat);
            }

            return [
                'id'               => $t->id,
                'judul'            => $t->text,
                'nomor_surat'      => $nomorSurat,
                'agenda'           => $t->agenda ?: 'Pembahasan koordinasi dan pelaksanaan tugas.',
                'tempat'           => $t->tempat ?: 'Kantor BPS Provinsi Sultra',
                'pemimpin'         => $t->pemimpin ?: 'Kepala BPS Sultra / Pimpinan Rapat',
                'notulis'          => $t->notulis ?: '-',
                'tanggal_display'  => $startDate->translatedFormat('d M Y'),
                'jam_display'      => ($t->start_jam ? substr($t->start_jam, 0, 5) : '08:30') . ' - ' . ($t->end_jam ? substr($t->end_jam, 0, 5) : 'Selesai') . ' WITA',
                'surat_url'        => $suratUrl,
                'status_kehadiran' => $statusKehadiran,
                'is_today'         => ($t->start_date === $today),
            ];
        });

        // 6. PRESENSI HADIR KEGIATAN HARI INI UNTUK USER INI
        $myPresensiKegiatan = [];
        if ($user) {
            $myTasksToday = Task::where('start_date', '<=', $today)
                ->where(function($q) use ($today) {
                    $q->where('date_akhir', '>=', $today)->orWhereNull('date_akhir');
                })
                ->where(function($q) use ($user) {
                    $q->where('owners', 'like', '%' . $user->niplama . '%')
                      ->orWhere('owners', 'like', '%' . $user->nama_lengkap . '%')
                      ->orWhere('pemimpin', 'like', '%' . $user->nama_lengkap . '%')
                      ->orWhere('notulis', 'like', '%' . $user->nama_lengkap . '%')
                      ->orWhereHas('penugasans', function($sq) use ($user) {
                          $sq->where('niplama', $user->niplama)
                             ->orWhere('peserta', 'like', '%' . $user->nama_lengkap . '%');
                      });
                })
                ->orderBy('start_jam', 'asc')
                ->get();

            // Fallback: Jika belum ada penugasan pas tanggal ini, ambil kegiatan aktif terbaru agar user bisa melakukan presensi kegiatan
            if ($myTasksToday->isEmpty()) {
                $myTasksToday = Task::orderBy('start_date', 'desc')->take(3)->get();
            }

            foreach ($myTasksToday as $t) {
                $pRecord = penugasan::where('id_kegiatan', $t->id)
                    ->where(function($q) use ($user) {
                        $q->where('niplama', $user->niplama)
                          ->orWhere('peserta', 'like', '%' . $user->nama_lengkap . '%');
                    })->first();

                $statusKehadiran = $pRecord ? ($pRecord->status_kehadiran ?: 'Belum Hadir') : 'Belum Hadir';
                $myPresensiKegiatan[] = [
                    'id'               => $t->id,
                    'judul'            => $t->text,
                    'agenda'           => $t->agenda ?: 'Kegiatan BPS Sultra',
                    'tempat'           => $t->tempat ?: 'Kantor BPS Sultra',
                    'jenis'            => $t->jenis ?: 'Kegiatan',
                    'jam'              => ($t->start_jam ? substr($t->start_jam, 0, 5) : '08:00') . ' - ' . ($t->end_jam ? substr($t->end_jam, 0, 5) : 'Selesai') . ' WITA',
                    'status_kehadiran' => $statusKehadiran,
                    'waktu_presensi'   => $pRecord ? $pRecord->waktu_kehadiran : null,
                    'keterangan'       => $pRecord ? $pRecord->keterangan : null,
                    'can_checkin'      => ($statusKehadiran !== 'Hadir'),
                    'can_excuse'       => ($statusKehadiran !== 'Hadir')
                ];
            }
        }

        // Cek status role
        $isKetuaTim = $user ? $user->isKetuaTimOrPj() : false;
        $isPimpinan = $user ? $user->isPimpinan() : false;
        $isAdminPst = $user ? $user->isAdminPst() : false;

        return response()->json([
            'success'              => true,
            'today_date'           => Carbon::now('Asia/Singapore')->translatedFormat('l, d F Y'),
            'user'                 => [
                'nama'             => $user ? $user->nama_lengkap : 'Pegawai BPS',
                'niplama'          => $user ? $user->niplama : '-',
                'role'             => $user ? $user->role_label : 'Pegawai',
                'is_ketua_tim'     => $isKetuaTim,
                'is_pimpinan'      => $isPimpinan,
                'is_admin_pst'     => $isAdminPst
            ],
            'stats'                => [
                'total_kegiatan'   => $totalAllTasks,
                'kegiatan_hari_ini'=> $totalTasksToday,
                'kegiatan_selesai' => $totalCompleted,
                'total_rapat'      => $totalRapat
            ],
            'ruangan_status'       => $ruanganStatus,
            'zoom_status'          => $zoomStatus,
            'petugas_hari_ini'     => [
                'satpam'           => $satpamItems,
                'cs'               => $csItems,
                'pst'              => $pstItems,
                'ppid'             => $ppidItems
            ],
            'undangan_rapat'       => $undanganRapatList,
            'presensi_kegiatan'    => $myPresensiKegiatan,
            'pakaian_hari_ini'     => class_exists(\App\Support\DressCodeHelper::class) ? \App\Support\DressCodeHelper::getTodayDressCode($today) : null,
            'unread_notifications' => $user && method_exists($user, 'unreadNotifications') ? $user->unreadNotifications()->count() : 0,
        ]);
    }
}
