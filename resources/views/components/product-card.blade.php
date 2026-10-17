@props(['product', 'wishlistIds' => null])

@php
    if ($wishlistIds === null) {
        $wishlistIds = Auth::check()
            ? \App\Models\Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray()
            : session('guest_wishlist', []);
    }
    $isWishlisted = in_array($product->id, $wishlistIds ?? []);
    $subtitle = $product->dimensions 
        ?: ($product->materials 
        ?: ($product->inventory_type === 'MADE_TO_ORDER' ? 'MADE TO ORDER' : ($product->category->name ?? 'FINE JEWELLERY')));
    $cardColors = $product->metal_colors ?? [];
    $cardColorImages = $product->color_images ?: [];
    $defaultColor = !empty($cardColors) ? $cardColors[0] : 'Yellow Gold';
    $defaultImg = $cardColorImages[$defaultColor] ?? ($product->primary_image_url ?: ($product->images[0] ?? null));
    $hasColorVariants = count($cardColors) > 1 && !empty($cardColorImages);
@endphp

<div class="group"
     x-data="{
         currentImg: '{{ $defaultImg }}',
         selectedColor: '{{ $defaultColor }}',
         colorImages: {{ Js::from($cardColorImages) }},
         getColorStyle(color) {
             const c = (color || '').toLowerCase();
             if (c.includes('yellow')) return 'background: linear-gradient(135deg, #FFE082 0%, #D4AF37 50%, #B8860B 100%);';
             if (c.includes('rose'))   return 'background: linear-gradient(135deg, #FAD0C4 0%, #E8A598 50%, #C97D6B 100%);';
             if (c.includes('white') || c.includes('silver') || c.includes('platinum')) {
                 return 'background: linear-gradient(135deg, #FFFFFF 0%, #E8E8E8 50%, #BDBDBD 100%);';
             }
             return 'background: linear-gradient(135deg, #FFE082 0%, #D4AF37 50%, #B8860B 100%);';
         },
         selectColor(color) {
             this.selectedColor = color;
             if (this.colorImages && this.colorImages[color]) {
                 this.currentImg = this.colorImages[color];
             }
         }
     }">
    {{-- Rounded Product Image Card --}}
    <div class="relative overflow-hidden aspect-[4/5] rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-3 group/card">
        <a :href="'{{ route('shop.show', $product->slug) }}' + (selectedColor ? '?color=' + encodeURIComponent(selectedColor) : '')" class="block w-full h-full">
            <template x-if="currentImg">
                <img :src="currentImg"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
            </template>
            <template x-if="!currentImg">
                <div class="w-full h-full flex items-center justify-center text-[#C4BDB4]">
                    <svg class="w-12 h-12 stroke-[1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
            </template>
        </a>

        {{-- Dynamic Color Swatches (Luxury switcher - Click only) --}}
        @if($hasColorVariants)
            <div class="absolute bottom-3.5 left-3.5 sm:bottom-4 sm:left-4 z-20 flex items-center gap-1.5 px-2.5 py-1.5 rounded-full glass-pill border border-white/80 shadow-xs pointer-events-auto"
                 @click.stop>
                @foreach($cardColors as $c)
                    <button type="button"
                            @click.prevent.stop="selectColor('{{ $c }}')"
                            title="{{ $c }}"
                            class="w-4 h-4 rounded-full border transition-all duration-200 cursor-pointer p-0.5 focus:outline-none"
                            :class="selectedColor === '{{ $c }}' ? 'border-[#1C1917] ring-1.5 ring-[#1C1917]/40 scale-110' : 'border-[#DFD9CE] hover:border-[#1C1917] hover:ring-1.5 hover:ring-[#1C1917]/30 hover:scale-110'">
                        <span class="block w-full h-full rounded-full shadow-2xs" :style="getColorStyle('{{ $c }}')"></span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Floating Action Buttons (Top Right): Always visible on mobile, hover-reveal on desktop --}}
        <div class="absolute top-3.5 right-3.5 sm:top-4 sm:right-4 flex flex-col space-y-2 z-20 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 translate-x-0 sm:translate-x-2 sm:group-hover:translate-x-0 transition-all duration-300 ease-out pointer-events-auto sm:pointer-events-none sm:group-hover:pointer-events-auto">
            {{-- Wishlist Toggle Button --}}
            <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $product->id }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit"
                        title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                        aria-label="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                        class="wishlist-btn w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer shadow-md {{ $isWishlisted ? 'bg-[#1C1917] text-white' : 'glass-pill border border-white/70 text-[#1C1917] hover:bg-[#1C1917] hover:text-white' }}"
                        data-product-id="{{ $product->id }}"
                        data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                        data-style-type="shop-grid">
                    <svg class="wishlist-icon w-4 h-4 stroke-[1.75] transition-all duration-200 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>
            </form>

            {{-- Quick Add to Bag Button --}}
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="color" :value="selectedColor">
                <button type="submit" title="Add to Bag"
                        class="glass-pill border border-white/70 w-9 h-9 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                    <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Meta Data Below Card (Clean Editorial 2-Row Typography) --}}
    <div class="space-y-0.5 px-1">
        <div class="flex items-baseline justify-between gap-2">
            <h3 class="font-editorial text-[17px] sm:text-[18px] font-normal text-[#1C1917] group-hover:text-[#AD9575] transition-colors leading-snug truncate">
                <a :href="'{{ route('shop.show', $product->slug) }}' + (selectedColor ? '?color=' + encodeURIComponent(selectedColor) : '')">
                    {{ $product->name }}
                </a>
            </h3>
            <div class="flex items-baseline gap-1.5 shrink-0">
                <span class="text-[15px] sm:text-[16px] font-medium text-[#1C1917]">
                    ₹{{ number_format($product->effective_price) }}
                </span>
                @if($product->effective_mrp && $product->effective_mrp > $product->effective_price)
                    <s class="text-[11.5px] sm:text-[12px] text-[#8E877D] font-light">
                        ₹{{ number_format($product->effective_mrp) }}
                    </s>
                @endif
            </div>
        </div>

        <p class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.2em] font-medium text-[#6E675E] truncate">
            {{ $subtitle }}
            @if($hasColorVariants)
                <span class="text-[#B87333] font-normal ml-1" x-text="'· ' + selectedColor"></span>
            @endif
        </p>
    </div>
</div>
