@php
    $featured = $featured ?? false;
@endphp

@if($featured)
    <!-- FEATURED ARTICLE CARD (2-Column Desktop Layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2rem] p-6 lg:p-8 transition-all duration-500 hover:shadow-[0_15px_45px_rgba(0,0,0,0.04)] group">
        <!-- Left Image -->
        <div class="lg:col-span-7 rounded-[1.5rem] overflow-hidden bg-[#FAF8F5]">
            <a href="{{ route('blog.show', $post->slug) }}" class="block overflow-hidden">
                @if(!empty($post->featured_image))
                    <img src="{{ asset('storage/' . $post->featured_image) }}" 
                         alt="{{ $post->title }}" 
                         class="w-full h-[320px] sm:h-[400px] lg:h-[460px] object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                         loading="eager"
                         decoding="async">
                @else
                    <div class="w-full h-[360px] bg-[#FAF8F5] flex items-center justify-center text-[#8E877D] text-xs uppercase tracking-widest">
                        Journal Image
                    </div>
                @endif
            </a>
        </div>

        <!-- Right Content -->
        <div class="lg:col-span-5 space-y-5 lg:py-4">
            <div class="flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                <span>{{ $post->category?->name ?? 'Journal' }}</span>
                <span>·</span>
                <span>{{ $post->reading_time ?? '5 MIN' }}</span>
            </div>

            <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[48px] text-[#1C1917] font-light leading-[1.12]">
                <a href="{{ route('blog.show', $post->slug) }}" class="hover:opacity-80 transition-opacity">
                    {{ $post->title }}
                </a>
            </h2>

            <p class="text-[14.5px] sm:text-base text-[#78716C] font-light leading-relaxed">
                {{ $post->excerpt }}
            </p>

            <div class="pt-2">
                <a href="{{ route('blog.show', $post->slug) }}" 
                   class="inline-flex items-center space-x-2.5 text-[11px] uppercase tracking-[0.25em] font-semibold text-[#1C1917] hover:opacity-60 transition-opacity group/link">
                    <span>READ THE PIECE</span>
                    <svg class="w-3.5 h-3.5 stroke-[2] transition-transform duration-300 group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
@else
    <!-- STANDARD GRID CARD (3-Column Layout) -->
    <div class="flex flex-col justify-between space-y-4 group">
        <div class="space-y-4">
            <!-- Image Container -->
            <div class="rounded-[1.5rem] overflow-hidden border border-[#E5DFD3] bg-[oklch(98.5%_0.008_85)]">
                <a href="{{ route('blog.show', $post->slug) }}" class="block overflow-hidden">
                    @if(!empty($post->featured_image))
                        <img src="{{ asset('storage/' . $post->featured_image) }}" 
                             alt="{{ $post->title }}" 
                             class="w-full h-[240px] sm:h-[270px] object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                             loading="lazy"
                             decoding="async">
                    @else
                        <div class="w-full h-[240px] bg-[#FAF8F5] flex items-center justify-center text-[#8E877D] text-xs uppercase tracking-widest">
                            Journal Image
                        </div>
                    @endif
                </a>
            </div>

            <!-- Meta Badge -->
            <div class="flex items-center space-x-2 text-[10px] uppercase tracking-[0.22em] font-semibold text-[#8E877D] pt-1">
                <span>{{ $post->category?->name ?? 'Journal' }}</span>
                <span>·</span>
                <span>{{ $post->reading_time ?? '5 MIN' }}</span>
            </div>

            <!-- Title -->
            <h3 class="font-editorial text-2xl lg:text-3xl text-[#1C1917] font-normal leading-[1.25]">
                <a href="{{ route('blog.show', $post->slug) }}" class="hover:opacity-75 transition-opacity">
                    {{ $post->title }}
                </a>
            </h3>

            <!-- Excerpt -->
            <p class="text-[13.5px] text-[#78716C] font-light leading-relaxed line-clamp-3">
                {{ $post->excerpt }}
            </p>
        </div>

        <!-- Read Link -->
        <div class="pt-1">
            <a href="{{ route('blog.show', $post->slug) }}" 
               class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] hover:opacity-60 transition-opacity group/link">
                <span>READ THE PIECE</span>
                <svg class="w-3 h-3 stroke-[2] transition-transform duration-300 group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>
    </div>
@endif
