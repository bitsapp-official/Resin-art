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
        ?: ($product->inventory_type === 'MADE_TO_ORDER' ? 'MADE TO ORDER' : ($product->category->name ?? 'HANDMADE RESIN')));
@endphp

<div class="group">
    {{-- Rounded Product Image Card --}}
    <div class="relative overflow-hidden aspect-[4/5] rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-3 group/card">
        <a href="{{ route('shop.show', $product->slug) }}" class="block w-full h-full">
            @if(!empty($product->images) && isset($product->images[0]))
                <img src="{{ $product->images[0] }}"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     decoding="async"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
            @else
                <div class="w-full h-full flex items-center justify-center text-[#C4BDB4]">
                    <svg class="w-12 h-12 stroke-[1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
            @endif
        </a>

        {{-- Top-Right Floating Wishlist Button --}}
        <div class="absolute top-4 right-4 z-20">
            <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $product->id }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit"
                        title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                        aria-label="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                        class="wishlist-btn glass-pill w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-[#1C1917] hover:bg-white transition-all duration-300 shadow-xs cursor-pointer"
                        data-product-id="{{ $product->id }}"
                        data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                        data-style-type="product-card">
                    <svg class="wishlist-icon w-4 h-4 transition-all duration-200 {{ $isWishlisted ? 'fill-[#B87333] stroke-[#B87333]' : 'fill-none stroke-[#1C1917]' }}" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>
            </form>
        </div>

        {{-- Frosted Glass QUICK ADD Pill Button (Bottom Inside Card on Hover) --}}
        <div class="absolute bottom-4 left-4 right-4 z-20 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-out pointer-events-none group-hover:pointer-events-auto">
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit"
                        class="glass-pill w-full py-3 px-6 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[10px] sm:text-[10.5px] uppercase tracking-[0.24em] font-semibold transition-all duration-300 shadow-md text-center cursor-pointer">
                    QUICK ADD
                </button>
            </form>
        </div>
    </div>

    {{-- Meta Data Below Card (Clean Editorial 2-Row Typography) --}}
    <div class="space-y-0.5 px-1">
        <div class="flex items-baseline justify-between gap-2">
            <h3 class="font-editorial text-[17px] sm:text-[18px] font-normal text-[#1C1917] group-hover:text-[#AD9575] transition-colors leading-snug truncate">
                <a href="{{ route('shop.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>
            <span class="text-[15px] sm:text-[16px] font-medium text-[#1C1917] shrink-0">
                ₹{{ number_format($product->effective_price) }}
            </span>
        </div>

        <p class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.2em] font-medium text-[#6E675E] truncate">
            {{ $subtitle }}
        </p>
    </div>
</div>
