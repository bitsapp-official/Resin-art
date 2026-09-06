<x-app-layout title="Saved Wishlist — Maison Résine">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-12 py-10">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-900 rounded-2xl text-xs flex items-center justify-between">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="border-b border-[#E6E1D7] pb-6 mb-8 flex items-baseline justify-between">
            <h1 class="font-editorial text-3xl sm:text-4xl italic font-light text-[#1C1917]">Your Saved Wishlist</h1>
            <span id="wishlist-page-count" class="text-xs uppercase tracking-widest text-[#78716C]">
                {{ $wishlists->count() }} {{ Str::plural('piece', $wishlists->count()) }}
            </span>
        </div>

        <div id="wishlist-items-container">
            @if($wishlists->count() > 0)
                <div id="wishlist-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($wishlists as $item)
                        @php $product = $item->product; @endphp
                        @if($product)
                            <div id="wishlist-card-{{ $product->id }}" class="wishlist-page-card bg-white/70 border border-[#E6E1D7] rounded-3xl overflow-hidden shadow-sm flex flex-col justify-between p-5 transition-all duration-300">
                                <div>
                                    <a href="{{ route('shop.show', $product->slug) }}" class="block aspect-square overflow-hidden bg-[#F5F2EB] rounded-2xl mb-4 relative">
                                        @if(!empty($product->images) && isset($product->images[0]))
                                            <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @endif
                                    </a>

                                    <h3 class="font-editorial text-xl text-[#1C1917] mb-1">
                                        <a href="{{ route('shop.show', $product->slug) }}" class="hover:text-[#8E7558] transition-colors">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    <p class="text-sm font-semibold text-[#1C1917] mb-4">
                                        ₹{{ number_format($product->effective_price) }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 pt-4 border-t border-[#E6E1D7]/60">
                                    @if($product->is_available)
                                        <form method="POST" action="{{ route('wishlist.move-to-cart') }}" class="flex-grow">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <button type="submit" class="w-full bg-[#1C1917] hover:bg-[#8E7558] text-white text-[10px] uppercase tracking-widest font-semibold py-2.5 px-4 rounded-full transition-all">
                                                Move to Bag
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] uppercase tracking-wider text-red-800 font-semibold">Currently Unavailable</span>
                                    @endif

                                    <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $product->id }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" title="Remove from Wishlist" class="wishlist-btn p-2 text-[#A8A29E] hover:text-red-700 transition-colors" data-product-id="{{ $product->id }}" data-style-type="wishlist-page-remove">
                                            <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div id="wishlist-empty-state" class="text-center py-20 bg-white/40 border border-[#E6E1D7] rounded-3xl p-8">
                    <svg class="w-12 h-12 mx-auto text-[#A8A29E] mb-4 stroke-[1.25]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <h3 class="font-editorial text-2xl text-[#1C1917] font-light mb-2">No saved pieces</h3>
                    <p class="text-xs text-[#78716C] mb-6">Save your favorite resin art creations to your wishlist while exploring.</p>
                    <a href="{{ route('shop.index') }}" class="inline-block bg-[#1C1917] text-white text-xs uppercase tracking-widest px-8 py-3.5 rounded-full font-semibold">
                        Browse Catalogue
                    </a>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
