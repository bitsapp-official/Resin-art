<x-app-layout title="Request Received — Maison Résine Atelier">

    <div class="py-10 sm:py-20 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 min-h-[75vh] flex flex-col items-center justify-center animate-fade-up w-full min-w-0">
        
        <div class="max-w-2xl w-full bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[1.5rem] sm:rounded-[2.5rem] p-5 sm:p-10 lg:p-16 text-center shadow-sm">
            
            <!-- Success Icon Badge -->
            <div class="w-14 h-14 sm:w-20 sm:h-20 bg-[#1C1917] text-[#FAF8F5] rounded-full flex items-center justify-center mx-auto shadow-sm mb-5 sm:mb-7">
                <svg class="w-7 h-7 sm:w-9 sm:h-9 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Header Section -->
            <div>
                <div class="flex items-center justify-center space-x-2 sm:space-x-3 text-[9px] sm:text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D] mb-3 sm:mb-4">
                    <span class="w-4 sm:w-6 h-[1px] bg-[#D9D2C5]"></span>
                    <span>CUSTOM ARTWORK REQUEST SUBMITTED</span>
                    <span class="w-4 sm:w-6 h-[1px] bg-[#D9D2C5]"></span>
                </div>

                <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-[1.08] tracking-tight mb-3 sm:mb-4">
                    Thank you.
                </h1>

                <p class="text-[13.5px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-md mx-auto mb-6 sm:mb-8">
                    Your custom resin artwork request has reached our atelier. Our artisans are excited to craft your bespoke resin piece.
                </p>
            </div>

            <!-- Reference Code Pill Box -->
            <div class="bg-white border border-[#E5DFD3] rounded-xl sm:rounded-2xl max-w-md mx-auto shadow-sm p-4 sm:p-6 mb-7 sm:mb-9">
                <span class="block text-[9.5px] sm:text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D] mb-1.5">YOUR REQUEST REFERENCE NUMBER</span>
                <span class="block text-xl sm:text-3xl font-editorial font-medium text-[#1C1917] tracking-wider mb-1">{{ $reference }}</span>
                <p class="text-[10.5px] sm:text-[11px] text-[#A8A29E] font-light">Keep this reference number handy for any future inquiries with our team.</p>
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
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mt-3">
                <a href="{{ route('account.custom-requests.index') }}" 
                   class="w-full sm:w-auto bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[10.5px] uppercase tracking-[0.25em] font-semibold px-6 sm:px-9 py-3.5 sm:py-4 rounded-full transition-all duration-300 shadow-sm text-center whitespace-nowrap">
                    Track In Account
                </a>
                
                <a href="{{ url('/') }}" 
                   class="w-full sm:w-auto border border-[#1C1917] text-[#1C1917] hover:bg-[#1C1917] hover:text-[#FAF8F5] text-[10.5px] uppercase tracking-[0.25em] font-semibold px-6 sm:px-9 py-3.5 sm:py-4 rounded-full transition-all duration-300 text-center whitespace-nowrap">
                    Back to Home
                </a>
            </div>

        </div>

    </div>

</x-app-layout>
