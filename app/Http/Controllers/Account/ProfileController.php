<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Rules\IndianPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('account.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', new IndianPhoneNumber()],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}

