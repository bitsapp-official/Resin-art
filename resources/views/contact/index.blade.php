<x-app-layout title="Contact — Maison Résine">

    @php
        $contactContent = \App\Models\ContactPageContent::getContent();
    @endphp

    <div class="py-10 lg:py-16">

        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16">

            <!-- Hero Section (Preceding line + CORRESPONDENCE, Editorial Title & Subtitle) -->
            <div class="max-w-3xl mb-12 lg:mb-16 space-y-4 animate-fade-up">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>{{ $contactContent->hero_badge }}</span>
                </div>

                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    {!! $contactContent->hero_title !!}
                </h1>

                <p class="text-[15px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                    {{ $contactContent->hero_subtitle }}
                </p>
            </div>

            <!-- Main Layout Grid (Always preserved) -->
            <div class="flex flex-col lg:flex-row gap-7 lg:gap-10 items-start">

                <!-- LEFT COLUMN: Studio Information Card & 4 Inquiry Type Cards -->
                <div class="w-full lg:w-[400px] xl:w-[420px] shrink-0 space-y-5">

                    <!-- Studio Card -->
                    <div class="bg-[oklch(98.5%_0.008_85)] rounded-[1.75rem] p-7 lg:p-9 space-y-6 animate-fade-up delay-100 border border-[#E5DFD3]">
                        
                        <div class="space-y-3">
                            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] block">{{ $contactContent->workshop_label ?? 'Workshop' }}</span>
                            <h3 class="font-editorial text-[26px] text-[#1C1917] font-normal leading-[1.25]">
                                {!! nl2br(e($contactContent->studio_address)) !!}
                            </h3>
                        </div>

                        <hr class="border-[#E5DFD3]">

                        <div class="text-[13px] text-[#78716C] font-light leading-[1.6]">
                            {!! nl2br(e($contactContent->studio_hours)) !!}
                        </div>

                        <hr class="border-[#E5DFD3]">

                        <div class="space-y-1.5 text-[13px] font-medium text-[#1C1917]">
                            <div><a href="mailto:{{ $contactContent->studio_email }}" class="hover:opacity-70 transition-opacity">{{ $contactContent->studio_email }}</a></div>
                            <div><a href="tel:{{ $contactContent->studio_phone }}" class="hover:opacity-70 transition-opacity">{{ $contactContent->studio_phone }}</a></div>
                        </div>
                    </div>

                    <!-- 4 Inquiry Cards (2x2 Grid) -->
                    <div class="grid grid-cols-2 gap-3.5 animate-fade-up delay-200">
                        <!-- Card 1: Custom Orders -->
                        <div class="bg-[oklch(98.5%_0.008_85)] p-5 sm:p-6 rounded-[1.25rem] border border-[#E5DFD3] flex flex-col justify-center h-full cursor-default select-none">
                            <h4 class="font-editorial text-[22px] text-[#1C1917] font-normal leading-tight mb-1.5">
                                Custom Orders
                            </h4>
                            <p class="text-[9px] uppercase tracking-[0.2em] text-[#8E877D] font-semibold">
                                Bespoke pieces
                            </p>
                        </div>

                        <!-- Card 2: Trade -->
                        <div class="bg-[oklch(98.5%_0.008_85)] p-5 sm:p-6 rounded-[1.25rem] border border-[#E5DFD3] flex flex-col justify-center h-full cursor-default select-none">
                            <h4 class="font-editorial text-[22px] text-[#1C1917] font-normal leading-tight mb-1.5">
                                Trade
                            </h4>
                            <p class="text-[9px] uppercase tracking-[0.2em] text-[#8E877D] font-semibold">
                                Designers & hotels
                            </p>
                        </div>

                        <!-- Card 3: Press -->
                        <div class="bg-[oklch(98.5%_0.008_85)] p-5 sm:p-6 rounded-[1.25rem] border border-[#E5DFD3] flex flex-col justify-center h-full cursor-default select-none">
                            <h4 class="font-editorial text-[22px] text-[#1C1917] font-normal leading-tight mb-1.5">
                                Press
                            </h4>
                            <p class="text-[9px] uppercase tracking-[0.2em] text-[#8E877D] font-semibold">
                                Editorial requests
                            </p>
                        </div>

                        <!-- Card 4: Visits -->
                        <div class="bg-[oklch(98.5%_0.008_85)] p-5 sm:p-6 rounded-[1.25rem] border border-[#E5DFD3] flex flex-col justify-center h-full cursor-default select-none">
                            <h4 class="font-editorial text-[22px] text-[#1C1917] font-normal leading-tight mb-1.5">
                                Visits
                            </h4>
                            <p class="text-[9px] uppercase tracking-[0.2em] text-[#8E877D] font-semibold">
                                Book the atelier
                            </p>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Contact Form / Success Container -->
                <div class="flex-1 w-full animate-fade-up delay-300">
                    <div class="bg-[oklch(98.5%_0.008_85)] rounded-[2rem] p-7 sm:p-10 lg:p-12 border border-[#E5DFD3]">

                        @if(isset($successName))
                            <!-- Success State Inside Right Column Grid -->
                            <div class="text-center py-6 sm:py-10 space-y-6 animate-fade-in">
                                <div class="w-16 h-16 mx-auto rounded-full bg-[#FAF8F5] border border-[#E5DFD3] flex items-center justify-center text-[#1C1917]">
                                    <svg class="w-7 h-7 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>

                                <span class="text-[10px] uppercase tracking-[0.3em] font-semibold text-[#8E877D]">
                                    LETTER RECEIVED
                                </span>

                                <h2 class="font-editorial text-3xl lg:text-4xl text-[#1C1917] font-normal">
                                    Thank you, {{ $successName }}.
                                </h2>

                                <p class="text-sm text-[#78716C] font-light leading-relaxed max-w-md mx-auto">
                                    We have received your letter. Our Bordeaux atelier team will review it attentively and send a response to your email address within 48 hours.
                                </p>

                                <div class="pt-4">
                                    <a href="{{ route('contact.index') }}" 
                                       class="inline-block bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-xs uppercase tracking-[0.2em] font-medium px-8 py-3.5 rounded-full transition-all duration-300">
                                        Send Another Letter →
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- Main Form -->
                            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6" novalidate>
                                @csrf

                                <!-- Hidden Input for Inquiry Type -->
                                <input type="hidden" name="inquiry_type" value="general">

                                <!-- Honeypot Trap Field -->
                                <div class="hidden" aria-hidden="true">
                                    <label for="website_url">Website (Do not fill)</label>
                                    <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                                </div>

                                <!-- Name & Email Row -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <!-- NAME -->
                                    <div class="space-y-2">
                                        <label for="name" class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                                            Name
                                        </label>
                                        <input type="text" 
                                               name="name" 
                                               id="name" 
                                               value="{{ old('name') }}" 
                                               required 
                                               class="input-pill w-full @error('name') border-[#D97706] @enderror">
                                        @error('name')
                                            <p class="text-[12px] text-red-600 font-medium pt-0.5">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- EMAIL -->
                                    <div class="space-y-2">
                                        <label for="email" class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                                            Email
                                        </label>
                                        <input type="email" 
                                               name="email" 
                                               id="email" 
                                               value="{{ old('email') }}" 
                                               required 
                                               class="input-pill w-full @error('email') border-[#D97706] @enderror">
                                        @error('email')
                                            <p class="text-[12px] text-red-600 font-medium pt-0.5">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- SUBJECT -->
                                <div class="space-y-2">
                                    <label for="subject" class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                                        Subject
                                    </label>
                                    <input type="text" 
                                           name="subject" 
                                           id="subject" 
                                           value="{{ old('subject') }}" 
                                           required 
                                           class="input-pill w-full @error('subject') border-[#D97706] @enderror">
                                    @error('subject')
                                        <p class="text-[12px] text-red-600 font-medium pt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- MESSAGE -->
                                <div class="space-y-2">
                                    <label for="message" class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                                        Message
                                    </label>
                                    <textarea name="message" 
                                              id="message" 
                                              rows="6" 
                                              required 
                                              class="textarea-rounded w-full leading-relaxed resize-y @error('message') border-[#D97706] @enderror">{{ old('message') }}</textarea>
                                    @error('message')
                                        <p class="text-[12px] text-red-600 font-medium pt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Form Footer Row -->
                                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-5">
                                    <span class="text-[12px] text-[#8E877D] font-light">
                                        We reply within 48 hours.
                                    </span>

                                    <button type="submit" 
                                            class="w-full sm:w-auto inline-flex items-center justify-center space-x-3 bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[10.5px] uppercase tracking-[0.28em] font-semibold px-9 py-3.5 rounded-full transition-all duration-300 shadow-sm cursor-pointer group">
                                        <span>SEND LETTER</span>
                                        <svg class="w-3.5 h-3.5 stroke-[2] transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </button>
                                </div>

                            </form>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>
