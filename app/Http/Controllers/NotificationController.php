<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notification;

class NotificationController extends Controller
{
    public function read($id)
    {
        $targetUrl = url('/daftar_kegiatan');

        $normalizeUrl = function ($url) {
            if (!$url) return url('/daftar_kegiatan');
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $host = parse_url($url, PHP_URL_HOST);
                if (in_array($host, ['localhost', '127.0.0.1'])) {
                    $path = parse_url($url, PHP_URL_PATH) ?: '/';
                    $query = parse_url($url, PHP_URL_QUERY);
                    return $path . ($query ? '?' . $query : '');
                }
            }
            return $url;
        };

        if (Auth::check()) {
            $notif = Auth::user()->notifications()->where('id', $id)->first();
            if ($notif) {
                $notif->markAsRead();
                $data = is_array($notif->data) ? $notif->data : (json_decode($notif->data ?? '{}', true) ?: []);
                $targetUrl = $normalizeUrl($data['url'] ?? ($data['link'] ?? $targetUrl));
                return redirect($targetUrl);
            }
        }

        // Fallback for custom table or legacy ID
        $legacy = DB::table('notifications')->where('id', $id)->first();
        if ($legacy) {
            DB::table('notifications')->where('id', $id)->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
            $data = is_array($legacy->data ?? null) ? $legacy->data : (json_decode($legacy->data ?? '{}', true) ?: []);
            $targetUrl = $normalizeUrl($data['url'] ?? ($legacy->url ?? ($legacy->link ?? $targetUrl)));
        }

        return redirect($targetUrl);
    }

    public function markAllRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    /**
     * Broadcast pengumuman / informasi resmi ke seluruh pegawai BPS
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'pesan' => 'required|string',
            'url'   => 'nullable|string',
        ]);

        $users = \App\User::all();
        $targetUrl = $request->url ?: '/dashboard';
        $sender = Auth::user() ? Auth::user()->nama_lengkap : 'Admin SIKEREN';

        foreach ($users as $u) {
            DB::table('notifications')->insert([
                'id'              => (string) \Illuminate\Support\Str::uuid(),
                'type'            => 'App\Notifications\PengumumanInformasiNotification',
                'notifiable_type' => 'App\User',
                'notifiable_id'   => $u->id,
                'data'            => json_encode([
                    'judul'    => $request->judul,
                    'pesan'    => $request->pesan,
                    'pengirim' => $sender,
                    'url'      => $targetUrl,
                ]),
                'read_at'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Pengumuman informasi berhasil disiarkan ke seluruh pegawai BPS (' . $users->count() . ' orang).');
    }
}