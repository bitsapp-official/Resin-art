<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JewelleryColorImagesFallbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_with_only_color_images_falls_back_gracefully_in_model_and_catalog()
    {
        $category = Category::create([
            'name' => 'Diamond Bracelets',
            'slug' => 'diamond-bracelets',
            'status' => 'active',
        ]);

        $product = Product::create([
            'name' => 'Solitairekart 0.41ct Floral Bloom Natural Diamond Bracelet',
            'slug' => 'solitairekart-041ct-floral-bloom-natural-diamond-bracelet',
            'sku' => 'SK-BR-041',
            'category_id' => $category->id,
            'price' => 126486,
            'inventory_type' => 'READY_TO_SHIP',
            'stock' => 5,
            'status' => 'published',
            'images' => null, // Main gallery left empty by admin
            'attributes' => [
                'metal_colors' => ['Yellow Gold', 'White Gold', 'Rose Gold'],
                'color_images' => [
                    'Yellow Gold' => 'products/colors/test_yellow.webp',
                    'White Gold'  => 'products/colors/test_white.webp',
                    'Rose Gold'   => 'products/colors/test_rose.webp',
                ],
                'gold_purities' => ['18KT Gold', '22KT Gold', '14KT Gold'],
            ],
        ]);

        // 1. Model should automatically auto-sync or fall back to color_images
        $this->assertNotEmpty($product->images);
        $this->assertCount(3, $product->images);
        $this->assertNotNull($product->primary_image_url);
        $this->assertStringContainsString('test_yellow', $product->primary_image_url);

        // 2. Shop catalog page should render the image and swatches without broken SVG placeholder
        $response = $this->get(route('shop.index'));
        $response->assertStatus(200);
        $response->assertSee('test_yellow.webp');
        $response->assertSee('Yellow Gold');
        $response->assertSee('White Gold');
        $response->assertSee('Rose Gold');

        // 3. Product detail page should support ?color=White+Gold query parameter
        $detailResponse = $this->get(route('shop.show', ['slug' => $product->slug, 'color' => 'White Gold']));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('White Gold');
        $detailResponse->assertSee('test_white.webp');
    }

    /** @test */
    public function product_with_multi_photo_color_galleries_renders_dynamic_gallery_and_falls_back()
    {
        $category = Category::create([
            'name' => 'Solitaire Rings',
            'slug' => 'solitaire-rings',
            'status' => 'active',
        ]);

        $product = Product::create([
            'name' => 'Endless Devotion 2.83ct Solitaire Ring',
            'slug' => 'endless-devotion-283ct-solitaire-ring',
            'sku' => 'SK-RNG-283',
            'category_id' => $category->id,
            'price' => 245000,
            'inventory_type' => 'MADE_TO_ORDER',
            'stock' => 10,
            'status' => 'published',
            'images' => null,
            'attributes' => [
                'metal_colors' => ['Yellow Gold', 'White Gold', 'Rose Gold'],
                'color_images' => [
                    'Yellow Gold' => [
                        'products/colors/ring_yg_front.webp',
                        'products/colors/ring_yg_angle.webp',
                    ],
                    'White Gold' => [
                        'products/colors/ring_wg_front.webp',
                        'products/colors/ring_wg_hand.webp',
                    ],
                    'Rose Gold' => [
                        'products/colors/ring_rg_front.webp',
                    ],
                ],
            ],
        ]);

        // 1. color_galleries should return arrays for each color
        $galleries = $product->color_galleries;
        $this->assertCount(2, $galleries['Yellow Gold']);
        $this->assertCount(2, $galleries['White Gold']);
        $this->assertCount(1, $galleries['Rose Gold']);

        // 2. color_images primary accessor should return first photo of each color
        $colorImages = $product->color_images;
        $this->assertStringContainsString('ring_yg_front', $colorImages['Yellow Gold']);
        $this->assertStringContainsString('ring_wg_front', $colorImages['White Gold']);
        $this->assertStringContainsString('ring_rg_front', $colorImages['Rose Gold']);

        // 3. images attribute should contain all 5 photos
        $this->assertCount(5, $product->images);

        // 4. Product page should render with all color images and Alpine productDetail config
        $response = $this->get(route('shop.show', ['slug' => $product->slug]));
        $response->assertStatus(200);
        $response->assertSee('ring_yg_front.webp');
        $response->assertSee('ring_yg_angle.webp');
        $response->assertSee('ring_wg_front.webp');
        $response->assertSee('ring_wg_hand.webp');
        $response->assertSee('ring_rg_front.webp');
    }
}
