<?php

namespace Tests\Unit;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactInquiryType;
use App\Services\ContactInquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactInquiryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_code_generation_format_and_uniqueness(): void
    {
        $service = new ContactInquiryService();

        $ref1 = $service->generatePublicReference();
        $ref2 = $service->generatePublicReference();

        $this->assertMatchesRegularExpression('/^RA-CON-\d{4}-[A-Z0-9]{4}$/', $ref1);
        $this->assertMatchesRegularExpression('/^RA-CON-\d{4}-[A-Z0-9]{4}$/', $ref2);
        $this->assertNotEquals($ref1, $ref2);
    }

    public function test_service_creates_inquiry_with_correct_enums(): void
    {
        $service = new ContactInquiryService();

        $inquiry = $service->createInquiry([
            'name' => 'Jean-Luc Godard',
            'email' => 'jeanluc@cinema.fr',
            'phone' => '+33 1 42 68 55 00',
            'inquiry_type' => 'press',
            'subject' => 'Interview with Master Resin Sculptor',
            'message' => 'We would love to feature Maison Résine in our upcoming art journal.',
        ]);

        $this->assertEquals('Jean-Luc Godard', $inquiry->name);
        $this->assertEquals(ContactInquiryType::PRESS, $inquiry->inquiry_type);
        $this->assertEquals(ContactInquiryStatus::NEW, $inquiry->status);
        $this->assertEquals(ContactInquiryPriority::NORMAL, $inquiry->priority);
        $this->assertStringStartsWith('RA-CON-', $inquiry->public_reference);
    }
}
