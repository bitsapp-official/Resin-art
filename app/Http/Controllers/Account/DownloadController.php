<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('account.downloads', compact('orders'));
    }
}
