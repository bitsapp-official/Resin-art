<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\GuestSessionMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard');
        }

        if ($request->has('redirect')) {
            $redirectUrl = $request->get('redirect');
            // Ensure safe internal redirect
            if (Str::startsWith($redirectUrl, '/')) {
                session(['url.intended' => $redirectUrl]);
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Capture guest state BEFORE session regeneration
        $guestSessionId = session()->getId();
        $guestWishlist = session('guest_wishlist', []);

        $request->session()->regenerate();

        // Senior-level migration: merge guest cart, wishlist, and recently viewed into user account
        GuestSessionMigrationService::migrate(Auth::user(), $guestSessionId, $guestWishlist);

        return redirect()->intended(route('account.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        // If an admin is currently authenticated in the Filament admin panel (admin guard),
        // preserve the admin session so logging out of the website dashboard
        // does NOT kick the administrator out of the Admin panel!
        if (Auth::guard('admin')->check()) {
            $request->session()->regenerate(false);
            $request->session()->regenerateToken();
        } else {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login');
    }
}
