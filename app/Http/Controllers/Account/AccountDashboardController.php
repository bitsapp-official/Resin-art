<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CustomRequest;
use Illuminate\Support\Facades\Auth;

class AccountDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $ordersCount = $user->orders()->count();
        $wishlistCount = $user->wishlists()->count();
        $addressCount = $user->addresses()->count();
        $unreadNotificationsCount = $user->customerNotifications()->where('is_read', false)->count();

        $latestOrder = $user->orders()->latest()->with('items')->first();

        $customRequestsCount = CustomRequest::where('user_id', $user->id)->orWhere('email', $user->email)->count();
        
        // Fetch only genuinely ACTIVE in-progress custom requests (exclude delivered, declined, expired)
        $latestCustomRequest = CustomRequest::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->whereNotIn('status', [
                \App\Enums\CustomRequestStatus::DELIVERED->value,
                \App\Enums\CustomRequestStatus::DECLINED->value,
                \App\Enums\CustomRequestStatus::EXPIRED->value,
            ])
            ->latest('submitted_at')
            ->latest('created_at')
            ->first();

        $notifications = $user->customerNotifications()->latest()->take(4)->get();
        $recentlyViewedCount = $user->recentlyViewed()->has('product')->count();
        $recentlyViewedProducts = $user->recentlyViewed()->has('product')->with('product')->latest('viewed_at')->take(4)->get();

        return view('account.dashboard', compact(
            'user',
            'ordersCount',
            'wishlistCount',
            'addressCount',
            'unreadNotificationsCount',
            'latestOrder',
            'customRequestsCount',
            'latestCustomRequest',
            'notifications',
            'recentlyViewedCount',
            'recentlyViewedProducts'
        ));
    }
}
