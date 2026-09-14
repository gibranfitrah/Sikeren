<?php

namespace App\Http\Controllers;

use App\Notification;

class NotificationController extends Controller
{
    public function read($id)
    {
        $notif = Notification::findOrFail($id);

        // tandai sudah dibaca
        $notif->is_read = 1;
        $notif->save();

        return redirect($notif->link);
    }
}