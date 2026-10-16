<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderCustomizationPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_items_preview_renders_customizations_and_store_links_without_errors(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Diamond Rings',
            'slug' => 'diamond-rings',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Eternity Solitaire Ring',
            'slug' => 'eternity-solitaire-ring',
            'price' => 75000,
            'base_price' => 75000,
            'gold_purities' => ['18KT Gold', '22KT Gold'],
            'metal_colors' => ['Rose Gold', 'Yellow Gold'],
            'sizes' => ['Size 14', 'Size 16'],
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_reference' => 'ORD-TEST-9988',
            'user_id' => $admin->id,
            'email' => 'client@solitaire.com',
            'currency' => 'INR',
            'subtotal' => 75000,
            'tax_total' => 2250,
            'shipping_total' => 0,
            'grand_total' => 77250,
            'payment_status' => 'paid',
            'fulfillment_status' => 'processing',
            'shipping_address_snapshot' => [
                'full_name' => 'Aarav Mehta',
                'address_line_1' => 'Bandra West',
                'city' => 'Mumbai',
                'postal_code' => '400050',
                'country' => 'IN',
            ],
        ]);

        // Item 1: Linked product with custom options
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 75000,
            'quantity' => 1,
            'subtotal' => 75000,
            'options' => [
                'metal_color' => 'Rose Gold',
                'gold_purity' => '18KT Gold',
                'size' => 'Size 16',
            ],
            'product_snapshot' => [
                'name' => $product->name,
                'slug' => $product->slug,
                'category' => 'Diamond Rings',
            ],
        ]);

        // Item 2: Unlinked product (e.g. deleted or snapshot only)
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => null,
            'product_name' => 'Vintage Gold Choker',
            'unit_price' => 120000,
            'quantity' => 1,
            'subtotal' => 120000,
            'options' => [
                'metal_color' => 'Yellow Gold',
                'gold_purity' => '22KT Gold',
            ],
            'product_snapshot' => [
                'name' => 'Vintage Gold Choker',
                'slug' => 'vintage-gold-choker',
                'category' => 'Necklaces',
            ],
        ]);

        // Test rendering the blade view directly
        $view = view('filament.orders.items-preview', [
            'getRecord' => fn () => $order->load('items.product.category'),
        ])->render();

        $this->assertStringContainsString('Eternity Solitaire Ring', $view);
        $this->assertStringContainsString('Rose Gold', $view);
        $this->assertStringContainsString('18KT Gold', $view);
        $this->assertStringContainsString('Size 16', $view);
        $this->assertStringContainsString('View in Store', $view);
        $this->assertStringContainsString('color=Rose', $view);
        $this->assertStringContainsString('purity=18KT', $view);
        $this->assertStringContainsString('size=Size', $view);

        // Test accessing Filament Order Edit page as Admin
        $response = $this->actingAs($admin, 'admin')->get("/admin/orders/{$order->id}/edit");
        $response->assertSuccessful();
        $response->assertSee('ORD-TEST-9988');
        $response->assertSee('Rose Gold');
        $response->assertSee('18KT Gold');
    }
}
