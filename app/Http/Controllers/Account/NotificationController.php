<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->customerNotifications()->latest()->paginate(15);
        return view('account.notifications', compact('notifications'));
    }

    public function markAsRead(CustomerNotification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update(['is_read' => true]);
        }
        return back();
    }

    public function markAllAsRead()
    {
        Auth::user()->customerNotifications()->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
