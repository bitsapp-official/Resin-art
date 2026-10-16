<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JewelleryCatalogAndInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
    }

    /**
     * Test Scenario 1: Multi-variant fine jewellery product page loads with swatches, purities and sizes.
     */
    public function test_multi_variant_jewellery_piece_displays_selectors_and_dynamic_pricing(): void
    {
        $cat = Category::create(['name' => 'Rings', 'slug' => 'rings', 'is_active' => true]);

        $ring = Product::create([
            'name' => 'Forever Solitaire Diamond Ring',
            'slug' => 'forever-solitaire-diamond-ring',
            'category_id' => $cat->id,
            'price' => 50000.00,
            'status' => 'published',
            'is_featured' => true,
            'images' => ['/images/catalog/ring_yellow_gold.jpg'],
            'attributes' => [
                'metal_colors' => ['Yellow Gold', 'White Gold', 'Rose Gold'],
                'color_images' => [
                    'Yellow Gold' => 'products/colors/ring_yellow_gold.jpg',
                    'White Gold' => 'products/colors/ring_white_gold.jpg',
                    'Rose Gold' => 'products/colors/ring_rose_gold.jpg',
                ],
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'size_label' => 'Ring Size',
                'size_variants' => [
                    ['size' => 'Size 12', 'price' => 50000],
                    ['size' => 'Size 14', 'price' => 52000],
                ],
                'diamond_carat' => '1.50 Carat',
                'diamond_color_clarity' => 'VVS1 · E Color',
                'certification' => 'IGI / GIA Certified',
            ],
        ]);

        $response = $this->get('/shop/' . $ring->slug);
        $response->assertStatus(200);
        $response->assertSee('Forever Solitaire Diamond Ring');
        $response->assertSee('GOLD PURITY');
        $response->assertSee('14KT Gold');
        $response->assertSee('18KT Gold');
        $response->assertSee('22KT Gold');
        $response->assertSee('Size 12');
        $response->assertSee('Size 14');
        $response->assertSee('COLOR');
        $response->assertSee('ADD TO BAG');
        $response->assertSee('BUY NOW');
    }

    /**
     * Test Scenario 2: Single-piece / No-variant jewellery product cleanly hides swatch buttons and purity pills.
     */
    public function test_single_piece_no_variant_product_hides_swatches_and_purity_buttons(): void
    {
        $cat = Category::create(['name' => 'Bangles', 'slug' => 'bangles', 'is_active' => true]);

        $bangle = Product::create([
            'name' => 'Heritage Royal Filigree 22KT Gold Bangle',
            'slug' => 'heritage-royal-filigree-gold-bangle',
            'category_id' => $cat->id,
            'price' => 78000.00,
            'status' => 'published',
            'is_featured' => true,
            'images' => ['/images/catalog/temple_gold_bangle.jpg'],
            'attributes' => [
                'metal_colors' => [], // NO metal color variants
                'gold_purities' => [], // NO purity variants
                'size_variants' => [], // NO size variants
                'diamond_carat' => null,
                'certification' => 'BIS Hallmarked 916 Gold',
                'gold_weight' => '18.45 gms approx',
            ],
        ]);

        $this->assertEquals([], $bangle->metal_colors);
        $this->assertEquals([], $bangle->gold_purities);

        $response = $this->get('/shop/' . $bangle->slug);
        $response->assertStatus(200);
        $response->assertSee('Heritage Royal Filigree 22KT Gold Bangle');
        $response->assertSee('78,000');
        $response->assertSee('ADD TO BAG');
        $response->assertSee('BUY NOW');

        // Verify that purity pills and color swatches are NOT shown
        $this->assertFalse(in_array('14KT Gold', $bangle->gold_purities));
        $this->assertFalse(in_array('Rose Gold', $bangle->metal_colors));
    }

    /**
     * Test Scenario 3: Single-color product displays single text label rather than clickable multiple swatches.
     */
    public function test_single_color_product_shows_clean_single_color_label(): void
    {
        $cat = Category::create(['name' => 'Pendants', 'slug' => 'pendants', 'is_active' => true]);

        $pendant = Product::create([
            'name' => 'Celestial Solitaire Gold Pendant',
            'slug' => 'celestial-solitaire-gold-pendant',
            'category_id' => $cat->id,
            'price' => 32000.00,
            'status' => 'published',
            'is_featured' => true,
            'images' => ['/images/catalog/diamond_pendant_necklace.jpg'],
            'attributes' => [
                'metal_colors' => ['Yellow Gold'], // Only 1 color
                'gold_purities' => ['18KT Gold'], // Only 1 purity
                'size_variants' => [],
            ],
        ]);

        $this->assertEquals(['Yellow Gold'], $pendant->metal_colors);
        $this->assertEquals(['18KT Gold'], $pendant->gold_purities);

        $response = $this->get('/shop/' . $pendant->slug);
        $response->assertStatus(200);
        $response->assertSee('Celestial Solitaire Gold Pendant');
        $response->assertSee('32,000');
    }

    /**
     * Test Scenario 4: Cart addition correctly calculates dynamic pricing for 18KT (+18%) and 22KT (+35%).
     */
    public function test_cart_addition_applies_correct_purity_pricing_server_side(): void
    {
        $cat = Category::create(['name' => 'Rings', 'slug' => 'rings', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Test Diamond Ring',
            'slug' => 'test-diamond-ring',
            'category_id' => $cat->id,
            'price' => 100000.00,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 10,
            'images' => ['/images/catalog/ring_yellow_gold.jpg'],
            'attributes' => [
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
                'metal_colors' => ['Yellow Gold', 'White Gold'],
            ],
        ]);

        // 1. Add 14KT (Base Price 100,000)
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '14KT Gold',
                'metal_color' => 'Yellow Gold',
            ],
        ])->assertRedirect();

        $cart = \App\Models\Cart::current();
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(100000.00, (float)$cart->items()->first()->price);

        // 2. Add 18KT (+18% -> 118,000)
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '18KT Gold',
                'metal_color' => 'Yellow Gold',
            ],
        ])->assertRedirect();

        $cart = \App\Models\Cart::current();
        $item18k = $cart->items()->where('options->gold_purity', '18KT Gold')->first();
        $this->assertNotNull($item18k);
        $this->assertEquals(118000.00, (float)$item18k->price);

        // 3. Add 22KT (+35% -> 135,000)
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'options' => [
                'gold_purity' => '22KT Gold',
                'metal_color' => 'Yellow Gold',
            ],
        ])->assertRedirect();

        $cart = \App\Models\Cart::current();
        $item22k = $cart->items()->where('options->gold_purity', '22KT Gold')->first();
        $this->assertNotNull($item22k);
        $this->assertEquals(135000.00, (float)$item22k->price);
    }

    /**
     * Test Scenario 5: Cart addition for single-piece product adds without options at base price.
     */
    public function test_single_piece_product_adds_to_cart_cleanly_at_base_price(): void
    {
        $cat = Category::create(['name' => 'Bangles', 'slug' => 'bangles', 'is_active' => true]);

        $bangle = Product::create([
            'name' => 'Classic Filigree Bangle',
            'slug' => 'classic-filigree-bangle',
            'category_id' => $cat->id,
            'price' => 78000.00,
            'status' => 'published',
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 5,
            'images' => ['/images/catalog/temple_gold_bangle.jpg'],
            'attributes' => [
                'metal_colors' => [],
                'gold_purities' => [],
                'size_variants' => [],
            ],
        ]);

        $this->post('/cart/add', [
            'product_id' => $bangle->id,
            'quantity' => 1,
        ])->assertRedirect();

        $cart = \App\Models\Cart::current();
        $item = $cart->items()->where('product_id', $bangle->id)->first();
        $this->assertNotNull($item);
        $this->assertEquals(78000.00, (float)$item->price);
    }

    /**
     * Test Scenario 6: Inquiry page loads properly under /inquiry and /custom with correct titles and navbar.
     */
    public function test_inquiry_page_loads_with_inquiry_navigation_labels(): void
    {
        $user = User::create([
            'name' => 'Kavita Verma',
            'email' => 'kavita@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // Test /inquiry route
        $responseInquiry = $this->actingAs($user)->get('/inquiry');
        $responseInquiry->assertStatus(200);
        $responseInquiry->assertSee('BESPOKE JEWELLERY &amp; BULK INQUIRIES', false);
        $responseInquiry->assertSee('Crafted for your signature elegance');

        // Test /custom route (backward compatibility)
        $responseCustom = $this->actingAs($user)->get('/custom');
        $responseCustom->assertStatus(200);
        $responseCustom->assertSee('Crafted for your signature elegance');

        // Verify navbar has 'Inquiry'
        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('Inquiry');
    }

    /**
     * Test Scenario 7: Submitting inquiry form creates inquiry record in database.
     */
    public function test_submitting_jewellery_inquiry_creates_inquiry_record(): void
    {
        $user = User::create([
            'name' => 'Aarav Mehta',
            'email' => 'aarav@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post('/inquiry', [
            'name' => 'Aarav Mehta',
            'email' => 'aarav@example.com',
            'phone' => '+91 98201 12345',
            'whatsapp' => '+91 98201 12345',
            'idea_description' => 'Looking for a bespoke 2.50ct oval natural diamond ring in 18KT yellow gold.',
            'terms_agreed' => '1',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('success?reference=', $response->headers->get('Location'));
        $this->assertDatabaseHas('custom_requests', [
            'name' => 'Aarav Mehta',
            'email' => 'aarav@example.com',
        ]);
    }

    /**
     * Test Scenario 8: Product model table image column generates valid HTTP URLs for all seeded items.
     */
    public function test_all_products_resolve_valid_images_without_broken_paths(): void
    {
        $cat = Category::create(['name' => 'Jewels', 'slug' => 'jewels', 'is_active' => true]);

        $products = [
            Product::create([
                'name' => 'Piece A',
                'slug' => 'piece-a',
                'category_id' => $cat->id,
                'price' => 20000,
                'images' => ['/images/catalog/pear_diamond_earrings.jpg'],
            ]),
            Product::create([
                'name' => 'Piece B',
                'slug' => 'piece-b',
                'category_id' => $cat->id,
                'price' => 30000,
                'images' => ['/images/catalog/temple_gold_bangle.jpg'],
            ]),
        ];

        foreach ($products as $p) {
            $img = is_array($p->images) ? ($p->images[0] ?? null) : $p->images;
            $url = str_starts_with($img, '/') ? url($img) : asset('storage/' . $img);
            $this->assertNotEmpty($url);
            $this->assertNotFalse(filter_var($url, FILTER_VALIDATE_URL));
        }
    }

    /**
     * Test Scenario 9: Shop page filters correctly by metal tone and gold purity.
     */
    public function test_shop_page_filters_by_metal_tone_and_gold_purity(): void
    {
        $cat = Category::create(['name' => 'Fine Jewellery', 'slug' => 'fine-jewellery', 'is_active' => true]);

        // Product A: 14KT & 18KT, Yellow Gold & White Gold
        Product::create([
            'name' => 'Dual Tone Ring',
            'slug' => 'dual-tone-ring',
            'category_id' => $cat->id,
            'price' => 45000,
            'status' => 'published',
            'attributes' => [
                'metal_colors' => ['Yellow Gold', 'White Gold'],
                'gold_purities' => ['14KT Gold', '18KT Gold'],
            ],
        ]);

        // Product B: 22KT only, Rose Gold only
        Product::create([
            'name' => 'Rose Heritage Bangle',
            'slug' => 'rose-heritage-bangle',
            'category_id' => $cat->id,
            'price' => 60000,
            'status' => 'published',
            'attributes' => [
                'metal_colors' => ['Rose Gold'],
                'gold_purities' => ['22KT Gold'],
            ],
        ]);

        // Filter by Rose Gold
        $resColor = $this->get('/shop?color=Rose+Gold');
        $resColor->assertStatus(200);
        $resColor->assertSee('Rose Heritage Bangle');
        $resColor->assertDontSee('Dual Tone Ring');

        // Filter by 14KT Gold
        $resPurity = $this->get('/shop?purity=14KT+Gold');
        $resPurity->assertStatus(200);
        $resPurity->assertSee('Dual Tone Ring');
        $resPurity->assertDontSee('Rose Heritage Bangle');
    }

    /**
     * Test Scenario 10: Product detail page shows MADE TO ORDER or READY TO SHIP badge and no duplicate CERTIFIED text.
     */
    public function test_product_detail_page_shows_inventory_status_badge_and_clean_specs(): void
    {
        $cat = Category::create(['name' => 'Solitaire Rings', 'slug' => 'solitaire-rings', 'is_active' => true]);

        $product = Product::create([
            'name' => 'Marquise Majesty Solitaire Diamond Ring',
            'slug' => 'marquise-majesty-solitaire-diamond-ring-test',
            'category_id' => $cat->id,
            'price' => 125000,
            'inventory_type' => 'MADE_TO_ORDER',
            'status' => 'published',
            'attributes' => [
                'diamond_carat' => '1.75 Carat',
                'diamond_color_clarity' => 'E Color · VVS2 Clarity',
                'certification' => 'GIA Certified',
                'metal_colors' => ['Yellow Gold', 'White Gold', 'Rose Gold'],
                'gold_purities' => ['14KT Gold', '18KT Gold', '22KT Gold'],
            ],
        ]);

        $response = $this->get('/shop/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee('MADE TO ORDER');
        // Ensure "CERTIFIED CERTIFIED" is not present
        $response->assertDontSee('CERTIFIED CERTIFIED');
        $response->assertSee('GIA CERTIFIED');
    }

    /**
     * Test Scenario 11: Admin ProductResource labels are clean (no emojis, no redundant cover text).
     */
    public function test_admin_product_resource_labels_contain_no_emojis_or_redundant_cover_text(): void
    {
        $fileContent = file_get_contents(app_path('Filament/Resources/ProductResource.php'));
        $this->assertStringNotContainsString('🟡', $fileContent);
        $this->assertStringNotContainsString('⚪', $fileContent);
        $this->assertStringNotContainsString('🌸', $fileContent);
        $this->assertStringNotContainsString('(Default First / Cover Image)', $fileContent);
        $this->assertStringContainsString("->label('Yellow Gold Photos')", $fileContent);
        $this->assertStringContainsString("->label('White Gold Photos')", $fileContent);
        $this->assertStringContainsString("->label('Rose Gold Photos')", $fileContent);
    }

    /**
     * Test Scenario 12: Guests can access /inquiry without login required.
     */
    public function test_inquiry_page_allows_unauthenticated_guests(): void
    {
        $response = $this->get('/inquiry');
        $response->assertStatus(200);
        $response->assertSee('Share your requirement.');
    }

    /**
     * Test Scenario 13: Product inquiry with product_id preloads product details into the form.
     */
    public function test_inquiry_prepopulates_product_data_when_product_id_provided(): void
    {
        $cat = Category::create(['name' => 'Solitaire', 'slug' => 'solitaire', 'is_active' => true]);
        $prod = Product::create([
            'name' => 'Celestial Radiance Ring',
            'slug' => 'celestial-radiance-ring',
            'sku' => 'CEL-RAD-001',
            'category_id' => $cat->id,
            'price' => 95000,
            'status' => 'published',
            'images' => ['/images/catalog/marquise_diamond_ring.jpg'],
        ]);

        $user = User::create([
            'name' => 'Kavita Roy',
            'email' => 'kavita@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/inquiry?product_id=' . $prod->id);
        $response->assertStatus(200);
        $response->assertSee('Celestial Radiance Ring');
        $response->assertSee('95,000');
        $response->assertSee('Inquiring regarding Celestial Radiance Ring', false);
    }

    /**
     * Test Scenario 14: Submitting inquiry with reference image file uploads saves image records.
     */
    public function test_submitting_inquiry_with_reference_image_uploads(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::create([
            'name' => 'Vikram Singhania',
            'email' => 'vikram@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $fakeImage = \Illuminate\Http\UploadedFile::fake()->image('design_sketch.jpg', 800, 600);

        $response = $this->actingAs($user)->post('/inquiry', [
            'name' => 'Vikram Singhania',
            'email' => 'vikram@example.com',
            'phone' => 'Nariman Point, Mumbai, India',
            'whatsapp' => '+91 98201 99999',
            'project_type' => 'Bespoke Jewellery Design',
            'idea_description' => 'Custom solitaire pendant with a 2ct round diamond in 18KT white gold.',
            'reference_images' => [$fakeImage],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('custom_requests', [
            'name' => 'Vikram Singhania',
            'email' => 'vikram@example.com',
            'project_type' => 'Bespoke Jewellery Design',
        ]);

        $customRequest = \App\Models\CustomRequest::where('email', 'vikram@example.com')->first();
        $this->assertNotNull($customRequest);
        $this->assertCount(1, $customRequest->images);
    }

    /**
     * Test Scenario 15: Inquiry submission validates required idea description.
     */
    public function test_inquiry_submission_validates_required_idea_description(): void
    {
        $user = User::create([
            'name' => 'Pooja Verma',
            'email' => 'pooja@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post('/inquiry', [
            'name' => 'Pooja Verma',
            'email' => 'pooja@example.com',
            'phone' => 'Delhi, India',
            'whatsapp' => '+91 98201 11111',
            'idea_description' => '', // Empty requirement
        ]);

        $response->assertSessionHasErrors(['idea_description']);
    }

    /**
     * Test Scenario 16: Inquiry submission validates valid Indian WhatsApp number.
     */
    public function test_inquiry_submission_rejects_invalid_whatsapp_number(): void
    {
        $user = User::create([
            'name' => 'Rajesh Sharma',
            'email' => 'rajesh@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post('/inquiry', [
            'name' => 'Rajesh Sharma',
            'email' => 'rajesh@example.com',
            'phone' => 'Jaipur, India',
            'whatsapp' => '12345', // Invalid format
            'idea_description' => 'Looking for emerald cut diamond ring.',
        ]);

        $response->assertSessionHasErrors(['whatsapp']);
    }

    /**
     * Test Scenario 17: Honeypot field silently discards bot submissions without database entry.
     */
    public function test_inquiry_honeypot_silently_discards_bot_submissions(): void
    {
        $user = User::create([
            'name' => 'Bot Spammer',
            'email' => 'bot@spammer.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post('/inquiry', [
            'name' => 'Bot Spammer',
            'email' => 'bot@spammer.com',
            'phone' => 'Unknown',
            'whatsapp' => '+91 98201 22222',
            'idea_description' => 'Spam content advertisement.',
            'website_url_honey' => 'http://spam-link-trap.com', // Honeypot filled by bot
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('custom_requests', [
            'email' => 'bot@spammer.com',
        ]);
    }

    /**
     * Test Scenario 18: Inquiry success page renders clean jewellery advisory text without legacy resin mentions.
     */
    public function test_inquiry_success_page_renders_clean_jewellery_advisory_steps(): void
    {
        $user = User::create([
            'name' => 'Meera Nair',
            'email' => 'meera@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/inquiry/success?reference=MA-2026-TEST123');
        $response->assertStatus(200);
        $response->assertSee('BESPOKE JEWELLERY INQUIRY SUBMITTED');
        $response->assertSee('MA-2026-TEST123');
        $response->assertSee('Artisan &amp; Gemologist Review', false);
        $response->assertDontSee('custom resin artwork');
        $response->assertDontSee('bespoke resin piece');
        $response->assertDontSee('wood slab');
    }

    /**
     * Test Scenario 19: Authenticated customer can view their submitted inquiry in account dashboard.
     */
    public function test_authenticated_customer_can_view_submitted_inquiry_in_account_dashboard(): void
    {
        $user = User::create([
            'name' => 'Devendra Patel',
            'email' => 'devendra@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        \App\Models\CustomRequest::create([
            'public_reference' => 'MA-DEV-789',
            'user_id' => $user->id,
            'name' => 'Devendra Patel',
            'email' => 'devendra@example.com',
            'phone' => 'Surat, Gujarat',
            'whatsapp' => '+91 98201 33333',
            'project_type' => 'Bespoke Jewellery Design',
            'idea_description' => 'Handcrafted 22KT gold temple necklace inquiry.',
            'status' => \App\Enums\CustomRequestStatus::SUBMITTED,
        ]);

        $response = $this->actingAs($user)->get('/account/custom-requests');
        $response->assertStatus(200);
        $response->assertSee('MA-DEV-789');
        $response->assertSee('Handcrafted 22KT gold temple necklace');
    }

    /**
     * Test Scenario 20: Admin can access inquiry / custom requests management in Filament panel.
     */
    public function test_admin_can_access_custom_requests_inquiry_resource_in_filament(): void
    {
        $admin = User::create([
            'name' => 'Head Admin',
            'email' => 'headadmin@atelier.com',
            'password' => bcrypt('password'),
        ]);
        $admin->is_admin = true;
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/admin/custom-requests');
        $response->assertStatus(200);
    }

    /**
     * Test Scenario 21: Product page displays luxury trust badges with icons and Worldwide Dispatch (no Pan India).
     */
    public function test_product_page_displays_luxury_trust_badges_with_icons_and_worldwide_dispatch(): void
    {
        \App\Models\SiteSetting::set('global_badge_1_icon', 'diamond');
        \App\Models\SiteSetting::set('global_badge_1_title', '100% Certified');
        \App\Models\SiteSetting::set('global_badge_1_subtitle', 'NATURAL DIAMONDS');

        \App\Models\SiteSetting::set('global_badge_2_icon', 'hallmark');
        \App\Models\SiteSetting::set('global_badge_2_title', 'BIS Hallmarked');
        \App\Models\SiteSetting::set('global_badge_2_subtitle', '14K - 22K GOLD');

        \App\Models\SiteSetting::set('global_badge_3_icon', 'globe');
        \App\Models\SiteSetting::set('global_badge_3_title', 'Free Insured');
        \App\Models\SiteSetting::set('global_badge_3_subtitle', 'WORLDWIDE DISPATCH');

        $cat = Category::create(['name' => 'Earrings', 'slug' => 'earrings', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Royal Solitaire Studs',
            'slug' => 'royal-solitaire-studs',
            'category_id' => $cat->id,
            'price' => 45000.00,
            'status' => 'published',
            'is_featured' => true,
            'images' => ['/images/catalog/earrings.jpg'],
        ]);

        $response = $this->get('/shop/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee('100% Certified');
        $response->assertSee('NATURAL DIAMONDS');
        $response->assertSee('BIS Hallmarked');
        $response->assertSee('14K - 22K GOLD');
        $response->assertSee('Free Insured');
        $response->assertSee('WORLDWIDE DISPATCH');
        $response->assertDontSee('PAN INDIA DISPATCH');
        $response->assertDontSee('PAN INDIA SHIP');
    }

    /**
     * Test Scenario 22: Custom inquiry page displays Worldwide Express Delivery.
     */
    public function test_custom_inquiry_page_displays_worldwide_express_delivery(): void
    {
        $user = User::create([
            'name' => 'Meera Kapoor',
            'email' => 'meera.inquiry@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/inquiry');
        $response->assertStatus(200);
        $response->assertSee('100% Insured Worldwide Express Delivery');
        $response->assertDontSee('100% Insured Pan-India Express Delivery');
    }

    /**
     * Test Scenario 23: Admin can access TrustBadgesSettings in Filament panel.
     */
    public function test_admin_can_access_trust_badges_settings_page(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@atelier.com',
            'password' => bcrypt('password'),
        ]);
        $admin->is_admin = true;
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/admin/trust-badges-settings');
        $response->assertStatus(200);
        $response->assertSee('Top Trust Badges');
        $response->assertSee('Badge 1');
        $response->assertSee('Badge 2');
        $response->assertSee('Badge 3');
    }

    /**
     * Test Scenario 24: Unauthenticated guest can access inquiry page without logging in (Delivery Address removed).
     */
    public function test_unauthenticated_guest_can_access_inquiry_page_without_auth(): void
    {
        $response = $this->get('/inquiry');
        $response->assertStatus(200);
        $response->assertSee('INQUIRY FORM');
        $response->assertSee('website_url_honey');
        $response->assertSee('form_time_token');
        $response->assertSee('Bespoke / Custom Jewellery Design');
        $response->assertSee('Bulk &amp; Wholesale B2B Order', false);
        $response->assertSee('General Inquiry &amp; Consultation', false);
        $response->assertDontSee('Delivery Address / Destination');
        $response->assertDontSee('Custom Ring, Necklace, Pendant, Bracelet');
        $response->assertDontSee('Or Inquire for Specific Category');
    }

    /**
     * Test Scenario 25: Unauthenticated guest can submit inquiry with up to 5 photos and international phone number.
     */
    public function test_guest_can_submit_inquiry_with_up_to_5_photos_and_international_phone(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $fakeImages = [
            \Illuminate\Http\UploadedFile::fake()->image('sketch1.jpg', 600, 600),
            \Illuminate\Http\UploadedFile::fake()->image('sketch2.jpg', 600, 600),
            \Illuminate\Http\UploadedFile::fake()->image('sketch3.jpg', 600, 600),
            \Illuminate\Http\UploadedFile::fake()->image('sketch4.jpg', 600, 600),
            \Illuminate\Http\UploadedFile::fake()->image('sketch5.jpg', 600, 600),
        ];

        $response = $this->post('/inquiry', [
            'name' => 'Alexander Wright',
            'email' => 'alexander.wright@london.co.uk',
            'whatsapp' => '+44 7911 123456', // UK international phone number
            'project_type' => 'Bespoke Handcrafted Design',
            'idea_description' => 'Looking to craft a bespoke heirloom solitaire piece with certified natural diamonds.',
            'reference_images' => $fakeImages,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('custom_requests', [
            'name' => 'Alexander Wright',
            'email' => 'alexander.wright@london.co.uk',
            'user_id' => null, // Guest submission
            'project_type' => 'Bespoke Handcrafted Design',
        ]);

        $inquiry = \App\Models\CustomRequest::where('email', 'alexander.wright@london.co.uk')->first();
        $this->assertNotNull($inquiry);
        $this->assertCount(5, $inquiry->images); // 5 photos supported
    }

    /**
     * Test Scenario 26: Contact page includes anti-bot honeypot and time trap protection.
     */
    public function test_contact_page_has_anti_bot_protection_and_accepts_international_phone(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertSee('website_url');
        $response->assertSee('form_time_token');

        $postResponse = $this->post('/contact', [
            'name' => 'Sophia Laurent',
            'email' => 'sophia@parisjewels.fr',
            'phone' => '+33 1 42 68 55 00', // French international number
            'inquiry_type' => 'general',
            'subject' => 'Atelier Private Viewing',
            'message' => 'Requesting an appointment for private diamond selection.',
        ]);

        $postResponse->assertStatus(200);
        $this->assertDatabaseHas('contact_inquiries', [
            'email' => 'sophia@parisjewels.fr',
        ]);
    }

    /**
     * Test Scenario 27: Real user data submission succeeds end-to-end even without reCAPTCHA configured.
     */
    public function test_real_user_data_submission_without_recaptcha_succeeds_end_to_end(): void
    {
        $payload = [
            'name' => 'Sandip Rathod',
            'email' => 'sandip200611@gmail.com',
            'whatsapp' => '+91 98201 45678',
            'project_type' => 'Bespoke Jewellery Design',
            'idea_description' => 'Looking for a bespoke handcrafted 18KT yellow gold diamond engagement ring with a certified 1.50 ct solitaire diamond.',
            'recaptcha_token' => '', // reCAPTCHA not configured / empty token works seamlessly
        ];

        $response = $this->post('/inquiry', $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('custom_requests', [
            'name' => 'Sandip Rathod',
            'email' => 'sandip200611@gmail.com',
            'whatsapp' => '+91 98201 45678',
            'project_type' => 'Bespoke Jewellery Design',
            'status' => \App\Enums\CustomRequestStatus::SUBMITTED->value,
        ]);

        $inquiry = \App\Models\CustomRequest::where('email', 'sandip200611@gmail.com')->first();
        $this->assertNotNull($inquiry);
        $this->assertStringStartsWith('CR-', $inquiry->public_reference);

        // Verify success page renders seamlessly
        $successResponse = $this->get(route('inquiry.success', ['reference' => $inquiry->public_reference]));
        $successResponse->assertStatus(200);
        $successResponse->assertSee($inquiry->public_reference);
        $successResponse->assertSee('Inquiry Received');
    }
}


