<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Task;
use App\SubKegiatan;
use App\penugasan;
use App\User;
use App\Venue;
use App\RoomBooking;
use Auth; 
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $today = Carbon::now()->format('Y-m-d');
        $user  = Auth::user();
        $nip   = $user->niplama ?? '';
        $nama  = $user->nama_lengkap ?? '';
        $username = $user->username ?? '';

        // 1. Task IDs from penugasan
        $taskIdsFromPenugasan = penugasan::where('niplama', $nip)
            ->orWhere('peserta', $nip)
            ->orWhere('peserta', 'LIKE', '%' . $nama . '%')
            ->pluck('id_kegiatan')
            ->filter()
            ->unique()
            ->toArray();

        // 2. Base query for all user's activities
        $userTaskQuery = function() use ($nip, $nama, $username, $taskIdsFromPenugasan) {
            return Task::with('subKegiatans')->where(function ($q) use ($nip, $nama, $username, $taskIdsFromPenugasan) {
                $q->where('owners', 'LIKE', '%' . $nip . '%')
                  ->orWhere('owners', 'LIKE', '%' . $nama . '%')
                  ->orWhere('pemimpin', 'LIKE', '%' . $nama . '%')
                  ->orWhere('notulis', 'LIKE', '%' . $nama . '%')
                  ->orWhere('tim_dokumentasi', 'LIKE', '%' . $nama . '%')
                  ->orWhere('penanggung_jawab', 'LIKE', '%' . $nama . '%')
                  ->orWhere('penanggung_jawab', $username);

                if (!empty($taskIdsFromPenugasan)) {
                    $q->orWhereIn('id', $taskIdsFromPenugasan);
                }
            });
        };

        // All activities (Rapat + Kegiatan)
        $allTasks = Task::with('subKegiatans')->orderBy('start_date', 'desc')->get();
        $kegiatansSaya = $userTaskQuery()->orderBy('start_date', 'desc')->get();

        // Sub kegiatans involving user
        $subKegiatansSaya = SubKegiatan::with('task')->where(function($q) use ($nama, $username, $nip) {
            $q->where('pj', 'LIKE', '%' . $nama . '%')
              ->orWhere('pj', $username)
              ->orWhere('anggota', 'LIKE', '%' . $nama . '%')
              ->orWhere('anggota', 'LIKE', '%' . $username . '%')
              ->orWhere('anggota', 'LIKE', '%' . $nip . '%');
        })->orderBy('end_date', 'asc')->get();

        // Kegiatan Belum & Selesai
        $kegiatan_belum = $userTaskQuery()
            ->where(function($q) {
                $q->where('status', '!=', 'Selesai')
                  ->orWhereNull('status');
            })
            ->where(function($q) {
                $q->where('notulen_selesai', '!=', 1)
                  ->orWhereNull('notulen_selesai');
            })
            ->orderBy('start_date', 'ASC')
            ->get();

        $kegiatan_selesai = $userTaskQuery()
            ->where(function($q) {
                $q->where('status', 'Selesai')
                  ->orWhere('notulen_selesai', 1);
            })
            ->orderBy('start_date', 'ASC')
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek   = Carbon::now()->endOfWeek()->format('Y-m-d');

        $kegiatan_mingguan = $userTaskQuery()
            ->where(function($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('start_date', [$startOfWeek, $endOfWeek])
                  ->orWhereBetween('date_akhir', [$startOfWeek, $endOfWeek]);
            })
            ->orderBy('start_date', 'ASC')
            ->get();

        $jumlah_kegiatan         = count($allTasks);
        $jumlah_kegiatan_saya    = count($kegiatansSaya);
        $jumlah_kegiatan_belum   = count($kegiatan_belum);
        $jumlah_kegiatan_selesai = count($kegiatan_selesai);
        $jumlah_sub_kegiatan     = SubKegiatan::count();

        // Monthly chart data
        $users = Task::select('id', 'start_date')
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->start_date)->format('m');
            });

        $usermcount = [];
        $userArr    = [];

        foreach ($users as $key => $value) {
            $usermcount[(int)$key] = count($value);
        }

        for ($i = 1; $i <= 12; $i++) {
            $userArr[$i] = !empty($usermcount[$i]) ? $usermcount[$i] : 0;
        }

        // Generate JSON Events for Dashboard Interactive Calendar
        $calendarEvents = [];
        foreach ($allTasks as $task) {
            $sDate = $task->start_date ?: $today;
            $eDate = $task->date_akhir ? Carbon::parse($task->date_akhir)->addDay()->format('Y-m-d') : $sDate;
            $isRapat = ($task->jenis === 'Rapat');

            $calendarEvents[] = [
                'id'    => 'task_' . $task->id,
                'title' => ($isRapat ? '[Rapat] ' : '[Kegiatan] ') . $task->text,
                'start' => $sDate . ($task->start_jam ? 'T' . $task->start_jam : ''),
                'end'   => $eDate . ($task->end_jam ? 'T' . $task->end_jam : ''),
                'url'   => url('/daftarkegiatan/' . $task->id),
                'backgroundColor' => $isRapat ? '#6366f1' : '#0ea5e9',
                'borderColor'     => $isRapat ? '#4f46e5' : '#0284c7',
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'tim'    => $task->tim ?? '-',
                    'pj'     => $task->penanggung_jawab ?? $task->pemimpin ?? '-',
                    'tempat' => $task->tempat ?? '-',
                    'status' => $task->status ?? 'Terjadwal'
                ]
            ];
        }

        $notifications = Auth::user()->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notif) {
                $notif->time_ago = Carbon::parse($notif->created_at)->diffForHumans();
                return $notif;
            });

        $jumlah_notif = Auth::user()->unreadNotifications()->count();
        $pegawais = User::orderBy('nama_lengkap', 'asc')->get();

        // 10. Real-time Status Ketersediaan 3 Ruangan & 2 Akun Zoom Hari Ini
        $currentTime = Carbon::now()->format('H:i:s');
        $venuesList = Venue::whereIn('id', [1, 2, 3])->orderBy('id', 'asc')->get();
        if ($venuesList->isEmpty()) {
            $venuesList = collect([
                (object)['id' => 1, 'name' => 'Aula Lantai 1', 'capacity' => 100, 'description' => 'Lantai 1 • Sound System, Proyektor, AC'],
                (object)['id' => 2, 'name' => 'Vicon Lantai 3', 'capacity' => 25, 'description' => 'Lantai 3 • TV Smart, Camera 360, Mic Polycom'],
                (object)['id' => 3, 'name' => 'Aula Lantai 4', 'capacity' => 150, 'description' => 'Lantai 4 • Videotron LED, Sound Besar, AC Central'],
            ]);
        }

        $todayRoomBookings = RoomBooking::with('task')
            ->where('booking_date', $today)
            ->where('status', '!=', 'Dibatalkan')
            ->orderBy('start_time', 'asc')
            ->get();

        $dashboardVenuesStatus = [];
        foreach ($venuesList as $venue) {
            $rBookings = $todayRoomBookings->where('venue_id', $venue->id);
            $activeNow = $rBookings->first(function ($b) use ($currentTime) {
                return $currentTime >= $b->start_time && $currentTime <= $b->end_time;
            });
            $nextBooking = $rBookings->first(function ($b) use ($currentTime) {
                return $b->start_time > $currentTime;
            });

            $dashboardVenuesStatus[$venue->id] = [
                'venue'        => $venue,
                'is_in_use'    => !is_null($activeNow),
                'active_now'   => $activeNow,
                'next_booking' => $nextBooking,
                'total_today'  => $rBookings->count(),
                'bookings'     => $rBookings
            ];
        }

        $dashboardZoomStatus = [];
        foreach (RoomBooking::$zoomAccounts as $key => $zoom) {
            $zBookings = $todayRoomBookings->where('zoom_account', $key);
            $activeNow = $zBookings->first(function ($b) use ($currentTime) {
                return $currentTime >= $b->start_time && $currentTime <= $b->end_time;
            });

            $dashboardZoomStatus[$key] = [
                'info'        => $zoom,
                'is_in_use'   => !is_null($activeNow),
                'active_now'  => $activeNow,
                'total_today' => $zBookings->count(),
                'bookings'    => $zBookings
            ];
        }

        return view('dashboard', compact(
            'userArr',
            'allTasks',
            'kegiatansSaya',
            'subKegiatansSaya',
            'jumlah_kegiatan',
            'jumlah_kegiatan_saya',
            'jumlah_kegiatan_belum',
            'jumlah_kegiatan_selesai',
            'jumlah_sub_kegiatan',
            'kegiatan_mingguan',
            'today',
            'kegiatan_belum',
            'notifications',
            'jumlah_notif',
            'calendarEvents',
            'pegawais',
            'dashboardVenuesStatus',
            'dashboardZoomStatus'
        ));
    }   
}