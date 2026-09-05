<?php

namespace App\Services;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use App\Mail\ContactInquiryReceived;
use App\Mail\NewContactInquiryAdminNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ContactInquiryService
{
    /**
     * Store a new contact inquiry and queue notification emails.
     *
     * @param array<string, mixed> $data
     */
    public function createInquiry(array $data): ContactInquiry
    {
        $reference = $this->generatePublicReference();

        $inquiry = ContactInquiry::create([
            'public_reference' => $reference,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'inquiry_type' => $data['inquiry_type'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => ContactInquiryStatus::NEW,
            'priority' => ContactInquiryPriority::NORMAL,
        ]);

        $this->dispatchNotifications($inquiry);

        return $inquiry;
    }

    /**
     * Generate a unique, customer-safe reference.
     * Example: RA-CON-2026-9B4F
     */
    public function generatePublicReference(): string
    {
        $year = date('Y');

        do {
            $randomHex = strtoupper(Str::random(4));
            $reference = "RA-CON-{$year}-{$randomHex}";
        } while (ContactInquiry::where('public_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Dispatch email notifications to customer and admin.
     */
    protected function dispatchNotifications(ContactInquiry $inquiry): void
    {
        try {
            // Send customer confirmation acknowledgment
            Mail::to($inquiry->email)->queue(new ContactInquiryReceived($inquiry));

            // Send admin notification
            $adminEmail = config('atelier.admin_email', config('mail.from.address'));
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new NewContactInquiryAdminNotification($inquiry));
            }
        } catch (\Throwable $e) {
            // Log failure gracefully without disrupting customer HTTP flow
            Log::error('Failed to queue contact inquiry notification email', [
                'inquiry_id' => $inquiry->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
