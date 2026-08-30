<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::where(function ($q) {
            $q->where('user_id', auth()->id())
                ->orWhereNull('user_id');
        })->orderByDesc('created_at')->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $count = AppNotification::where(function ($q) {
            $q->where('user_id', auth()->id())
                ->orWhereNull('user_id');
        })->where('is_read', false)->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(AppNotification $notification)
    {
        $notification->update(['is_read' => true]);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    public function markAllRead()
    {
        AppNotification::where(function ($q) {
            $q->where('user_id', auth()->id())
                ->orWhereNull('user_id');
        })->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
