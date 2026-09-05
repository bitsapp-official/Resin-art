@php
    $cart = null;
    $cartItems = collect();
    if (Auth::check()) {
        $cart = \App\Models\Cart::where('user_id', Auth::id())->with('items.product')->first();
    } else {
        $cart = \App\Models\Cart::where('session_id', session()->getId())->whereNull('user_id')->with('items.product')->first();
    }
    if ($cart) {
        $cartItems = $cart->items;
    }
@endphp

{{-- Cart Drawer Overlay --}}
<div x-show="cartOpen"
     x-cloak
     class="fixed inset-0 z-[100] overflow-hidden"
     role="dialog"
     aria-modal="true">

    {{-- Backdrop --}}
    <div x-show="cartOpen"
         x-transition:enter="ease-in-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="cartOpen = false"
         class="fixed inset-0 bg-black/30 transition-opacity"></div>

    {{-- Panel --}}
    <div class="fixed inset-y-0 right-0 max-w-full flex">
        <div x-show="cartOpen"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="w-[420px] max-w-full bg-[#FAF9F6] flex flex-col shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between px-7 py-6 border-b border-[#DFD9CE]/60">
                <h2 class="font-editorial text-2xl sm:text-3xl text-[#1C1917] font-light">Your bag</h2>
                <button type="button" @click="cartOpen = false"
                        class="text-[9.5px] uppercase tracking-[0.25em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors cursor-pointer">
                    CLOSE
                </button>
            </div>

            {{-- Items Container --}}
            <div class="flex-1 overflow-y-auto px-7 py-6 space-y-6">
                @if($cartItems->count() > 0)
                    @foreach($cartItems as $item)
                        <div class="flex gap-4 pb-6 border-b border-[#DFD9CE]/40 last:border-b-0">
                            {{-- Thumbnail (Strict 80x80 Size Container) --}}
                            <div class="w-20 h-20 shrink-0 bg-[#F5F2EB] border border-[#DFD9CE]/60 overflow-hidden rounded-xl" style="width: 80px; height: 80px; min-width: 80px; min-height: 80px; max-width: 80px; max-height: 80px;">
                                @if(!empty($item->product?->images) && isset($item->product->images[0]))
                                    <img src="{{ $item->product->images[0] }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 flex flex-col justify-between">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h3 class="font-editorial text-[1.05rem] text-[#1C1917] font-light leading-snug">
                                            <a href="{{ route('shop.show', $item->product?->slug ?? '#') }}" class="hover:text-[#8E877D] transition-colors">
                                                {{ $item->product_name }}
                                            </a>
                                        </h3>
                                        <p class="text-[9.5px] uppercase tracking-[0.18em] text-[#8E877D] font-light mt-0.5">
                                            @if(!empty($item->options['size']))
                                                INK · {{ $item->options['size'] }}
                                            @else
                                                {{ $item->product?->category?->name ?? 'HANDCRAFTED ATELIER PIECE' }}
                                            @endif
                                        </p>
                                    </div>
                                    <span class="font-editorial text-lg text-[#1C1917] font-light whitespace-nowrap">
                                        ₹ {{ number_format($item->subtotal) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between mt-3">
                                    {{-- Oval Quantity Pill (Matching Screenshot) --}}
                                    <form method="POST" action="{{ route('cart.update') }}" class="inline-flex items-center">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        <div class="flex items-center border border-[#DFD9CE] rounded-full px-3 py-1 bg-white gap-3 shadow-2xs">
                                            <button type="submit" name="quantity" value="{{ max(0, $item->quantity - 1) }}"
                                                    class="text-[#78716C] hover:text-[#1C1917] text-sm leading-none font-light cursor-pointer px-1">−</button>
                                            <span class="text-xs font-semibold text-[#1C1917] min-w-[14px] text-center">{{ $item->quantity }}</span>
                                            <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}"
                                                    class="text-[#78716C] hover:text-[#1C1917] text-sm leading-none font-light cursor-pointer px-1">+</button>
                                        </div>
                                    </form>

                                    {{-- Remove Link --}}
                                    <form method="POST" action="{{ route('cart.remove') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        <button type="submit"
                                                class="text-[9px] uppercase tracking-[0.22em] text-[#8E877D] hover:text-[#1C1917] font-medium transition-colors cursor-pointer">
                                            REMOVE
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-20 text-center space-y-3">
                        <p class="font-editorial text-2xl text-[#1C1917] font-light italic">Still empty.</p>
                        <p class="text-xs text-[#8E877D] font-light">Every piece is one of one — begin with the index.</p>
                        <a href="{{ route('shop.index') }}" @click="cartOpen = false"
                           class="inline-block mt-4 bg-[#1C1917] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold px-8 py-3 rounded-full hover:bg-[#2D2825] transition-colors shadow-xs">
                            BROWSE SHOP
                        </a>
                    </div>
                @endif
            </div>

            {{-- Footer (Matching Screenshot) --}}
            @if($cartItems->count() > 0)
                <div class="border-t border-[#DFD9CE]/60 px-7 py-6 space-y-4 bg-[#FAF8F5]">
                    <div class="flex justify-between items-baseline">
                        <span class="text-[9.5px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">SUBTOTAL</span>
                        <span class="font-editorial text-2xl text-[#1C1917] font-light">₹ {{ number_format($cart->total) }}</span>
                    </div>
                    <p class="text-[8.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D] mt-0.5">SHIPPING CALCULATED AT CHECKOUT</p>

                    <div class="space-y-2 pt-2">
                        <a href="{{ route('checkout.index') }}"
                           class="block w-full bg-[#1C1917] hover:bg-[#2D2825] text-white text-center text-[10px] uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-xs">
                            CHECKOUT
                        </a>
                        <a href="{{ route('cart.index') }}"
                           class="block w-full border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-center text-[10px] uppercase tracking-[0.25em] font-semibold py-3.5 rounded-full transition-all shadow-2xs">
                            VIEW BAG
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
