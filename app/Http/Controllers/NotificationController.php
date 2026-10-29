<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request, string $notification)
    {
        $user = $request->user();

        $userNotification = $user->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $userNotification->markAsRead();

        return back()->with('success', 'Notifikasi berhasil dibaca.');
    }
}
