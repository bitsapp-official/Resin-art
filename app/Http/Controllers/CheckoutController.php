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
    private function getCart(): ?Cart
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->with('items.product')->first();
        }

        $sessionId = session()->getId();
        return Cart::where('session_id', $sessionId)->whereNull('user_id')->with('items.product')->first();
    }

    public function index()
    {
        $cart = $this->getCart();

        if (!$cart || $cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
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
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        // Validate Checkout Fields (Strictly online payment via Stripe - No Cash on Delivery)
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

        $cart->recalculateTotal();

        // 1. Re-validate Product stock and availability server-side
        foreach ($cart->items as $item) {
            $product = Product::published()->find($item->product_id);
            if (!$product || !$product->is_available) {
                return back()->with('error', "The piece '{$item->product->name}' is no longer available.");
            }

            if ($product->inventory_type === 'READY_TO_SHIP' && $item->quantity > $product->stock) {
                return back()->with('error', "Only {$product->stock} unit(s) of '{$product->name}' remain in stock.");
            }
        }

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

        // Create Order and Line Items in Atomic Transaction
        $order = DB::transaction(function () use ($request, $cart, $shippingSnapshot, $billingSnapshot) {
            $orderReference = 'MR-' . date('Y') . '-' . strtoupper(Str::random(6));

            $subtotal = $cart->total;
            $shippingFee = 0.00; // Complimentary atelier shipping
            $taxRate = floatval(SiteSetting::get('invoice_tax_rate', '5'));
            $showTax = (bool) SiteSetting::get('invoice_show_tax', '1');
            $tax = $showTax ? round($subtotal * ($taxRate / 100), 2) : 0.00;
            $grandTotal = $subtotal + $shippingFee + $tax;

            // Create Order in PENDING_PAYMENT status
            $order = Order::create([
                'order_reference'           => $orderReference,
                'user_id'                   => Auth::id(),
                'email'                     => Auth::check() ? Auth::user()->email : $request->email,
                'status'                    => 'PENDING_PAYMENT',
                'payment_status'            => 'unpaid',
                'payment_method'            => 'stripe',
                'payment_reference'         => null,
                'subtotal'                  => $subtotal,
                'discount'                  => 0.00,
                'tax'                       => $tax,
                'shipping_fee'              => $shippingFee,
                'grand_total'               => $grandTotal,
                'shipping_address_snapshot' => $shippingSnapshot,
                'billing_address_snapshot'  => $billingSnapshot,
                'notes'                     => $request->notes,
            ]);

            // Create Order Items Snapshot (Inventory will be deducted upon confirmed payment)
            foreach ($cart->items as $item) {
                $product = Product::find($item->product_id);

                OrderItem::create([
                    'order_id'         => $order->id,
                    'product_id'       => $product?->id,
                    'product_name'     => $product ? $product->name : $item->product_name,
                    'sku'              => $product?->sku,
                    'unit_price'       => $item->price,
                    'quantity'         => $item->quantity,
                    'subtotal'         => $item->price * $item->quantity,
                    'options'          => $item->options,
                    'product_snapshot' => [
                        'name'           => $product?->name,
                        'sku'            => $product?->sku,
                        'images'         => $product?->images,
                        'inventory_type' => $product?->inventory_type,
                        'options'        => $item->options,
                    ],
                ]);
            }

            return $order;
        });

        // Check if real Stripe credentials are configured
        $stripeSecret = config('services.stripe.secret');
        $isLiveConfigured = !empty($stripeSecret) && !str_starts_with($stripeSecret, 'sk_test_your_secret');

        if ($isLiveConfigured) {
            try {
                $stripe = new StripeClient($stripeSecret);
                $currency = strtolower(config('services.stripe.currency', 'inr'));

                // Build Stripe Line Items
                $lineItems = [];
                foreach ($cart->items as $item) {
                    $unitAmountPaise = intval(round($item->price * 100));
                    $lineItems[] = [
                        'price_data' => [
                            'currency'     => $currency,
                            'product_data' => [
                                'name'        => $item->product?->name ?? $item->product_name,
                                'description' => "Maison Résine Handcrafted Artwork (SKU: " . ($item->product?->sku ?? 'RESIN') . ")",
                            ],
                            'unit_amount'  => $unitAmountPaise,
                        ],
                        'quantity'   => $item->quantity,
                    ];
                }

                // Add GST Tax Line Item if applicable
                if ($order->tax > 0) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency'     => $currency,
                            'product_data' => [
                                'name'        => 'GST & Preservation Tax',
                                'description' => 'Mandatory tax & certificate documentation',
                            ],
                            'unit_amount'  => intval(round($order->tax * 100)),
                        ],
                        'quantity'   => 1,
                    ];
                }

                // Create Hosted Stripe Checkout Session
                $session = $stripe->checkout->sessions->create([
                    'payment_method_types' => ['card'],
                    'customer_email'       => $order->email,
                    'client_reference_id'  => $order->order_reference,
                    'metadata'             => [
                        'order_reference' => $order->order_reference,
                        'order_id'        => $order->id,
                    ],
                    'line_items'           => $lineItems,
                    'mode'                 => 'payment',
                    'success_url'          => route('checkout.confirmation', ['order' => $order->order_reference]) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'           => route('checkout.cancel', ['order' => $order->order_reference]),
                ]);

                // Store preliminary session ID on Order
                $order->update(['payment_reference' => $session->id]);

                // Redirect client to Stripe Hosted Secure Payment Page
                return redirect()->away($session->url);

            } catch (\Exception $e) {
                Log::error("Stripe Session Creation Failed: " . $e->getMessage());
                return back()->with('error', 'Unable to initiate secure Stripe checkout. Please verify connection and try again: ' . $e->getMessage());
            }
        }

        // Fallback / Sandbox Demo Mode (When Stripe keys are placeholder in .env)
        // Automatically fulfills the order so testing works without crashing
        $simulatedRef = 'STRIPE-TEST-' . strtoupper(Str::random(10));
        OrderFulfillmentService::fulfill($order, $simulatedRef, [
            'mode' => 'sandbox_simulation',
            'note' => 'To activate live Stripe checkout, add your STRIPE_KEY and STRIPE_SECRET in .env',
        ]);

        // Empty Cart
        $cart->items()->delete();
        $cart->update(['total' => 0]);

        return redirect()->route('checkout.confirmation', ['order' => $order->order_reference])
            ->with('message', 'Stripe Sandbox Simulation: Payment recorded. Please add your real Stripe API keys in .env to connect live Stripe checkout.');
    }

    public function confirmation(string $orderReference, Request $request)
    {
        $order = Order::where('order_reference', $orderReference)->with('items.product')->firstOrFail();

        // If returned from Stripe with session_id, verify payment with Stripe API
        $sessionId = $request->query('session_id');
        $stripeSecret = config('services.stripe.secret');

        if ($sessionId && !empty($stripeSecret) && !str_starts_with($stripeSecret, 'sk_test_your_secret')) {
            try {
                $stripe = new StripeClient($stripeSecret);
                $session = $stripe->checkout->sessions->retrieve($sessionId);

                if ($session && $session->payment_status === 'paid') {
                    $paymentRef = $session->payment_intent ?? $session->id;
                    OrderFulfillmentService::fulfill($order, $paymentRef, (array) $session);
                }
            } catch (\Exception $e) {
                Log::warning("Stripe Session Confirmation Check Warning: " . $e->getMessage());
            }
        }

        // Empty current user/guest cart once confirmed
        $cart = $this->getCart();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['total' => 0]);
        }

        return view('checkout.confirmation', compact('order'));
    }

    public function cancel(string $orderReference, Request $request)
    {
        $order = Order::where('order_reference', $orderReference)->first();

        if ($order && $order->payment_status === 'unpaid') {
            $order->update([
                'status'        => 'CANCELLED',
                'cancel_reason' => 'Customer cancelled checkout on Stripe page',
                'canceled_at'   => now(),
            ]);
        }

        return redirect()->route('checkout.index')->with('error', 'Stripe payment was interrupted or cancelled. Your shopping bag has been saved.');
    }
}
