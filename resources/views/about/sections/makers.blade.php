<!-- About Page Craft Makers / Artisans Section ("The hands.") -->
@if(isset($artisans) && $artisans->count() > 0)
    <div class="py-12 lg:py-16 space-y-10">

        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-[#E5DFD3] pb-6">
            <div class="space-y-2">
                <span class="text-[10px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                    THE ARTISANS
                </span>
                <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[54px] text-[#1C1917] font-light leading-[1.08]">
                    The hands.
                </h2>
            </div>

            <a href="{{ route('our-process.index') }}" 
               class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.25em] font-semibold text-[#1C1917] hover:text-[#78716C] transition-colors">
                <span>SEE HOW A PIECE IS MADE</span>
                <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </a>
        </div>

        <!-- Artisans Dynamic Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-10">
            @foreach($artisans as $artisan)
                <div class="artisan-card space-y-4 group" style="opacity: 0;">
                    <div class="rounded-[2rem] overflow-hidden border border-[#E5DFD3] bg-[oklch(98.5%_0.008_85)] aspect-[4/5] shadow-sm">
                        @if(!empty($artisan->image_path))
                            <img src="{{ asset('storage/' . $artisan->image_path) }}" 
                                 alt="{{ $artisan->name }} — {{ $artisan->role }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                 loading="lazy">
                        @else
                            <div class="w-full h-full bg-[#FAF8F5] flex items-center justify-center text-[#8E877D] text-xs uppercase tracking-widest p-4 text-center">
                                {{ $artisan->name }} Photo
                            </div>
                        @endif
                    </div>
                    <div class="space-y-1 px-1">
                        <h3 class="font-editorial text-2xl text-[#1C1917] font-light">
                            {{ $artisan->name }}
                        </h3>
                        <span class="block text-[9.5px] uppercase tracking-[0.25em] font-bold text-[#8E877D]">
                            {{ $artisan->role }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
@endif
