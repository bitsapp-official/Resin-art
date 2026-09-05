<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAddressPhoneTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'phone_test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function profile_rejects_invalid_indian_phone_numbers()
    {
        $this->actingAs($this->user);

        // 9-digit number
        $response = $this->put(route('account.profile.update'), [
            'name' => 'Valid Name',
            'phone' => '987456321',
        ]);
        $response->assertSessionHasErrors('phone');

        // Invalid prefix starting with 3
        $response = $this->put(route('account.profile.update'), [
            'name' => 'Valid Name',
            'phone' => '3123456789',
        ]);
        $response->assertSessionHasErrors('phone');

        // Dummy repeated digits
        $response = $this->put(route('account.profile.update'), [
            'name' => 'Valid Name',
            'phone' => '8888888888',
        ]);
        $response->assertSessionHasErrors('phone');

        // Valid Indian phone (+91 format)
        $response = $this->put(route('account.profile.update'), [
            'name' => 'Valid Name',
            'phone' => '+91 98201 45678',
        ]);
        $response->assertSessionDoesntHaveErrors('phone');
        $this->assertEquals('+91 98201 45678', $this->user->fresh()->phone);
    }

    /** @test */
    public function address_book_rejects_invalid_indian_phone_numbers()
    {
        $this->actingAs($this->user);

        $baseAddress = [
            'address_line_1' => 'Flat 402, Royal Palms, MG Road',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400053',
            'country' => 'India',
        ];

        // 9-digit number
        $response = $this->post(route('account.addresses.store'), array_merge($baseAddress, ['phone' => '987456321']));
        $response->assertSessionHasErrors('phone');

        // Invalid prefix starting with 5
        $response = $this->post(route('account.addresses.store'), array_merge($baseAddress, ['phone' => '5123456789']));
        $response->assertSessionHasErrors('phone');

        // Valid 10-digit number
        $response = $this->post(route('account.addresses.store'), array_merge($baseAddress, ['phone' => '9820145678']));
        $response->assertSessionDoesntHaveErrors('phone');
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'phone' => '9820145678',
            'city' => 'Mumbai',
        ]);
    }
}
