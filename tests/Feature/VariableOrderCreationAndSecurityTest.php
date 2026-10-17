<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BullionPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;
use Tests\TestCase;

class VariableOrderCreationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::set('gold_rate_24kt', '7600');
        SiteSetting::set('gold_rate_22kt', '6966');
        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('gold_rate_14kt', '4433');
        SiteSetting::set('gold_gst_rate', '3');
        SiteSetting::set('invoice_tax_rate', '3');
        SiteSetting::set('invoice_show_tax', '0'); // Taxes already included in jewellery pricing

        config([
            'services.stripe.secret'   => 'sk_test_mock_secret_key_12345',
            'services.stripe.currency' => 'inr',
        ]);

        $this->user = User::factory()->create([
            'email' => 'patron.collector@example.com',
            'name'  => 'Devavrat Singhania',
        ]);

        $this->product = Product::create([
            'name'           => 'Aura Royal Solitaire Diamond Ring',
            'slug'           => 'aura-royal-solitaire-diamond-ring',
            'sku'            => 'SOL-AURA-01',
            'price'          => 56526,
            'sale_price'     => 47105,
            'status'         => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock'          => 10,
            'attributes'     => [
                'pricing_mode'      => 'dynamic',
                'gold_weight_grams' => 4.0,
                'making_charge'     => 750,
                'diamond_value'     => 25000,
                'metal_colors'      => ['Yellow Gold', 'White Gold', 'Rose Gold'],
                'gold_purities'     => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants'     => [
                    [
                        'size'         => 'Size 12',
                        'weight_grams' => 4.0,
                        'price_14k'    => 47105,
                        'mrp_14k'      => 56526,
                        'price_18k'    => 52324,
                        'mrp_18k'      => 62789,
                        'price_22k'    => 57543,
                        'mrp_22k'      => 69052,
                        'price'        => 52324,
                        'mrp'          => 62789,
                    ],
                    [
                        'size'         => 'Size 14',
                        'weight_grams' => 4.3,
                        'price_14k'    => 48707,
                        'mrp_14k'      => 58448,
                        'price_18k'    => 54317,
                        'mrp_18k'      => 65180,
                        'price_22k'    => 59927,
                        'mrp_22k'      => 71912,
                        'price'        => 54317,
                        'mrp'          => 65180,
                    ],
                ],
            ],
        ]);
    }

    private function mockStripe(?array &$capturedParams = null): void
    {
        $mockSessions = \Mockery::mock();
        $mockSessions->shouldReceive('create')
            ->andReturnUsing(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return (object) [
                    'id'  => 'cs_test_var_order_999',
                    'url' => 'https://checkout.stripe.com/pay/cs_test_var_order_999',
                ];
            });

        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->checkout = (object) ['sessions' => $mockSessions];
        $this->app->instance(StripeClient::class, $mockStripe);
    }

    /** @test */
    public function order_creation_with_all_variables_preserves_purity_size_color_end_to_end(): void
    {
        $this->actingAs($this->user);

        // 1. Add variable item to cart (Size 14, 18KT Gold, Rose Gold)
        $addResponse = $this->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 1,
            'options'    => [
                'gold_purity' => '18KT Gold',
                'size'        => 'Size 14',
                'metal_color' => 'Rose Gold',
            ],
        ]);
        $addResponse->assertRedirect();

        // Verify Cart Item was created with exact price 54317 (18KT Size 14)
        $cartItem = CartItem::where('product_id', $this->product->id)->firstOrFail();
        $this->assertEquals(54317.00, (float) $cartItem->price);
        $this->assertEquals('Size 14', $cartItem->options['size']);
        $this->assertEquals('18KT Gold', $cartItem->options['gold_purity']);
        $this->assertEquals('Rose Gold', $cartItem->options['metal_color']);

        // 2. Process checkout with authoritative payment gateway
        $capturedStripeParams = null;
        $this->mockStripe($capturedStripeParams);

        $checkoutResponse = $this->post(route('checkout.process'), [
            'full_name'        => 'Devavrat Singhania',
            'email'            => $this->user->email,
            'phone'            => '+91 98201 11223',
            'address_line_1'   => '44 Altamount Road, Cumballa Hill',
            'city'             => 'Mumbai',
            'state'            => 'Maharashtra',
            'postal_code'      => '400026',
            'country'          => 'India',
            'payment_method'   => 'card',
            'same_as_shipping' => '1',
        ]);

        $checkoutResponse->assertRedirect('https://checkout.stripe.com/pay/cs_test_var_order_999');

        // 3. Verify Order & OrderItem in Database
        $order = Order::where('user_id', $this->user->id)->latest()->firstOrFail();
        $this->assertEquals(54317.00, (float) $order->subtotal);
        $this->assertEquals('PENDING_PAYMENT', $order->status);

        $orderItem = OrderItem::where('order_id', $order->id)->firstOrFail();
        $this->assertEquals(54317.00, (float) $orderItem->unit_price);
        $this->assertEquals(54317.00, (float) $orderItem->subtotal);
        $this->assertEquals('Size 14', $orderItem->options['size']);
        $this->assertEquals('18KT Gold', $orderItem->options['gold_purity']);
        $this->assertEquals('Rose Gold', $orderItem->options['metal_color']);

        // 4. Verify Stripe was called with exact paise
        $this->assertNotNull($capturedStripeParams);
        $this->assertEquals(5431700, $capturedStripeParams['line_items'][0]['price_data']['unit_amount']);
    }

    /** @test */
    public function admin_order_items_preview_renders_all_variable_customizations(): void
    {
        $this->actingAs($this->user);

        $order = Order::create([
            'order_reference'           => 'MR-2026-VARTEST',
            'user_id'                   => $this->user->id,
            'email'                     => $this->user->email,
            'status'                    => 'CONFIRMED',
            'payment_status'            => 'paid',
            'payment_method'            => 'stripe',
            'subtotal'                  => 54317.00,
            'grand_total'               => 54317.00,
            'shipping_address_snapshot' => ['city' => 'Mumbai'],
            'billing_address_snapshot'  => ['city' => 'Mumbai'],
        ]);

        OrderItem::create([
            'order_id'         => $order->id,
            'product_id'       => $this->product->id,
            'product_name'     => $this->product->name,
            'sku'              => $this->product->sku,
            'unit_price'       => 54317.00,
            'quantity'         => 1,
            'subtotal'         => 54317.00,
            'options'          => [
                'size'        => 'Size 14',
                'gold_purity' => '18KT Gold',
                'metal_color' => 'Rose Gold',
            ],
            'product_snapshot' => [
                'name'    => $this->product->name,
                'sku'     => $this->product->sku,
                'options' => [
                    'size'        => 'Size 14',
                    'gold_purity' => '18KT Gold',
                    'metal_color' => 'Rose Gold',
                ],
            ],
        ]);

        $renderedHtml = view('filament.orders.items-preview', ['order' => $order])->render();

        $this->assertStringContainsString('Rose Gold', $renderedHtml);
        $this->assertStringContainsString('18KT Gold', $renderedHtml);
        $this->assertStringContainsString('Size 14', $renderedHtml);
        $this->assertStringContainsString('54,317', $renderedHtml);
    }

    /** @test */
    public function multi_item_order_with_distinct_variants_calculates_correct_totals(): void
    {
        $this->actingAs($this->user);

        // Add Variant 1: Size 12 in 14KT (Price: 47105)
        $this->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 1,
            'options'    => [
                'gold_purity' => '14KT Gold',
                'size'        => 'Size 12',
                'metal_color' => 'Yellow Gold',
            ],
        ])->assertRedirect();

        // Add Variant 2: Size 14 in 18KT (Price: 54317)
        $this->post(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity'   => 1,
            'options'    => [
                'gold_purity' => '18KT Gold',
                'size'        => 'Size 14',
                'metal_color' => 'White Gold',
            ],
        ])->assertRedirect();

        $cart = Cart::where('user_id', $this->user->id)->firstOrFail();
        $this->assertEquals(2, $cart->items()->count());
        $this->assertEquals(47105 + 54317, (int) round($cart->total));

        // Checkout
        $capturedStripeParams = null;
        $this->mockStripe($capturedStripeParams);

        $this->post(route('checkout.process'), [
            'full_name'        => 'Devavrat Singhania',
            'email'            => $this->user->email,
            'phone'            => '+91 98201 11223',
            'address_line_1'   => '44 Altamount Road',
            'city'             => 'Mumbai',
            'state'            => 'Maharashtra',
            'postal_code'      => '400026',
            'country'          => 'India',
            'payment_method'   => 'card',
            'same_as_shipping' => '1',
        ])->assertRedirect('https://checkout.stripe.com/pay/cs_test_var_order_999');

        $order = Order::where('user_id', $this->user->id)->latest()->firstOrFail();
        $this->assertEquals(101422.00, (float) $order->subtotal);
        $this->assertEquals(2, $order->items()->count());
    }
}
