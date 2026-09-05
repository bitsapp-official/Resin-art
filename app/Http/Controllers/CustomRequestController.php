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
     * Display the custom artwork requirement request form.
     */
    public function create(): View
    {
        return view('custom.create');
    }

    /**
     * Store a newly created custom request in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Backend Validation
        $validated = $request->validate([
            'project_type' => ['nullable', 'string', 'max:255'],
            'project_type_other' => ['nullable', 'string', 'max:255'],
            'idea_description' => ['required', 'string', 'max:5000'],
            
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'string', new IndianPhoneNumber()],
            
            // Honeypot anti-spam
            'website_url_honey' => ['nullable', 'string', 'max:0'],

            // Optional reference images
            'reference_images.*' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'], // max 5MB
        ]);

        if (!empty($validated['website_url_honey'])) {
            // Silently abort for spam bots
            return redirect()->route('home');
        }

        // Generate Reference Number
        $reference = CustomRequest::generateReference();

        DB::transaction(function () use ($validated, $request, $reference) {
            // 2. Create the Custom Request Record
            $customRequest = CustomRequest::create([
                'public_reference' => $reference,
                'user_id' => auth()->id(), // null for guests, ID for logged-in users
                'project_type' => $validated['project_type'] ?? 'Custom Artwork',
                'project_type_other' => $validated['project_type_other'] ?? null,
                'quantity' => 1,
                'idea_description' => $validated['idea_description'],
                'timeline_type' => 'Flexible',
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'status' => CustomRequestStatus::SUBMITTED,
                'submitted_at' => now(),
            ]);

            // 3. Handle Optional Reference Images
            if ($request->hasFile('reference_images')) {
                $files = $request->file('reference_images');
                $files = array_slice($files, 0, 3); // Max 3 images
                
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

        return redirect()->route('custom.success', ['reference' => $reference]);
    }

    /**
     * Display the success page.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('custom.index');
        }

        return view('custom.success', compact('reference'));
    }
}
