@php
    $index = $index ?? 0;
    $isImageLeft = ($index % 2 === 0);
@endphp

<div class="process-step-item space-y-8 lg:space-y-12 opacity-0">
    @if($index > 0)
        <!-- Crisp Full-Width Section Line Divider -->
        <div class="w-full h-[1px] bg-[#E5DFD3] my-8 lg:my-12"></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center py-2 lg:py-4 group">

        <!-- Image Column -->
        <div class="lg:col-span-7 {{ $isImageLeft ? 'lg:order-first' : 'lg:order-last' }}">
            <div class="rounded-[2rem] overflow-hidden border border-[#E5DFD3] bg-[oklch(98.5%_0.008_85)] shadow-[0_10px_35px_rgba(0,0,0,0.03)]">
                @if(!empty($step->image_path))
                    <img src="{{ asset('storage/' . $step->image_path) }}" 
                         alt="{{ $step->image_alt ?? $step->title }}" 
                         class="w-full h-[360px] sm:h-[480px] lg:h-[540px] object-cover transition-transform duration-700 group-hover:scale-[1.02]"
                         loading="lazy"
                         decoding="async">
                @else
                    <div class="w-full h-[380px] bg-[#FAF8F5] flex items-center justify-center text-[#8E877D] text-xs uppercase tracking-widest">
                        {{ $step->title }} Image
                    </div>
                @endif
            </div>
            @if(!empty($step->image_caption))
                <p class="text-[11px] font-mono text-[#8E877D] uppercase tracking-wider pt-2.5 px-2">
                    {{ $step->image_caption }}
                </p>
            @endif
        </div>

        <!-- Content Column -->
        <div class="lg:col-span-5 space-y-5 {{ $isImageLeft ? 'lg:order-last lg:pl-4' : 'lg:order-first lg:pr-4' }}">
            <!-- Step Number Badge -->
            <div class="flex items-center space-x-3 text-[10.5px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                <span>STEP {{ $step->formatted_step_number }}</span>
            </div>

            <!-- Title -->
            <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[46px] text-[#1C1917] font-light leading-[1.1] tracking-tight">
                {{ $step->title }}
            </h2>

            <!-- Description -->
            <p class="text-[15px] sm:text-base text-[#78716C] font-light leading-[1.75]">
                {{ $step->description }}
            </p>
        </div>

    </div>
</div>
