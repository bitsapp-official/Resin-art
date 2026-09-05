<!-- Editorial Media Gallery Section -->
@if(isset($mediaItems) && $mediaItems->count() > 0)
    <div class="py-12 border-t border-[#E5DFD3] space-y-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10">
            @foreach($mediaItems as $item)
                <div class="space-y-4 group">
                    <div class="rounded-[1.75rem] overflow-hidden border border-[#E5DFD3] bg-[oklch(98.5%_0.008_85)] shadow-sm">
                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                             alt="{{ $item->alt_text ?? 'Maison Résine atelier craftsmanship' }}" 
                             class="w-full h-[360px] sm:h-[420px] object-cover transition-transform duration-700 group-hover:scale-[1.02]"
                             loading="lazy"
                             decoding="async">
                    </div>
                    @if(!empty($item->caption))
                        <p class="text-[12px] text-[#8E877D] font-light italic tracking-wide px-2">
                            {{ $item->caption }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
