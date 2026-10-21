<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notification;
use App\User;
use App\Task;
use App\SubKegiatan;
use App\penugasan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationApiController extends Controller
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

    public function index(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        // 1. Sinkronkan Notifikasi Tugas Kegiatan & Sub-Kegiatan Otomatis
        $this->syncTaskAndSubKegiatanNotifications($user);

        // 2. Sinkronkan Notifikasi Pelepasan Ruangan Otomatis
        $this->syncRoomReleaseNotifications($user);

        $notifs = Notification::where('niplama', $user->niplama)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'judul'      => $n->judul,
                    'pesan'      => $n->pesan,
                    'link'       => $n->link,
                    'tipe'       => $n->tipe ?: 'general',
                    'is_read'    => (bool) $n->is_read,
                    'waktu'      => Carbon::parse($n->created_at)->diffForHumans(),
                    'created_at' => $n->created_at
                ];
            });

        $unreadCount = Notification::where('niplama', $user->niplama)
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'success'      => true,
            'unread_count' => $unreadCount,
            'data'         => $notifs
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        Notification::where('id', $id)
            ->where('niplama', $user->niplama)
            ->update(['is_read' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        Notification::where('niplama', $user->niplama)
            ->update(['is_read' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca'
        ]);
    }

    /**
     * POST /api/v1/ruangan/lepas
     * Konfirmasi pelepasan ruangan rapat
     */
    public function lepasRuangan(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 401);
        }

        $taskId = $request->input('task_id');
        $namaRuangan = $request->input('nama_ruangan');

        $task = null;
        if ($taskId) {
            $task = Task::find($taskId);
        } elseif ($namaRuangan) {
            $task = Task::where('tempat', 'like', "%{$namaRuangan}%")
                ->where('start_date', Carbon::today()->format('Y-m-d'))
                ->first();
        }

        if ($task) {
            $task->status_ruangan = 'Selesai';
            $task->save();

            // Tandai booking selesai jika ada
            if (DB::getSchemaBuilder()->hasTable('room_bookings')) {
                DB::table('room_bookings')
                    ->where('task_id', $task->id)
                    ->update(['status' => 'Dilepas', 'updated_at' => now()]);
            }

            // Tandai notifikasi terkait sebagai read
            Notification::where('niplama', $user->niplama)
                ->where('link', 'like', "%action:lepas_ruangan:{$task->id}%")
                ->update(['is_read' => 1]);

            $roomName = $task->tempat ?: 'Ruangan Rapat';
            return response()->json([
                'success' => true,
                'message' => "Ruangan '{$roomName}' berhasil dilepaskan. Sekarang ruangan siap digunakan oleh tim lain."
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data agenda atau ruangan tidak ditemukan.'
        ], 404);
    }

    /**
     * Sinkronkan notifikasi tugas kegiatan dan sub-kegiatan
     */
    private function syncTaskAndSubKegiatanNotifications($user)
    {
        $today = Carbon::today()->format('Y-m-d');

        // A. Cek Kegiatan / Rapat Hari Ini & Besok yang melibatkan user
        $myTasks = Task::where(function ($q) use ($user) {
                $q->where('pemimpin', 'like', "%{$user->nama_lengkap}%")
                  ->orWhere('notulis', 'like', "%{$user->nama_lengkap}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$user->nama_lengkap}%")
                  ->orWhereHas('penugasans', function ($sq) use ($user) {
                      $sq->where('niplama', $user->niplama)
                         ->orWhere('peserta', 'like', "%{$user->nama_lengkap}%");
                  });
            })
            ->where('start_date', '>=', $today)
            ->take(5)
            ->get();

        foreach ($myTasks as $t) {
            $identifier = "task:{$t->id}:{$user->niplama}";
            $exists = Notification::where('niplama', $user->niplama)
                ->where('link', $identifier)
                ->exists();

            if (!$exists) {
                $tgl = Carbon::parse($t->start_date)->translatedFormat('d F Y');
                $jam = $t->start_jam ? substr($t->start_jam, 0, 5) : '08:00';
                Notification::create([
                    'niplama'    => $user->niplama,
                    'judul'      => "Tugas Kegiatan: {$t->text}",
                    'pesan'      => "Anda memiliki agenda '{$t->text}' pada tanggal {$tgl} pukul {$jam} WITA di {$t->tempat}.",
                    'link'       => $identifier,
                    'tipe'       => 'tugas_kegiatan',
                    'is_read'    => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // B. Cek Sub-Kegiatan yang melibatkan user
        if (DB::getSchemaBuilder()->hasTable('sub_kegiatans')) {
            $mySubs = SubKegiatan::where(function ($q) use ($user) {
                    $q->where('pj', 'like', "%{$user->nama_lengkap}%")
                      ->orWhere('anggota', 'like', "%{$user->nama_lengkap}%")
                      ->orWhere('anggota', 'like', "%{$user->niplama}%");
                })
                ->where('status', '!=', 'Selesai')
                ->take(5)
                ->get();

            foreach ($mySubs as $sub) {
                $identifier = "sub_kegiatan:{$sub->id}:{$user->niplama}";
                $exists = Notification::where('niplama', $user->niplama)
                    ->where('link', $identifier)
                    ->exists();

                if (!$exists) {
                    $deadline = $sub->end_date ? Carbon::parse($sub->end_date)->translatedFormat('d F Y') : 'sesuai jadwal';
                    Notification::create([
                        'niplama'    => $user->niplama,
                        'judul'      => "Sub-Kegiatan: {$sub->nama_sub}",
                        'pesan'      => "Anda ditugaskan pada sub-kegiatan '{$sub->nama_sub}' (Tim: {$sub->tim}). Batas waktu penyelesaian: {$deadline}.",
                        'link'       => $identifier,
                        'tipe'       => 'sub_kegiatan',
                        'is_read'    => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Sinkronkan notifikasi pelepasan ruangan rapat
     */
    private function syncRoomReleaseNotifications($user)
    {
        $today = Carbon::today()->format('Y-m-d');

        // Cari rapat yang menggunakan ruangan hari ini
        $activeMeetings = Task::where('start_date', $today)
            ->whereNotNull('tempat')
            ->where('tempat', '!=', '')
            ->where('tempat', '!=', '-')
            ->where('status_ruangan', '!=', 'Selesai')
            ->where(function ($q) use ($user) {
                $q->where('pemimpin', 'like', "%{$user->nama_lengkap}%")
                  ->orWhere('notulis', 'like', "%{$user->nama_lengkap}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$user->nama_lengkap}%")
                  ->orWhereHas('penugasans', function ($sq) use ($user) {
                      $sq->where('niplama', $user->niplama);
                  });
            })
            ->get();

        foreach ($activeMeetings as $m) {
            $identifier = "action:lepas_ruangan:{$m->id}";
            $exists = Notification::where('niplama', $user->niplama)
                ->where('link', $identifier)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'niplama'    => $user->niplama,
                    'judul'      => "Lepas Ruangan: {$m->tempat}",
                    'pesan'      => "Rapat '{$m->text}' di {$m->tempat} selesai pada pukul " . substr($m->end_jam ?: '16:00', 0, 5) . " WITA. Mohon lepaskan ruangan jika selesai digunakan.",
                    'link'       => $identifier,
                    'tipe'       => 'ruangan_release',
                    'is_read'    => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
