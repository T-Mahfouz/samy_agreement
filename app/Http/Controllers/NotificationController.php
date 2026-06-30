<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['is_read' => true]);

        return $notification->link ? redirect($notification->link) : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->appNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back();
    }
}
