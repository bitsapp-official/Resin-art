<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountDeletionController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password entered for account deletion.']);
        }

        Auth::guard('web')->logout();

        if (Auth::guard('admin')->check()) {
            $request->session()->regenerate(false);
            $request->session()->regenerateToken();
        } else {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Preserve financial orders by nullifying user_id before user deletion if needed
        $user->orders()->update(['user_id' => null]);
        $user->delete();

        return redirect()->route('shop.index')->with('success', 'Your account has been deleted.');
    }
}
