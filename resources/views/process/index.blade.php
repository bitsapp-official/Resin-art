<x-app-layout title="Our Process — Maison Résine Atelier">

    <div class="py-10 lg:py-16">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-12 lg:space-y-16">

            <!-- Hero Header Section -->
            <div class="max-w-3xl space-y-4 animate-fade-up">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>{{ $processPage->eyebrow ?? 'OUR PROCESS' }}</span>
                </div>

                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    {{ $processPage->title ?? 'Six weeks, one object.' }}
                </h1>

                <p class="text-[15px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                    {{ $processPage->description ?? 'From timber selection to the final hand-polish, nothing here is hurried.' }}
                </p>
            </div>

            <!-- Alternating Process Steps List -->
            @if(isset($steps) && $steps->count() > 0)
                <div class="space-y-4 lg:space-y-6 pt-4">
                    @foreach($steps as $step)
                        @include('process.components.process-step', ['step' => $step, 'index' => $loop->index])
                    @endforeach
                </div>
            @else
                <div class="max-w-2xl mx-auto bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2rem] p-12 text-center space-y-4 my-12">
                    <h2 class="font-editorial text-3xl text-[#1C1917] font-light">Process documentation coming soon.</h2>
                    <p class="text-sm text-[#78716C]">The atelier team is preparing our detailed step-by-step craft walkthrough.</p>
                </div>
            @endif

            <!-- Custom Requirements CTA Section -->
            <div class="bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2.5rem] p-12 lg:p-20 text-center space-y-6 my-16 shadow-sm animate-fade-up delay-200">
                <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[54px] text-[#1C1917] font-light tracking-tight">
                    {{ $processPage->cta_title ?? 'Have a custom piece in mind?' }}
                </h2>

                <div class="pt-2">
                    <a href="{{ $processPage->cta_url ?? '/custom' }}" 
                       class="inline-block bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[11px] uppercase tracking-[0.25em] font-medium px-9 py-4 rounded-full transition-all duration-300 shadow-sm">
                        {{ $processPage->cta_button_text ?? 'SUBMIT YOUR REQUIREMENTS' }}
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- GSAP ScrollTrigger Animations for Our Process Steps -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            setTimeout(function() {
                ScrollTrigger.refresh();
            }, 200);

            const steps = document.querySelectorAll('.process-step-item');
            steps.forEach(function(step) {
                gsap.fromTo(step,
                    { opacity: 0, y: 35 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.8,
                        ease: 'power3.out',
                        scrollTrigger: {
                            trigger: step,
                            start: 'top 85%',
                            once: true
                        }
                    }
                );
            });

            window.addEventListener('resize', function() {
                ScrollTrigger.refresh();
            });
        }
    });
    </script>

</x-app-layout>
