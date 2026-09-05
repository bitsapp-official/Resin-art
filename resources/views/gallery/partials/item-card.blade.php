@php
    $index = $index ?? 0;
    // Instagram / Pinterest mixed aspect ratios for dynamic visual arrangement
    $aspectRatios = ['aspect-[3/4]', 'aspect-[4/5]', 'aspect-square', 'aspect-[16/11]', 'aspect-[3/5]', 'aspect-[4/3]'];
    $ratioClass = $aspectRatios[$index % count($aspectRatios)];
@endphp

<div class="break-inside-avoid mb-6 group relative rounded-2xl sm:rounded-[1.5rem] overflow-hidden cursor-pointer transition-all duration-500 hover:shadow-2xl gallery-grid-card"
     @click="openLightbox({{ $globalIndex }})"
     tabindex="0"
     @keydown.enter="openLightbox({{ $globalIndex }})"
     aria-label="View {{ $item->title }} gallery image">

    <!-- Image with Natural Aspect Ratio Container -->
    <div class="w-full {{ $ratioClass }} overflow-hidden relative rounded-2xl sm:rounded-[1.5rem] bg-[oklch(98.5%_0.008_85)]">
        <img src="{{ asset('storage/' . $item->image_path) }}" 
             alt="{{ $item->image_alt ?? $item->title }}" 
             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
             loading="lazy"
             decoding="async">

        <!-- Elegant Minimal Hover Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 flex flex-col justify-between rounded-2xl sm:rounded-[1.5rem]">
            <div class="flex justify-end">
                <span class="bg-white/20 backdrop-blur-md text-white text-[9.5px] uppercase tracking-[0.2em] font-semibold px-3 py-1 rounded-full border border-white/30">
                    {{ $item->galleryCategory ? $item->galleryCategory->name : 'Gallery' }}
                </span>
            </div>
            <div class="space-y-1 text-white">
                @if(!empty($item->location))
                    <span class="block text-[9.5px] uppercase tracking-[0.25em] text-[#D9D2C5] font-medium">
                        {{ $item->location }}
                    </span>
                @endif
                <h3 class="text-sm uppercase tracking-[0.2em] font-medium text-white">
                    {{ $item->title }}
                </h3>
            </div>
        </div>
    </div>
</div>
