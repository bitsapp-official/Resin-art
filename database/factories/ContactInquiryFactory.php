<?php

namespace Database\Factories;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Enums\ContactInquiryType;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactInquiry>
 */
class ContactInquiryFactory extends Factory
{
    protected $model = ContactInquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_reference' => 'RA-CON-' . date('Y') . '-' . strtoupper(Str::random(4)),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'inquiry_type' => fake()->randomElement(ContactInquiryType::cases()),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraphs(2, true),
            'status' => ContactInquiryStatus::NEW,
            'priority' => ContactInquiryPriority::NORMAL,
            'admin_notes' => null,
            'replied_at' => null,
            'closed_at' => null,
        ];
    }
}
