<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\EcommerceSeeder::class);
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->product = Product::published()->where('inventory_type', 'READY_TO_SHIP')->where('stock', '>', 2)->first();
    }

    private function setupCart()
    {
        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => $this->product->price,
        ]);
        $cart->recalculateTotal();
    }

    /** @test */
    public function it_requires_all_mandatory_address_fields()
    {
        $this->actingAs($this->user);
        $this->setupCart();

        $response = $this->post(route('checkout.process'), [
            // Intentionally leave address_line_1 empty
            'address_line_1' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => '',
            'phone' => '',
            // other required fields
            'full_name' => 'John Doe',
            'email' => $this->user->email,
            'payment_method' => 'cod',
        ]);

        $response->assertSessionHasErrors([
            'address_line_1',
            'city',
            'state',
            'postal_code',
            'country',
            'phone',
        ]);
    }

    /** @test */
    public function it_validates_phone_number_correctly()
    {
        $this->actingAs($this->user);
        $this->setupCart();

        // 9-digit phone (must be rejected)
        $response = $this->post(route('checkout.process'), $this->validData(['phone' => '987456321']));
        $response->assertSessionHasErrors('phone');

        // Invalid Indian prefix (starting with 4, not 6-9)
        $response = $this->post(route('checkout.process'), $this->validData(['phone' => '4123456789']));
        $response->assertSessionHasErrors('phone');

        // Repeated digits dummy pattern
        $response = $this->post(route('checkout.process'), $this->validData(['phone' => '9999999999']));
        $response->assertSessionHasErrors('phone');

        // Valid Indian phone (+91 format) should pass
        $response = $this->post(route('checkout.process'), $this->validData(['phone' => '+91 98201 45678']));
        $response->assertSessionDoesntHaveErrors('phone');

        // Valid Indian phone (10-digit format) should pass
        $response = $this->post(route('checkout.process'), $this->validData(['phone' => '9820145678']));
        $response->assertSessionDoesntHaveErrors('phone');
    }

    /** @test */
    public function it_successfully_creates_an_order_with_valid_data()
    {
        $this->actingAs($this->user);
        $this->setupCart();

        $response = $this->post(route('checkout.process'), $this->validData());
        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'payment_method' => 'stripe',
        ]);
    }

    /**
     * Helper to generate a full set of valid data, optionally overriding fields.
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'John Doe',
            'email' => $this->user->email,
            'phone' => '+91 98201 45678',
            'address_line_1' => 'Flat 402, Royal Palms Society, MG Road',
            'address_line_2' => 'Near Phoenix Mall, Andheri West',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400053',
            'country' => 'India',
            'payment_method' => 'card',
        ], $overrides);
    }
}

