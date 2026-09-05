<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $throttleKey = 'forgot-password|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ["Too many password reset attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey);

        // Don't reveal if user exists for security
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If an account exists with that email, a password reset link has been sent.');
    }
}
