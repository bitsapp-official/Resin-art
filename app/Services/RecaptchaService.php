<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify a Google reCAPTCHA v3 response token.
     * Returns true if verification passes or if reCAPTCHA is not configured/in testing.
     */
    public static function verify(?string $token, ?string $ip = null): bool
    {
        $secretKey = config('services.recaptcha.secret_key');

        // If no secret key is configured or we are in testing environment, allow smoothly
        if (empty($secretKey) || app()->environment('testing')) {
            return true;
        }

        if (empty($token)) {
            Log::warning('reCAPTCHA v3 verification failed: Empty token received.');
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $minScore = (float) config('services.recaptcha.min_score', 0.5);

                if (!empty($data['success']) && ($data['score'] ?? 1.0) >= $minScore) {
                    return true;
                }

                Log::warning('reCAPTCHA v3 score below threshold or failed', [
                    'success' => $data['success'] ?? false,
                    'score'   => $data['score'] ?? null,
                    'action'  => $data['action'] ?? null,
                    'errors'  => $data['error-codes'] ?? [],
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA v3 verification exception: ' . $e->getMessage());
            // Graceful degradation: in case of Google network glitch, avoid locking out legitimate users
            return true;
        }

        return false;
    }
}
