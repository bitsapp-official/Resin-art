<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CustomRequest;
use Illuminate\Support\Facades\Auth;

class CustomRequestAccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Fetch user's custom requests (Newest first, server-side paginated)
        $customRequests = CustomRequest::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            })
            ->with(['images', 'invoice'])
            ->latest('id')
            ->paginate(5);

        return view('account.custom-requests', compact('customRequests'));
    }
}
