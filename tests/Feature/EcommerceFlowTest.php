<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\EcommerceSeeder::class);
    }

    /** @test */
    public function shop_index_displays_products_and_supports_filters()
    {
        $response = $this->get(route('shop.index'));
        $response->assertStatus(200);
        $response->assertSee('THE INDEX');

        // Test filtering by category
        $category = Category::first();
        $filteredResponse = $this->get(route('shop.index', ['category' => $category->slug]));
        $filteredResponse->assertStatus(200);
    }

    /** @test */
    public function product_detail_page_loads_correctly()
    {
        $product = Product::published()->first();
        $response = $this->get(route('shop.show', $product->slug));
        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /** @test */
    public function user_can_add_item_to_cart_and_update_quantity()
    {
        $user = User::factory()->create();
        $product = Product::published()->where('inventory_type', 'READY_TO_SHIP')->first();

        // Add to cart
        $response = $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));

        // View Cart
        $cartResponse = $this->actingAs($user)->get(route('cart.index'));
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee($product->name);

        $cart = Cart::where('user_id', $user->id)->first();
        $cartItem = $cart->items->first();

        // Update quantity
        $updateResponse = $this->actingAs($user)->post(route('cart.update'), [
            'item_id' => $cartItem->id,
            'quantity' => 3,
        ]);
        $updateResponse->assertSessionHasNoErrors();
        $this->assertEquals(3, $cartItem->fresh()->quantity);
    }

    /** @test */
    public function wishlist_prevents_duplicate_records_for_user()
    {
        $user = User::factory()->create();
        $product = Product::published()->first();

        $this->actingAs($user)->post(route('wishlist.toggle'), ['product_id' => $product->id]);
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);

        // Toggle again removes it
        $this->actingAs($user)->post(route('wishlist.toggle'), ['product_id' => $product->id]);
        $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    /** @test */
    public function user_can_complete_checkout_and_place_order()
    {
        $user = User::factory()->create();
        $product = Product::published()->where('inventory_type', 'READY_TO_SHIP')->where('stock', '>', 2)->first();
        $initialStock = $product->stock;

        // Add item to user cart
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->effective_price,
        ]);
        $cart->recalculateTotal();

        $checkoutData = [
            'email' => $user->email,
            'full_name' => $user->name,
            'phone' => '+91 98201 45678',
            'address_line_1' => 'Flat 402, Royal Palms, MG Road',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400053',
            'country' => 'India',
            'payment_method' => 'card',
        ];

        $response = $this->actingAs($user)->post(route('checkout.process'), $checkoutData);

        $order = Order::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('checkout.confirmation', $order->order_reference));

        // Stock deduction check
        $this->assertEquals($initialStock - 2, $product->fresh()->stock);

        // Address snapshot check
        $this->assertEquals('Flat 402, Royal Palms, MG Road', $order->shipping_address_snapshot['address_line_1']);
    }

    /** @test */
    public function guest_can_track_order_with_valid_reference_and_email()
    {
        $order = Order::create([
            'order_reference' => 'MR-2026-TEST99',
            'email'           => 'guest@example.com',
            'status'          => 'SHIPPED',
            'payment_status'  => 'paid',
            'payment_method'  => 'card',
            'subtotal'        => 100.00,
            'grand_total'     => 105.00,
            'shipping_address_snapshot' => ['full_name' => 'Guest User'],
        ]);

        // Search with correct reference and email via POST (email stays out of URL)
        $response = $this->post(route('tracking.search'), [
            'order_reference' => 'MR-2026-TEST99',
            'email'           => 'guest@example.com',
        ]);

        // Should redirect back to GET index with session result
        $response->assertRedirect(route('tracking.index'));

        // Follow redirect and check result is shown
        $followed = $this->followRedirects($response);
        $followed->assertStatus(200);
        $followed->assertSee('MR-2026-TEST99');
        $followed->assertSee('PROGRESS');

        // Invalid email — should redirect with error in session
        $invalidResponse = $this->post(route('tracking.search'), [
            'order_reference' => 'MR-2026-TEST99',
            'email'           => 'wrong@example.com',
        ]);

        // Invalid email — should redirect back (order not found)
        $invalidResponse->assertRedirect(route('tracking.index'));
    }

    /** @test */
    public function customer_cannot_access_another_customers_order_detail_idor_check()
    {
        $customerA = User::factory()->create();
        $customerB = User::factory()->create();

        $orderB = Order::create([
            'order_reference' => 'MR-2026-CUSTOMERB',
            'user_id' => $customerB->id,
            'email' => $customerB->email,
            'status' => 'CONFIRMED',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'subtotal' => 200.00,
            'grand_total' => 210.00,
            'shipping_address_snapshot' => ['full_name' => 'Customer B'],
        ]);

        // Customer A tries to access Customer B's order details
        $response = $this->actingAs($customerA)->get(route('account.orders.show', $orderB->order_reference));

        $response->assertStatus(403);
    }

    /** @test */
    public function new_arrivals_page_loads_correctly()
    {
        $response = $this->get(route('shop.new-arrivals'));
        $response->assertStatus(200);
        $response->assertSee('NEW CREATIONS');
        $response->assertSee('New arrivals.');
    }

    /** @test */
    public function best_sellers_page_loads_correctly()
    {
        $response = $this->get(route('shop.best-sellers'));
        $response->assertStatus(200);
        $response->assertSee('MOST LOVED');
        $response->assertSee('Best sellers.');
    }
}
