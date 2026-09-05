<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
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
        $request->session()->regenerate();

        // Safe cart merging from guest session to authenticated user cart
        $guestSessionId = session()->getId();
        $guestCart = Cart::where('session_id', $guestSessionId)->whereNull('user_id')->first();
        if ($guestCart && $guestCart->items->count() > 0) {
            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            foreach ($guestCart->items as $item) {
                $existingItem = CartItem::where('cart_id', $userCart->id)
                    ->where('product_id', $item->product_id)
                    ->first();
                if ($existingItem) {
                    $existingItem->quantity += $item->quantity;
                    $existingItem->save();
                } else {
                    CartItem::create([
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
