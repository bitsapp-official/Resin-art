<?php

namespace Tests\Feature;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactInquiryType;
use App\Mail\ContactInquiryReceived;
use App\Mail\NewContactInquiryAdminNotification;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_returns_successful_response(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertSee('Write to the');
        $response->assertSee('atelier.');
        $response->assertSee('Custom Orders');
    }

    public function test_valid_contact_form_submission_creates_inquiry_and_dispatches_emails(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Eleanor Vance',
            'email' => 'eleanor@example.com',
            'phone' => '+33 6 12 34 56 78',
            'inquiry_type' => 'custom',
            'subject' => 'Bespoke Resin Dining Table Custom Order',
            'message' => 'I would like to request a bespoke 8-seater dining table with oceanic resin fluid dynamics.',
        ];

        $response = $this->post('/contact', $payload);

        $response->assertStatus(200);
        $response->assertSee('LETTER RECEIVED');
        $response->assertSee('Eleanor Vance');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Eleanor Vance',
            'email' => 'eleanor@example.com',
            'inquiry_type' => ContactInquiryType::CUSTOM->value,
            'subject' => 'Bespoke Resin Dining Table Custom Order',
            'status' => ContactInquiryStatus::NEW->value,
        ]);

        $inquiry = ContactInquiry::where('email', 'eleanor@example.com')->first();
        $this->assertNotNull($inquiry);
        $this->assertStringStartsWith('RA-CON-', $inquiry->public_reference);

        Mail::assertQueued(ContactInquiryReceived::class, function ($mail) use ($inquiry) {
            return $mail->hasTo('eleanor@example.com') && $mail->inquiry->id === $inquiry->id;
        });

        Mail::assertQueued(NewContactInquiryAdminNotification::class);
    }

    public function test_missing_name_fails_validation(): void
    {
        $response = $this->post('/contact', [
            'email' => 'test@example.com',
            'inquiry_type' => 'trade',
            'subject' => 'Trade Inquiry',
            'message' => 'Valid message content exceeding minimum length requirement.',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_invalid_email_fails_validation(): void
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'invalid-email-string',
            'inquiry_type' => 'press',
            'subject' => 'Press Inquiry',
            'message' => 'Valid message content exceeding minimum length requirement.',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_short_message_fails_validation(): void
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'inquiry_type' => 'general',
            'subject' => 'Short',
            'message' => 'Too short',
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_invalid_inquiry_type_fails_validation(): void
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'inquiry_type' => 'unsupported_type',
            'subject' => 'Invalid Type Inquiry',
            'message' => 'Valid message content exceeding minimum length requirement.',
        ]);

        $response->assertSessionHasErrors('inquiry_type');
    }

    public function test_honeypot_field_silently_handles_bot_submission(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name' => 'Spam Bot',
            'email' => 'bot@spammer.com',
            'inquiry_type' => 'general',
            'subject' => 'Buy Cheap Meds',
            'message' => 'Spam content injected by automated crawler script.',
            'website_url' => 'http://spam-link.com', // Honeypot filled
        ]);

        $response->assertStatus(200);

        // Database should NOT contain the spam inquiry
        $this->assertDatabaseMissing('contact_inquiries', [
            'email' => 'bot@spammer.com',
        ]);

        Mail::assertNothingQueued();
    }
}
