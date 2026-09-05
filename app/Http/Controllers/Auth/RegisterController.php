<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
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

        Auth::login($user);
        $request->session()->regenerate();

        // Send email verification notification immediately
        $user->sendEmailVerificationNotification();

        // Safe cart merging from guest session to authenticated user cart
        $guestSessionId = session()->getId();
        $guestCart = \App\Models\Cart::where('session_id', $guestSessionId)->whereNull('user_id')->first();
        if ($guestCart && $guestCart->items->count() > 0) {
            $userCart = \App\Models\Cart::firstOrCreate(['user_id' => Auth::id()]);
            foreach ($guestCart->items as $item) {
                $existingItem = \App\Models\CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($existingItem) {
                    $existingItem->quantity += $item->quantity;
                    $existingItem->save();
                } else {
                    \App\Models\CartItem::create([
                        'cart_id' => $userCart->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ]);
                }
            }
            $userCart->recalculateTotal();
            $guestCart->delete();
        }

        // Redirect to email verification notice (verified middleware will block dashboard until verified)
        return redirect()->route('verification.notice');
    }
}
