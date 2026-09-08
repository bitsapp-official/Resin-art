<x-app-layout title="Your Bag — Maison Résine">
    <div class="max-w-[1200px] mx-auto w-full min-w-0 px-3.5 sm:px-6 lg:px-12 py-6 sm:py-10">

        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6">
                <x-alert type="success" :message="session('success')" />
            </div>
        @endif

        @if(session('error') && !str_contains(strtolower(session('error')), 'empty'))
            <div class="mb-6">
                <x-alert type="error" :message="session('error')" />
            </div>
        @endif

        <div class="border-b border-[#E6E1D7] pb-4 sm:pb-6 mb-6 sm:mb-8 flex items-baseline justify-between">
            <h1 class="font-editorial text-3xl sm:text-5xl italic text-[#1C1917] font-light">Your bag</h1>
            <span class="text-xs uppercase tracking-widest text-[#78716C] font-semibold">{{ $cart->items->count() }} Piece(s)</span>
        </div>

        @if($cart->items->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start w-full min-w-0">
                
                <!-- Items Table -->
                <div class="lg:col-span-8 space-y-4 sm:space-y-6 w-full min-w-0">
                    @foreach($cart->items->sortByDesc('id') as $item)
                        <div class="bg-[#FAF8F5] border border-[#E6E1D7] rounded-[1.5rem] sm:rounded-3xl p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6 shadow-xs w-full min-w-0">
                            <div class="flex items-center space-x-3.5 sm:space-x-4 min-w-0 flex-1">
                                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-[#F5F2EB] rounded-2xl border border-[#E6E1D7] overflow-hidden shrink-0">
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
                <div class="lg:col-span-4 sticky top-8 w-full min-w-0">
                    <div class="bg-[#FAF8F5] border border-[#E6E1D7] rounded-[1.5rem] sm:rounded-[2rem] p-4 sm:p-7 md:p-8 space-y-5 sm:space-y-6 shadow-xs w-full min-w-0">
                        
                        {{-- Header --}}
                        <div class="flex items-center justify-between border-b border-[#E6E1D7] pb-4">
                            <div>
                                <span class="text-[9.5px] uppercase tracking-[0.22em] font-semibold text-[#8E877D] block">ORDER OVERVIEW</span>
                                <h3 class="font-editorial text-2xl text-[#1C1917] font-normal mt-0.5">Bag <em class="italic">summary</em></h3>
                            </div>
                            <span class="text-[10px] uppercase tracking-[0.18em] font-medium text-[#78716C] bg-white border border-[#E6E1D7] px-3 py-1 rounded-full">
                                {{ $cart->items->sum('quantity') }} {{ Str::plural('piece', $cart->items->sum('quantity')) }}
                            </span>
                        </div>

                        {{-- Line breakdown --}}
                        <div class="space-y-3.5 text-xs text-[#78716C]">
                            <div class="flex justify-between items-center">
                                <span class="font-light">Subtotal</span>
                                <span class="font-medium text-sm text-[#1C1917]">₹ {{ number_format($cart->total) }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="font-light">Insured Crate Delivery</span>
                                <span class="font-medium text-xs text-[#1C1917]">Complimentary</span>
                            </div>

                            <div class="border-t border-[#E6E1D7] pt-4 mt-2">
                                <div class="flex justify-between items-baseline">
                                    <div class="space-y-0.5">
                                        <span class="text-xs uppercase tracking-[0.2em] font-bold text-[#1C1917] block">Estimated Total</span>
                                        <span class="text-[10px] text-[#8E877D] font-light block">Taxes &amp; crate shipping included</span>
                                    </div>
                                    <span class="font-editorial text-2xl sm:text-3xl text-[#1C1917] font-normal tracking-tight">₹ {{ number_format($cart->total) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="space-y-3 pt-1">
                            <a href="{{ route('checkout.index') }}" class="w-full bg-[#1A1615] hover:bg-[#2C2724] text-white text-[10px] sm:text-[11px] uppercase tracking-[0.25em] font-semibold py-3.5 sm:py-4 rounded-full transition-all duration-300 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 group">
                                <span>PROCEED TO CHECKOUT</span>
                                <svg class="w-3.5 h-3.5 text-white/70 group-hover:text-white group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>

                            <a href="{{ route('shop.index') }}" class="block text-center text-[10.5px] text-[#78716C] hover:text-[#1C1917] uppercase tracking-[0.2em] font-medium py-2 transition-colors">
                                ← Continue Shopping
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        @else
            <div class="text-center py-16 sm:py-20 bg-white/40 border border-[#E6E1D7] rounded-2xl sm:rounded-3xl p-6 sm:p-8 space-y-4">
                <h2 class="font-editorial text-2xl sm:text-3xl italic text-[#1C1917]">Still empty.</h2>
                <p class="text-xs text-[#78716C] max-w-sm mx-auto">Every piece is one of one — begin with the index.</p>
                <a href="{{ route('shop.index') }}" class="inline-block bg-[#1C1917] text-white text-[10.5px] uppercase tracking-[0.25em] font-semibold px-6 sm:px-8 py-3 sm:py-3.5 rounded-full whitespace-nowrap">
                    BROWSE THE SHOP
                </a>
            </div>
        @endif

    </div>
</x-app-layout>
