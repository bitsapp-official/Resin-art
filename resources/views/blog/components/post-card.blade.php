@php
    $featured = $featured ?? false;
@endphp

@if($featured)
    <!-- FEATURED ARTICLE HERO CARD -->
    <article class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-center rounded-3xl bg-[#FCFAF7] border border-[#E8E2D8] p-5 sm:p-7 lg:p-8 hover:border-[#D5C7B2] hover:shadow-[0_20px_50px_rgba(173,149,117,0.15)] transition-all duration-500 group">
        <!-- Left Image -->
        <div class="lg:col-span-7 aspect-[16/10] sm:aspect-[16/9] rounded-2xl overflow-hidden bg-[#F3ECE2] relative">
            <a href="{{ route('blog.show', $post->slug) }}" class="block w-full h-full">
                <img src="{{ $post->image_url }}" 
                     alt="{{ $post->title }}" 
                     class="w-full h-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-105"
                     loading="eager"
                     decoding="async">
            </a>
            @if($post->category)
                <span class="absolute top-4 left-4 px-3 py-1 rounded-full bg-white/95 backdrop-blur-md text-[9.5px] uppercase tracking-wider font-semibold text-[#1C1917] shadow-xs">
                    {{ $post->category->name }}
                </span>
            @endif
        </div>

        <!-- Right Content -->
        <div class="lg:col-span-5 flex flex-col justify-between space-y-4 lg:py-2">
            <div class="space-y-3">
                <div class="flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.25em] font-semibold text-[#AD9575]">
                    <span>Featured Atelier Story</span>
                </div>

                <h2 class="font-editorial text-2xl sm:text-4xl lg:text-[40px] text-[#1C1917] font-normal leading-[1.18] group-hover:text-[#AD9575] transition-colors">
                    <a href="{{ route('blog.show', $post->slug) }}">
                        {{ $post->title }}
                    </a>
                </h2>

                <p class="text-sm sm:text-[15px] text-[#6E675E] font-light leading-relaxed line-clamp-4">
                    {{ $post->excerpt }}
                </p>
            </div>

            <!-- Footer Meta & CTA -->
            <div class="pt-5 border-t border-[#EAE3D8] flex items-center justify-between">
                <span class="text-[10.5px] text-[#8E877D] tracking-wider uppercase font-medium">
                    {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Recent' }}
                </span>
                <a href="{{ route('blog.show', $post->slug) }}" 
                   class="inline-flex items-center space-x-2 text-[11px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] group-hover:text-[#AD9575] transition-colors group/link">
                    <span>Read Full Piece</span>
                    <span class="group-hover/link:translate-x-1.5 transition-transform duration-300">&rarr;</span>
                </a>
            </div>
        </div>
    </article>
@else
    <article class="group flex flex-col justify-between rounded-3xl bg-[#FCFAF7] border border-[#E8E2D8] p-4 sm:p-5 hover:border-[#D5C7B2] hover:shadow-[0_16px_40px_rgba(173,149,117,0.12)] hover:-translate-y-1.5 transition-all duration-300 {{ $extraClass ?? '' }}">
        
        <div class="space-y-4">
            <!-- Image Container with Smooth Zoom -->
            <div class="aspect-[16/10] w-full rounded-2xl overflow-hidden bg-[#F3ECE2] relative">
                <a href="{{ route('blog.show', $post->slug) }}" class="block w-full h-full">
                    <img src="{{ $post->image_url }}" 
                         alt="{{ $post->title }}"
                         loading="lazy"
                         decoding="async"
                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out">
                </a>
                @if($post->category)
                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-md text-[9px] uppercase tracking-wider font-semibold text-[#1C1917] shadow-xs">
                        {{ $post->category->name }}
                    </span>
                @endif
            </div>

            <!-- Meta & Title -->
            <div class="space-y-2">
                <h3 class="font-editorial text-xl sm:text-2xl text-[#1C1917] font-normal leading-snug group-hover:text-[#AD9575] transition-colors">
                    <a href="{{ route('blog.show', $post->slug) }}" class="line-clamp-2">
                        {{ $post->title }}
                    </a>
                </h3>

                <p class="text-xs sm:text-[13px] text-[#6E675E] font-light leading-relaxed line-clamp-3">
                    {{ $post->excerpt }}
                </p>
            </div>
        </div>

        <!-- Footer CTA Link -->
        <div class="pt-4 mt-4 border-t border-[#EAE3D8] flex items-center justify-between">
            <span class="text-[10px] text-[#8E877D] tracking-wider uppercase font-medium">
                {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Recent' }}
            </span>
            <a href="{{ route('blog.show', $post->slug) }}" 
               class="inline-flex items-center space-x-1.5 text-[10.5px] uppercase tracking-[0.2em] font-semibold text-[#AD9575] group-hover:text-[#1C1917] transition-colors">
                <span>Read Piece</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

    </article>
@endif
