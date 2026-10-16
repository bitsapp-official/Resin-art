<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactInquiryRequest;
use App\Services\ContactInquiryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display the Contact Us page.
     */
    public function index(): View
    {
        $timeToken = \App\Services\BotProtectionService::generateTimeToken();
        return view('contact.index', compact('timeToken'));
    }

    /**
     * Store a new contact inquiry.
     */
    public function store(ContactInquiryRequest $request, ContactInquiryService $service): View|RedirectResponse
    {
        // 1. Anti-Bot Security (Honeypot, Fast-Submit Time Trap, reCAPTCHA v3)
        if (\App\Services\BotProtectionService::isHoneypotTriggered($request->input('website_url'))) {
            // Silently complete without error to deceive automated bots
            return view('contact.index', [
                'successName' => $request->input('name') ?? 'Patron',
            ]);
        }

        if (\App\Services\BotProtectionService::isSubmissionTooFast($request->input('form_time_token'), 2)) {
            return redirect()->back()->withInput()->withErrors([
                'message' => 'Security check: Form was submitted too quickly. Please try again.',
            ]);
        }

        if (!\App\Services\BotProtectionService::verifyRecaptcha($request->input('recaptcha_token'), $request->ip())) {
            return redirect()->back()->withInput()->withErrors([
                'message' => 'Security check: Google reCAPTCHA anti-spam verification failed. Please try again.',
            ]);
        }

        $validated = $request->validated();

        $inquiry = $service->createInquiry($validated);

        try {
            \App\Services\AdminNotificationService::newContactInquiry($inquiry);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Admin contact notification could not be sent: " . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for writing to our atelier.',
            ]);
        }

        return view('contact.index', [
            'successName' => $inquiry->name,
        ]);
    }
}
