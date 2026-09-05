<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IndianPhoneNumber implements ValidationRule
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

        // Handle country code +91 or other prefixes
        if (str_starts_with($val, '+91')) {
            $mobile10 = preg_replace('/[^0-9]/', '', substr($val, 3));
        } elseif (str_starts_with($val, '+')) {
            $digits = preg_replace('/[^0-9]/', '', $val);
            if (str_starts_with($digits, '91') && strlen($digits) > 10) {
                $mobile10 = substr($digits, 2);
            } else {
                $mobile10 = $digits;
            }
        } else {
            $rawDigits = preg_replace('/[^0-9]/', '', $val);
            if (strlen($rawDigits) === 12 && str_starts_with($rawDigits, '91')) {
                $mobile10 = substr($rawDigits, 2);
            } elseif (strlen($rawDigits) === 11 && str_starts_with($rawDigits, '0')) {
                $mobile10 = substr($rawDigits, 1);
            } else {
                $mobile10 = $rawDigits;
            }
        }

        // 1. Must be exactly 10 digits
        if (strlen($mobile10) !== 10) {
            $fail('Please enter a valid 10-digit mobile number.');
            return;
        }

        // 2. Must start with 6, 7, 8, or 9
        if (!in_array($mobile10[0], ['6', '7', '8', '9'])) {
            $fail('Please enter a valid mobile number starting with 6, 7, 8, or 9.');
            return;
        }

        // 3. Reject dummy / repeated digits / test sequences
        $dummyPatterns = [
            '1234567890',
            '0123456789',
            '9876543210',
            '8765432109',
            '7890123456',
            '9876598765',
            '1234512345',
            '9876512345',
            '9123456780',
            '9898989898',
            '9797979797',
            '9696969696',
            '9595959595',
            '9191919191',
        ];

        if (in_array($mobile10, $dummyPatterns) || preg_match('/(.)\1{5,}/', $mobile10)) {
            $fail('Please provide a genuine, reachable contact number.');
            return;
        }
    }
}
