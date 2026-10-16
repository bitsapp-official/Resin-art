<x-app-layout title="Shop Fine Jewellery & Diamond Rings — Solitaire Fine Jewels">
<div class="min-h-screen bg-transparent w-full min-w-0">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 pt-2 sm:pt-4 pb-16 sm:pb-24 w-full min-w-0">

        {{-- ── HERO HEADER SECTION ──────────────────────────────── --}}
        <div class="pt-1 pb-6 sm:pb-8 border-b border-[#E5DFD3]/80 space-y-2 sm:space-y-3">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                <span>THE INDEX</span>
            </div>

            <h1 class="font-editorial text-3xl sm:text-5xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                Every piece, <em class="italic font-normal">one of one.</em>
            </h1>

            <p class="text-xs sm:text-[14px] md:text-[15px] text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                Master handcrafted in hallmarked gold with certified natural & lab-grown diamonds. Uncompromising brilliance. Modern heirlooms.
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
              class="flex flex-wrap items-center justify-between gap-3.5 py-4 mb-4">
            
            {{-- Retain search query if present --}}
            @if(request()->filled('q') || request()->filled('search'))
                <input type="hidden" name="q" value="{{ request('q') ?: request('search') }}">
            @endif

            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                {{-- 1. Categories Pill Dropdown --}}
                <div class="relative">
                    <select name="category" onchange="this.form.submit()"
                            class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border border-[#DFD9CE] rounded-full pl-4 sm:pl-5 pr-9 sm:pr-10 py-2 sm:py-2.5 text-[10px] sm:text-[10.5px] uppercase tracking-[0.16em] sm:tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Metal Color / Tone Filter Dropdown --}}
                <div class="relative">
                    <select name="color" onchange="this.form.submit()"
                            class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border {{ request('color') ? 'border-[#1C1917] bg-[#F7F4EE] font-semibold' : 'border-[#DFD9CE]' }} rounded-full pl-4 sm:pl-5 pr-9 sm:pr-10 py-2 sm:py-2.5 text-[10px] sm:text-[10.5px] uppercase tracking-[0.16em] sm:tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                        <option value="">Metal: All Tones</option>
                        <option value="Yellow Gold" {{ in_array(request('color'), ['Yellow Gold', 'yellow', 'yellow-gold']) ? 'selected' : '' }}>Yellow Gold</option>
                        <option value="White Gold" {{ in_array(request('color'), ['White Gold', 'white', 'white-gold']) ? 'selected' : '' }}>White Gold</option>
                        <option value="Rose Gold" {{ in_array(request('color'), ['Rose Gold', 'rose', 'rose-gold']) ? 'selected' : '' }}>Rose Gold</option>
                    </select>
                </div>

                {{-- 3. Gold Purity Filter Dropdown --}}
                <div class="relative">
                    <select name="purity" onchange="this.form.submit()"
                            class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border {{ request('purity') ? 'border-[#1C1917] bg-[#F7F4EE] font-semibold' : 'border-[#DFD9CE]' }} rounded-full pl-4 sm:pl-5 pr-9 sm:pr-10 py-2 sm:py-2.5 text-[10px] sm:text-[10.5px] uppercase tracking-[0.16em] sm:tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                        <option value="">Purity: All Purities</option>
                        <option value="14KT Gold" {{ in_array(request('purity'), ['14KT Gold', '14KT', '14kt', '14k']) ? 'selected' : '' }}>14KT Gold</option>
                        <option value="18KT Gold" {{ in_array(request('purity'), ['18KT Gold', '18KT', '18kt', '18k']) ? 'selected' : '' }}>18KT Gold</option>
                        <option value="22KT Gold" {{ in_array(request('purity'), ['22KT Gold', '22KT', '22kt', '22k']) ? 'selected' : '' }}>22KT Gold</option>
                    </select>
                </div>

                {{-- 4. Price Filter Dropdown Pill --}}
                <div class="relative">
                    <select name="price" onchange="this.form.submit()"
                            class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border {{ request('price') ? 'border-[#1C1917] bg-[#F7F4EE] font-semibold' : 'border-[#DFD9CE]' }} rounded-full pl-4 sm:pl-5 pr-9 sm:pr-10 py-2 sm:py-2.5 text-[10px] sm:text-[10.5px] uppercase tracking-[0.16em] sm:tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
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
                        class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border border-[#DFD9CE] rounded-full pl-4 sm:pl-5 pr-9 sm:pr-10 py-2 sm:py-2.5 text-[10px] sm:text-[10.5px] uppercase tracking-[0.16em] sm:tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_12px_center] bg-no-repeat">
                    <option value="curated" {{ request('sort','curated') == 'curated' ? 'selected' : '' }}>Sort: Curated</option>
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Sort: Price Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Sort: Price High to Low</option>
                </select>
            </div>
        </form>

        {{-- ── ACTIVE FILTER CHIPS (Quick Clear Bar) ─────────────── --}}
        @php
            $hasActiveFilters = request()->filled('category') || request()->filled('color') || request()->filled('purity') || request()->filled('price');
        @endphp
        @if($hasActiveFilters)
            <div class="flex items-center gap-2 flex-wrap mb-6 pt-1">
                <span class="text-[9.5px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] mr-1">Active:</span>

                {{-- Category Chip --}}
                @if(request()->filled('category'))
                    @php
                        $activeCat = $categories->firstWhere('slug', request('category'));
                    @endphp
                    <a href="{{ route('shop.index', request()->except('category')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1C1917] text-white text-[9px] uppercase tracking-[0.16em] hover:bg-[#8E877D] transition-colors shadow-2xs">
                        <span>{{ $activeCat?->name ?? request('category') }}</span>
                        <span class="text-xs leading-none">&times;</span>
                    </a>
                @endif

                {{-- Color Chip --}}
                @if(request()->filled('color'))
                    <a href="{{ route('shop.index', request()->except('color')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1C1917] text-white text-[9px] uppercase tracking-[0.16em] hover:bg-[#8E877D] transition-colors shadow-2xs">
                        <span>{{ request('color') }}</span>
                        <span class="text-xs leading-none">&times;</span>
                    </a>
                @endif

                {{-- Purity Chip --}}
                @if(request()->filled('purity'))
                    <a href="{{ route('shop.index', request()->except('purity')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1C1917] text-white text-[9px] uppercase tracking-[0.16em] hover:bg-[#8E877D] transition-colors shadow-2xs">
                        <span>{{ request('purity') }}</span>
                        <span class="text-xs leading-none">&times;</span>
                    </a>
                @endif

                {{-- Price Chip --}}
                @if(request()->filled('price'))
                    @php
                        $priceLabels = [
                            'under_15000' => 'Under ₹15k',
                            'under_50000' => 'Under ₹50k',
                            'under_150000' => 'Under ₹1.5L',
                            'under_500000' => 'Under ₹5L',
                        ];
                    @endphp
                    <a href="{{ route('shop.index', request()->except('price')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1C1917] text-white text-[9px] uppercase tracking-[0.16em] hover:bg-[#8E877D] transition-colors shadow-2xs">
                        <span>{{ $priceLabels[request('price')] ?? request('price') }}</span>
                        <span class="text-xs leading-none">&times;</span>
                    </a>
                @endif

                {{-- Clear All Reset Link --}}
                <a href="{{ route('shop.index', request()->only(['q', 'search'])) }}"
                   class="text-[9.5px] uppercase tracking-[0.18em] text-[#8E877D] hover:text-[#1C1917] underline underline-offset-4 font-medium ml-2 transition-colors">
                    Reset All
                </a>
            </div>
        @endif

        <style>
            @keyframes luxuryShimmer {
                0% { transform: translateX(-100%); }
                100% { transform: translateX(100%); }
            }
            .animate-luxury-shimmer {
                animation: luxuryShimmer 1.6s infinite ease-in-out;
            }
        </style>

        {{-- ── PRODUCT GRID WITH INFINITE SCROLL & SKELETON LOADERS ── --}}
        @if($products->count() > 0)
            <div x-data="{
                    nextPageUrl: @js($products->nextPageUrl()),
                    hasMore: @js($products->hasMorePages()),
                    loading: false,
                    observer: null,
                    init() {
                        this.setupObserver();
                    },
                    setupObserver() {
                        if (!this.hasMore || !this.nextPageUrl) return;
                        if (this.observer) this.observer.disconnect();

                        this.observer = new IntersectionObserver((entries) => {
                            if (entries[0] && entries[0].isIntersecting && !this.loading && this.hasMore && this.nextPageUrl) {
                                this.loadMore();
                            }
                        }, {
                            rootMargin: '300px 0px'
                        });

                        this.$nextTick(() => {
                            if (this.$refs.scrollTrigger) {
                                this.observer.observe(this.$refs.scrollTrigger);
                            }
                        });
                    },
                    async loadMore() {
                        if (this.loading || !this.hasMore || !this.nextPageUrl) return;
                        this.loading = true;

                        try {
                            const url = new URL(this.nextPageUrl, window.location.origin);
                            url.searchParams.set('ajax', '1');

                            const res = await fetch(url.toString(), {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            if (!res.ok) throw new Error('Network error');

                            const data = await res.json();

                            if (data.html && this.$refs.productGrid) {
                                const tempDiv = document.createElement('div');
                                tempDiv.innerHTML = data.html;
                                const newCards = Array.from(tempDiv.children);
                                newCards.forEach(card => {
                                    this.$refs.productGrid.appendChild(card);
                                    if (window.Alpine) {
                                        window.Alpine.initTree(card);
                                    }
                                });
                            }

                            this.hasMore = !!data.has_more;
                            this.nextPageUrl = data.next_page_url;

                            if (this.hasMore && this.nextPageUrl) {
                                this.$nextTick(() => this.setupObserver());
                            } else if (this.observer) {
                                this.observer.disconnect();
                            }
                        } catch (e) {
                            console.error('Infinite scroll error:', e);
                        } finally {
                            this.loading = false;
                        }
                    }
                 }"
                 class="w-full">

                {{-- Product Grid Container --}}
                <div x-ref="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12 pt-4 pb-8">
                    @include('shop.partials.product-grid-items', ['products' => $products, 'wishlistIds' => $wishlistIds])
                </div>

                {{-- Skeleton Loading Grid (Shown during scroll fetching) --}}
                <div x-show="loading" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12 pb-12 transition-opacity duration-300">
                    @for($i = 0; $i < 3; $i++)
                        <div class="space-y-4 animate-pulse">
                            {{-- Skeleton Image Card with Luxury Shimmer --}}
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[2rem] bg-[#EFECE6] border border-[#DFD9CE]/50">
                                <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/50 to-transparent animate-luxury-shimmer"></div>
                                <div class="absolute bottom-4 left-4 flex gap-1.5 px-3 py-1.5 rounded-full bg-white/60">
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#DFD9CE]"></div>
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#DFD9CE]"></div>
                                    <div class="w-3.5 h-3.5 rounded-full bg-[#DFD9CE]"></div>
                                </div>
                            </div>
                            {{-- Skeleton Typography Bars --}}
                            <div class="flex items-start justify-between gap-3 px-1 pt-1">
                                <div class="space-y-2 flex-1">
                                    <div class="h-4 bg-[#E5DFD3] rounded-full w-3/4"></div>
                                    <div class="h-3 bg-[#EAE4D9] rounded-full w-1/2"></div>
                                </div>
                                <div class="h-4 bg-[#E5DFD3] rounded-full w-20 shrink-0"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Intersection Observer Sentinel Trigger --}}
                <div x-ref="scrollTrigger" class="h-10 w-full pointer-events-none"></div>

                {{-- End of Catalog Subtle Indicator --}}
                <div x-show="!hasMore" class="text-center pt-6 pb-14">
                    <span class="inline-flex items-center justify-center border border-[#DFD9CE] text-[#8E877D] text-[10px] uppercase tracking-[0.25em] font-medium px-8 py-3 rounded-full bg-white/60 shadow-2xs cursor-default">
                        ALL PIECES SHOWN
                    </span>
                </div>
            </div>
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
