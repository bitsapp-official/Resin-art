<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserNotBlocked
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->is_blocked) {
            Auth::guard('web')->logout();

            if (Auth::guard('admin')->check()) {
                $request->session()->regenerate(false);
                $request->session()->regenerateToken();
            } else {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact support for assistance.',
            ]);
        }

        return $next($request);
    }
}
