<x-app-layout title="Request Received — Maison Résine Atelier">

    <div class="py-16 sm:py-24 max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 min-h-[75vh] flex flex-col items-center justify-center animate-fade-up">
        
        <div class="max-w-2xl w-full bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2.5rem] p-8 sm:p-14 lg:p-16 text-center shadow-sm">
            
            <!-- Success Icon Badge -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-[#1C1917] text-[#FAF8F5] rounded-full flex items-center justify-center mx-auto shadow-sm"
                 style="margin-bottom: 28px;">
                <svg class="w-8 h-8 sm:w-9 sm:h-9 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Header Section -->
            <div>
                <div class="flex items-center justify-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]"
                     style="margin-bottom: 16px;">
                    <span class="w-6 h-[1px] bg-[#D9D2C5]"></span>
                    <span>CUSTOM ARTWORK REQUEST SUBMITTED</span>
                    <span class="w-6 h-[1px] bg-[#D9D2C5]"></span>
                </div>

                <h1 class="font-editorial text-4xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-[1.08] tracking-tight"
                    style="margin-bottom: 16px;">
                    Thank you.
                </h1>

                <p class="text-sm sm:text-base text-[#78716C] font-light leading-relaxed max-w-md mx-auto"
                   style="margin-bottom: 32px;">
                    Your custom resin artwork request has reached our atelier. Our artisans are excited to craft your bespoke resin piece.
                </p>
            </div>

            <!-- Reference Code Pill Box (Strict Inline Spacing) -->
            <div class="bg-white border border-[#E5DFD3] rounded-2xl max-w-md mx-auto shadow-sm"
                 style="padding: 24px; margin-bottom: 36px;">
                <span class="block text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]" style="margin-bottom: 8px;">YOUR REQUEST REFERENCE NUMBER</span>
                <span class="block text-2xl sm:text-3xl font-editorial font-medium text-[#1C1917] tracking-wider" style="margin-bottom: 6px;">{{ $reference }}</span>
                <p class="text-[11px] text-[#A8A29E] font-light">Keep this reference number handy for any future inquiries with our team.</p>
            </div>

            <!-- What Happens Next Section (Strict Inline Spacing) -->
            <div class="max-w-lg mx-auto" style="margin-bottom: 40px;">
                <div class="flex items-center justify-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]"
                     style="margin-bottom: 24px;">
                    <span class="w-6 h-[1px] bg-[#D9D2C5]"></span>
                    <span>WHAT HAPPENS NEXT</span>
                    <span class="w-6 h-[1px] bg-[#D9D2C5]"></span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-center">
                    <div>
                        <span class="text-[11px] font-mono tracking-widest text-[#8E877D] font-medium block" style="margin-bottom: 6px;">01</span>
                        <h3 class="font-editorial text-lg text-[#1C1917] font-normal" style="margin-bottom: 6px;">Artisan Design Review</h3>
                        <p class="text-xs text-[#78716C] font-light leading-relaxed">
                            Our lead resin artist reviews your dimensions, color palette, wood slab choices, and reference photos.
                        </p>
                    </div>

                    <div>
                        <span class="text-[11px] font-mono tracking-widest text-[#8E877D] font-medium block" style="margin-bottom: 6px;">02</span>
                        <h3 class="font-editorial text-lg text-[#1C1917] font-normal" style="margin-bottom: 6px;">Custom Quote & Proposal</h3>
                        <p class="text-xs text-[#78716C] font-light leading-relaxed">
                            We will send you a custom price estimate and design details on WhatsApp or Email within 24 hours.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons (Strict Inline Spacing) -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4" style="margin-top: 12px;">
                <a href="{{ route('account.custom-requests.index') }}" 
                   class="w-full sm:w-auto bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[11px] uppercase tracking-[0.25em] font-semibold px-9 py-4 rounded-full transition-all duration-300 shadow-sm text-center">
                    Track In Account
                </a>
                
                <a href="{{ url('/') }}" 
                   class="w-full sm:w-auto border border-[#1C1917] text-[#1C1917] hover:bg-[#1C1917] hover:text-[#FAF8F5] text-[11px] uppercase tracking-[0.25em] font-semibold px-9 py-4 rounded-full transition-all duration-300 text-center">
                    Back to Home
                </a>
            </div>

        </div>

    </div>

</x-app-layout>
