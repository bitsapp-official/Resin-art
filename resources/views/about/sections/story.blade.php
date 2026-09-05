<!-- About Page Story & Featured Atelier Section -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start py-6">

    <!-- LEFT COLUMN: Large Featured Atelier Image with Floating Founder Quote Overlay -->
    <div class="lg:col-span-6 relative animate-fade-up delay-100 mb-6 lg:mb-0">
        <div class="rounded-[2rem] overflow-hidden shadow-[0_15px_45px_rgba(0,0,0,0.04)] border border-[#E5DFD3] bg-[oklch(98.5%_0.008_85)]">
            @if(!empty($aboutPage->hero_image))
                <img src="{{ asset('storage/' . $aboutPage->hero_image) }}" 
                     alt="{{ $aboutPage->hero_image_alt ?? 'Atelier artisan craftsman pouring resin' }}" 
                     class="w-full h-[420px] sm:h-[500px] lg:h-[580px] object-cover transition-transform duration-700 hover:scale-[1.02]"
                     loading="eager"
                     decoding="async">
            @else
                <div class="w-full h-[450px] bg-[#FAF8F5] flex items-center justify-center text-[#8E877D] text-xs uppercase tracking-widest">
                    Atelier Image Placeholder
                </div>
            @endif
        </div>

        <!-- Floating Founder Quote Glass Card Overlay (Overlapping Bottom-Right Corner) -->
        @if(!empty($aboutPage->founder_quote))
            <div class="absolute -bottom-5 -right-2 sm:-bottom-7 sm:-right-5 max-w-[85%] sm:max-w-[320px] glass-quote rounded-[1.75rem] p-6 lg:p-7 shadow-[0_15px_35px_rgba(0,0,0,0.06)] border border-white/70 animate-fade-in delay-300 z-10">
                <p class="font-editorial text-lg sm:text-xl text-[#1C1917] italic leading-snug">
                    {{ $aboutPage->founder_quote }}
                </p>
                @if(!empty($aboutPage->founder_name))
                    <span class="block text-[9.5px] uppercase tracking-[0.25em] text-[#1C1917] font-semibold mt-3.5 opacity-90">
                        {{ $aboutPage->founder_name }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- RIGHT COLUMN: Story Narrative & Visit Atelier CTA -->
    <div class="lg:col-span-6 space-y-8 animate-fade-up delay-200 lg:pt-4">

        <!-- Section Header -->
        <div class="space-y-4">
            <span class="text-[10px] uppercase tracking-[0.25em] font-bold text-[#8E877D] block">
                {{ $aboutPage->story_eyebrow ?? 'OUR STORY' }}
            </span>
            <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[54px] text-[#1C1917] font-light leading-[1.1]">
                {!! $aboutPage->story_title ?? 'Twelve years, one rhythm.' !!}
            </h2>
        </div>

        <!-- Story Content Body -->
        @if(!empty($aboutPage->story_content))
            <div class="text-[15px] sm:text-base text-[#78716C] font-light leading-[1.75] space-y-4">
                {!! nl2br(e($aboutPage->story_content)) !!}
            </div>
        @endif

        <!-- Responsible Materials / Sourcing Block -->
        @if(!empty($aboutPage->materials_content))
            <div class="p-6 rounded-2xl bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] space-y-2">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] block">
                    Materials & Sourcing
                </span>
                <p class="text-[13.5px] text-[#78716C] font-light leading-relaxed">
                    {{ $aboutPage->materials_content }}
                </p>
            </div>
        @endif

        <!-- Visit Atelier CTA Button -->
        <div class="pt-2">
            <a href="{{ route('contact.index') }}" 
               class="inline-flex items-center space-x-3 bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[10.5px] uppercase tracking-[0.28em] font-semibold px-9 py-4 rounded-full transition-all duration-300 shadow-sm group">
                <span>VISIT THE ATELIER</span>
                <svg class="w-3.5 h-3.5 stroke-[2] transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>

    </div>

</div>
