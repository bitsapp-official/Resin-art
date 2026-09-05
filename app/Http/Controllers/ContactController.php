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
        return view('contact.index');
    }

    /**
     * Store a new contact inquiry.
     */
    public function store(ContactInquiryRequest $request, ContactInquiryService $service): View|RedirectResponse
    {
        $validated = $request->validated();

        // Honeypot trap check
        if (!empty($request->input('website_url'))) {
            // Silently complete without error to deceive automated bots
            return view('contact.index', [
                'successName' => $validated['name'] ?? 'Patron',
            ]);
        }

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
