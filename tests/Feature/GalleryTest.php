<?php

namespace Tests\Feature;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic admin user and categories if needed
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
    }

    public function test_public_gallery_page_returns_successful_response(): void
    {
        $response = $this->get('/gallery');

        $response->assertStatus(200);
        $response->assertSee('Pieces in place.');
        $response->assertSee('GALLERY');
    }

    public function test_active_gallery_items_appear_publicly(): void
    {
        $category = GalleryCategory::create([
            'name' => 'Tables',
            'slug' => 'tables',
            'is_active' => true,
        ]);

        $item = GalleryItem::create([
            'gallery_category_id' => $category->id,
            'title' => 'Sègre River Table N°04',
            'slug' => 'segre-river-table-n04',
            'image_path' => 'demo/hero_atelier.png',
            'image_alt' => 'Sègre River Table',
            'is_active' => true,
        ]);

        $response = $this->get('/gallery');

        $response->assertStatus(200);
        $response->assertSee('Sègre River Table N°04');
    }

    public function test_inactive_gallery_items_do_not_appear_publicly(): void
    {
        $category = GalleryCategory::create([
            'name' => 'Tables',
            'slug' => 'tables',
            'is_active' => true,
        ]);

        $item = GalleryItem::create([
            'gallery_category_id' => $category->id,
            'title' => 'Secret Draft Table',
            'slug' => 'secret-draft-table',
            'image_path' => 'demo/hero_atelier.png',
            'image_alt' => 'Secret Draft Table',
            'is_active' => false,
        ]);

        $response = $this->get('/gallery');

        $response->assertStatus(200);
        $response->assertDontSee('Secret Draft Table');
    }

    public function test_category_filtering_works_correctly(): void
    {
        $cat1 = GalleryCategory::create(['name' => 'Tables', 'slug' => 'tables', 'is_active' => true]);
        $cat2 = GalleryCategory::create(['name' => 'Wall Art', 'slug' => 'wall-art', 'is_active' => true]);

        $item1 = GalleryItem::create([
            'gallery_category_id' => $cat1->id,
            'title' => 'Awesome River Table',
            'slug' => 'awesome-river-table',
            'image_path' => 'demo/hero_atelier.png',
            'image_alt' => 'Awesome River Table',
            'is_active' => true,
        ]);

        $item2 = GalleryItem::create([
            'gallery_category_id' => $cat2->id,
            'title' => 'Oceanic Relief Wall Panel',
            'slug' => 'oceanic-relief-wall-panel',
            'image_path' => 'demo/hero_atelier.png',
            'image_alt' => 'Oceanic Relief Wall Panel',
            'is_active' => true,
        ]);

        $response = $this->get('/gallery?category=tables');

        $response->assertStatus(200);
        $response->assertSee('Awesome River Table');
        $response->assertDontSee('Oceanic Relief Wall Panel');
    }

    public function test_invalid_category_slug_falls_back_gracefully(): void
    {
        $category = GalleryCategory::create(['name' => 'Tables', 'slug' => 'tables', 'is_active' => true]);
        $item = GalleryItem::create([
            'gallery_category_id' => $category->id,
            'title' => 'Standard Gallery Piece',
            'slug' => 'standard-gallery-piece',
            'image_path' => 'demo/hero_atelier.png',
            'image_alt' => 'Standard Gallery Piece',
            'is_active' => true,
        ]);

        $response = $this->get('/gallery?category=non-existent-category');

        $response->assertStatus(200);
        $response->assertSee('Standard Gallery Piece');
    }

    public function test_server_side_pagination_works_and_preserves_query_string(): void
    {
        $category = GalleryCategory::create(['name' => 'Tables', 'slug' => 'tables', 'is_active' => true]);

        for ($i = 1; $i <= 25; $i++) {
            GalleryItem::create([
                'gallery_category_id' => $category->id,
                'title' => "Gallery Item Number {$i}",
                'slug' => "gallery-item-number-{$i}",
                'image_path' => 'demo/hero_atelier.png',
                'image_alt' => "Gallery Item Number {$i}",
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $response = $this->get('/gallery?category=tables&page=2');

        $response->assertStatus(200);
        $response->assertSee('Gallery Item Number 21');
    }

    public function test_unauthorized_user_cannot_access_gallery_cms(): void
    {
        $response = $this->get('/admin/gallery-items');
        $response->assertRedirect('/admin/login');
    }

    public function test_authorized_admin_can_access_gallery_cms(): void
    {
        $admin = User::where('email', 'admin@maisonresine.com')->first();

        $response = $this->actingAs($admin)->get('/admin/gallery-items');
        $response->assertStatus(200);
    }
}
