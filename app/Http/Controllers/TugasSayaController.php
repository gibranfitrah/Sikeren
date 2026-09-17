<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\SubKegiatan;
use App\RoomBooking;
use App\penugasan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TugasSayaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $nip = $user->niplama;
        $nama = $user->nama_lengkap;
        $username = $user->username;

        $filterStatus = $request->input('status', 'all');

        // 1. Task IDs from penugasans
        $taskIdsFromPenugasan = penugasan::where('niplama', $nip)
            ->orWhere('peserta', $nip)
            ->orWhere('peserta', 'LIKE', '%' . $nama . '%')
            ->pluck('id_kegiatan')
            ->filter()
            ->unique()
            ->toArray();

        // 2. Main Tasks for user
        $tasksQuery = Task::where(function($q) use ($nip, $nama, $username, $taskIdsFromPenugasan) {
            $q->where('penanggung_jawab', 'LIKE', '%' . $nama . '%')
              ->orWhere('penanggung_jawab', $username)
              ->orWhere('pemimpin', 'LIKE', '%' . $nama . '%')
              ->orWhere('notulis', 'LIKE', '%' . $nama . '%')
              ->orWhere('tim_dokumentasi', 'LIKE', '%' . $nama . '%')
              ->orWhere('owners', 'LIKE', '%' . $nip . '%');

            if (!empty($taskIdsFromPenugasan)) {
                $q->orWhereIn('id', $taskIdsFromPenugasan);
            }
        });

        if ($filterStatus === 'selesai') {
            $tasksQuery->where(function($q) {
                $q->where('status', 'Selesai')
                  ->orWhere('notulen_selesai', 1);
            });
        } elseif ($filterStatus === 'berjalan') {
            $tasksQuery->where(function($q) {
                $q->where('status', '!=', 'Selesai')
                  ->orWhereNull('status');
            });
        }

        $myTasks = $tasksQuery->orderBy('start_date', 'desc')->get();

        // 3. Sub-kegiatans where user is PJ or member
        $subQuery = SubKegiatan::with('task')->where(function($q) use ($nama, $username, $nip) {
            $q->where('pj', 'LIKE', '%' . $nama . '%')
              ->orWhere('pj', $username)
              ->orWhere('anggota', 'LIKE', '%' . $nama . '%')
              ->orWhere('anggota', 'LIKE', '%' . $username . '%')
              ->orWhere('anggota', 'LIKE', '%' . $nip . '%');
        });

        if ($filterStatus === 'selesai') {
            $subQuery->where('status', 'Selesai');
        } elseif ($filterStatus === 'berjalan') {
            $subQuery->where('status', '!=', 'Selesai');
        }

        $mySubKegiatans = $subQuery->orderBy('end_date', 'asc')->get();

        // 4. Booking ruangan yang dibuat / diselenggarakan oleh user
        // Termasuk booking standalone (tanpa task_id) agar jadwal dari
        // halaman Booking Ruangan ikut muncul di tabel Tugas Saya.
        $bookingQuery = RoomBooking::with(['venue', 'task'])
            ->where(function ($q) use ($user, $nama, $username) {
                $q->where('created_by', $user->id)
                  ->orWhere('penyelenggara', 'LIKE', '%' . $nama . '%');
                if (!empty($username)) {
                    $q->orWhere('penyelenggara', 'LIKE', '%' . $username . '%');
                }
            })
            ->where('status', '!=', 'Dibatalkan');

        if ($filterStatus === 'selesai') {
            $bookingQuery->where('booking_date', '<', Carbon::today()->format('Y-m-d'));
        } elseif ($filterStatus === 'berjalan') {
            $bookingQuery->where('booking_date', '>=', Carbon::today()->format('Y-m-d'));
        }

        $myBookings = $bookingQuery
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        $notifications = Auth::user()->notifications()->latest()->take(5)->get();
        $jumlah_notif = Auth::user()->unreadNotifications()->count();

        return view('tugas_saya.index', compact(
            'myTasks',
            'mySubKegiatans',
            'myBookings',
            'filterStatus',
            'notifications',
            'jumlah_notif'
        ));
    }
}