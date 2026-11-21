<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BullionPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BullionPricingAndDynamicGoldTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic benchmark gold & silver rates
        SiteSetting::set('gold_rate_24kt', '7600');
        SiteSetting::set('gold_rate_22kt', '6966');
        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('gold_rate_14kt', '4433');
        SiteSetting::set('gold_rate_10kt', '3167');
        SiteSetting::set('silver_rate_925', '95');
        SiteSetting::set('gold_default_making_per_gram', '650');
        SiteSetting::set('gold_gst_rate', '3');
    }

    public function test_bullion_pricing_service_returns_accurate_benchmark_rates(): void
    {
        $rates = BullionPricingService::getRates();

        $this->assertEquals(7600.0, $rates['rate_24kt']);
        $this->assertEquals(6966.0, $rates['rate_22kt']);
        $this->assertEquals(5700.0, $rates['rate_18kt']);
        $this->assertEquals(4433.0, $rates['rate_14kt']);
        $this->assertEquals(3167.0, $rates['rate_10kt']);
        $this->assertEquals(95.0, $rates['rate_silver_925']);
        $this->assertEquals(650.0, $rates['making_per_gram']);
        $this->assertEquals(3.0, $rates['gst_rate']);
    }

    public function test_10kt_gold_and_silver_purity_price_calculations(): void
    {
        // 10KT Gold: 4.0g @ 3167 = 12668; Making: 4.0 * 500 = 2000; Stone: 0; Subtotal: 14668; GST (3%): 440.04; Total: 15108
        $calc10k = BullionPricingService::calculateFinalPrice(
            baseGoldWeight: 4.0,
            makingChargePerGram: 500.0,
            diamondStoneValue: 0.0,
            purity: '10KT Gold',
            explicitWeightGrams: 4.0
        );
        $this->assertEquals(3167.0, $calc10k['karat_gold_rate']);
        $this->assertEquals(12668.0, $calc10k['gold_value']);
        $this->assertEquals(2000.0, $calc10k['making_value']);
        $this->assertEquals(15108.0, $calc10k['final_price']);

        // 925 Silver: 10.0g @ 95 = 950; Making: 10.0 * 150 = 1500; Stone: 500; Subtotal: 2950; GST (3%): 88.5; Total: 3039
        $calcSilver = BullionPricingService::calculateFinalPrice(
            baseGoldWeight: 10.0,
            makingChargePerGram: 150.0,
            diamondStoneValue: 500.0,
            purity: '925 Silver',
            explicitWeightGrams: 10.0
        );
        $this->assertEquals(95.0, $calcSilver['karat_gold_rate']);
        $this->assertEquals(950.0, $calcSilver['gold_value']);
        $this->assertEquals(1500.0, $calcSilver['making_value']);
        $this->assertEquals(3039.0, $calcSilver['final_price']);
    }

    public function test_purity_price_calculation_formula(): void
    {
        // 3.0 grams of 18KT gold, making charge 700/gm, diamond value 10000
        // Gold value: 3.0 * 5700 = 17100
        // Making: 3.0 * 700 = 2100
        // Diamond: 10000
        // Subtotal: 17100 + 2100 + 10000 = 29200
        // GST (3%): 29200 * 0.03 = 876
        // Total selling price: 29200 + 876 = 30076

        $calc = BullionPricingService::calculatePurityPrice(
            '18KT Gold',
            3.0,
            700.0,
            'per_gram',
            10000.0
        );

        $this->assertEquals(17100.0, $calc['gold_value']);
        $this->assertEquals(2100.0, $calc['making_value']);
        $this->assertEquals(10000.0, $calc['diamond_value']);
        $this->assertEquals(29200.0, $calc['subtotal']);
        $this->assertEquals(876.0, $calc['gst_amount']);
        $this->assertEquals(30076.0, $calc['selling_price']);
    }

    public function test_batch_sync_updates_dynamic_products_when_gold_rates_rise(): void
    {
        $dynamicProduct = Product::create([
            'name' => 'Royal Heritage Solitaire Band',
            'slug' => 'royal-heritage-solitaire-band',
            'sku' => 'SOL-TEST-001',
            'description' => 'Fine luxury band',
            'price' => 50000,
            'status' => 'published',
            'attributes' => [
                'pricing_mode' => 'dynamic',
                'gold_weight_grams' => 4.0,
                'making_charge' => 650,
                'diamond_value' => 15000,
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'purity_prices' => [
                    '14KT Gold' => 35000,
                    '18KT Gold' => 40000,
                    '22KT Gold' => 45000,
                ],
            ],
        ]);

        $manualProduct = Product::create([
            'name' => 'Vintage Manual Artwork Piece',
            'slug' => 'vintage-manual-artwork-piece',
            'sku' => 'SOL-TEST-002',
            'description' => 'Fixed price piece',
            'price' => 99999,
            'status' => 'published',
            'attributes' => [
                'pricing_mode' => 'manual',
                'gold_purities' => ['18KT Gold'],
                'purity_prices' => [
                    '18KT Gold' => 99999,
                ],
            ],
        ]);

        // Gold rate jumps: 24KT to 8000, 18KT to 6000
        SiteSetting::set('gold_rate_24kt', '8000');
        SiteSetting::set('gold_rate_18kt', '6000');
        SiteSetting::set('gold_rate_14kt', '4667');
        SiteSetting::set('gold_rate_22kt', '7333');

        $result = BullionPricingService::syncAllStoreProducts();

        $this->assertEquals(1, $result['synced_products']);
        $this->assertEquals(1, $result['skipped']);

        $dynamicProduct->refresh();
        $manualProduct->refresh();

        // 4.0g @ 18KT 6000 = 24000
        // Making: 4.0 * 650 = 2600
        // Diamond: 15000
        // Subtotal: 24000 + 2600 + 15000 = 41600
        // GST (3%): 1248
        // Total selling price: 42848
        $this->assertEquals(42848, $dynamicProduct->attributes['purity_prices']['18KT Gold']);

        // Manual product price is completely untouched
        $this->assertEquals(99999, $manualProduct->attributes['purity_prices']['18KT Gold']);
    }

    public function test_admin_can_access_gold_rates_settings_page(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@solitairefinejewels.com',
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/gold-rates-settings');
        $response->assertStatus(200);
        $response->assertSee('Daily Gold & Bullion Rates');
        $response->assertSee('22KT Gold Rate');
        $response->assertSee('22KT Gold Rate');
        $response->assertSee('18KT Gold Rate');
        $response->assertSee('14KT Gold Rate');
        $response->assertSee('10KT Gold Rate');
        $response->assertSee('925 Silver Rate');
        $response->assertSee('VVS1 Diamond Rate');
        $response->assertSee('VVS2 Diamond Rate');
        $response->assertSee('VS1 Diamond Rate');
        $response->assertSee('VS2 Diamond Rate');
    }

    public function test_diamond_clarity_rates_and_dynamic_calculations_across_scenarios(): void
    {
        // 1. Configure custom diamond clarity rates
        SiteSetting::set('diamond_rate_vvs1', '120000');
        SiteSetting::set('diamond_rate_vvs2', '95000');
        SiteSetting::set('diamond_rate_vs1', '80000');
        SiteSetting::set('diamond_rate_vs2', '65000');

        $rates = BullionPricingService::getRates();
        $this->assertEquals(120000.0, $rates['diamond_rate_vvs1']);
        $this->assertEquals(95000.0, $rates['diamond_rate_vvs2']);
        $this->assertEquals(80000.0, $rates['diamond_rate_vs1']);
        $this->assertEquals(65000.0, $rates['diamond_rate_vs2']);

        // Check clarity getter
        $this->assertEquals(120000.0, BullionPricingService::getDiamondRateForClarity('VVS1'));
        $this->assertEquals(95000.0, BullionPricingService::getDiamondRateForClarity('VVS2'));
        $this->assertEquals(80000.0, BullionPricingService::getDiamondRateForClarity('VS1'));
        $this->assertEquals(65000.0, BullionPricingService::getDiamondRateForClarity('VS2'));

        // Scenario 1: 0.50 ct VVS1 with 3.0g 18KT gold (rate 5700) + making 700/g
        // Gold: 3.0 * 5700 = 17100
        // Making: 3.0 * 700 = 2100
        // Diamond: 0.50 * 120000 = 60000
        // Subtotal: 17100 + 2100 + 60000 = 79200
        // GST (3%): 2376
        // Final Price: 81576
        $calcVvs1 = BullionPricingService::calculateFinalPrice(
            baseGoldWeight: 3.0,
            makingChargePerGram: 700.0,
            purity: '18KT Gold',
            diamondCaratWeight: 0.50,
            diamondClarity: 'VVS1'
        );
        $this->assertEquals(17100.0, $calcVvs1['gold_value']);
        $this->assertEquals(2100.0, $calcVvs1['making_value']);
        $this->assertEquals(60000.0, $calcVvs1['diamond_stone_value']);
        $this->assertEquals(79200.0, $calcVvs1['subtotal']);
        $this->assertEquals(81576.0, $calcVvs1['final_price']);

        // Scenario 2: Same piece with VS2 clarity
        // Diamond: 0.50 * 65000 = 32500
        // Subtotal: 17100 + 2100 + 32500 = 51700
        // GST (3%): 1551
        // Final Price: 53251
        $calcVs2 = BullionPricingService::calculateFinalPrice(
            baseGoldWeight: 3.0,
            makingChargePerGram: 700.0,
            purity: '18KT Gold',
            diamondCaratWeight: 0.50,
            diamondClarity: 'VS2'
        );
        $this->assertEquals(32500.0, $calcVs2['diamond_stone_value']);
        $this->assertEquals(51700.0, $calcVs2['subtotal']);
        $this->assertEquals(53251.0, $calcVs2['final_price']);
    }

    public function test_cart_addition_and_authoritative_diamond_clarity_price_resolution(): void
    {
        SiteSetting::set('gold_rate_24kt', '7600');
        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('diamond_rate_vvs1', '100000');
        SiteSetting::set('diamond_rate_vs2', '60000');

        $product = Product::create([
            'name'        => 'Solitaire Diamond Pave Band',
            'slug'        => 'solitaire-diamond-pave-band',
            'sku'         => 'SOL-DIA-001',
            'description' => 'Certified natural diamond band',
            'price'       => 60000,
            'status'      => 'published',
            'attributes'  => [
                'pricing_mode'        => 'dynamic',
                'gold_weight_grams'   => 2.0,
                'making_charge'       => 500,
                'diamond_carat'       => 0.40,
                'diamond_clarities'   => ['VVS1', 'VS2'],
                'gold_purities'       => ['18KT Gold'],
                'profit_margin_percent' => 0,
            ],
        ]);

        // Gold: 2.0 * 5700 = 11400
        // Making: 2.0 * 500 = 1000
        // VVS1: 0.40 * 100000 = 40000; Subtotal: 52400; +3% GST = 53972
        // VS2:  0.40 * 60000  = 24000; Subtotal: 36400; +3% GST = 37492

        // Customer adds VVS1 to cart
        $responseVvs1 = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
            'options'    => [
                'gold_purity'     => '18KT Gold',
                'diamond_clarity' => 'VVS1',
            ],
        ]);
        $responseVvs1->assertRedirect();

        $cart = \App\Models\Cart::first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->items()->count());
        $vvs1Item = $cart->items()->first();
        $this->assertEquals(53972.0, (float) $vvs1Item->price);
        $this->assertEquals('VVS1', $vvs1Item->options['diamond_clarity']);

        // Customer adds VS2 option as separate line item
        $responseVs2 = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity'   => 1,
            'options'    => [
                'gold_purity'     => '18KT Gold',
                'diamond_clarity' => 'VS2',
            ],
        ]);
        $responseVs2->assertRedirect();

        $cart->refresh();
        $totalItems = \App\Models\CartItem::count();
        $this->assertEquals(2, $totalItems);
        $vs2Item = \App\Models\CartItem::all()->first(fn($i) => ($i->options['diamond_clarity'] ?? null) === 'VS2');
        $this->assertNotNull($vs2Item);
        $this->assertEquals(37492.0, (float) $vs2Item->price);
    }
}
