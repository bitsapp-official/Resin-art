<!-- About Page Hero Section -->
<div class="max-w-4xl mb-12 lg:mb-16 space-y-4 animate-fade-up">
    <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
        <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
        <span>{{ $aboutPage->eyebrow ?? 'THE HOUSE · EST. 2013' }}</span>
    </div>

    <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
        {!! $aboutPage->hero_title ?? 'A quiet atelier.' !!}
    </h1>

    @if(!empty($aboutPage->hero_description))
        <p class="text-[15px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-2xl pt-1">
            {{ $aboutPage->hero_description }}
        </p>
    @endif
</div>
