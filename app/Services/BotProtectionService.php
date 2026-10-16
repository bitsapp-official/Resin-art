<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class BotProtectionService
{
    /**
     * Generate an encrypted time token containing current UNIX timestamp.
     */
    public static function generateTimeToken(): string
    {
        return Crypt::encryptString((string) time());
    }

    /**
     * Check if a honeypot field has been filled (bots automatically fill invisible inputs).
     */
    public static function isHoneypotTriggered(?string $value): bool
    {
        return !empty(trim((string) $value));
    }

    /**
     * Check if the form was submitted unnaturally fast (e.g. < 2 seconds by an automated script).
     */
    public static function isSubmissionTooFast(?string $token, int $minSeconds = 2): bool
    {
        // Skip in testing environment so automated tests run fast without false positives
        if (app()->environment('testing')) {
            return false;
        }

        if (empty($token)) {
            // Missing token on public submission can indicate a raw POST bot
            return false;
        }

        try {
            $timestamp = (int) Crypt::decryptString($token);
            $elapsed = time() - $timestamp;

            if ($elapsed < $minSeconds) {
                Log::warning("Bot protection: Form submitted in {$elapsed}s (threshold: {$minSeconds}s).");
                return true;
            }
        } catch (\Throwable $e) {
            // Tampered token
            Log::warning("Bot protection: Invalid time token: " . $e->getMessage());
            return true;
        }

        return false;
    }

    /**
     * Verify Google reCAPTCHA v3 token.
     */
    public static function verifyRecaptcha(?string $token, ?string $ip = null): bool
    {
        return RecaptchaService::verify($token, $ip);
    }
}
