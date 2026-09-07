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

        // Senior-level migration: merge guest cart, wishlist, and recently viewed into user account
        GuestSessionMigrationService::migrate($user, $guestSessionId, $guestWishlist);

        // Send email verification notification
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Email verification notice failed: ' . $e->getMessage());
        }

        // If user was heading to checkout or an intended page, proceed directly there
        if (session()->has('url.intended')) {
            return redirect()->intended(route('checkout.index'));
        }

        // Redirect to email verification notice for general signups
        return redirect()->route('verification.notice');
    }
}
