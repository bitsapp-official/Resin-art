<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RecentlyViewedController extends Controller
{
    public function index()
    {
        $recentlyViewed = Auth::user()->recentlyViewed()
            ->with('product')
            ->orderBy('viewed_at', 'desc')
            ->take(12)
            ->get();

        return view('account.recently-viewed', compact('recentlyViewed'));
    }
}
