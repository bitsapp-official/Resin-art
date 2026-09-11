<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Rules\IndianPhoneNumber;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    private function getCart(): Cart
    {
        return Cart::current();
    }

    public function index()
    {
        $cart = $this->getCart();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index');
        }

        $cart->recalculateTotal();

        $savedAddresses = collect();
        $defaultAddress = null;
        if (Auth::check()) {
            $savedAddresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->get();
            $defaultAddress = $savedAddresses->first();
        }

        $cartItems = $cart->items;
        $subtotal = $cart->total;

        return view('checkout.index', compact('cart', 'cartItems', 'subtotal', 'savedAddresses', 'defaultAddress'));
    }

    public function process(Request $request)
    {
        $cart = $this->getCart();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index');
        }

        // Validate Checkout Fields (Strictly online payment via Stripe)
        $rules = [
            'email'            => ['required', 'email'],
            'full_name'        => ['required', 'string', 'max:255'],
            'phone'            => ['required', 'string', new IndianPhoneNumber()],
            'address_line_1'   => ['required', 'string', 'max:255'],
            'address_line_2'   => ['nullable', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:100'],
            'state'            => ['required', 'string', 'max:100'],
            'postal_code'      => ['required', 'string', 'max:20'],
            'country'          => ['required', 'string', 'max:100'],
            'payment_method'   => ['required', 'string', 'in:stripe,card,online'],
            'same_as_shipping' => ['nullable'],
        ];

        // Conditional validation if custom billing address is provided
        if (!$request->boolean('same_as_shipping', true)) {
            $rules['billing_full_name']      = ['required', 'string', 'max:255'];
            $rules['billing_address_line_1'] = ['required', 'string', 'max:255'];
            $rules['billing_address_line_2'] = ['nullable', 'string', 'max:255'];
            $rules['billing_city']           = ['required', 'string', 'max:100'];
            $rules['billing_state']          = ['required', 'string', 'max:100'];
            $rules['billing_postal_code']    = ['required', 'string', 'max:20'];
            $rules['billing_country']        = ['required', 'string', 'max:100'];
        }

        $request->validate($rules);

        // 1. Authoritative Server-Side Product Stock, Availability & Price Recalculation
        $authoritativeItems = [];
        $calculatedSubtotal = 0.0;

        foreach ($cart->items as $item) {
            $product = Product::published()->find($item->product_id);
            if (!$product || !$product->is_available) {
                return back()->with('error', "The piece '{$item->product_name}' is no longer available.");
            }

            // Auto-convert READY_TO_SHIP to MADE_TO_ORDER when stock is 0 or if requested quantity exceeds ready stock
            if ($product->inventory_type === 'READY_TO_SHIP') {
                if ($product->stock <= 0) {
                    $product->update([
                        'inventory_type' => 'MADE_TO_ORDER',
                        'stock'          => 0,
                    ]);
                } elseif ($item->quantity > $product->stock) {
                    // When order quantity exceeds ready stock, seamlessly convert to MADE_TO_ORDER
                    $product->update([
                        'inventory_type' => 'MADE_TO_ORDER',
                    ]);
                }
            }

            // Determine authoritative price from database
            $unitPrice = (float) $product->effective_price;
            $options = $item->options ?? [];
            if (!empty($options['size'])) {
                $sizeVariants = $product->attributes['size_variants'] ?? [];
                foreach ($sizeVariants as $variant) {
                    if (($variant['size'] ?? '') === $options['size'] && !empty($variant['price'])) {
                        $unitPrice = (float) $variant['price'];
                        break;
                    }
                }
            }

            // Sync item price if mismatched
            if (abs((float)$item->price - $unitPrice) > 0.001) {
                $item->price = $unitPrice;
                $item->save();
            }

            $lineSubtotal = round($unitPrice * $item->quantity, 2);
            $calculatedSubtotal += $lineSubtotal;

            $authoritativeItems[] = [
                'item'         => $item,
                'product'      => $product,
                'unit_price'   => $unitPrice,
                'quantity'     => $item->quantity,
                'line_total'   => $lineSubtotal,
                'options'      => $options,
            ];
        }

        $cart->total = $calculatedSubtotal;
        $cart->save();

        // 2. Authoritative Tax & Grand Total Computation
        $shippingFee = 0.00; // Complimentary atelier crated shipping
        $taxRate = floatval(SiteSetting::get('invoice_tax_rate', '5'));
        $showTax = (bool) SiteSetting::get('invoice_show_tax', '1');
        $tax = $showTax ? round($calculatedSubtotal * ($taxRate / 100), 2) : 0.00;
        $grandTotal = round($calculatedSubtotal + $shippingFee + $tax, 2);

        // 3. Determine Currency based on Stripe India rules
        // Domestic India customers must use INR.
        // International customers can also be charged in INR (or store currency), supported by Stripe.
        $currency = 'inr'; // Standard authoritative currency for Stripe India

        // Prepare shipping address snapshot (Physical Parcel Delivery)
        $shippingSnapshot = [
            'full_name'      => $request->full_name,
            'phone'          => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city'           => $request->city,
            'state'          => $request->state,
            'postal_code'    => $request->postal_code,
            'country'        => $request->country,
        ];

        // Prepare billing address snapshot (Tax Invoice & Bank Record)
        if ($request->boolean('same_as_shipping', true)) {
            $billingSnapshot = $shippingSnapshot;
        } else {
            $billingSnapshot = [
                'full_name'      => $request->billing_full_name,
                'phone'          => $request->phone,
                'address_line_1' => $request->billing_address_line_1,
                'address_line_2' => $request->billing_address_line_2,
                'city'           => $request->billing_city,
                'state'          => $request->billing_state,
                'postal_code'    => $request->billing_postal_code,
                'country'        => $request->billing_country,
            ];
        }

        // Persist/Update address in user's address book if requested
        if (Auth::check()) {
            $user = Auth::user();
            if ($request->filled('phone')) {
                $user->update(['phone' => $request->phone]);
            }

            if ($request->boolean('save_to_address_book', true) || $user->addresses()->count() === 0) {
                Address::updateOrCreate(
                    [
                        'user_id'        => $user->id,
                        'address_line_1' => $request->address_line_1,
                        'postal_code'    => $request->postal_code,
                    ],
                    [
                        'full_name'      => $request->full_name,
                        'phone'          => $request->phone,
                        'address_line_2' => $request->address_line_2,
                        'city'           => $request->city,
                        'state'          => $request->state,
                        'country'        => $request->country,
                        'type'           => 'shipping',
                        'is_default'     => $user->addresses()->count() === 0,
                    ]
                );
            }
        }

        // 4. Create Order and Line Items in Atomic Transaction (Status: PENDING_PAYMENT)
        $order = DB::transaction(function () use ($request, $calculatedSubtotal, $shippingFee, $tax, $grandTotal, $shippingSnapshot, $billingSnapshot, $authoritativeItems) {
            // Cancel previous abandoned PENDING_PAYMENT orders for this user to prevent clutter
            if (Auth::check()) {
                Order::where('user_id', Auth::id())
                    ->where('status', 'PENDING_PAYMENT')
                    ->where('payment_status', 'unpaid')
                    ->where('created_at', '>=', now()->subHours(2))
                    ->update([
                        'status'        => 'CANCELLED',
                        'cancel_reason' => 'Superseded by new checkout attempt.',
                    ]);
            }

            $orderReference = 'MR-' . date('Y') . '-' . strtoupper(Str::random(6));

            $order = Order::create([
                'order_reference'           => $orderReference,
                'user_id'                   => Auth::id(),
                'email'                     => Auth::check() ? Auth::user()->email : $request->email,
                'status'                    => 'PENDING_PAYMENT',
                'payment_status'            => 'unpaid',
                'payment_method'            => 'stripe',
                'payment_reference'         => null,
                'subtotal'                  => $calculatedSubtotal,
                'discount'                  => 0.00,
                'tax'                       => $tax,
                'shipping_fee'              => $shippingFee,
                'grand_total'               => $grandTotal,
                'shipping_address_snapshot' => $shippingSnapshot,
                'billing_address_snapshot'  => $billingSnapshot,
                'notes'                     => $request->notes,
            ]);

            foreach ($authoritativeItems as $authItem) {
                $prod = $authItem['product'];
                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $prod->id,
                    'product_name'     => $prod->name,
                    'sku'              => $prod->sku,
                    'unit_price'       => $authItem['unit_price'],
                    'quantity'         => $authItem['quantity'],
                    'subtotal'         => $authItem['line_total'],
                    'options'          => $authItem['options'],
                    'product_snapshot' => [
                        'name'           => $prod->name,
                        'sku'            => $prod->sku,
                        'images'         => $prod->images,
                        'inventory_type' => $prod->inventory_type,
                        'options'        => $authItem['options'],
                    ],
                ]);
            }

            return $order;
        });

        // Store reference in session for IDOR verification
        session(['checkout_order_ref' => $order->order_reference]);

        // 5. Create Stripe Checkout Session
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret) || str_starts_with($stripeSecret, 'sk_test_your_secret')) {
            Log::error("Stripe payment error: Stripe secret key is not properly configured in environment.");
            return back()->with('error', 'Payment gateway is temporarily unavailable. Please contact atelier concierge.');
        }

        try {
            $stripe = app()->bound(StripeClient::class)
                ? app(StripeClient::class)
                : new StripeClient($stripeSecret);

            // Build Stripe Line Items
            $lineItems = [];
            foreach ($authoritativeItems as $authItem) {
                $unitAmountPaise = intval(round($authItem['unit_price'] * 100));
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => [
                            'name'        => $authItem['product']->name,
                            'description' => "Maison Résine Handcrafted Artwork (SKU: " . ($authItem['product']->sku ?? 'RESIN') . ")",
                        ],
                        'unit_amount'  => $unitAmountPaise,
                    ],
                    'quantity'   => $authItem['quantity'],
                ];
            }

            // Add Tax Line Item if applicable
            if ($order->tax > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => $currency,
                        'product_data' => [
                            'name'        => 'Taxes & Documentation',
                            'description' => 'Mandatory preservation & tax documentation',
                        ],
                        'unit_amount'  => intval(round($order->tax * 100)),
                    ],
                    'quantity'   => 1,
                ];
            }

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'customer_email'       => $order->email,
                'client_reference_id'  => $order->order_reference,
                'metadata'             => [
                    'order_reference' => $order->order_reference,
                    'order_id'        => (string) $order->id,
                ],
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'success_url'          => route('checkout.confirmation', ['order' => $order->order_reference]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => route('checkout.cancel', ['order' => $order->order_reference]),
            ], [
                'idempotency_key' => 'checkout_sess_' . $order->order_reference,
            ]);

            // Save Stripe Session ID on order
            $order->update(['payment_reference' => $session->id]);

            // Redirect customer to Stripe hosted checkout page
            // Cart remains preserved in DB/session until payment confirmation!
            return redirect()->away($session->url);

        } catch (\Exception $e) {
            Log::error("Stripe Session Creation Failed for Order {$order->order_reference}: " . $e->getMessage());
            return back()->with('error', 'Unable to initiate secure payment. Please verify your connection or try again: ' . $e->getMessage());
        }
    }

    public function confirmation(string $orderReference, Request $request)
    {
        $order = Order::where('order_reference', $orderReference)->with('items.product')->firstOrFail();

        // IDOR Protection: verify access
        if (Auth::check()) {
            if ($order->user_id && $order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access to order.');
            }
        } else {
            $sessionOrderRef = session('checkout_order_ref');
            if ($sessionOrderRef !== $orderReference) {
                abort(403, 'Unauthorized access to order.');
            }
        }

        // If returned from Stripe with session_id, verify payment with Stripe API
        $sessionId = $request->query('session_id');
        $stripeSecret = config('services.stripe.secret');

        if ($sessionId && !empty($stripeSecret) && !str_starts_with($stripeSecret, 'sk_test_your_secret')) {
            try {
                $stripe = app()->bound(StripeClient::class)
                    ? app(StripeClient::class)
                    : new StripeClient($stripeSecret);
                $session = $stripe->checkout->sessions->retrieve($sessionId);

                if ($session && $session->payment_status === 'paid') {
                    $paymentRef = $session->payment_intent ?? $session->id;
                    OrderFulfillmentService::fulfill($order, $paymentRef, (array) $session);
                }
            } catch (\Exception $e) {
                Log::warning("Stripe Session Confirmation Check Warning for {$orderReference}: " . $e->getMessage());
            }
        }

        // Empty current user/guest cart once confirmed
        $cart = $this->getCart();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['total' => 0]);
        }

        // Clear session reference
        session()->forget('checkout_order_ref');

        return view('checkout.confirmation', compact('order'));
    }

    public function cancel(string $orderReference, Request $request)
    {
        $order = Order::where('order_reference', $orderReference)->first();

        if ($order) {
            // IDOR Protection
            if (Auth::check() && $order->user_id && $order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access.');
            }

            if ($order->payment_status === 'unpaid') {
                $order->update([
                    'status'        => 'CANCELLED',
                    'cancel_reason' => 'Customer cancelled checkout on payment page.',
                    'canceled_at'   => now(),
                ]);
            }
        }

        // Cart is intentionally preserved so user doesn't lose their selected pieces!
        return redirect()->route('checkout.index')->with('error', 'Payment was not completed. Your shopping bag has been safely preserved.');
    }
}
