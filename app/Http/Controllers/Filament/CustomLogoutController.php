<?php

namespace App\Http\Controllers\Filament;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Support\Facades\Auth;

class CustomLogoutController
{
    public function __invoke(): LogoutResponse
    {
        Filament::auth()->logout();

        // If a customer is currently authenticated on the website (web guard),
        // preserve the customer's session so logging out of the Admin panel
        // does NOT kick the customer out of their storefront session.
        if (Auth::guard('web')->check()) {
            session()->regenerate(false);
            session()->regenerateToken();
        } else {
            session()->invalidate();
            session()->regenerateToken();
        }

        return app(LogoutResponse::class);
    }
}
