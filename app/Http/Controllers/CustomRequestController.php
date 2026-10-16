<?php

namespace App\Http\Controllers;

use App\Enums\CustomRequestImageType;
use App\Enums\CustomRequestStatus;
use App\Models\CustomRequest;
use App\Models\CustomRequestImage;
use App\Rules\IndianPhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomRequestController extends Controller
{
    /**
     * Display the custom jewellery requirement and bulk inquiry request form.
     */
    /**
     * Display the custom jewellery requirement and bulk inquiry request form.
     */
    public function create(Request $request): View
    {
        $product = null;
        if ($request->filled('product_id')) {
            $product = \App\Models\Product::find($request->product_id);
        }
        $inquiryType = $request->query('type', 'Bespoke Jewellery Design');
        $timeToken = \App\Services\BotProtectionService::generateTimeToken();

        return view('custom.create', compact('product', 'inquiryType', 'timeToken'));
    }

    /**
     * Store a newly created custom request in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Anti-Bot Security Checks (Honeypot, Fast-Submit Time Trap, reCAPTCHA v3)
        if (\App\Services\BotProtectionService::isHoneypotTriggered($request->input('website_url_honey'))) {
            // Silently discard automated spambots
            return redirect()->route('home');
        }

        if (\App\Services\BotProtectionService::isSubmissionTooFast($request->input('form_time_token'), 2)) {
            return redirect()->back()->withInput()->withErrors([
                'idea_description' => 'Security check: Form was submitted too quickly. Please try again.',
            ]);
        }

        if (!\App\Services\BotProtectionService::verifyRecaptcha($request->input('recaptcha_token'), $request->ip())) {
            return redirect()->back()->withInput()->withErrors([
                'idea_description' => 'Security check: Google reCAPTCHA anti-spam verification failed. Please try again.',
            ]);
        }

        // 2. Backend Validation (Delivery address removed; international phone supported; max 5 images)
        $validated = $request->validate([
            'project_type' => ['nullable', 'string', 'max:255'],
            'project_type_other' => ['nullable', 'string', 'max:255'],
            'idea_description' => ['required', 'string', 'max:5000'],
            
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', new \App\Rules\InternationalPhoneNumber()],

            // Optional reference images (up to 5 images)
            'reference_images' => ['nullable', 'array', 'max:5'],
            'reference_images.*' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'], // max 5MB
        ], [
            'reference_images.max' => 'You can upload a maximum of 5 inspiration photos.',
            'whatsapp.required' => 'Please provide a valid phone or WhatsApp number so our atelier can contact you.',
        ]);

        // Generate Reference Number
        $reference = CustomRequest::generateReference();

        DB::transaction(function () use ($validated, $request, $reference) {
            // 3. Create the Custom Request Record (Works for both Guests user_id=null and Authenticated users)
            $customRequest = CustomRequest::create([
                'public_reference' => $reference,
                'user_id' => auth()->id(), // null for guests, ID for logged-in users
                'project_type' => $validated['project_type'] ?? 'Bespoke Handcrafted Design',
                'project_type_other' => $validated['project_type_other'] ?? null,
                'quantity' => 1,
                'idea_description' => $validated['idea_description'],
                'timeline_type' => 'Flexible',
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $validated['whatsapp'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'status' => CustomRequestStatus::SUBMITTED,
                'submitted_at' => now(),
            ]);

            // 4. Handle Optional Reference Images (Max 5 images)
            if ($request->hasFile('reference_images')) {
                $files = $request->file('reference_images');
                $files = array_slice($files, 0, 5); // Max 5 images
                
                foreach ($files as $index => $file) {
                    $path = $file->store('custom-requests/references', 'public');

                    CustomRequestImage::create([
                        'custom_request_id' => $customRequest->id,
                        'type' => CustomRequestImageType::REFERENCE,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'sort_order' => $index,
                    ]);
                }
            }

            // Dispatch Real-time Admin Bell Notification
            try {
                \App\Services\AdminNotificationService::newCustomRequest($customRequest);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Admin custom request notification could not be sent: " . $e->getMessage());
            }
        });

        $redirectRoute = ($request->routeIs('inquiry.*') || $request->is('inquiry*')) ? 'inquiry.success' : 'custom.success';
        return redirect()->route($redirectRoute, ['reference' => $reference]);
    }

    /**
     * Display the success page.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            $fallback = ($request->routeIs('inquiry.*') || $request->is('inquiry*')) ? 'inquiry.index' : 'custom.index';
            return redirect()->route($fallback);
        }

        return view('custom.success', compact('reference'));
    }
}
