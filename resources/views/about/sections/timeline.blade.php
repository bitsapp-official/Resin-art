<!-- About Page Brand Story Timeline Section -->
@if(isset($timelineSteps) && $timelineSteps->count() > 0)
    <div class="py-12 lg:py-16 my-8">
        <div class="max-w-4xl mx-auto space-y-12">
            
            <!-- Timeline Header -->
            <div class="text-center space-y-3 timeline-header" style="opacity: 0;">
                <span class="text-[10px] uppercase tracking-[0.3em] font-semibold text-[#8E877D]">
                    OUR CHRONICLE
                </span>
                <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[52px] text-[#1C1917] font-light tracking-tight">
                    A house built on patience.
                </h2>
                <p class="text-[15px] text-[#78716C] font-light max-w-lg mx-auto">
                    Maison Résine began with one poured panel in a rented flat and a stubborn belief that resin could be quiet.
                </p>
            </div>

            <!-- Vertical Timeline List -->
            <div class="relative pl-6 sm:pl-10 space-y-10 border-l border-[#D9D2C5] ml-4 sm:ml-12 pt-4 timeline-container">
                @foreach($timelineSteps as $step)
                    <div class="relative space-y-2 group timeline-step" style="opacity: 0;">
                        <div class="absolute -left-[31px] sm:-left-[47px] top-1.5 w-3.5 h-3.5 rounded-full {{ $loop->first || $loop->last ? 'bg-[#1C1917]' : 'bg-[#8E877D]' }} border-4 border-[#FAF8F5] timeline-dot" style="opacity: 0;"></div>
                        <div class="flex items-baseline space-x-3">
                            <span class="font-editorial text-3xl sm:text-4xl text-[#1C1917] font-light">{{ $step->year }}</span>
                            <span class="text-xs uppercase tracking-[0.2em] font-bold text-[#8E877D]">{{ $step->title }}</span>
                        </div>
                        <p class="text-[14.5px] text-[#78716C] font-light leading-relaxed max-w-xl">
                            {{ $step->description }}
                        </p>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <!-- GSAP ScrollTrigger Step-by-Step Animations for Timeline -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            // Refresh ScrollTrigger after a brief delay to properly calculate positions
            // This ensures external mice, trackpads and all scroll devices work correctly
            setTimeout(function() {
                ScrollTrigger.refresh();
            }, 200);

            // Header animation
            gsap.fromTo('.timeline-header',
                { opacity: 0, y: 40 },
                { 
                    opacity: 1, 
                    y: 0, 
                    duration: 1, 
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: '.timeline-header',
                        start: 'top 85%',
                        once: true
                    }
                }
            );

            // Step-by-step timeline animation
            const steps = document.querySelectorAll('.timeline-step');
            
            steps.forEach(function(step, idx) {
                const dot = step.querySelector('.timeline-dot');

                const tl = gsap.timeline({
                    scrollTrigger: {
                        trigger: step,
                        start: 'top 85%',
                        once: true
                    }
                });

                if (dot) {
                    tl.fromTo(dot, 
                        { scale: 0, opacity: 0 },
                        { scale: 1, opacity: 1, duration: 0.5, ease: 'back.out(1.7)' }
                    );
                }

                tl.fromTo(step,
                    { opacity: 0, x: -30 },
                    { opacity: 1, x: 0, duration: 0.7, ease: 'power3.out' },
                    "-=0.25"
                );
            });

            // Values cards animation
            const valueCards = document.querySelectorAll('.value-card');
            valueCards.forEach(function(card, idx) {
                gsap.fromTo(card,
                    { opacity: 0, y: 30 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.7,
                        delay: idx * 0.12,
                        ease: 'power3.out',
                        scrollTrigger: {
                            trigger: card,
                            start: 'top 88%',
                            once: true
                        }
                    }
                );
            });

            // Artisan cards animation
            const artisanCards = document.querySelectorAll('.artisan-card');
            artisanCards.forEach(function(card, idx) {
                gsap.fromTo(card,
                    { opacity: 0, y: 40 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.8,
                        delay: idx * 0.15,
                        ease: 'power3.out',
                        scrollTrigger: {
                            trigger: card,
                            start: 'top 88%',
                            once: true
                        }
                    }
                );
            });

            // Refresh on any resize for external mouse/window changes
            window.addEventListener('resize', function() {
                ScrollTrigger.refresh();
            });
        }
    });
    </script>
@endif
