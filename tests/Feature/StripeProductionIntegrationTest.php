<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Services\OrderFulfillmentService;
use App\Services\StripeRefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;
use Stripe\WebhookSignature;
use Tests\TestCase;

class StripeProductionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Product $product;
    protected string $testWebhookSecret = 'whsec_test_secret_key_1234567890abcdef';
    protected string $testStripeSecret = 'sk_test_mock_secret_key_12345';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\EcommerceSeeder::class);

        $this->user = User::factory()->create([
            'email'             => 'patron@example.com',
            'name'              => 'Patron User',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->otherUser = User::factory()->create([
            'email'             => 'stranger@example.com',
            'name'              => 'Stranger User',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->product = Product::published()
            ->where('inventory_type', 'READY_TO_SHIP')
            ->where('stock', '>', 5)
            ->firstOrFail();

        config([
            'services.stripe.secret'         => $this->testStripeSecret,
            'services.stripe.webhook_secret' => $this->testWebhookSecret,
            'services.stripe.currency'       => 'inr',
        ]);
    }

    private function setupUserCart(User $user, Product $product, int $quantity = 1, ?float $price = null): Cart
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cart->items()->delete();

        CartItem::create([
            'cart_id'      => $cart->id,
            'product_id'   => $product->id,
            'quantity'     => $quantity,
            'price'        => $price ?? $product->effective_price,
            'options'      => [],
        ]);

        $cart->recalculateTotal();
        return $cart;
    }

    private function validCheckoutData(array $overrides = []): array
    {
        return array_merge([
            'full_name'        => 'Aarav Sharma',
            'email'            => $this->user->email,
            'phone'            => '+91 98201 45678',
            'address_line_1'   => 'Flat 502, Oceanic Towers, Marine Drive',
            'address_line_2'   => 'Near Nariman Point',
            'city'             => 'Mumbai',
            'state'            => 'Maharashtra',
            'postal_code'      => '400021',
            'country'          => 'India',
            'payment_method'   => 'card',
            'same_as_shipping' => '1',
        ], $overrides);
    }

    /**
     * Helper to mock StripeClient for Checkout Sessions
     */
    private function mockStripeCheckoutSession(string $sessionId = 'cs_test_mock_session_123', ?array &$capturedParams = null): void
    {
        $mockSessions = \Mockery::mock();
        $mockSessions->shouldReceive('create')
            ->andReturnUsing(function ($params, $opts = null) use ($sessionId, &$capturedParams) {
                $capturedParams = $params;
                return (object) [
                    'id'  => $sessionId,
                    'url' => "https://checkout.stripe.com/pay/{$sessionId}",
                ];
            });

        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->checkout = (object) ['sessions' => $mockSessions];

        $this->app->instance(StripeClient::class, $mockStripe);
    }

    /**
     * 1. Domestic India Card (INR): Checkout creates session with currency: inr.
     */
    public function test_01_domestic_india_checkout_enforces_inr_currency(): void
    {
        $this->actingAs($this->user);
        $this->setupUserCart($this->user, $this->product, 1);

        $capturedParams = null;
        $this->mockStripeCheckoutSession('cs_test_india_123', $capturedParams);

        $response = $this->post(route('checkout.process'), $this->validCheckoutData([
            'country' => 'India',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_india_123');

        $this->assertNotNull($capturedParams);
        $this->assertIsArray($capturedParams['line_items']);
        $this->assertEquals('inr', $capturedParams['line_items'][0]['price_data']['currency']);
    }

    /**
     * 2. International Card: Checkout passes INR authoritative currency to Stripe.
     */
    public function test_02_international_checkout_uses_inr_or_store_currency(): void
    {
        $this->actingAs($this->user);
        $this->setupUserCart($this->user, $this->product, 1);

        $capturedParams = null;
        $this->mockStripeCheckoutSession('cs_test_intl_123', $capturedParams);

        $response = $this->post(route('checkout.process'), $this->validCheckoutData([
            'country'     => 'United States',
            'city'        => 'New York',
            'state'       => 'NY',
            'postal_code' => '10001',
        ]));

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_intl_123');

        $this->assertNotNull($capturedParams);
        $this->assertEquals('inr', $capturedParams['line_items'][0]['price_data']['currency']);
    }

    /**
     * 3. Currency Mismatch Protection: Server locks currency, client cannot alter it.
     */
    public function test_03_client_cannot_tamper_currency(): void
    {
        $this->actingAs($this->user);
        $this->setupUserCart($this->user, $this->product, 1);

        $capturedParams = null;
        $this->mockStripeCheckoutSession('cs_test_tamper_curr', $capturedParams);

        // Attempt injecting malicious or altered currency in request
        $response = $this->post(route('checkout.process'), $this->validCheckoutData([
            'currency' => 'USD',
            'amount'   => '1.00',
        ]));

        $response->assertRedirect();
        $this->assertNotNull($capturedParams);
        $this->assertEquals('inr', $capturedParams['line_items'][0]['price_data']['currency']);
    }

    /**
     * 4. Amount Tampering Rejection: Server-side recalculation overrides manipulated cart prices.
     */
    public function test_04_server_authoritative_price_recalculation_rejects_client_tampering(): void
    {
        $this->actingAs($this->user);
        // User puts item in cart with manipulated price 5.00 instead of authentic price
        $authenticPrice = (float) $this->product->effective_price;
        $cart = $this->setupUserCart($this->user, $this->product, 1, 5.00);

        // Intentionally tamper DB directly before calling checkout
        CartItem::where('cart_id', $cart->id)->update(['price' => 5.00]);

        $capturedParams = null;
        $this->mockStripeCheckoutSession('cs_test_tamper_amount', $capturedParams);

        $response = $this->post(route('checkout.process'), $this->validCheckoutData());
        $response->assertRedirect();

        // Check captured line item price sent to Stripe
        $this->assertNotNull($capturedParams);
        $expectedPaise = intval(round($authenticPrice * 100));
        $this->assertEquals($expectedPaise, $capturedParams['line_items'][0]['price_data']['unit_amount']);

        // Check order in DB has authoritative grand_total
        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertGreaterThan(5.00, (float) $order->grand_total);
    }

    /**
     * 5. Missing / Invalid Stripe Keys: Graceful error without fake simulation auto-fulfillment.
     */
    public function test_05_missing_or_invalid_stripe_keys_gracefully_fails_without_auto_fulfillment(): void
    {
        $this->actingAs($this->user);
        $this->setupUserCart($this->user, $this->product, 1);

        // Wipe stripe secret key
        config(['services.stripe.secret' => '']);

        $response = $this->post(route('checkout.process'), $this->validCheckoutData());

        // Must redirect back with session error
        $response->assertSessionHas('error');

        // Order must NOT be marked paid or fulfilled
        $paidOrders = Order::where('user_id', $this->user->id)->where('payment_status', 'paid')->count();
        $this->assertEquals(0, $paidOrders);
    }

    /**
     * 6. Cart Retention on Stripe Cancel: Order marked CANCELLED, cart items preserved.
     */
    public function test_06_cart_retained_when_customer_cancels_stripe_checkout(): void
    {
        $this->actingAs($this->user);
        $cart = $this->setupUserCart($this->user, $this->product, 2);

        $order = Order::create([
            'order_reference'           => 'MR-2026-CANCEL1',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 1000,
            'grand_total'               => 1050,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        $response = $this->get(route('checkout.cancel', ['order' => $order->order_reference]));
        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('CANCELLED', $order->status);

        // Cart items must still be intact!
        $cart->refresh();
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }

    /**
     * 7. Cart Retention on Payment Failure: Order marked failed, user cart preserved.
     */
    public function test_07_cart_retained_on_payment_intent_failed_webhook(): void
    {
        $this->actingAs($this->user);
        $cart = $this->setupUserCart($this->user, $this->product, 1);

        $order = Order::create([
            'order_reference'           => 'MR-2026-FAIL01',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 5000,
            'grand_total'               => 5250,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        $payload = json_encode([
            'id'   => 'evt_test_pi_failed_1',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id'                 => 'pi_failed_123',
                    'metadata'           => ['order_reference' => $order->order_reference],
                    'last_payment_error' => ['message' => 'Card was declined by bank issuer'],
                ],
            ],
        ]);

        $sigHeader = WebhookSignature::generateSignatureHeader($payload, $this->testWebhookSecret, time());

        $response = $this->call(
            'POST',
            route('webhook.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $sigHeader,
                'CONTENT_TYPE'          => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('failed', $order->payment_status);

        // Cart remains intact
        $cart->refresh();
        $this->assertEquals(1, $cart->items()->count());
    }

    /**
     * 8. Valid Webhook Signature: checkout.session.completed verifies signature and fulfills order.
     */
    public function test_08_valid_webhook_signature_fulfills_order(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-SIGOK1',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 8000,
            'grand_total'               => 8400,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        $payload = json_encode([
            'id'   => 'evt_test_sig_ok_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id'                  => 'cs_test_sig_ok_123',
                    'payment_intent'      => 'pi_test_sig_ok_456',
                    'client_reference_id' => $order->order_reference,
                    'metadata'            => ['order_reference' => $order->order_reference],
                ],
            ],
        ]);

        $sigHeader = WebhookSignature::generateSignatureHeader($payload, $this->testWebhookSecret, time());

        $response = $this->call(
            'POST',
            route('webhook.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $sigHeader,
                'CONTENT_TYPE'          => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('pi_test_sig_ok_456', $order->payment_reference);
    }

    /**
     * 9. Invalid Webhook Signature: Returns HTTP 400 Bad Request.
     */
    public function test_09_invalid_webhook_signature_returns_400(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-INVSIG',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 5000,
            'grand_total'               => 5250,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        $payload = json_encode([
            'id'   => 'evt_test_inv_sig_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['client_reference_id' => $order->order_reference]],
        ]);

        $invalidSigHeader = 't=' . time() . ',v1=invalid_signature_hash_xyz';

        $response = $this->call(
            'POST',
            route('webhook.stripe'),
            [],
            [],
            [],
            [
                'HTTP_STRIPE_SIGNATURE' => $invalidSigHeader,
                'CONTENT_TYPE'          => 'application/json',
            ],
            $payload
        );

        $response->assertStatus(400);

        $order->refresh();
        $this->assertEquals('PENDING_PAYMENT', $order->status);
        $this->assertEquals('unpaid', $order->payment_status);
    }

    /**
     * 10. Webhook Idempotency: Duplicate webhook delivery returns HTTP 200 without duplicate processing.
     */
    public function test_10_webhook_idempotency_prevents_duplicate_processing(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-IDEMP1',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 6000,
            'grand_total'               => 6300,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        $eventId = 'evt_test_dedup_999';
        $payload = json_encode([
            'id'   => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id'             => 'cs_test_dedup_1',
                    'payment_intent' => 'pi_test_dedup_1',
                    'metadata'       => ['order_reference' => $order->order_reference],
                ],
            ],
        ]);

        $sigHeader = WebhookSignature::generateSignatureHeader($payload, $this->testWebhookSecret, time());

        // First delivery
        $res1 = $this->call('POST', route('webhook.stripe'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sigHeader,
            'CONTENT_TYPE'          => 'application/json',
        ], $payload);

        $res1->assertStatus(200);
        $res1->assertJson(['status' => 'success']);

        // Second duplicate delivery with exact same event ID
        $res2 = $this->call('POST', route('webhook.stripe'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $sigHeader,
            'CONTENT_TYPE'          => 'application/json',
        ], $payload);

        $res2->assertStatus(200);
        $res2->assertJson(['status' => 'already_processed']);

        // Assert record exists in stripe_webhook_events exactly once
        $this->assertEquals(1, StripeWebhookEvent::where('stripe_event_id', $eventId)->count());
    }

    /**
     * 11. Row Locking & Concurrency: Simultaneous fulfill calls fulfill strictly once.
     */
    public function test_11_row_locking_concurrent_webhook_and_redirect_sync_fulfill_strictly_once(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-CONCURR',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 7000,
            'grand_total'               => 7350,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        // Call fulfill consecutively simulating parallel webhook + redirect
        $first = OrderFulfillmentService::fulfill($order, 'pi_simultaneous_1');
        $second = OrderFulfillmentService::fulfill($order, 'pi_simultaneous_1');

        $this->assertEquals(Order::STATUS_CONFIRMED, $first->status);
        $this->assertEquals(Order::STATUS_CONFIRMED, $second->status);

        // Payments table should only have 1 payment record
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());
    }

    /**
     * 12. Single Inventory Deduction: Stock is decremented only once across multiple fulfillments.
     */
    public function test_12_single_inventory_decrement_for_ready_to_ship(): void
    {
        $product = Product::create([
            'name'           => 'Unique Resin Clock',
            'slug'           => 'unique-resin-clock-' . uniqid(),
            'sku'            => 'CLK-' . uniqid(),
            'price'          => 12000,
            'stock'          => 10,
            'inventory_type' => 'READY_TO_SHIP',
            'status'         => 'active',
            'is_featured'    => false,
        ]);

        $order = Order::create([
            'order_reference'           => 'MR-2026-STOCKDEC',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 24000,
            'grand_total'               => 25200,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'quantity'     => 2,
            'unit_price'   => 12000,
            'subtotal'     => 24000,
        ]);

        // Fulfill 1st time
        OrderFulfillmentService::fulfill($order, 'pi_stock_1');
        $product->refresh();
        $this->assertEquals(8, $product->stock); // 10 - 2

        // Redundant fulfillment call
        OrderFulfillmentService::fulfill($order, 'pi_stock_1');
        $product->refresh();
        // Must stay 8, NOT decrement again to 6!
        $this->assertEquals(8, $product->stock);
    }

    /**
     * 13. Out of Stock Auto-converts to MADE_TO_ORDER when stock reaches 0.
     */
    public function test_13_out_of_stock_auto_converts_to_made_to_order(): void
    {
        $product = Product::create([
            'name'           => 'Limited Resin Ocean Board',
            'slug'           => 'limited-board-' . uniqid(),
            'sku'            => 'BRD-' . uniqid(),
            'price'          => 8000,
            'stock'          => 2,
            'inventory_type' => 'READY_TO_SHIP',
            'status'         => 'active',
            'is_featured'    => false,
        ]);

        $order = Order::create([
            'order_reference'           => 'MR-2026-MTO1',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 16000,
            'grand_total'               => 16800,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'quantity'     => 2,
            'unit_price'   => 8000,
            'subtotal'     => 16000,
        ]);

        OrderFulfillmentService::fulfill($order, 'pi_mto_test_1');

        $product->refresh();
        $this->assertEquals(0, $product->stock);
        $this->assertEquals('MADE_TO_ORDER', $product->inventory_type);
    }

    /**
     * 14. Refund Processing: StripeRefundService calls Stripe API and records stripe_refund_id.
     */
    public function test_14_stripe_refund_service_executes_refund_and_records_id(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-REFUND1',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => Order::STATUS_CONFIRMED,
            'payment_status'            => 'paid',
            'payment_method'            => 'stripe',
            'payment_reference'         => 'pi_live_test_refund_123',
            'subtotal'                  => 5000,
            'grand_total'               => 5250,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'provider'          => 'stripe',
            'payment_reference' => 'pi_live_test_refund_123',
            'amount'            => 5250,
            'currency'          => 'inr',
            'status'            => 'successful',
        ]);

        $refundReq = RefundRequest::create([
            'order_id' => $order->id,
            'user_id'  => $this->user->id,
            'amount'   => 5250,
            'reason'   => 'Customer changed mind',
            'status'   => 'REQUESTED',
        ]);

        // Mock StripeClient refunds->create
        $mockRefunds = \Mockery::mock();
        $mockRefunds->shouldReceive('create')
            ->once()
            ->andReturn((object) [
                'id'     => 're_mock_refund_abc123',
                'status' => 'succeeded',
            ]);

        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->refunds = $mockRefunds;
        $this->app->instance(StripeClient::class, $mockStripe);

        // Sub-test: Attempting refund without admin approval MUST throw an exception
        try {
            StripeRefundService::processRefund($refundReq);
            $this->fail("Expected Exception when processing refund without admin approval.");
        } catch (\Exception $e) {
            $this->assertStringContainsString("must be APPROVED by Admin first", $e->getMessage());
        }

        // Admin approves the refund request
        $refundReq->update(['status' => 'APPROVED']);

        // Now process refund succeeds
        $res = StripeRefundService::processRefund($refundReq);

        $this->assertTrue($res['success']);
        $this->assertEquals('re_mock_refund_abc123', $res['stripe_refund_id']);

        $refundReq->refresh();
        $this->assertEquals('COMPLETED', $refundReq->status);
        $this->assertEquals('re_mock_refund_abc123', $refundReq->stripe_refund_id);
    }

    /**
     * 15. Full Refund Updates Order Status: payment_status => refunded and status => CANCELLED.
     */
    public function test_15_full_refund_marks_order_refunded_and_cancelled(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-REFUND2',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => Order::STATUS_CONFIRMED,
            'payment_status'            => 'paid',
            'payment_method'            => 'stripe',
            'payment_reference'         => 'pi_test_fullref_456',
            'subtotal'                  => 4000,
            'grand_total'               => 4200,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'provider'          => 'stripe',
            'payment_reference' => 'pi_test_fullref_456',
            'amount'            => 4200,
            'currency'          => 'inr',
            'status'            => 'successful',
        ]);

        $refundReq = RefundRequest::create([
            'order_id' => $order->id,
            'user_id'  => $this->user->id,
            'amount'   => 4200,
            'reason'   => 'Collector relocation cancellation',
            'status'   => 'APPROVED',
        ]);

        $mockRefunds = \Mockery::mock();
        $mockRefunds->shouldReceive('create')
            ->once()
            ->andReturn((object) [
                'id'     => 're_full_cancelled_999',
                'status' => 'succeeded',
            ]);

        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->refunds = $mockRefunds;
        $this->app->instance(StripeClient::class, $mockStripe);

        StripeRefundService::processRefund($refundReq);

        $order->refresh();
        $this->assertEquals('refunded', $order->payment_status);
        $this->assertEquals(Order::STATUS_CANCELLED, $order->status);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('refunded', $payment->status);
    }

    /**
     * 16. Refund Idempotency: Duplicate refund call does not invoke Stripe API again.
     */
    public function test_16_refund_idempotency_prevents_duplicate_api_calls(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-REFUND3',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => Order::STATUS_CANCELLED,
            'payment_status'            => 'refunded',
            'payment_method'            => 'stripe',
            'payment_reference'         => 'pi_already_refunded_1',
            'subtotal'                  => 3000,
            'grand_total'               => 3150,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        $refundReq = RefundRequest::create([
            'order_id'         => $order->id,
            'user_id'          => $this->user->id,
            'amount'           => 3150,
            'reason'           => 'Already processed refund',
            'status'           => 'COMPLETED',
            'stripe_refund_id' => 're_existing_refund_id',
        ]);

        // Notice: Mock refunds->create is NOT called (shouldReceive('create')->never())
        $mockRefunds = \Mockery::mock();
        $mockRefunds->shouldReceive('create')->never();

        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->refunds = $mockRefunds;
        $this->app->instance(StripeClient::class, $mockStripe);

        $res = StripeRefundService::processRefund($refundReq);

        $this->assertTrue($res['success']);
        $this->assertTrue($res['already_refunded']);
        $this->assertEquals('re_existing_refund_id', $res['stripe_refund_id']);
    }

    /**
     * 17. IDOR Protection: Non-owner cannot access confirmation or cancel pages for another user's order.
     */
    public function test_17_idor_protection_prevents_unauthorized_order_access(): void
    {
        $order = Order::create([
            'order_reference'           => 'MR-2026-PRIVATE1',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'PENDING_PAYMENT',
            'payment_status'            => 'unpaid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 10000,
            'grand_total'               => 10500,
            'shipping_address_snapshot' => [],
            'billing_address_snapshot'  => [],
        ]);

        // Act as otherUser (attacker / unauthorized user)
        $this->actingAs($this->otherUser);

        // Attempting to access confirmation page
        $res1 = $this->get(route('checkout.confirmation', ['order' => $order->order_reference]));
        $res1->assertStatus(403);

        // Attempting to access cancel route
        $res2 = $this->get(route('checkout.cancel', ['order' => $order->order_reference]));
        $res2->assertStatus(403);
    }
}
