<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
    }

    public function test_public_collections_index_page_returns_successful_response(): void
    {
        $response = $this->get('/collections');

        $response->assertStatus(200);
        $response->assertSee('COLLECTIONS');
        $response->assertSee('Four ways to');
    }

    public function test_only_active_collections_appear_on_index_page(): void
    {
        $activeCollection = Collection::create([
            'name' => 'Active Ocean Series',
            'slug' => 'active-ocean-series',
            'description' => 'Active ocean series description',
            'status' => 'ACTIVE',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $inactiveCollection = Collection::create([
            'name' => 'Hidden Secret Series',
            'slug' => 'hidden-secret-series',
            'description' => 'Hidden collection description',
            'status' => 'INACTIVE',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get('/collections');

        $response->assertStatus(200);
        $response->assertSee('Active Ocean Series');
        $response->assertDontSee('Hidden Secret Series');
    }

    public function test_collection_slug_resolves_correctly_on_detail_page(): void
    {
        $collection = Collection::create([
            'name' => 'Walnut Slab Collection',
            'slug' => 'walnut-slab-collection',
            'description' => 'Unique handcrafted walnut slabs.',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $response = $this->get('/collections/walnut-slab-collection');

        $response->assertStatus(200);
        $response->assertSee('Walnut Slab Collection');
        $response->assertSee('Unique handcrafted walnut slabs.');
    }

    public function test_invalid_collection_slug_returns_404(): void
    {
        $response = $this->get('/collections/non-existent-slug');

        $response->assertStatus(404);
    }

    public function test_inactive_collection_slug_returns_404(): void
    {
        Collection::create([
            'name' => 'Draft Collection',
            'slug' => 'draft-collection',
            'status' => 'INACTIVE',
            'is_active' => false,
        ]);

        $response = $this->get('/collections/draft-collection');

        $response->assertStatus(404);
    }

    public function test_only_published_products_appear_on_collection_detail_page(): void
    {
        $collection = Collection::create([
            'name' => 'River Collection',
            'slug' => 'river-collection',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Tables',
            'slug' => 'tables',
            'is_active' => true,
        ]);

        $publishedProduct = Product::create([
            'name' => 'Published River Table',
            'slug' => 'published-river-table',
            'sku' => 'RIV-001',
            'price' => 45000.00,
            'category_id' => $category->id,
            'status' => 'published',
            'stock' => 5,
        ]);

        $draftProduct = Product::create([
            'name' => 'Draft Unreleased Table',
            'slug' => 'draft-unreleased-table',
            'sku' => 'RIV-002',
            'price' => 50000.00,
            'category_id' => $category->id,
            'status' => 'draft',
            'stock' => 5,
        ]);

        // Attach both to collection via pivot table
        $collection->products()->attach([$publishedProduct->id, $draftProduct->id]);

        $response = $this->get('/collections/river-collection');

        $response->assertStatus(200);
        $response->assertSee('Published River Table');
        $response->assertDontSee('Draft Unreleased Table');
    }

    public function test_product_can_belong_to_multiple_collections(): void
    {
        $collectionA = Collection::create([
            'name' => 'Collection Alpha',
            'slug' => 'collection-alpha',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $collectionB = Collection::create([
            'name' => 'Collection Beta',
            'slug' => 'collection-beta',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Wall Pieces',
            'slug' => 'wall-pieces',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Versatile Wave Piece',
            'slug' => 'versatile-wave-piece',
            'sku' => 'VERS-001',
            'price' => 18000.00,
            'category_id' => $category->id,
            'status' => 'published',
            'stock' => 2,
        ]);

        // Attach product to BOTH collections
        $product->collections()->attach([$collectionA->id, $collectionB->id]);

        $this->assertCount(2, $product->collections);

        // Verify product appears on both collection detail pages
        $this->get('/collections/collection-alpha')->assertSee('Versatile Wave Piece');
        $this->get('/collections/collection-beta')->assertSee('Versatile Wave Piece');
    }

    public function test_duplicate_collection_product_pivot_relationship_is_prevented(): void
    {
        $collection = Collection::create([
            'name' => 'Unique Pivot Series',
            'slug' => 'unique-pivot-series',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Coasters',
            'slug' => 'coasters',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Single Coaster Set',
            'slug' => 'single-coaster-set',
            'sku' => 'CST-999',
            'price' => 3500.00,
            'category_id' => $category->id,
            'status' => 'published',
            'stock' => 10,
        ]);

        $collection->products()->attach($product->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $collection->products()->attach($product->id);
    }

    public function test_customer_can_view_collections_without_login(): void
    {
        $response = $this->get('/collections');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_filament_collection_management(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'admin')->get('/admin/collections');

        $response->assertStatus(200);
    }

    public function test_unauthorized_guest_cannot_access_filament_collection_management(): void
    {
        $response = $this->get('/admin/collections');

        $response->assertRedirect('/admin/login');
    }

    public function test_empty_collection_displays_curation_notice(): void
    {
        $collection = Collection::create([
            'name' => 'Empty Curated Collection',
            'slug' => 'empty-curated-collection',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $response = $this->get('/collections/empty-curated-collection');

        $response->assertStatus(200);
        $response->assertSee('This collection is currently being curated.');
    }

    public function test_collection_seo_metadata_renders_correctly(): void
    {
        $collection = Collection::create([
            'name' => 'SEO Featured Collection',
            'slug' => 'seo-featured-collection',
            'meta_title' => 'Custom SEO Title | Maison Résine',
            'meta_description' => 'Custom SEO Meta Description for Collection',
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        $response = $this->get('/collections/seo-featured-collection');

        $response->assertStatus(200);
        $response->assertSee('Custom SEO Title | Maison Résine', false);
        $response->assertSee('Custom SEO Meta Description for Collection', false);
    }
}
