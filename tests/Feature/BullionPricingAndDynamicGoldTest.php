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

        // Seed basic benchmark gold rates
        SiteSetting::set('gold_rate_24kt', '7600');
        SiteSetting::set('gold_rate_22kt', '6966');
        SiteSetting::set('gold_rate_18kt', '5700');
        SiteSetting::set('gold_rate_14kt', '4433');
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
        $this->assertEquals(650.0, $rates['making_per_gram']);
        $this->assertEquals(3.0, $rates['gst_rate']);
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
        $response->assertSee('18KT Gold Rate');
        $response->assertSee('14KT Gold Rate');
    }
}
