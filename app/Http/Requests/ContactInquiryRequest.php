<?php

namespace App\Http\Requests;

use App\Enums\ContactInquiryType;
use App\Rules\IndianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ContactInquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', new IndianPhoneNumber()],
            'inquiry_type' => ['required', new Enum(ContactInquiryType::class)],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website_url' => ['nullable', 'string'], // Honeypot trap handled in Controller
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please share your name with the atelier.',
            'email.required' => 'An email address is required so we may respond.',
            'email.email' => 'Please provide a valid email address.',
            'inquiry_type.required' => 'Please select the type of inquiry.',
            'subject.required' => 'A subject line is required.',
            'message.required' => 'Please enter your message for the atelier.',
            'message.min' => 'Your letter should be at least 10 characters long.',
            'website_url.max' => 'Spam submission detected.',
        ];
    }

    /**
     * Prepare the data for validation (sanitization / normalization).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => $this->input('phone') ? trim((string) $this->input('phone')) : null,
            'subject' => trim((string) $this->input('subject')),
            'message' => trim((string) $this->input('message')),
            'inquiry_type' => strtolower(trim((string) $this->input('inquiry_type', 'general'))),
        ]);
    }
}
