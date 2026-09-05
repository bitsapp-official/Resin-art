<x-app-layout title="Your Bag — Maison Résine">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-12 py-10">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-xs flex items-center justify-between">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="border-b border-[#E6E1D7] pb-6 mb-8 flex items-baseline justify-between">
            <h1 class="font-editorial text-4xl sm:text-5xl italic text-[#1C1917] font-light">Your bag</h1>
            <span class="text-xs uppercase tracking-widest text-[#78716C] font-semibold">{{ $cart->items->count() }} Piece(s)</span>
        </div>

        @if($cart->items->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- Items Table -->
                <div class="lg:col-span-8 space-y-6">
                    @foreach($cart->items as $item)
                        <div class="bg-[#FAF8F5] border border-[#E6E1D7] rounded-3xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 shadow-xs">
                            <div class="flex items-center space-x-4">
                                <div class="w-24 h-24 bg-[#F5F2EB] rounded-2xl border border-[#E6E1D7] overflow-hidden shrink-0">
                                    @if(!empty($item->product?->images) && isset($item->product->images[0]))
                                        <img src="{{ $item->product->images[0] }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div>
                                    <h3 class="font-editorial text-xl text-[#1C1917] font-normal">
                                        <a href="{{ route('shop.show', $item->product?->slug ?? '#') }}">{{ $item->product_name }}</a>
                                    </h3>
                                    <span class="text-[11px] text-[#78716C] block mt-0.5">
                                        {{ $item->product?->category?->name ?? 'Handcrafted resin piece' }}
                                        @if(!empty($item->options['size']))
                                            <span class="text-[#1C1917] font-semibold"> &bull; {{ $item->options['size'] }}</span>
                                        @endif
                                    </span>
                                    <span class="font-semibold text-sm text-[#1C1917] block mt-2">₹ {{ number_format($item->price) }} each</span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-6 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 border-[#E6E1D7] pt-4 sm:pt-0">
                                <!-- Quantity controls -->
                                <form method="POST" action="{{ route('cart.update') }}" class="flex items-center space-x-2 border border-[#E6E1D7] bg-white rounded-full px-3 py-1 text-xs">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <button type="submit" name="quantity" value="{{ max(1, $item->quantity - 1) }}" class="text-[#78716C] font-bold px-1">-</button>
                                    <span class="font-semibold text-xs text-[#1C1917] px-2">{{ $item->quantity }}</span>
                                    <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="text-[#78716C] font-bold px-1">+</button>
                                </form>

                                <span class="font-semibold text-base text-[#1C1917]">₹ {{ number_format($item->subtotal) }}</span>

                                <form method="POST" action="{{ route('cart.remove') }}">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <button type="submit" class="text-[#78716C] hover:text-red-700 p-1" title="Remove item">
                                        <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Summary Panel -->
                <div class="lg:col-span-4 bg-[#FAF8F5] border border-[#E6E1D7] rounded-3xl p-6 sm:p-8 space-y-6 shadow-xs">
                    <h3 class="font-editorial text-xl text-[#1C1917] italic border-b border-[#E6E1D7] pb-3">Bag summary</h3>

                    <div class="space-y-3 text-xs text-[#78716C]">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-semibold text-[#1C1917]">₹ {{ number_format($cart->total) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Insured Crate Delivery</span>
                            <span class="text-emerald-800 font-semibold">Complimentary</span>
                        </div>
                        <div class="flex justify-between border-t border-[#E6E1D7] pt-3 text-base">
                            <span class="font-editorial text-[#1C1917] italic">TOTAL</span>
                            <span class="font-editorial font-bold text-[#1C1917]">₹ {{ number_format($cart->total) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="block w-full bg-[#1C1917] hover:bg-[#2D2825] text-white text-center text-xs uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all duration-300 shadow-sm">
                        PROCEED TO CHECKOUT
                    </a>

                    <a href="{{ route('shop.index') }}" class="block text-center text-xs text-[#8E7558] hover:underline uppercase tracking-wider font-semibold">
                        ← Continue Shopping
                    </a>
                </div>

            </div>
        @else
            <div class="text-center py-20 bg-white/40 border border-[#E6E1D7] rounded-3xl p-8 space-y-4">
                <h2 class="font-editorial text-3xl italic text-[#1C1917]">Still empty.</h2>
                <p class="text-xs text-[#78716C] max-w-sm mx-auto">Every piece is one of one — begin with the index.</p>
                <a href="{{ route('shop.index') }}" class="inline-block bg-[#1C1917] text-white text-xs uppercase tracking-[0.25em] font-semibold px-8 py-3.5 rounded-full">
                    BROWSE THE SHOP
                </a>
            </div>
        @endif

    </div>
</x-app-layout>
