<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualProductTestingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected Product $testProduct;
    protected Category $category;
    protected Collection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Solitaire Rings',
            'slug' => 'solitaire-rings',
            'description' => 'Certified natural diamond solitaire rings.',
            'is_active' => true,
        ]);

        $this->collection = Collection::create([
            'name' => 'Celestial Gold Collection',
            'slug' => 'celestial-gold-collection',
            'description' => 'Fine diamond rings in 14KT, 18KT and 22KT gold.',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $this->testProduct = Product::create([
            'name' => 'Imperial Oval-Cut Diamond Solitaire Ring',
            'slug' => 'imperial-oval-cut-diamond-solitaire-ring',
            'sku' => 'SOL-OVAL-2026',
            'category_id' => $this->category->id,
            'collection_id' => $this->collection->id,
            'price' => 72000.00,
            'sale_price' => 65000.00,
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 8,
            'low_stock_threshold' => 2,
            'status' => 'published',
            'is_featured' => true,
            'is_new' => true,
            'is_bestseller' => true,
            'images' => [
                '/images/catalog/ring_yellow_gold.jpg',
                '/images/catalog/ring_white_gold.jpg',
                '/images/catalog/ring_rose_gold.jpg',
                '/images/catalog/diamond_ring_box.jpg',
            ],
            'attributes' => [
                'metal_colors' => [
                    'Yellow Gold',
                    'White Gold',
                    'Rose Gold',
                ],
                'color_images' => [
                    'Yellow Gold' => '/images/catalog/ring_yellow_gold.jpg',
                    'White Gold'  => '/images/catalog/ring_white_gold.jpg',
                    'Rose Gold'   => '/images/catalog/ring_rose_gold.jpg',
                ],
                'gold_purities' => [
                    '14KT Gold',
                    '18KT Gold',
                    '22KT Gold',
                ],
                'purity_prices' => [
                    '14KT Gold' => 0,
                    '18KT Gold' => 8500,
                    '22KT Gold' => 16500,
                ],
                'size_label' => 'Ring Size',
                'size_variants' => [
                    ['size' => 'Size 12', 'price' => 65000],
                    ['size' => 'Size 14', 'price' => 67500],
                    ['size' => 'Size 16', 'price' => 70000],
                    ['size' => 'Size 18', 'price' => 72500],
                ],
                'diamond_carat' => '1.25 Carat',
                'diamond_color_clarity' => 'VVS1 / E-F Color',
                'diamond_cut' => 'Oval Brilliant Ideal Cut',
                'certification' => 'IGI Certified Natural Diamond',
                'gold_weight' => '3.95 Grams',
                'dimensions' => 'Band Width: 1.8mm | Crown Height: 5.6mm',
            ],
            'description' => '<p>An exquisite masterpiece of classical haute joaillerie. Handcrafted with a 1.25 Carat oval brilliant natural diamond.</p>',
        ]);
    }

    /** @test */
    public function manual_product_appears_in_shop_catalog_and_category_filtering()
    {
        $response = $this->get(route('shop.index'));
        $response->assertStatus(200);
        $response->assertSee('Imperial Oval-Cut Diamond Solitaire Ring');
        $response->assertSee('65,000');

        // Test category filter
        $catResponse = $this->get(route('shop.index', ['category' => 'solitaire-rings']));
        $catResponse->assertStatus(200);
        $catResponse->assertSee('Imperial Oval-Cut Diamond Solitaire Ring');
    }

    /** @test */
    public function manual_product_detail_page_renders_all_fine_jewellery_specs_and_variants()
    {
        $response = $this->get(route('shop.show', $this->testProduct->slug));
        $response->assertStatus(200);

        // Name, SKU, Price
        $response->assertSee('Imperial Oval-Cut Diamond Solitaire Ring');
        $response->assertSee('SOL-OVAL-2026');
        $response->assertSee('65,000');

        // Gemological Specs
        $response->assertSee('1.25 Carat');
        $response->assertSee('VVS1 / E-F Color');
        $response->assertSee('IGI Certified Natural Diamond');

        // Variants
        $response->assertSee('Yellow Gold');
        $response->assertSee('White Gold');
        $response->assertSee('Rose Gold');
        $response->assertSee('14KT Gold');
        $response->assertSee('18KT Gold');
        $response->assertSee('22KT Gold');
        $response->assertSee('Size 12');
        $response->assertSee('Size 14');
        $response->assertSee('Size 16');
        $response->assertSee('Size 18');

        // Action Buttons
        $response->assertSee('ADD TO BAG');
        $response->assertSee('BUY NOW');
        $response->assertSee('Bulk / Wholesale Inquiry');
        $response->assertSee('Order via WhatsApp');
    }

    /** @test */
    public function patron_can_add_manual_product_with_selected_variants_to_bag()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $this->testProduct->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'metal_color' => 'White Gold',
                'size' => 'Size 14',
            ],
        ]);

        $response->assertRedirect(route('cart.index'));

        // Visit cart page
        $cartPage = $this->actingAs($user)->get(route('cart.index'));
        $cartPage->assertStatus(200);
        $cartPage->assertSee('Imperial Oval-Cut Diamond Solitaire Ring');
        $cartPage->assertSee('18KT Gold');
        $cartPage->assertSee('White Gold');
        $cartPage->assertSee('Size 14');
    }

    /** @test */
    public function inquiry_button_prepopulates_product_card_on_inquiry_page()
    {
        $response = $this->get(route('inquiry.index', ['product_id' => $this->testProduct->id]));
        $response->assertStatus(200);
        $response->assertSee('Inquiring About Piece:');
        $response->assertSee('Imperial Oval-Cut Diamond Solitaire Ring');
        $response->assertSee('65,000');
    }
}
