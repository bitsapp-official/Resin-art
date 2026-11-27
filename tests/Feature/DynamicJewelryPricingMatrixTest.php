<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BullionPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\StripeClient;
use Tests\TestCase;


class DynamicJewelryPricingMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::set('gold_rate_22kt', '6966.67');
        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('gold_rate_14kt', '4433.33');
        SiteSetting::set('gold_gst_rate', '3');
    }

    public function test_density_multipliers_match_metallurgical_standards(): void
    {
        $product = new Product();

        $this->assertEquals(1.000, $product->getDensityMultiplier('14KT Gold'));
        $this->assertEquals(1.185, $product->getDensityMultiplier('18KT Gold'));
        $this->assertEquals(1.365, $product->getDensityMultiplier('22KT Gold'));
    }

    public function test_ring_size_multiplier_scales_by_1_5_percent_from_benchmark_size_12(): void
    {
        $product = new Product();

        // Size 12 benchmark = 1.000
        $this->assertEquals(1.000, $product->getSizeMultiplier('Size 12'));
        // Size 14 = 1 + (2 * 0.015) = 1.030
        $this->assertEquals(1.030, $product->getSizeMultiplier('Size 14'));
        // Size 10 = 1 + (-2 * 0.015) = 0.970
        $this->assertEquals(0.970, $product->getSizeMultiplier('Size 10'));
        // Size 16 = 1 + (4 * 0.015) = 1.060
        $this->assertEquals(1.060, $product->getSizeMultiplier('Size 16'));
    }

    public function test_valuation_engine_exact_formula_calculation(): void
    {
        // Exact gold weight: 4.0g
        // Making charge: 800/g
        // Diamond value: 20000
        // Purity: 18KT (Karat Rate = 7600 * 18/24 = 5700)
        //
        // Final Gold Weight = 4.0g
        // Gold Value = 4.0 * 5700 = 22800.00
        // Making Value = 4.0 * 800 = 3200.00
        // Subtotal = 22800.00 + 3200.00 + 20000 = 46000.00
        // GST (3%) = 46000.00 * 0.03 = 1380.00
        // Final Price = Round(46000.00 * 1.03) = 47380

        $calc = BullionPricingService::calculateFinalPrice(
            baseGoldWeight: 4.0,
            makingChargePerGram: 800.0,
            diamondStoneValue: 20000.0,
            purity: '18KT Gold',
            size: 'Size 14'
        );

        $this->assertEquals(4.0, $calc['final_gold_weight']);
        $this->assertEquals(22800.0, $calc['gold_value']);
        $this->assertEquals(3200.0, $calc['making_value']);
        $this->assertEquals(20000.0, $calc['diamond_stone_value']);
        $this->assertEquals(46000.0, $calc['subtotal']);
        $this->assertEquals(47380.0, $calc['final_price']);
    }

    public function test_cart_addition_uses_secure_dynamic_matrix_calculation(): void
    {
        $product = Product::create([
            'name' => 'Royal Heritage Solitaire Ring',
            'slug' => 'royal-heritage-solitaire-ring',
            'sku' => 'SOL-MATRIX-01',
            'description' => 'Fine luxury engagement ring',
            'price' => 90000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'gold_weight_grams' => 4.0,
                'making_charge' => 800.0,
                'diamond_value' => 20000.0,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants' => [
                    ['size' => 'Size 10', 'weight_grams' => 3.60],
                    ['size' => 'Size 12', 'weight_grams' => 4.00],
                    ['size' => 'Size 14', 'weight_grams' => 4.20],
                ],
            ],
        ]);

        // Add Size 14 in 18KT to cart (Size 14 is 4.20 gm: 4.2*5700 + 4.2*800 + 20000 = 47300 * 1.03 = 48719)
        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'size' => 'Size 14',
                'metal_color' => 'Yellow Gold',
            ],
        ]);

        $response->assertRedirect();

        // Check cart item in DB
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 48719.00, // Verified calculated dynamic price
        ]);
    }

    public function test_switching_size_changes_cart_price_without_fatal_purity_override(): void
    {
        $product = Product::create([
            'name' => 'Solitaire Diamond Band',
            'slug' => 'solitaire-diamond-band',
            'sku' => 'SOL-MATRIX-02',
            'description' => 'Fine luxury band',
            'price' => 70000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'gold_weight_grams' => 3.5,
                'making_charge' => 600.0,
                'diamond_value' => 10000.0,
                'gold_purities' => ['18KT Gold'],
                'size_variants' => [
                    ['size' => 'Size 10', 'weight_grams' => 3.20],
                    ['size' => 'Size 16', 'weight_grams' => 3.80],
                ],
            ],
        ]);

        // Size 10 calculation (3.20g)
        $calcSize10 = BullionPricingService::calculateFinalPrice(3.5, 600, 10000, '18KT Gold', 'Size 10', null, 3.20);
        // Size 16 calculation (3.80g)
        $calcSize16 = BullionPricingService::calculateFinalPrice(3.5, 600, 10000, '18KT Gold', 'Size 16', null, 3.80);

        $this->assertGreaterThan($calcSize10['final_price'], $calcSize16['final_price']);

        // Post Size 16 to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'size' => 'Size 16',
            ],
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => $calcSize16['final_price'],
        ]);
    }

    public function test_full_lifecycle_gold_rate_change_and_size_switching_with_cart_security(): void
    {
        $product = Product::create([
            'name' => 'Imperial Solitaire Diamond Ring',
            'slug' => 'imperial-solitaire-diamond-ring',
            'sku' => 'IMP-RING-01',
            'description' => 'Tested gold product',
            'price' => 85000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 15,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'gold_weight_grams' => 4.0,
                'making_charge' => 700.0,
                'diamond_value' => 15000.0,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants' => [
                    ['size' => 'Size 10', 'weight_grams' => 3.50],
                    ['size' => 'Size 12', 'weight_grams' => 4.00],
                    ['size' => 'Size 14', 'weight_grams' => 4.50],
                ],
            ],
        ]);

        // 2. Initial checks under Rate 5700 (18KT Rate = 5700)
        // Size 10 (3.5g): (3.5*5700 + 3.5*700 + 15000) * 1.03 = 37400 * 1.03 = 38522
        $calc10_initial = BullionPricingService::calculateFinalPrice(4.0, 700, 15000, '18KT Gold', 'Size 10', null, 3.50);
        $this->assertEquals(38522.0, $calc10_initial['final_price']);

        // Size 14 (4.5g): (4.5*5700 + 4.5*700 + 15000) * 1.03 = 43800 * 1.03 = 45114
        $calc14_initial = BullionPricingService::calculateFinalPrice(4.0, 700, 15000, '18KT Gold', 'Size 14', null, 4.50);
        $this->assertEquals(45114.0, $calc14_initial['final_price']);

        // 3. Now change 18K Gold Rate to 6000!
        SiteSetting::set('gold_rate_18kt', '6000');
        SiteSetting::set('gold_rate_22kt', '7333.33');

        // Size 10 (3.5g) under Rate 8000 (18KT Rate = 6000):
        // (3.5*6000 + 3.5*700 + 15000) * 1.03 = (21000 + 2450 + 15000) * 1.03 = 38450 * 1.03 = 39604
        $calc10_new = BullionPricingService::calculateFinalPrice(4.0, 700, 15000, '18KT Gold', 'Size 10', null, 3.50);
        $this->assertEquals(39604.0, $calc10_new['final_price']);

        // Add Size 10 to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'size' => 'Size 10',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 39604.00,
        ]);

        // Add Size 14 in 22KT to cart under Rate 8000:
        // Karat rate = 7333.33
        // (4.5*7333.33 + 4.5*700 + 15000) * 1.03 = (32999.99 + 3150 + 15000) * 1.03 = 51149.99 * 1.03 = 52684
        $calc14_22k = BullionPricingService::calculateFinalPrice(4.0, 700, 15000, '22KT Gold', 'Size 14', null, 4.50);
        $this->assertEquals(52684.0, $calc14_22k['final_price']);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '22KT Gold',
                'size' => 'Size 14',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 52684.00,
        ]);
    }

    public function test_size_variants_with_distinct_14k_18k_22k_pricing_matrix(): void
    {
        // 24K Rate = 7600
        // 14K = 4433.33, 18K = 5700, 22K = 6966.67
        // Size 16 (4.50 gm), Making = 700/gm, Diamond = 10000
        // 14K Subtotal: (4.50 * 4433.33) + (4.50 * 700) + 10000 = 19950 + 3150 + 10000 = 33100; + 3% GST = 34093
        // 18K Subtotal: (4.50 * 5700) + (4.50 * 700) + 10000 = 25650 + 3150 + 10000 = 38800; + 3% GST = 39964
        // 22K Subtotal: (4.50 * 6966.67) + (4.50 * 700) + 10000 = 31350 + 3150 + 10000 = 44500; + 3% GST = 45835

        $c14 = BullionPricingService::calculateFinalPrice(4.50, 700, 10000, '14KT Gold', null, null, 4.50);
        $c18 = BullionPricingService::calculateFinalPrice(4.50, 700, 10000, '18KT Gold', null, null, 4.50);
        $c22 = BullionPricingService::calculateFinalPrice(4.50, 700, 10000, '22KT Gold', null, null, 4.50);

        $this->assertEquals(34093.0, $c14['final_price']);
        $this->assertEquals(39964.0, $c18['final_price']);
        $this->assertEquals(45835.0, $c22['final_price']);

        // Check MRP has 20% markup
        $this->assertEquals(round(34093 * 1.20), $c14['mrp_price']);
        $this->assertEquals(round(39964 * 1.20), $c18['mrp_price']);
        $this->assertEquals(round(45835 * 1.20), $c22['mrp_price']);

        $product = Product::create([
            'name' => 'Royal Signature Eternity Band',
            'slug' => 'royal-signature-eternity-band',
            'price' => 39964,
            'sale_price' => null,
            'status' => 'published',
            'stock' => 5,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'making_charge' => 700,
                'diamond_value' => 10000,
                'gold_weight_grams' => 4.50,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants' => [
                    [
                        'size' => 'Size 16',
                        'weight_grams' => 4.50,
                        'price_14k' => $c14['final_price'],
                        'mrp_14k' => $c14['mrp_price'],
                        'price_18k' => $c18['final_price'],
                        'mrp_18k' => $c18['mrp_price'],
                        'price_22k' => $c22['final_price'],
                        'mrp_22k' => $c22['mrp_price'],
                        'price' => $c18['final_price'],
                        'mrp' => $c18['mrp_price'],
                    ]
                ],
            ],
        ]);

        // Add 14KT Size 16 to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '14KT Gold',
                'size' => 'Size 16',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 34093.00,
        ]);

        // Add 22KT Size 16 to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '22KT Gold',
                'size' => 'Size 16',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 45835.00,
        ]);
    }

    public function test_buy_now_uses_authoritative_dynamic_matrix_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::create([
            'name' => 'Royal Solitaire Ring',
            'slug' => 'royal-solitaire-ring',
            'price' => 50000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 5,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'making_charge' => 750,
                'diamond_value' => 25000,
                'gold_weight_grams' => 4.0,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants' => [
                    [
                        'size' => 'Size 12',
                        'weight_grams' => 4.0,
                        'price_14k' => 47105,
                        'mrp_14k' => 56526,
                        'price_18k' => 52324,
                        'mrp_18k' => 62789,
                        'price_22k' => 57543,
                        'mrp_22k' => 69052,
                        'price' => 52324,
                        'mrp' => 62789,
                    ],
                ],
            ],
        ]);

        $response = $this->post(route('cart.buy-now'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '14KT Gold',
                'size' => 'Size 12',
            ],
        ]);

        $response->assertRedirect(route('checkout.index'));

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 47105.00,
        ]);
    }

    public function test_end_to_end_cart_to_checkout_security_recalculates_authoritative_purity_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        config([
            'services.stripe.secret' => 'sk_test_mock_secret_key_12345',
            'services.stripe.currency' => 'inr',
        ]);

        $product = Product::create([
            'name' => 'Aura Royal Diamond Ring',
            'slug' => 'aura-royal-diamond-ring',
            'sku' => 'AURA-RING-01',
            'price' => 50000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 5,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'making_charge' => 750,
                'diamond_value' => 25000,
                'gold_weight_grams' => 4.0,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants' => [
                    [
                        'size' => 'Size 12',
                        'weight_grams' => 4.0,
                        'price_14k' => 47105,
                        'mrp_14k' => 56526,
                        'price_18k' => 52324,
                        'mrp_18k' => 62789,
                        'price_22k' => 57543,
                        'mrp_22k' => 69052,
                        'price' => 52324,
                        'mrp' => 62789,
                    ],
                ],
            ],
        ]);

        // 1. Add 14KT Size 12 to cart (authentic price = 47105)
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '14KT Gold',
                'size' => 'Size 12',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 47105.00,
        ]);

        // 2. Tamper the cart item directly in DB to 5.00 (simulating attack)
        CartItem::where('product_id', $product->id)->update(['price' => 5.00]);

        // 3. Mock Stripe session
        $capturedParams = null;
        $mockSessions = \Mockery::mock();
        $mockSessions->shouldReceive('create')
            ->andReturnUsing(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return (object) [
                    'id' => 'cs_test_authoritative_123',
                    'url' => 'https://checkout.stripe.com/pay/cs_test_authoritative_123',
                ];
            });
        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->checkout = (object) ['sessions' => $mockSessions];
        $this->app->instance(StripeClient::class, $mockStripe);

        // 4. Submit checkout
        $response = $this->post(route('checkout.process'), [
            'full_name' => 'Aarav Sharma',
            'email' => $user->email,
            'phone' => '+91 98201 45678',
            'address_line_1' => 'Flat 502, Oceanic Towers',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400021',
            'country' => 'India',
            'payment_method' => 'card',
            'same_as_shipping' => '1',
        ]);

        $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_authoritative_123');

        // 5. Verify security: Cart price was restored to authoritative 47105, NOT 5.00
        $this->assertNotNull($capturedParams);
        $this->assertEquals(4710500, $capturedParams['line_items'][0]['price_data']['unit_amount']);

        // Verify order in database
        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'unit_price' => 47105.00,
        ]);
    }

    public function test_manual_fixed_price_product_preserves_admin_price_in_cart_and_checkout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        config([
            'services.stripe.secret' => 'sk_test_mock_secret_key_12345',
            'services.stripe.currency' => 'inr',
        ]);

        $product = Product::create([
            'name' => 'Heritage Temple Coin Pendant',
            'slug' => 'heritage-temple-coin-pendant',
            'sku' => 'TEMPLE-COIN-01',
            'price' => 45000,
            'sale_price' => 42000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 3,
            'attributes' => [
                'pricing_mode' => 'manual',
                'gold_weight_grams' => 6.8,
                'gold_purities' => [],
            ],
        ]);

        // Add manual product to cart
        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [],
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'price' => 42000.00,
        ]);

        // Mock Stripe
        $capturedParams = null;
        $mockSessions = \Mockery::mock();
        $mockSessions->shouldReceive('create')
            ->andReturnUsing(function ($params) use (&$capturedParams) {
                $capturedParams = $params;
                return (object) [
                    'id' => 'cs_test_manual_123',
                    'url' => 'https://checkout.stripe.com/pay/cs_test_manual_123',
                ];
            });
        $mockStripe = \Mockery::mock(StripeClient::class);
        $mockStripe->checkout = (object) ['sessions' => $mockSessions];
        $this->app->instance(StripeClient::class, $mockStripe);

        // Submit checkout
        $this->post(route('checkout.process'), [
            'full_name' => 'Aarav Sharma',
            'email' => $user->email,
            'phone' => '+91 98201 45678',
            'address_line_1' => 'Flat 502, Oceanic Towers',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400021',
            'country' => 'India',
            'payment_method' => 'card',
            'same_as_shipping' => '1',
        ])->assertRedirect('https://checkout.stripe.com/pay/cs_test_manual_123');

        $this->assertEquals(4200000, $capturedParams['line_items'][0]['price_data']['unit_amount']);
    }

    public function test_dynamic_pricing_with_profit_margin_percentage(): void
    {
        // Cost: Gold (22800) + Making (3200) + Diamond (20000) = 46000
        // Cost with GST: 46000 * 1.03 = 47380
        // Profit Margin (5%): 47380 * 0.05 = 2369
        // Final Selling Price: 47380 + 2369 = 49749
        $calc = BullionPricingService::calculateFinalPrice(
            baseGoldWeight: 4.0,
            makingChargePerGram: 800.0,
            diamondStoneValue: 20000.0,
            purity: '18KT Gold',
            size: 'Size 14',
            explicitWeightGrams: 4.0,
            profitMarginPercent: 5.0
        );

        $this->assertEquals(4.0, $calc['final_gold_weight']);
        $this->assertEquals(22800.0, $calc['gold_value']);
        $this->assertEquals(3200.0, $calc['making_value']);
        $this->assertEquals(20000.0, $calc['diamond_stone_value']);
        $this->assertEquals(46000.0, $calc['subtotal']);
        $this->assertEquals(47380.0, $calc['cost_with_gst']);
        $this->assertEquals(5.0, $calc['profit_margin_percent']);
        $this->assertEquals(2369.0, $calc['profit_amount']);
        $this->assertEquals(49749.0, $calc['final_price']);
    }

    public function test_common_cut_price_applies_uniformly_across_all_sizes_and_product_mrp(): void
    {
        $product = Product::create([
            'name' => 'Uniform Cut Price Solitaire Band',
            'slug' => 'uniform-cut-price-solitaire-band-' . uniqid(),
            'sku' => 'UNI-CUT-' . uniqid(),
            'price' => 85000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'making_charge' => 750,
                'diamond_value' => 20000,
                'common_cut_price' => 85000,
                'gold_weight_grams' => 4.0,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_variants' => [
                    [
                        'size' => 'Size 6',
                        'weight_grams' => 3.2,
                        'price_14k' => 42000,
                        'mrp_14k' => 85000,
                        'price_18k' => 46000,
                        'mrp_18k' => 85000,
                        'price_22k' => 51000,
                        'mrp_22k' => 85000,
                        'price' => 46000,
                        'mrp' => 85000,
                    ],
                    [
                        'size' => 'Size 12',
                        'weight_grams' => 4.0,
                        'price_14k' => 47105,
                        'mrp_14k' => 85000,
                        'price_18k' => 52324,
                        'mrp_18k' => 85000,
                        'price_22k' => 57543,
                        'mrp_22k' => 85000,
                        'price' => 52324,
                        'mrp' => 85000,
                    ],
                ],
            ],
        ]);

        $this->assertEquals(85000.0, $product->effective_mrp);

        $response = $this->get(route('shop.show', $product->slug));
        $response->assertStatus(200);
        $response->assertSee('85000');
    }

    public function test_10kt_gold_and_silver_cart_addition_and_purity_resolution(): void
    {
        SiteSetting::set('gold_rate_10kt', '3166.67');
        SiteSetting::set('silver_rate_925', '95');

        $product = Product::create([
            'name' => 'Artisan Halo Solitaire Band',
            'slug' => 'artisan-halo-solitaire-band',
            'sku' => 'HALO-TEST-10K',
            'description' => 'Fine luxury band',
            'price' => 50000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'gold_weight_grams' => 4.0,
                'making_charge' => 800.0,
                'diamond_value' => 10000.0,
                'gold_purities' => ['10KT Gold', '14KT Gold', '18KT Gold', '22KT Gold', '925 Silver'],
            ],
        ]);

        // 1. Add 10KT Gold variant to cart
        $response10k = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '10KT Gold',
                'metal_color' => 'Yellow Gold',
            ],
        ]);
        $response10k->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 26643.00,
        ]);

        // 2. Add 925 Silver variant to cart
        $responseSilver = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '925 Silver',
                'metal_color' => 'White Gold',
            ],
        ]);
        $responseSilver->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 13987.00,
        ]);
    }

    public function test_configurable_diamond_carat_options_scale_pricing_and_cart_addition(): void
    {
        SiteSetting::set('diamond_rate_vvs1', '100000');
        SiteSetting::set('diamond_rate_vs1', '70000');
        SiteSetting::set('gold_rate_18kt', '5700');

        $product = Product::create([
            'name' => 'Aura Grand Solitaire Diamond Ring',
            'slug' => 'aura-grand-solitaire-diamond-ring',
            'sku' => 'AURA-SOL-MULTI-CT',
            'description' => 'Configurable diamond solitaire ring from 1.00 ct to 5.00 ct',
            'price' => 250000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'gold_weight_grams' => 4.0,
                'making_charge' => 750.0,
                'profit_margin_percent' => 5.0,
                'diamond_carat' => 1.0,
                'diamond_carat_options' => ['1.00 ct', '1.50 ct', '2.00 ct', '2.50 ct', '3.00 ct', '4.00 ct', '5.00 ct'],
                'diamond_clarities' => ['VVS1', 'VS1'],
                'gold_purities' => ['18KT Gold'],
                'metal_colors' => ['Yellow Gold', 'White Gold', 'Rose Gold'],
            ],
        ]);

        $this->assertCount(7, $product->diamond_carat_options);
        $this->assertEquals(['1.00 ct', '1.50 ct', '2.00 ct', '2.50 ct', '3.00 ct', '4.00 ct', '5.00 ct'], $product->diamond_carat_options);

        // 1. Verify 1.00 ct pricing:
        // Gold: 4.0 * 5700 = 22800
        // Making: 4.0 * 750 = 3000
        // Diamond 1.00 ct @ 100,000 = 100,000
        // Subtotal = 125,800
        // GST (3%) = 3774
        // Cost with GST = 129,574
        // Profit 5% = 6478.70
        // Final Price = Round(129574 + 6478.70) = 136,053
        $price1ct = BullionPricingService::resolveItemPrice($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '1.00 ct',
        ]);
        $this->assertEquals(136053.0, $price1ct);

        // 2. Verify 2.00 ct pricing:
        // Diamond 2.00 ct @ 100,000 = 200,000
        // Subtotal = 22800 + 3000 + 200,000 = 225,800
        // GST (3%) = 6774
        // Cost with GST = 232,574
        // Profit 5% = 11,628.70
        // Final Price = Round(232574 + 11628.70) = 244,203
        $price2ct = BullionPricingService::resolveItemPrice($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '2.00 ct',
        ]);
        $this->assertEquals(244203.0, $price2ct);

        // 3. Verify 5.00 ct pricing:
        // Diamond 5.00 ct @ 100,000 = 500,000
        // Subtotal = 22800 + 3000 + 500,000 = 525,800
        // GST (3%) = 15,774
        // Cost with GST = 541,574
        // Profit 5% = 27,078.70
        // Final Price = Round(541574 + 27078.70) = 568,653
        $price5ct = BullionPricingService::resolveItemPrice($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '5.00 ct',
        ]);
        $this->assertEquals(568653.0, $price5ct);

        // 4. Cart addition with selected 2.00 ct option
        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'metal_color' => 'Yellow Gold',
                'diamond_clarity' => 'VVS1',
                'diamond_carat' => '2.00 ct',
            ],
        ]);
        $response->assertRedirect();

        $cartItem = CartItem::where('product_id', $product->id)->first();
        $this->assertNotNull($cartItem);
        $this->assertEquals(244203.0, (float) $cartItem->price);
        $this->assertEquals('2.00 ct', $cartItem->options['diamond_carat']);
        $this->assertEquals('VVS1', $cartItem->options['diamond_clarity']);

        // 5. Storefront view renders Diamond Size Carat Options
        $viewResponse = $this->get(route('shop.show', ['slug' => $product->slug, 'carat' => '2.00 ct']));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('DIAMOND SIZE');
        $viewResponse->assertSee('5.00 ct');
    }

    public function test_moissanite_clarity_option_calculates_correct_pricing_and_renders_in_specifications(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('gold_default_making_per_gram', '750');
        SiteSetting::set('diamond_rate_moissanite', '3500');

        $product = Product::create([
            'name' => 'Forever Solitaire Lab Grown Diamond Ring',
            'slug' => 'forever-solitaire-lab-grown-diamond-ring',
            'sku' => 'MOISS-TEST-01',
            'price' => 85000,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'making_charge' => 750,
                'making_charge_type' => 'per_gram',
                'gold_weight_grams' => 4.0,
                'diamond_carat_weight' => 2.00,
                'diamond_carat_options' => ['1.00 ct', '2.00 ct', '3.00 ct'],
                'diamond_clarities' => ['VVS1', 'VS2', 'Moissanite'],
                'gold_purities' => ['14KT Gold', '18KT Gold', '925 Silver'],
                'metal_colors' => ['Yellow Gold', 'White Gold'],
                'profit_margin_percent' => 5,
            ],
        ]);

        // Gold 18KT = 4.0 * 5700 = 22,800
        // Making = 4.0 * 750 = 3,000
        // Moissanite 2.00 ct = 2.00 * 3500 = 7,000
        // Subtotal = 32,800
        // GST (3%) = 984 -> 33,784
        // Profit (5%) = 1689.20 -> Final Price = 35,473
        $priceMoiss = BullionPricingService::resolveItemPrice($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'Moissanite',
            'diamond_carat' => '2.00 ct',
        ]);
        $this->assertEquals(35473.0, $priceMoiss);

        // Cart addition with Moissanite
        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'metal_color' => 'Yellow Gold',
                'diamond_clarity' => 'Moissanite',
                'diamond_carat' => '2.00 ct',
            ],
        ]);
        $response->assertRedirect();

        $cartItem = CartItem::where('product_id', $product->id)->first();
        $this->assertNotNull($cartItem);
        $this->assertEquals(35473.0, (float) $cartItem->price);
        $this->assertEquals('Moissanite', $cartItem->options['diamond_clarity']);

        // Storefront view renders specifications with Moissanite logic
        $viewResponse = $this->get(route('shop.show', ['slug' => $product->slug, 'clarity' => 'Moissanite']));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Moissanite');
        $viewResponse->assertSee('GRA Moissanite Authenticity Report');
    }

    public function test_percentage_cut_price_scales_mrp_across_diamond_carats_and_purities(): void
    {
        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('gold_rate_14kt', '4433');
        SiteSetting::set('gold_default_making_per_gram', '750');
        SiteSetting::set('diamond_rate_vvs1', '100000');

        // Product with 25% Strike Cut Price Markup
        $product = Product::create([
            'name' => 'Percentage Cut MRP Solitaire Ring',
            'slug' => 'percentage-cut-mrp-solitaire-ring',
            'sku' => 'PCT-CUT-01',
            'price' => 100000,
            'status' => 'published',
            'inventory_type' => 'MADE_TO_ORDER',
            'stock' => 0,
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'making_charge' => 750,
                'making_charge_type' => 'per_gram',
                'gold_weight_grams' => 4.0,
                'diamond_carat_weight' => 1.00,
                'diamond_carat_options' => ['1.00 ct', '5.00 ct'],
                'diamond_clarities' => ['VVS1', 'VS1'],
                'gold_purities' => ['14KT Gold', '18KT Gold'],
                'profit_margin_percent' => 5,
                'cut_price_percent' => 25,
            ],
        ]);

        // 1. At 1.00 ct (18KT Gold, VVS1)
        $price1ct = BullionPricingService::resolveItemPrice($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '1.00 ct',
        ]);
        $mrp1ct = BullionPricingService::resolveItemMrp($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '1.00 ct',
        ]);
        $this->assertEquals((float) round($price1ct * 1.25), $mrp1ct);
        $this->assertGreaterThan($price1ct, $mrp1ct);

        // 2. At 5.00 ct (18KT Gold, VVS1) — selling price jumps way above 1ct MRP, cut MRP MUST scale by +25%
        $price5ct = BullionPricingService::resolveItemPrice($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '5.00 ct',
        ]);
        $mrp5ct = BullionPricingService::resolveItemMrp($product, [
            'gold_purity' => '18KT Gold',
            'diamond_clarity' => 'VVS1',
            'diamond_carat' => '5.00 ct',
        ]);
        $this->assertEquals(568653.0, $price5ct);
        $this->assertEquals((float) round(568653.0 * 1.25), $mrp5ct);
        $this->assertGreaterThan($price5ct, $mrp5ct);

        // 3. Product effective_mrp matches +25% of base effective_price (first purity 14KT Gold)
        $this->assertEquals((float) round($product->effective_price * 1.25), $product->effective_mrp);

        // 4. Storefront passes cutPricePercent: 25 to Alpine dynamic engine
        $response = $this->get(route('shop.show', $product->slug));
        $response->assertStatus(200);
        $response->assertSee('cutPricePercent: 25', false);
    }

    public function test_admin_diamond_carat_preset_action_populates_standard_carats_and_syncs_previews(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-preset@solitairefinejewels.com',
            'is_admin' => true,
        ]);

        $this->actingAs($admin, 'admin');

        \Livewire\Livewire::test(\App\Filament\Resources\ProductResource\Pages\CreateProduct::class)
            ->assertSee('+ Quick Preset: Diamond Carats (1, 1.5, 2, 2.5, 3, 4, 5 ct)')
            ->set('data.attributes.gold_weight_grams', 4.0)
            ->set('data.attributes.making_charge', 750)
            ->callFormComponentAction('loadStandardDiamondCaratsAction', 'loadStandardDiamondCarats')
            ->assertSet('data.attributes.diamond_carat_options', [
                '1.00 ct',
                '1.50 ct',
                '2.00 ct',
                '2.50 ct',
                '3.00 ct',
                '4.00 ct',
                '5.00 ct',
            ])
            ->assertSet('data.attributes.diamond_carat', 1.0);
    }
}



