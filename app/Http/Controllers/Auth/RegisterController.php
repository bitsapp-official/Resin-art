<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\GuestSessionMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $throttleKey = 'register|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ["Too many registration attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        UserSetting::create(['user_id' => $user->id]);

        \App\Models\CustomerNotification::create([
            'user_id' => $user->id,
            'title'   => 'Welcome to Maison Résine',
            'message' => 'Your atelier account is now active. Explore our handcrafted resin creations, track upcoming orders, and commission bespoke artwork.',
            'type'    => 'welcome',
            'is_read' => false,
        ]);

        // Capture guest state BEFORE login and session regeneration
        $guestSessionId = session()->getId();
        $guestWishlist = session('guest_wishlist', []);

        Auth::login($user);
        $request->session()->regenerate();

        // Senior-level migration: merge guest cart, wishlist, and recently viewed into user account
        GuestSessionMigrationService::migrate($user, $guestSessionId, $guestWishlist);

        // Send email verification notification immediately
        $user->sendEmailVerificationNotification();

        // Redirect to email verification notice (verified middleware will block dashboard until verified)
        return redirect()->route('verification.notice');
    }
}
