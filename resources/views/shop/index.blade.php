<x-app-layout title="Shop All Resin Art — Maison Résine Atelier">
<div class="min-h-screen bg-transparent">
    <div class="max-w-[1360px] mx-auto px-6 lg:px-12 xl:px-16 pt-8 pb-24">

        {{-- ── HERO HEADER SECTION ──────────────────────────────── --}}
        <div class="py-10 border-b border-[#E5DFD3]/80 space-y-3.5">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                <span>THE INDEX</span>
            </div>

            <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                Every piece, <em class="italic font-normal">one of one.</em>
            </h1>

            <p class="text-[14px] sm:text-[15px] text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                Poured by hand in Bordeaux with resin, hardwood, and raw pigments. Unrepeatable design. Objects for residence.
            </p>
        </div>

        {{-- ── SEARCH RESULTS BANNER ────────────────────────────────── --}}
        @if(request()->filled('search') || request()->filled('q'))
            @php
                $searchQuery = request('search') ?: request('q');
            @endphp
            <div class="mt-6 p-6 rounded-[2.25rem] bg-white/60 backdrop-blur-xs border border-[#E5DFD3] flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-fade-up">
                <div class="space-y-1">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[#8E877D] font-bold">Search Results</p>
                    <h2 class="font-editorial text-2xl text-[#1C1917]">
                        Showing results for &ldquo;{{ $searchQuery }}&rdquo;
                        <span class="text-sm font-sans font-normal text-[#8E877D] ml-2">({{ $products->total() }} {{ Str::plural('piece', $products->total()) }} found)</span>
                    </h2>
                </div>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full bg-[#1C1917] px-6 py-3 text-[10px] uppercase tracking-[0.24em] font-medium text-white hover:bg-[#AD9575] transition-all shadow-xs">
                    Clear Search
                </a>
            </div>
        @endif

        {{-- ── FILTER & SORT BAR ────────────────────────────────── --}}
        <form method="GET" action="{{ route('shop.index') }}"
              class="flex flex-wrap items-center justify-between gap-4 py-4 mb-6">
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Categories Pill Dropdown --}}
                <div class="relative">
                    <select name="category" onchange="this.form.submit()"
                            class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border border-[#DFD9CE] rounded-full pl-5 pr-10 py-2.5 text-[10.5px] uppercase tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_14px_center] bg-no-repeat">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Price Filter Slider Indicator / Dropdown Pill --}}
                <div class="relative">
                    <select name="price" onchange="this.form.submit()"
                            class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border border-[#DFD9CE] rounded-full pl-5 pr-10 py-2.5 text-[10.5px] uppercase tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_14px_center] bg-no-repeat">
                        <option value="">Price: All Ranges</option>
                        <option value="under_15000" {{ request('price') == 'under_15000' ? 'selected' : '' }}>Under ₹ 15,000</option>
                        <option value="under_50000" {{ request('price') == 'under_50000' ? 'selected' : '' }}>Under ₹ 50,000</option>
                        <option value="under_150000" {{ request('price') == 'under_150000' ? 'selected' : '' }}>Under ₹ 1,50,000</option>
                        <option value="under_500000" {{ request('price') == 'under_500000' ? 'selected' : '' }}>Under ₹ 5,00,000</option>
                    </select>
                </div>
            </div>

            {{-- Right side Sort Dropdown --}}
            <div class="relative">
                <select name="sort" onchange="this.form.submit()"
                        class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border border-[#DFD9CE] rounded-full pl-5 pr-10 py-2.5 text-[10.5px] uppercase tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_14px_center] bg-no-repeat">
                    <option value="curated" {{ request('sort','curated') == 'curated' ? 'selected' : '' }}>Sort: Curated</option>
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Sort: Price Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Sort: Price High to Low</option>
                </select>
            </div>
        </form>

        {{-- ── PRODUCT GRID ─────────────────────────────────────── --}}
        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12 pt-4 pb-16">
                @foreach($products as $product)
                    <div class="group">
                        {{-- Rounded Product Image Card --}}
                        <div class="relative overflow-hidden aspect-[4/5] rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-4 group/card">
                            <a href="{{ route('shop.show', $product->slug) }}" class="block w-full h-full">
                                @if(!empty($product->images) && isset($product->images[0]))
                                    <img src="{{ $product->images[0] }}"
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#C4BDB4]">
                                        <svg class="w-12 h-12 stroke-[1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </div>
                                @endif
                            </a>

                            {{-- Badge top-left --}}
                            @if($product->is_bestseller)
                                <span class="glass-pill absolute top-4 left-4 text-[#1C1917] text-[9px] uppercase tracking-[0.18em] font-medium px-3.5 py-1.5 rounded-full pointer-events-none">BESTSELLER</span>
                            @elseif($product->is_featured)
                                <span class="glass-pill absolute top-4 left-4 text-[#1C1917] text-[9px] uppercase tracking-[0.18em] font-medium px-3.5 py-1.5 rounded-full pointer-events-none">FEATURED</span>
                            @elseif($product->is_new)
                                <span class="glass-pill absolute top-4 left-4 text-[#1C1917] text-[9px] uppercase tracking-[0.18em] font-medium px-3.5 py-1.5 rounded-full pointer-events-none">NEW</span>
                            @endif

                            @php
                                $isWishlisted = in_array($product->id, $wishlistIds ?? []);
                            @endphp

                            {{-- Floating Action Buttons (Top Right): Hover Animated with Glass Border --}}
                            <div class="absolute top-4 right-4 flex flex-col space-y-2 z-20 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300 ease-out pointer-events-none group-hover:pointer-events-auto">
                                {{-- Wishlist Toggle Button --}}
                                <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $product->id }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
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
                                    <button type="submit" title="Add to Bag"
                                            class="glass-pill border border-white/70 w-9 h-9 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Card Metadata --}}
                        <div class="flex items-start justify-between gap-3 px-1">
                            <div class="min-w-0">
                                <h3 class="font-editorial text-[1.2rem] text-[#1C1917] font-normal leading-snug group-hover:text-[#8E877D] transition-colors">
                                    <a href="{{ route('shop.show', $product->slug) }}">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="text-[11px] text-[#8E877D] font-light mt-0.5 truncate">
                                    {{ $product->category?->name ?? 'Handcrafted Resin Art' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0 pt-0.5">
                                <div class="flex items-baseline justify-end gap-2">
                                    <span class="text-[13.5px] font-semibold text-[#1C1917] tracking-tight">
                                        ₹{{ number_format($product->effective_price) }}
                                    </span>
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                        <s class="text-[11px] text-[#8E877D] font-light">
                                            ₹{{ number_format($product->price) }}
                                        </s>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Load More Pagination Button --}}
            @if($products->hasMorePages())
                <div class="text-center pt-4 pb-12">
                    <a href="{{ $products->nextPageUrl() }}"
                       class="inline-flex items-center justify-center border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[10.5px] uppercase tracking-[0.25em] font-semibold px-10 py-3.5 rounded-full transition-all duration-300 shadow-2xs">
                        LOAD MORE
                    </a>
                </div>
            @else
                <div class="text-center pt-4 pb-12">
                    <span class="inline-flex items-center justify-center border border-[#DFD9CE] text-[#8E877D] text-[10.5px] uppercase tracking-[0.25em] font-semibold px-10 py-3.5 rounded-full bg-white/50 cursor-default">
                        ALL PIECES SHOWN
                    </span>
                </div>
            @endif
        @else
            <div class="py-24 text-center space-y-4">
                <p class="font-editorial text-4xl italic text-[#1C1917]">No pieces found.</p>
                <p class="text-sm text-[#78716C] font-light">Try adjusting your filters or search keywords.</p>
                <a href="{{ route('shop.index') }}"
                   class="inline-block mt-4 border border-[#1C1917] text-[#1C1917] text-[10.5px] uppercase tracking-[0.22em] font-semibold px-8 py-3.5 rounded-full hover:bg-[#1C1917] hover:text-white transition-colors">
                    CLEAR FILTERS
                </a>
            </div>
        @endif

    </div>
</div>
</x-app-layout>
