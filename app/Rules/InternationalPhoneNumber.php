<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InternationalPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $val = trim((string) $value);
        if (empty($val)) {
            return;
        }

        // 1. Basic character check: digits, optional leading +, spaces, dashes, parentheses
        if (!preg_match('/^\+?[0-9\s\-\(\)\.]{7,25}$/', $val)) {
            $fail('Please enter a valid phone or WhatsApp number.');
            return;
        }

        // Extract pure digits
        $digits = preg_replace('/[^0-9]/', '', $val);

        // 2. Length check: between 7 and 16 digits (E.164 international standard)
        if (strlen($digits) < 7 || strlen($digits) > 16) {
            $fail('Phone number must contain between 7 and 16 digits.');
            return;
        }

        // 3. Reject dummy patterns & repeating digits
        $dummyPatterns = [
            '12345',
            '123456',
            '1234567',
            '12345678',
            '123456789',
            '1234567890',
            '0123456789',
            '9876543210',
            '8765432109',
            '0000000',
            '1111111',
            '9999999',
        ];

        $isDummy = in_array($digits, $dummyPatterns);
        if (!$isDummy) {
            foreach (['1234567890', '9876543210', '0123456789', '8765432109', '12345678'] as $dummySeq) {
                if (str_contains($digits, $dummySeq)) {
                    $isDummy = true;
                    break;
                }
            }
        }

        if ($isDummy || preg_match('/(.)\1{5,}/', $digits)) {
            $fail('Please provide a genuine, reachable phone or WhatsApp number.');
            return;
        }
    }
}
