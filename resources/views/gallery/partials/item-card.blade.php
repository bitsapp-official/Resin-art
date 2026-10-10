@php
    $index = $index ?? 0;
    // Instagram / Pinterest mixed aspect ratios for dynamic visual arrangement
    $aspectRatios = ['aspect-[3/4]', 'aspect-[4/5]', 'aspect-square', 'aspect-[16/11]', 'aspect-[3/5]', 'aspect-[4/3]'];
    $ratioClass = $aspectRatios[$index % count($aspectRatios)];
    $isVideo = $item->is_video;
@endphp

<div class="break-inside-avoid mb-6 group relative rounded-2xl sm:rounded-[1.5rem] overflow-hidden cursor-pointer transition-all duration-500 hover:shadow-2xl gallery-grid-card"
     @click="openLightbox({{ $globalIndex }})"
     tabindex="0"
     @keydown.enter="openLightbox({{ $globalIndex }})"
     aria-label="View {{ $item->title }} gallery {{ $isVideo ? 'video' : 'image' }}">

    <!-- Image / Video Poster Container -->
    <div class="w-full {{ $ratioClass }} overflow-hidden relative rounded-2xl sm:rounded-[1.5rem] bg-[oklch(98.5%_0.008_85)]">
        <img src="{{ $item->thumbnail_url }}" 
             alt="{{ $item->image_alt ?? $item->title }}" 
             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
             loading="lazy"
             decoding="async">

        @if($isVideo)
            <!-- Permanent Top-Left Video Badge -->
            <div class="absolute top-3.5 left-3.5 z-20 pointer-events-none">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1C1917]/90 text-white text-[9px] font-bold uppercase tracking-wider backdrop-blur-md shadow-md border border-white/20">
                    <svg class="w-3 h-3 fill-current text-[#FBBF24]" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Video
                </span>
            </div>

            <!-- Centered Play Disc -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10 transition-transform duration-300 group-hover:scale-110">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white/95 text-[#1C1917] flex items-center justify-center shadow-xl border border-white/40">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </div>
            </div>
        @endif

        <!-- Elegant Minimal Hover Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 flex flex-col justify-between rounded-2xl sm:rounded-[1.5rem] z-20">
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
                <h3 class="text-sm uppercase tracking-[0.2em] font-medium text-white flex items-center gap-2">
                    @if($isVideo)
                        <svg class="w-4 h-4 fill-current text-[#FBBF24] shrink-0" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    @endif
                    <span>{{ $item->title }}</span>
                </h3>
            </div>
        </div>
    </div>
</div>
