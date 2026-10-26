<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Task;
use App\penugasan;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KegiatanApiController extends Controller
{
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->bearerToken() ?: $request->header('X-Token') ?: $request->input('token');
        if ($token) {
            $user = User::where('token_id', $token)->first();
            if ($user) return $user;
        }

        // Fallback to niplama if passed directly
        if ($request->has('niplama')) {
            return User::where('niplama', $request->niplama)->first();
        }

        return User::first(); // Fallback for convenience in dev
    }

    /**
     * Get user activities & meetings
     */
    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');
        $search = $request->input('search');
        $type = $request->input('type'); // 'all', 'today', 'upcoming', 'rapat', 'kegiatan'
        $scope = $request->input('scope', 'auto'); // 'my', 'office', 'auto'

        $nip = $user->niplama;
        $nama = $user->nama_lengkap;
        $username = $user->username;

        $taskIdsFromPenugasan = DB::table('penugasans')
            ->where('niplama', $nip)
            ->orWhere('peserta', $nip)
            ->orWhere('peserta', 'LIKE', '%' . $nama . '%')
            ->pluck('id_kegiatan')
            ->filter()
            ->unique()
            ->toArray();

        $userFilter = function ($q) use ($nip, $nama, $taskIdsFromPenugasan) {
            $q->where('owners', 'LIKE', '%' . $nip . '%')
              ->orWhere('owners', 'LIKE', '%' . $nama . '%')
              ->orWhere('pemimpin', 'LIKE', '%' . $nama . '%')
              ->orWhere('notulis', 'LIKE', '%' . $nama . '%');

            if (!empty($taskIdsFromPenugasan)) {
                $q->orWhereIn('id', $taskIdsFromPenugasan);
            }
        };

        if ($scope === 'office') {
            $baseQuery = Task::query();
        } else {
            $baseQuery = Task::where($userFilter);
            // If user has 0 activities assigned, fallback to office activities so the screen is useful
            if ($scope === 'auto' && (clone $baseQuery)->count() === 0) {
                $baseQuery = Task::query();
            }
        }

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('text', 'like', "%{$search}%")
                  ->orWhere('agenda', 'like', "%{$search}%")
                  ->orWhere('tempat', 'like', "%{$search}%");
            });
        }

        // Calculate statistics
        $allTasks = (clone $baseQuery)->get();
        $totalToday = $allTasks->filter(function ($t) use ($today) {
            return $t->start_date <= $today && ($t->date_akhir ? $t->date_akhir >= $today : true);
        })->count();

        $totalUpcoming = $allTasks->filter(function ($t) use ($today) {
            return $t->start_date > $today;
        })->count();

        $totalRapat = $allTasks->where('jenis', 'Rapat')->count();
        $totalKegiatan = $allTasks->where('jenis', 'Kegiatan')->count();

        // Apply tab filters
        if ($type === 'today') {
            $baseQuery->where('start_date', '<=', $today)
                      ->where(function($q) use ($today) {
                          $q->where('date_akhir', '>=', $today)
                            ->orWhereNull('date_akhir');
                      });
        } elseif ($type === 'upcoming') {
            $baseQuery->where('start_date', '>', $today);
        } elseif ($type === 'history') {
            $baseQuery->where('date_akhir', '<', $today);
        } elseif ($type === 'rapat') {
            $baseQuery->where('jenis', 'Rapat');
        } elseif ($type === 'kegiatan') {
            $baseQuery->where('jenis', 'Kegiatan');
        }

        $tasks = $baseQuery->orderBy('start_date', 'desc')
                           ->orderBy('start_jam', 'asc')
                           ->get();

        // Format tasks
        $formatted = $tasks->map(function ($t) use ($today) {
            $startDate = Carbon::parse($t->start_date);
            $endDate = $t->date_akhir ? Carbon::parse($t->date_akhir) : $startDate;

            $isToday = ($t->start_date <= $today && ($t->date_akhir ? $t->date_akhir >= $today : true));
            $isUpcoming = $t->start_date > $today;

            return [
                'id'            => $t->id,
                'judul'         => $t->text ?: 'Kegiatan BPS',
                'agenda'        => $t->agenda ?: '-',
                'tempat'        => $t->tempat ?: 'Kantor BPS Sultra',
                'pemimpin'      => $t->pemimpin ?: '-',
                'notulis'       => $t->notulis ?: '-',
                'jenis'         => $t->jenis ?: 'Kegiatan',
                'nomor'         => $t->nomor,
                'start_date'    => $t->start_date,
                'date_akhir'    => $t->date_akhir ?: $t->start_date,
                'start_jam'     => $t->start_jam ? substr($t->start_jam, 0, 5) : '08:00',
                'end_jam'       => $t->end_jam ? substr($t->end_jam, 0, 5) : 'Selesai',
                'jam_display'   => ($t->start_jam ? substr($t->start_jam, 0, 5) : '08:00') . ' - ' . ($t->end_jam ? substr($t->end_jam, 0, 5) : 'Selesai'),
                'tanggal_display' => $startDate->translatedFormat('d M Y'),
                'hari'          => $startDate->translatedFormat('l'),
                'status'        => $t->status ?: ($isToday ? 'Hari Ini' : ($isUpcoming ? 'Mendatang' : 'Selesai')),
                'is_today'      => $isToday,
                'is_upcoming'   => $isUpcoming,
                'notulen'       => $t->notulen,
                'surat'         => $t->surat,
            ];
        });

        return response()->json([
            'success' => true,
            'stats'   => [
                'total_all'      => $allTasks->count(),
                'total_today'    => $totalToday,
                'total_upcoming' => $totalUpcoming,
                'total_rapat'    => $totalRapat,
                'total_kegiatan' => $totalKegiatan
            ],
            'data'    => $formatted
        ]);
    }

    /**
     * Get detail of a specific activity
     */
    public function show($id)
    {
        $task = Task::findOrFail($id);

        // Fetch participants
        $participantNips = penugasan::where('id_kegiatan', $id)->pluck('niplama')->toArray();
        if ($task->owners) {
            $ownerNips = explode(',', $task->owners);
            $participantNips = array_unique(array_merge($participantNips, $ownerNips));
        }

        $participants = User::whereIn('niplama', $participantNips)
            ->select('id', 'nama_lengkap', 'niplama', 'foto')
            ->get()
            ->map(function ($u) {
                return [
                    'id'           => $u->id,
                    'nama_lengkap' => $u->nama_lengkap,
                    'niplama'      => $u->niplama,
                    'foto'         => $u->foto ? url('storage/foto/' . $u->foto) : null
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $task->id,
                'judul'         => $task->text,
                'agenda'        => $task->agenda,
                'tempat'        => $task->tempat,
                'pemimpin'      => $task->pemimpin,
                'notulis'       => $task->notulis,
                'jenis'         => $task->jenis,
                'nomor'         => $task->nomor,
                'start_date'    => $task->start_date,
                'date_akhir'    => $task->date_akhir,
                'start_jam'     => $task->start_jam ? substr($task->start_jam, 0, 5) : '08:00',
                'end_jam'       => $task->end_jam ? substr($task->end_jam, 0, 5) : 'Selesai',
                'notulen'       => $task->notulen,
                'surat'         => $task->surat,
                'participants'  => $participants
            ]
        ]);
    }

    /**
     * Daily Dashboard Summary
     */
    public function dailySummary(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        $today = Carbon::now('Asia/Singapore')->format('Y-m-d');
        $nip = $user->niplama;
        $nama = $user->nama_lengkap;

        $taskIdsFromPenugasan = DB::table('penugasans')
            ->where('niplama', $nip)
            ->orWhere('peserta', $nip)
            ->orWhere('peserta', 'LIKE', '%' . $nama . '%')
            ->pluck('id_kegiatan')
            ->filter()
            ->unique()
            ->toArray();

        // Today's activities
        $todayTasksQuery = Task::where(function ($q) use ($nip, $nama, $taskIdsFromPenugasan) {
            $q->where('owners', 'LIKE', '%' . $nip . '%')
              ->orWhere('owners', 'LIKE', '%' . $nama . '%')
              ->orWhere('pemimpin', 'LIKE', '%' . $nama . '%')
              ->orWhere('notulis', 'LIKE', '%' . $nama . '%');

            if (!empty($taskIdsFromPenugasan)) {
                $q->orWhereIn('id', $taskIdsFromPenugasan);
            }
        })
        ->where('start_date', '<=', $today)
        ->where(function($q) use ($today) {
            $q->where('date_akhir', '>=', $today)
              ->orWhereNull('date_akhir');
        });

        $todayTasks = $todayTasksQuery->orderBy('start_jam', 'asc')->get();

        // If no user activities today, fetch today's office activities
        if ($todayTasks->isEmpty()) {
            $todayTasks = Task::where('start_date', '<=', $today)
                ->where(function($q) use ($today) {
                    $q->where('date_akhir', '>=', $today)
                      ->orWhereNull('date_akhir');
                })
                ->orderBy('start_jam', 'asc')
                ->get();
        }

        // Presensi status today
        $presensiToday = DB::table('presensis')
            ->where('niplama', $user->niplama)
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $latestPresensi = $presensiToday->last();

        $statusLabels = [
            1 => 'Masuk',
            2 => 'Keluar Pribadi',
            3 => 'Kembali',
            4 => 'Pulang',
            5 => 'Keluar Dinas',
            6 => 'Cuti',
            7 => 'Tugas Luar',
        ];

        $currentPresensiStatus = $latestPresensi 
            ? ($statusLabels[$latestPresensi->status] ?? 'Tercatat')
            : 'Belum Hadir';

        $checkInTime = null;
        $firstMasuk = $presensiToday->firstWhere('status', 1);
        if ($firstMasuk) {
            $checkInTime = Carbon::parse($firstMasuk->created_at)->format('H:i');
        }

        // Unread notifications
        $unreadNotifCount = DB::table('notifications')
            ->where('niplama', $user->niplama)
            ->where('is_read', 0)
            ->count();

        // Jadwal Petugas PST Hari Ini
        $hasJadwalPsts = \Illuminate\Support\Facades\Schema::hasTable('jadwal_psts');
        $todayPst = $hasJadwalPsts
            ? \App\JadwalPst::where('tanggal', $today)
                ->orderBy('sesi')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'               => $item->id,
                        'sesi'             => $item->sesi,
                        'sesi_label'       => $item->sesi_label,
                        'jam'              => "{$item->jam_mulai} - {$item->jam_selesai}",
                        'nama_petugas'     => $item->nama_petugas,
                        'status_kehadiran' => $item->status_kehadiran,
                        'status_tukar'     => $item->status_tukar,
                        'pengganti_nama'   => $item->pengganti_nama,
                        'display_nama'     => !empty($item->pengganti_nama) 
                                              ? "{$item->pengganti_nama} (Gantikan {$item->nama_petugas})" 
                                              : $item->nama_petugas
                    ];
                })
            : collect();

        // Cek apakah user bertugas PST hari ini
        $myPst = $hasJadwalPsts
            ? \App\JadwalPst::where('tanggal', $today)
                ->where(function ($q) use ($user) {
                    $q->where('niplama', $user->niplama)
                      ->orWhere('pengganti_niplama', $user->niplama);
                })
                ->first()
            : null;

        $myPstData = null;
        if ($myPst) {
            $myPstData = [
                'id'                 => $myPst->id,
                'sesi'               => $myPst->sesi,
                'sesi_label'         => $myPst->sesi_label,
                'jam'                => "{$myPst->jam_mulai} - {$myPst->jam_selesai} WITA",
                'status_kehadiran'   => $myPst->status_kehadiran,
                'status_tukar'       => $myPst->status_tukar,
                'can_report_absence' => ($myPst->status_kehadiran !== 'Berhalangan' && $myPst->status_kehadiran !== 'Digantikan')
            ];
        }

        $rapatList = $todayTasks->filter(function ($t) {
            return $t->jenis === 'Rapat' || stripos($t->text, 'rapat') !== false;
        })->values();

        return response()->json([
            'success' => true,
            'today_date' => Carbon::now('Asia/Singapore')->translatedFormat('l, d F Y'),
            'is_pimpinan' => $user->isPimpinan(),
            'is_admin_pst' => $user->isAdminPst(),
            'presensi' => [
                'current_status' => $currentPresensiStatus,
                'has_checked_in' => $firstMasuk ? true : false,
                'check_in_time'  => $checkInTime,
                'latest_time'    => $latestPresensi ? Carbon::parse($latestPresensi->created_at)->format('H:i') : null,
                'total_logs'     => $presensiToday->count(),
            ],
            'unread_notifications' => $unreadNotifCount,
            'kegiatan_today_count' => $todayTasks->count(),
            'kegiatan_today'       => $todayTasks->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'judul'       => $t->text,
                    'agenda'      => $t->agenda,
                    'tempat'      => $t->tempat,
                    'jenis'       => $t->jenis,
                    'pemimpin'    => $t->pemimpin,
                    'start_jam'   => $t->start_jam ? substr($t->start_jam, 0, 5) : '08:00',
                    'end_jam'     => $t->end_jam ? substr($t->end_jam, 0, 5) : 'Selesai',
                ];
            }),
            'rapat_today'          => $rapatList->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'judul'       => $t->text,
                    'agenda'      => $t->agenda,
                    'tempat'      => $t->tempat,
                    'jenis'       => 'Rapat',
                    'pemimpin'    => $t->pemimpin,
                    'start_jam'   => $t->start_jam ? substr($t->start_jam, 0, 5) : '08:00',
                    'end_jam'     => $t->end_jam ? substr($t->end_jam, 0, 5) : 'Selesai',
                ];
            }),
            'pst_today'            => $todayPst,
            'my_pst_today'         => $myPstData,
        ]);
    }
}
