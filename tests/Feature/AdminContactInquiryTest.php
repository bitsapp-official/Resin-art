<?php

namespace Tests\Feature;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactInquiryType;
use App\Mail\ContactInquiryReply;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminContactInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_redirected_from_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin');

        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    public function test_admin_can_access_contact_inquiries_resource(): void
    {
        $admin = User::factory()->admin()->create();
        $inquiry = ContactInquiry::factory()->create([
            'public_reference' => 'RA-CON-2026-TEST',
            'name' => 'Madame Dupuis',
        ]);

        $this->assertDatabaseHas('contact_inquiries', [
            'public_reference' => 'RA-CON-2026-TEST',
            'name' => 'Madame Dupuis',
        ]);
    }

    public function test_admin_can_update_inquiry_status_and_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $inquiry = ContactInquiry::factory()->create([
            'status' => ContactInquiryStatus::NEW,
            'priority' => ContactInquiryPriority::NORMAL,
        ]);

        $inquiry->update([
            'status' => ContactInquiryStatus::IN_PROGRESS,
            'priority' => ContactInquiryPriority::HIGH,
            'admin_notes' => 'Discussed with master artisan. Raw materials reserved.',
        ]);

        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'status' => ContactInquiryStatus::IN_PROGRESS->value,
            'priority' => ContactInquiryPriority::HIGH->value,
            'admin_notes' => 'Discussed with master artisan. Raw materials reserved.',
        ]);
    }

    public function test_admin_can_dispatch_reply_email_to_customer(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $inquiry = ContactInquiry::factory()->create([
            'email' => 'client@luxury-hotel.com',
            'status' => ContactInquiryStatus::NEW,
        ]);

        Mail::to($inquiry->email)->queue(new ContactInquiryReply($inquiry, 'Dear Client, We are delighted to accept your trade order.'));

        $inquiry->update([
            'status' => ContactInquiryStatus::REPLIED,
            'replied_at' => now(),
        ]);

        Mail::assertQueued(ContactInquiryReply::class, function ($mail) use ($inquiry) {
            return $mail->hasTo('client@luxury-hotel.com') && $mail->inquiry->id === $inquiry->id;
        });

        $this->assertDatabaseHas('contact_inquiries', [
            'id' => $inquiry->id,
            'status' => ContactInquiryStatus::REPLIED->value,
        ]);
    }
}
