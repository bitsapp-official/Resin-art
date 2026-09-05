<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OrderTrackingController extends Controller
{
    /**
     * GET /track-order — Display tracking page and process tracking query if parameters exist.
     */
    public function index(Request $request)
    {
        $order = null;
        $searched = false;
        $error = Session::pull('tracking_error');

        // 1. Check if result was stored in session from POST submit
        $sessionData = Session::pull('tracking_result');
        if ($sessionData) {
            $searched = true;
            $reference = trim($sessionData['order_reference']);
            $email = trim($sessionData['email']);

            $order = Order::where(function ($query) use ($reference) {
                    $query->whereRaw('LOWER(order_reference) = ?', [strtolower($reference)])
                          ->orWhere('id', $reference);
                })
                ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                ->with(['items.product'])
                ->first();
        }

        // 2. Direct GET tracking (e.g. from URL params or direct link)
        $refParam = trim($request->query('order_reference', $request->query('order_id', $request->query('order', $request->query('reference', '')))));
        $emailParam = trim($request->query('email', ''));

        if (!$order && !empty($refParam)) {
            if (!empty($emailParam)) {
                $searched = true;
                $order = Order::where(function ($query) use ($refParam) {
                        $query->whereRaw('LOWER(order_reference) = ?', [strtolower($refParam)])
                              ->orWhere('id', $refParam);
                    })
                    ->whereRaw('LOWER(email) = ?', [strtolower($emailParam)])
                    ->with(['items.product'])
                    ->first();
            } elseif (Auth::check()) {
                $searched = true;
                $user = Auth::user();
                $order = Order::where(function ($query) use ($refParam) {
                        $query->whereRaw('LOWER(order_reference) = ?', [strtolower($refParam)])
                              ->orWhere('id', $refParam);
                    })
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                              ->orWhereRaw('LOWER(email) = ?', [strtolower($user->email)]);
                    })
                    ->with(['items.product'])
                    ->first();
            }
        }

        return view('tracking.index', compact('order', 'searched', 'error'));
    }

    /**
     * POST /track-order — Process form submission securely.
     */
    public function search(Request $request)
    {
        $request->validate([
            'order_reference' => ['required', 'string', 'max:50'],
            'email'           => ['required', 'email', 'max:191'],
        ], [
            'order_reference.required' => 'Please enter your order number.',
            'email.required'           => 'Please enter your email address.',
            'email.email'              => 'Please enter a valid email address.',
        ]);

        $reference = trim($request->order_reference);
        $email     = trim($request->email);

        $order = Order::where(function ($query) use ($reference) {
                $query->whereRaw('LOWER(order_reference) = ?', [strtolower($reference)])
                      ->orWhere('id', $reference);
            })
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();

        if (!$order) {
            $safeRef = e($reference);
            $safeEmail = e($email);
            return redirect()->route('tracking.index')
                ->withInput()
                ->with('tracking_error', "No order found for reference <strong class=\"font-mono\">{$safeRef}</strong> and email <strong class=\"font-mono\">{$safeEmail}</strong>. Please check your order details.");
        }

        Session::put('tracking_result', [
            'order_reference' => $order->order_reference,
            'email'           => $email,
        ]);

        return redirect()->route('tracking.index');
    }
}
