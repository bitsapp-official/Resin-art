<?php

namespace Tests\Feature;

use App\Enums\CustomRequestStatus;
use App\Models\CustomRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_request_page_redirects_guests_to_login(): void
    {
        $response = $this->get('/custom');
        $response->assertRedirect(route('login'));
    }

    public function test_custom_request_page_shows_form_to_authenticated_users(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get('/custom');
        $response->assertStatus(200);
        $response->assertSee('Made for your space.');
    }

    public function test_custom_request_submission_creates_record_and_redirects(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Sophia Laurent',
            'email' => 'sophia@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $refFile = UploadedFile::fake()->image('ref1.jpg', 800, 600);

        $response = $this->actingAs($user)->post('/custom', [
            'name' => 'Sophia Laurent',
            'email' => 'sophia@example.com',
            'phone' => '102 Green Park, Mumbai, Maharashtra, India',
            'whatsapp' => '+91 98201 45678',
            'idea_description' => 'A large river table with walnut slabs and deep ocean resin.',
            'terms_agreed' => '1',
            'reference_images' => [$refFile],
        ]);

        $this->assertDatabaseHas('custom_requests', [
            'name' => 'Sophia Laurent',
            'email' => 'sophia@example.com',
            'status' => CustomRequestStatus::SUBMITTED->value,
        ]);

        $requestRecord = CustomRequest::where('email', 'sophia@example.com')->first();
        $this->assertNotNull($requestRecord);
        $this->assertStringStartsWith('CR-', $requestRecord->public_reference);

        $response->assertRedirect(route('custom.success', ['reference' => $requestRecord->public_reference]));
    }

    public function test_custom_request_rejects_invalid_whatsapp_number(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_invalid@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // 8-digit dummy number 12345678
        $response = $this->actingAs($user)->post('/custom', [
            'name' => 'Test User',
            'email' => 'test_invalid@example.com',
            'phone' => '102 Green Park, Mumbai, India',
            'whatsapp' => '12345678',
            'idea_description' => 'Test artwork requirement description.',
        ]);

        $response->assertSessionHasErrors('whatsapp');

        // +91 with only 8 digits
        $response = $this->actingAs($user)->post('/custom', [
            'name' => 'Test User',
            'email' => 'test_invalid@example.com',
            'phone' => '102 Green Park, Mumbai, India',
            'whatsapp' => '+91 12345678',
            'idea_description' => 'Test artwork requirement description.',
        ]);

        $response->assertSessionHasErrors('whatsapp');

        // Dummy sequential number
        $response = $this->actingAs($user)->post('/custom', [
            'name' => 'Test User',
            'email' => 'test_invalid@example.com',
            'phone' => '102 Green Park, Mumbai, India',
            'whatsapp' => '+91 98765 43210',
            'idea_description' => 'Test artwork requirement description.',
        ]);

        $response->assertSessionHasErrors('whatsapp');
    }
}
