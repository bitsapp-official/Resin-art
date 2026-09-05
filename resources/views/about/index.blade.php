<x-app-layout :title="$aboutPage->seo_title ?? 'About — Maison Résine Atelier'">

    <div class="py-10 lg:py-16">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16">

            @if($aboutPage && $aboutPage->is_published)

                <!-- Section 1: Hero Header -->
                @include('about.sections.hero', ['aboutPage' => $aboutPage])

                <!-- Section 2: Story & Featured Atelier Image + Founder Quote Overlay -->
                @include('about.sections.story', ['aboutPage' => $aboutPage])

                <!-- Section 3: Brand Chronicle Timeline (2016-2026) -->
                @include('about.sections.timeline', ['timelineSteps' => $aboutPage->activeTimelineSteps])

                <!-- Section 4: Editorial Values Grid (Slow, Honest, Quiet) -->
                @include('about.sections.values', ['values' => $aboutPage->activeValues])

                <!-- Section 5: The Hands / Artisans Team ("Camille, Yanis, Ines") -->
                @include('about.sections.makers', ['artisans' => $aboutPage->activeArtisans])

            @else

                <!-- Graceful Fallback Overlay when Draft / Unpublished -->
                <div class="max-w-2xl mx-auto bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2rem] p-12 lg:p-16 text-center space-y-6 my-16 shadow-sm">
                    <span class="text-[10px] uppercase tracking-[0.3em] font-semibold text-[#8E877D]">
                        Maison Résine Atelier
                    </span>
                    <h1 class="font-editorial text-4xl lg:text-5xl text-[#1C1917] font-light">
                        Our story is being curated.
                    </h1>
                    <p class="text-sm text-[#78716C] font-light leading-relaxed max-w-md mx-auto">
                        We are currently updating our atelier narrative. Please check back shortly or feel free to write to our team directly.
                    </p>
                    <div class="pt-4">
                        <a href="{{ route('contact.index') }}" 
                           class="inline-block bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-xs uppercase tracking-[0.2em] font-medium px-8 py-3.5 rounded-full transition-all duration-300">
                            Write to the Atelier →
                        </a>
                    </div>
                </div>

            @endif

        </div>
    </div>

</x-app-layout>
