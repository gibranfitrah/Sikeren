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

        if (Auth::check()) {
            $notif = Auth::user()->notifications()->where('id', $id)->first();
            if ($notif) {
                $notif->markAsRead();
                $data = is_array($notif->data) ? $notif->data : (json_decode($notif->data ?? '{}', true) ?: []);
                $targetUrl = $data['url'] ?? ($data['link'] ?? $targetUrl);
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
            $targetUrl = $data['url'] ?? ($legacy->url ?? ($legacy->link ?? $targetUrl));
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
}