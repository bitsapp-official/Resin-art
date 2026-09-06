<x-app-layout :title="$product->name . ' — Maison Résine Atelier'">
<div class="min-h-screen bg-transparent">
    <div class="max-w-[1320px] mx-auto px-6 lg:px-12 xl:px-16 pt-8 pb-24"
         x-data="{ 
            activeImage: 0, 
            selectedSize: '{{ !empty($product->attributes['size_variants']) ? ($product->attributes['size_variants'][0]['size'] ?? '') : (!empty($product->attributes['sizes']) ? $product->attributes['sizes'][0] : '') }}', 
            qty: 1, 
            showLightbox: false, 
            productImages: {{ json_encode($product->images ?? []) }},
            sizeVariants: {{ json_encode($product->attributes['size_variants'] ?? []) }},
            basePrice: {{ (float) $product->effective_price }},
            get currentPrice() {
                if (this.sizeVariants.length > 0) {
                    const variant = this.sizeVariants.find(v => v.size === this.selectedSize);
                    return variant ? variant.price : this.basePrice;
                }
                return this.basePrice;
            },
            formatPrice(price) {
                return new Intl.NumberFormat('en-IN').format(price);
            }
         }">

        {{-- ── FULL-SCREEN LIGHTBOX MODAL (LIGHT THEME MATCHING SCREENSHOT 1) ── --}}
        <div x-show="showLightbox" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="showLightbox = false"
             @click.self="showLightbox = false"
             class="fixed inset-0 z-[99999] bg-[#FAF8F5]/85 backdrop-blur-xl flex flex-col items-center justify-center p-4 sm:p-8 cursor-pointer">
            
            {{-- White Circle Close Button (Matching Screenshot 1) --}}
            <button type="button" @click.stop="showLightbox = false"
                    class="absolute top-6 right-6 text-[#1C1917] bg-white hover:bg-[#1C1917] hover:text-white border border-[#DFD9CE] w-11 h-11 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-md z-50">
                <svg class="w-5 h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Image Container --}}
            <div @click.self="showLightbox = false" class="relative max-w-5xl w-full flex-1 flex items-center justify-center p-2">
                <img :src="productImages[activeImage]"
                     alt="{{ $product->name }}"
                     @click.stop=""
                     class="max-w-full max-h-[78vh] object-contain rounded-2xl shadow-xl border border-white/60 transition-all duration-300 cursor-default">
            </div>

            {{-- Lightbox Thumbnails Navigation --}}
            <template x-if="productImages.length > 1">
                <div @click.stop="" class="flex items-center justify-center gap-3 pt-2 pb-4 z-50 cursor-default">
                    <template x-for="(img, idx) in productImages" :key="idx">
                        <button type="button" @click="activeImage = idx"
                                class="w-12 h-12 rounded-xl overflow-hidden border-2 transition-all cursor-pointer"
                                :class="activeImage === idx ? 'border-[#1C1917] scale-110 shadow-md opacity-100' : 'border-[#DFD9CE] opacity-60 hover:opacity-100'">
                            <img :src="img" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </template>
        </div>

        {{-- ── BREADCRUMB ──────────────────────────────────────── --}}
        <nav class="flex items-center gap-2 text-[10px] uppercase tracking-[0.2em] text-[#8E877D] mb-8 font-medium">
            <a href="{{ url('/') }}" class="hover:text-[#1C1917] transition-colors">Home</a>
            <span>·</span>
            <a href="{{ route('shop.index') }}" class="hover:text-[#1C1917] transition-colors">Shop</a>
            @if($product->category)
            <span>·</span>
            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-[#1C1917] transition-colors">{{ $product->category->name }}</a>
            @endif
            <span>·</span>
            <span class="text-[#1C1917] font-semibold">{{ $product->name }}</span>
        </nav>

        {{-- ── MAIN PRODUCT LAYOUT ─────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 mb-20">

            {{-- LEFT: Gallery --}}
            <div class="space-y-4">
                {{-- Main Gallery Image (Clickable + Smooth Hover Zoom + Full Screen Button) --}}
                <div class="relative overflow-hidden aspect-square rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs group cursor-zoom-in"
                     @click="showLightbox = true">
                    @if(!empty($product->images))
                        @foreach($product->images as $index => $img)
                            <img x-show="activeImage === {{ $index }}"
                                 src="{{ $img }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover absolute inset-0 transition-all duration-700 ease-out group-hover:scale-105"
                                 :class="activeImage === {{ $index }} ? 'opacity-100' : 'opacity-0'">
                        @endforeach
                    @endif

                    {{-- FULL SCREEN Pill Button (Bottom Right) --}}
                    <button type="button" @click.stop="showLightbox = true"
                            class="glass-pill absolute bottom-4 right-4 text-[#1C1917] text-[9px] uppercase tracking-[0.2em] font-medium px-4 py-1.5 rounded-full cursor-pointer hover:bg-white transition-all shadow-2xs z-10">
                        FULL SCREEN
                    </button>
                </div>

                {{-- Thumbnails --}}
                @if(!empty($product->images) && count($product->images) > 1)
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(array_slice($product->images, 0, 3) as $index => $img)
                            <button type="button" @click="activeImage = {{ $index }}"
                                    class="aspect-square overflow-hidden rounded-[1.25rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 transition-all duration-200 cursor-pointer"
                                    :class="activeImage === {{ $index }} ? 'ring-2 ring-[#1C1917] opacity-100' : 'opacity-60 hover:opacity-100'">
                                <img src="{{ $img }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- RIGHT: Product Info & Actions --}}
            <div class="space-y-6">
                {{-- Category & Title --}}
                <div>
                    <p class="text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D] mb-2">
                        {{ $product->category?->name ?? 'COASTERS' }}
                    </p>
                    <h1 class="font-editorial text-4xl sm:text-5xl text-[#1C1917] font-light leading-[1.08] tracking-tight">
                        {{ $product->name }}
                    </h1>
                    <p class="text-[10.5px] uppercase tracking-[0.18em] text-[#8E877D] font-light mt-3.5">
                        {{ $product->category?->name ?? 'BESPOKE ART' }} &middot; {{ $product->inventory_type === 'MADE_TO_ORDER' ? 'MADE TO ORDER' : 'IN STOCK' }}
                    </p>
                </div>

                {{-- Price & Review Rating (Interlinked to Customer Reviews) --}}
                @php
                    $revCount = $product->reviews_count ?? ($product->reviews ? $product->reviews->count() : 0);
                    $avgRating = $product->average_rating ?? 5.0;
                    $fullStars = floor($avgRating);
                @endphp
                <div class="flex items-center space-x-4 pt-1">
                    <div class="flex items-baseline gap-3">
                        <span class="font-sans font-medium text-2xl sm:text-3.5xl text-[#1C1917] tracking-tight">
                            ₹ <span x-text="formatPrice(currentPrice)">{{ number_format($product->effective_price) }}</span>
                        </span>
                        @if($product->sale_price && $product->sale_price < $product->price)
                            <s class="text-sm text-[#8E877D] font-light font-sans" x-show="sizeVariants.length === 0">
                                ₹ {{ number_format($product->price) }}
                            </s>
                        @endif
                    </div>
                    @php
                        $avgRating = $product->average_rating ?? 5.0;
                        $fullStars = floor($avgRating);
                        $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                    @endphp
                    <a href="#customer-reviews" onclick="document.getElementById('customer-reviews')?.scrollIntoView({ behavior: 'smooth' });"
                       class="text-[10px] uppercase tracking-[0.18em] text-[#8E877D] hover:text-[#1C1917] font-light flex items-center gap-1.5 transition-colors cursor-pointer">
                        <span class="text-[#C8A96E] text-xs tracking-wider flex items-center">
                            {!! str_repeat('★', $fullStars) !!}{!! $hasHalfStar ? '★' : '' !!}{!! str_repeat('☆', $emptyStars) !!}
                        </span>
                        <span>· {{ $revCount }} {{ Str::plural('REVIEW', $revCount) }}</span>
                    </a>
                </div>


                {{-- Dynamic Size Selector (Managed via Admin Panel) --}}
                @php
                    $productSizeVariants = $product->attributes['size_variants'] ?? [];
                    $legacySizes = $product->attributes['sizes'] ?? [];
                    
                    // Normalize into simple array of size names for rendering buttons
                    $displaySizes = [];
                    if (!empty($productSizeVariants)) {
                        foreach($productSizeVariants as $sv) {
                            if(isset($sv['size'])) $displaySizes[] = $sv['size'];
                        }
                    } elseif (!empty($legacySizes)) {
                        $displaySizes = $legacySizes;
                    }
                @endphp
                
                <template x-if="sizeVariants.length > 0 || {{ count($displaySizes) > 0 ? 'true' : 'false' }}">
                    <div class="space-y-2 pb-2">
                        <p class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917]">SIZE</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            @foreach($displaySizes as $size)
                                <button type="button" @click="selectedSize = '{{ addslashes($size) }}'"
                                        :class="selectedSize === '{{ addslashes($size) }}' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#1C1917] border-[#EBE6DD] hover:border-[#1C1917]'"
                                        class="px-5 py-2 rounded-full border text-[9.5px] uppercase tracking-[0.2em] font-medium transition-all duration-200 cursor-pointer">
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </template>

                {{-- Quantity + Add to Bag + Wishlist Row (Un-nested Clean Forms) --}}
                <div class="space-y-2.5 pt-1" x-data="{ showShareModal: false }">
                    <div class="flex items-center gap-3">
                        {{-- Add to Bag Form --}}
                        <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-3 flex-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            {{-- Pass selected size only when product has size variants --}}
                            <template x-if="sizeVariants.length > 0">
                                <input type="hidden" name="options[size]" :value="selectedSize">
                            </template>

                            {{-- Qty Counter Pill --}}
                            <div class="flex items-center justify-between border border-[#DFD9CE] rounded-full px-4 py-2.5 bg-white min-w-[100px]">
                                <button type="button" @click="if(qty > 1) qty--"
                                        class="w-5 h-5 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] text-base leading-none font-light cursor-pointer">−</button>
                                <span class="text-xs font-semibold text-[#1C1917]" x-text="qty"></span>
                                <button type="button" @click="qty++"
                                        class="w-5 h-5 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] text-base leading-none font-light cursor-pointer">+</button>
                                <input type="hidden" name="quantity" :value="qty">
                            </div>

                            {{-- Add to Bag Button --}}
                            <button type="submit"
                                    class="flex-1 bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.25em] font-semibold py-3.5 rounded-full transition-all duration-300 shadow-xs cursor-pointer text-center">
                                ADD TO BAG
                            </button>
                        </form>

                        {{-- Independent Wishlist Toggle Form (Matching Screenshot 4 Styling) --}}
                        @php
                            $isWishlisted = false;
                            if (Auth::check()) {
                                $isWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists();
                            } else {
                                $isWishlisted = in_array($product->id, session('guest_wishlist', []));
                            }
                        @endphp
                        <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form inline" data-product-id="{{ $product->id }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" title="{{ $isWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                    class="wishlist-btn w-12 h-12 rounded-full transition-all cursor-pointer shrink-0 flex items-center justify-center {{ $isWishlisted ? 'bg-[#1C1917] text-white shadow-xs' : 'bg-white border border-[#DFD9CE] text-[#1C1917] hover:border-[#1C1917] shadow-2xs' }}"
                                    data-product-id="{{ $product->id }}"
                                    data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
                                    data-style-type="detail-main">
                                <svg class="wishlist-icon w-4 h-4 stroke-[1.75] transition-all duration-200 {{ $isWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    {{-- Buy Now + Share Row --}}
                    <div class="flex items-center gap-2.5">

                        {{-- BUY NOW: POST form — adds to cart + redirects to checkout atomically --}}
                        <form method="POST" action="{{ route('cart.buy-now') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" :value="qty">
                            {{-- Only send size when product has size variants --}}
                            <template x-if="sizeVariants.length > 0">
                                <input type="hidden" name="options[size]" :value="selectedSize">
                            </template>
                            <button type="submit"
                                    class="w-full text-center border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] uppercase tracking-[0.25em] font-semibold py-3 rounded-full transition-all duration-300 shadow-2xs cursor-pointer">
                                BUY NOW
                            </button>
                        </form>

                        {{-- Native Share Button / Modal Trigger --}}
                        <button type="button"
                                @click="if (navigator.share) { navigator.share({ title: '{{ $product->name }}', url: window.location.href }); } else { showShareModal = true; }"
                                class="border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] uppercase tracking-[0.25em] font-semibold py-3 px-6 rounded-full transition-all duration-300 shadow-2xs cursor-pointer">
                            SHARE
                        </button>
                    </div>

                    {{-- ── SHARE POPUP MODAL ────────────────────────── --}}
                    <div x-show="showShareModal" x-cloak
                         @keydown.escape.window="showShareModal = false"
                         class="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                        <div @click.away="showShareModal = false"
                             class="bg-white rounded-[1.75rem] p-6 max-w-sm w-full space-y-4 text-center shadow-xl">
                            <div class="flex justify-between items-center pb-2 border-b border-[#DFD9CE]/60">
                                <h3 class="text-sm font-semibold text-[#1C1917]">Share this artwork</h3>
                                <button type="button" @click="showShareModal = false" class="text-gray-400 hover:text-black text-lg">&times;</button>
                            </div>
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Check out ' + '{{ $product->name }}' + ' on Maison Résine: ' + window.location.href)"
                                   target="_blank" class="flex items-center justify-center gap-2 bg-emerald-50 text-emerald-800 text-xs font-semibold py-2.5 rounded-xl hover:bg-emerald-100 transition-colors">
                                    <span>WhatsApp</span>
                                </a>
                                <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent('{{ $product->name }}') + '&url=' + encodeURIComponent(window.location.href)"
                                   target="_blank" class="flex items-center justify-center gap-2 bg-sky-50 text-sky-800 text-xs font-semibold py-2.5 rounded-xl hover:bg-sky-100 transition-colors">
                                    <span>Twitter / X</span>
                                </a>
                                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href)"
                                   target="_blank" class="flex items-center justify-center gap-2 bg-blue-50 text-blue-800 text-xs font-semibold py-2.5 rounded-xl hover:bg-blue-100 transition-colors">
                                    <span>Facebook</span>
                                </a>
                                <button type="button" @click="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!'); showShareModal = false;"
                                        class="flex items-center justify-center gap-2 bg-gray-100 text-[#1C1917] text-xs font-semibold py-2.5 rounded-xl hover:bg-gray-200 transition-colors cursor-pointer">
                                    <span>Copy Link</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Make it Bespoke Accordion Card (Subtle Luxury Hover Effect) --}}
                <a href="{{ route('custom.index') }}"
                   class="flex items-center justify-between bg-[#F7F4EE] border border-[#DFD9CE]/60 rounded-[1.5rem] px-6 py-4 hover:border-[#8E877D]/40 hover:bg-[#F2EDE4] transition-all duration-300 group shadow-2xs">
                    <div class="space-y-0.5">
                        <p class="text-xs sm:text-[13px] font-semibold text-[#1C1917]">Make it bespoke</p>
                        <p class="text-[11px] text-[#8E877D] font-light leading-normal">Custom palette, size or engraving</p>
                    </div>
                    <span class="text-base text-[#8E877D] group-hover:text-[#1C1917] group-hover:translate-x-1 transition-all ml-4 shrink-0">→</span>
                </a>

                {{-- Atelier Trust Highlights / E-Commerce Badges (Managed globally via Admin Panel → Site Settings) --}}
                @php
                    $b1Title = \App\Models\SiteSetting::get('global_badge_1_title', 'Hand-Poured');
                    $b1Sub   = \App\Models\SiteSetting::get('global_badge_1_subtitle', '100% HANDMADE');

                    $defaultB2Title = $product->inventory_type === 'MADE_TO_ORDER' ? 'Custom Made' : 'Atelier Piece';
                    $b2Title = \App\Models\SiteSetting::get('global_badge_2_title', $defaultB2Title);
                    $b2Sub   = \App\Models\SiteSetting::get('global_badge_2_subtitle', 'ORIGINAL ART');

                    $b3Title = \App\Models\SiteSetting::get('global_badge_3_title', 'Free Express');
                    $b3Sub   = \App\Models\SiteSetting::get('global_badge_3_subtitle', 'PAN INDIA SHIP');
                @endphp
                <div class="grid grid-cols-3 gap-2.5 sm:gap-3.5 pt-1">
                    <div class="text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3.5 px-2 shadow-2xs overflow-hidden">
                        <p class="font-editorial text-base sm:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b1Title }}</p>
                        <p class="text-[7.5px] sm:text-[9px] uppercase tracking-[0.1em] text-[#8E877D] font-medium mt-1.5 truncate">{{ $b1Sub }}</p>
                    </div>
                    <div class="text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3.5 px-2 shadow-2xs overflow-hidden">
                        <p class="font-editorial text-base sm:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b2Title }}</p>
                        <p class="text-[7.5px] sm:text-[9px] uppercase tracking-[0.1em] text-[#8E877D] font-medium mt-1.5 truncate">{{ $b2Sub }}</p>
                    </div>
                    <div class="text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3.5 px-2 shadow-2xs overflow-hidden">
                        <p class="font-editorial text-base sm:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b3Title }}</p>
                        <p class="text-[7.5px] sm:text-[9px] uppercase tracking-[0.1em] text-[#8E877D] font-medium mt-1.5 truncate">{{ $b3Sub }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DETAIL TABS & POLICY SECTION ── --}}
        <div class="pt-12 mb-20" x-data="{ activeTab: 'description' }">
            <div class="flex items-center gap-3 flex-wrap pb-4 mb-8">
                <button type="button" @click="activeTab = 'description'"
                        :class="activeTab === 'description' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#78716C] border-[#DFD9CE] hover:border-[#1C1917] hover:text-[#1C1917]'"
                        class="px-5 py-2.5 rounded-full border text-[9.5px] uppercase tracking-[0.2em] font-medium transition-all duration-300 cursor-pointer shadow-2xs">
                    PRODUCT DETAILS
                </button>
                <button type="button" @click="activeTab = 'shipping'"
                        :class="activeTab === 'shipping' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#78716C] border-[#DFD9CE] hover:border-[#1C1917] hover:text-[#1C1917]'"
                        class="px-5 py-2.5 rounded-full border text-[9.5px] uppercase tracking-[0.2em] font-medium transition-all duration-300 cursor-pointer shadow-2xs">
                    SHIPPING &amp; POLICY
                </button>
            </div>

            {{-- Single Clean Full-Width Content Container --}}
            <div class="glass rounded-[2rem] border border-[#DFD9CE] p-8 sm:p-12 shadow-xs">
                <div class="space-y-6">
                    <div x-show="activeTab === 'description'">
                        <div class="artisan-rich-text">
                            {!! $product->description ?? 'A unique handcrafted fluid resin art piece. Each carries its own tide — no two are alike.' !!}
                        </div>
                    </div>

                    <style>
                        .artisan-rich-text {
                            color: #44403C;
                            font-size: 0.95rem;
                            line-height: 1.8;
                        }
                        .artisan-rich-text p {
                            margin-bottom: 1.25rem;
                        }
                        .artisan-rich-text strong, .artisan-rich-text b {
                            color: #1C1917;
                            font-weight: 600;
                        }
                        .artisan-rich-text h1, .artisan-rich-text h2, .artisan-rich-text h3, .artisan-rich-text h4 {
                            color: #1C1917;
                            font-family: 'Cormorant Garamond', Georgia, serif;
                            font-weight: 600;
                            margin-top: 1.75rem;
                            margin-bottom: 0.75rem;
                            line-height: 1.3;
                        }
                        .artisan-rich-text h1 { font-size: 1.75rem; }
                        .artisan-rich-text h2 { font-size: 1.5rem; }
                        .artisan-rich-text h3 { font-size: 1.25rem; }
                        .artisan-rich-text ul {
                            list-style-type: disc !important;
                            margin-top: 0.75rem;
                            margin-bottom: 1.25rem;
                            padding-left: 1.75rem !important;
                        }
                        .artisan-rich-text ol {
                            list-style-type: decimal !important;
                            margin-top: 0.75rem;
                            margin-bottom: 1.25rem;
                            padding-left: 1.75rem !important;
                        }
                        .artisan-rich-text li {
                            margin-bottom: 0.5rem;
                            padding-left: 0.25rem;
                            color: #57534E;
                        }
                        .artisan-rich-text blockquote {
                            border-left: 3.5px solid #C8A96E;
                            background: rgba(245, 242, 235, 0.75);
                            padding: 1rem 1.25rem;
                            margin: 1.5rem 0;
                            border-radius: 0 0.75rem 0.75rem 0;
                            font-style: italic;
                            color: #292524;
                        }
                        .artisan-rich-text a {
                            color: #9A7B4F;
                            text-decoration: underline;
                        }
                    </style>
                    <div x-show="activeTab === 'shipping'" x-cloak class="text-sm text-[#78716C] font-light leading-relaxed space-y-4">
                        @php
                            $shippingPage = \App\Models\PolicyPage::findBySlug('shipping');
                            $returnPage   = \App\Models\PolicyPage::findBySlug('return');

                            $shippingNote = \App\Models\SiteSetting::get('product_tab_shipping_note', 'Ships within 24–48 business hours.');

                            $c1Title = \App\Models\SiteSetting::get('product_tab_ship_badge_1_title', 'Free Ship');
                            $c1Sub   = \App\Models\SiteSetting::get('product_tab_ship_badge_1_subtitle', 'PAN INDIA');

                            $c2Title = \App\Models\SiteSetting::get('product_tab_ship_badge_2_title', '24–48 hrs');
                            $c2Sub   = \App\Models\SiteSetting::get('product_tab_ship_badge_2_subtitle', 'DISPATCH');

                            $c3Title = \App\Models\SiteSetting::get('product_tab_ship_badge_3_title', 'Insured');
                            $c3Sub   = \App\Models\SiteSetting::get('product_tab_ship_badge_3_subtitle', 'PACKAGING');

                            $b1 = \App\Models\SiteSetting::get('product_tab_policy_bullet_1', '3 hours to cancel after placing order');
                            $b2 = \App\Models\SiteSetting::get('product_tab_policy_bullet_2', 'No returns once dispatched (handcrafted / made-to-order)');
                            $b3 = \App\Models\SiteSetting::get('product_tab_policy_bullet_3', 'Damage claims accepted within 48 hrs of delivery');
                        @endphp

                        {{-- Shipping line --}}
                        <p class="text-[13px] text-[#524C46]">
                            {{ $shippingNote }}
                        </p>

                        {{-- Shipping highlights grid --}}
                        <div class="grid grid-cols-3 gap-2.5 pt-1">
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-3 text-center">
                                <p class="text-[11px] font-semibold text-[#1C1917]">{{ $c1Title }}</p>
                                <p class="text-[9px] uppercase tracking-[0.12em] text-[#8E877D] mt-0.5">{{ $c1Sub }}</p>
                            </div>
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-3 text-center">
                                <p class="text-[11px] font-semibold text-[#1C1917]">{{ $c2Title }}</p>
                                <p class="text-[9px] uppercase tracking-[0.12em] text-[#8E877D] mt-0.5">{{ $c2Sub }}</p>
                            </div>
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-3 text-center">
                                <p class="text-[11px] font-semibold text-[#1C1917]">{{ $c3Title }}</p>
                                <p class="text-[9px] uppercase tracking-[0.12em] text-[#8E877D] mt-0.5">{{ $c3Sub }}</p>
                            </div>
                        </div>

                        {{-- Policy summary (short bullets only) --}}
                        <div class="pt-2 space-y-2">
                            <p class="text-[9px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">Cancellation & Returns</p>
                            <ul class="space-y-1.5 text-[12px] text-[#524C46] font-light">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#B87333] mt-0.5 shrink-0">⏱</span>
                                    <span>{{ $b1 }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#8E877D] mt-0.5 shrink-0">✕</span>
                                    <span>{{ $b2 }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A7C4A] mt-0.5 shrink-0">✓</span>
                                    <span>{{ $b3 }}</span>
                                </li>
                            </ul>
                            @if($returnPage)
                            <a href="{{ route('legal.return') }}" class="inline-flex items-center gap-1 text-[10px] uppercase tracking-[0.2em] text-[#8E877D] hover:text-[#1C1917] transition-colors mt-1 font-medium">
                                Full policy →
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CUSTOMER REVIEWS (INTERLINKED & DYNAMIC) ── --}}
        <div id="customer-reviews" class="pt-14 mb-20" x-data="{ showReviewForm: false, newRating: 5, visibleReviews: 6 }">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 text-emerald-800 text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
                </div>
            @endif

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                <div>
                    <h2 class="font-editorial text-3xl sm:text-4xl text-[#1C1917] font-light mb-2">Customer Reviews.</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-[#C8A96E] text-base tracking-widest flex items-center">
                            @php
                                $avgRating = $product->average_rating ?? 5.0;
                                $fullStars = floor($avgRating);
                                $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                            @endphp
                            {!! str_repeat('★', $fullStars) !!}{!! $hasHalfStar ? '★' : '' !!}{!! str_repeat('☆', $emptyStars) !!}
                        </span>
                        <span class="text-sm font-semibold text-[#1C1917]">{{ number_format($avgRating, 1) }} out of 5</span>
                        <span class="text-xs text-[#8E877D] font-light">({{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }})</span>
                    </div>
                </div>

                @auth
                    <button type="button" @click="showReviewForm = !showReviewForm"
                            class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.2em] font-semibold px-6 py-3 rounded-full transition-all cursor-pointer shadow-xs self-start md:self-auto">
                        <span x-text="showReviewForm ? 'Cancel Review' : '★ Write a Review'"></span>
                    </button>
                @else
                    <a href="{{ route('login', ['redirect' => request()->url()]) }}"
                       class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.2em] font-semibold px-6 py-3 rounded-full transition-all cursor-pointer shadow-xs self-start md:self-auto">
                        ★ Write a Review
                    </a>
                @endauth
            </div>

            {{-- Write Review Form (Glass Card Styling matching Auth & About pages) --}}
            <div x-show="showReviewForm" x-cloak
                 class="glass rounded-[2rem] p-6 sm:p-8 mb-12 border border-[#DFD9CE] shadow-sm transition-all duration-300">
                <h3 class="font-editorial text-xl font-light text-[#1C1917] mb-1">Write your review</h3>
                <p class="text-xs text-[#8E877D] font-light mb-6">Share your experience with this bespoke atelier piece.</p>

                <form method="POST" action="{{ route('reviews.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="rating" :value="newRating">

                    {{-- Star Picker --}}
                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1.5">Rating *</label>
                        <div class="flex items-center gap-2">
                            <template x-for="star in [1, 2, 3, 4, 5]">
                                <button type="button" @click="newRating = star"
                                        class="text-2xl transition-transform hover:scale-125 focus:outline-none cursor-pointer"
                                        :class="star <= newRating ? 'text-[#C8A96E]' : 'text-[#DFD9CE]'">
                                    ★
                                </button>
                            </template>
                            <span class="text-xs font-medium text-[#78716C] ml-2" x-text="newRating + ' / 5 Stars'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Your Name *</label>
                            <input type="text" name="reviewer_name" required value="{{ Auth::check() ? Auth::user()->name : '' }}" placeholder="Enter your name"
                                   class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] px-5 py-3 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Review Title (Optional)</label>
                            <input type="text" name="title" placeholder="e.g. Breathtaking finish!"
                                   class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] px-5 py-3 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Your Review *</label>
                        <textarea name="comment" rows="3" required placeholder="Write your thoughts about this piece..."
                                  class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] p-4 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all"></textarea>
                    </div>

                    <button type="submit"
                            class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.25em] font-semibold px-8 py-3.5 rounded-full transition-all cursor-pointer shadow-xs">
                        Submit Review
                    </button>
                </form>
            </div>

            {{-- Reviews Grid (Glass Card Styling matching Login & About pages) --}}
            @if($product->reviews && $product->reviews->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($product->reviews as $index => $review)
                        <div class="glass rounded-[1.75rem] border border-[#DFD9CE] p-6 space-y-4 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                             x-show="{{ $index }} < visibleReviews" x-cloak>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-[#C8A96E] text-xs tracking-widest">
                                        {{ str_repeat('★', $review->rating) }}
                                    </div>
                                </div>

                                @if($review->title)
                                    <p class="font-editorial text-lg text-[#1C1917] font-normal leading-snug">{{ $review->title }}</p>
                                @endif

                                <p class="text-[12.5px] text-[#78716C] font-light leading-relaxed italic">"{{ $review->comment }}"</p>
                            </div>

                            <div class="pt-3 border-t border-[#DFD9CE]/60 flex items-center justify-between">
                                <span class="text-[9.5px] uppercase tracking-[0.18em] text-[#1C1917] font-semibold">
                                    {{ strtoupper($review->reviewer_name) }}
                                </span>
                                <span class="text-[9px] uppercase tracking-[0.15em] text-[#8E877D] font-light">
                                    {{ $review->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Load More / Show Less Button --}}
                @if($product->reviews->count() > 6)
                    <div class="text-center pt-8">
                        <button type="button" @click="visibleReviews = visibleReviews >= {{ $product->reviews->count() }} ? 6 : {{ $product->reviews->count() }}"
                                class="bg-white border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[10px] uppercase tracking-[0.25em] font-semibold px-8 py-3.5 rounded-full transition-all cursor-pointer shadow-2xs">
                            <span x-text="visibleReviews >= {{ $product->reviews->count() }} ? 'SHOW LESS REVIEWS' : 'LOAD MORE REVIEWS ({{ $product->reviews->count() - 6 }} MORE)'"></span>
                        </button>
                    </div>
                @endif
            @else
                <div class="glass rounded-[1.75rem] border border-[#DFD9CE] p-8 text-center space-y-2">
                    <p class="font-editorial text-xl italic text-[#1C1917]">No reviews yet.</p>
                    <p class="text-xs text-[#8E877D] font-light">Be the first collector to share your experience with this piece.</p>
                </div>
            @endif
        </div>

        {{-- ── YOU MAY ALSO LOVE ────────────────────────────────── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="pt-14 mb-20">
                <h2 class="font-editorial text-3xl sm:text-4xl text-[#1C1917] font-light mb-8">You may also love.</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts->take(4) as $rel)
                        <div class="group">
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-3 group/card">
                                <a href="{{ route('shop.show', $rel->slug) }}" class="block w-full h-full">
                                    @if(!empty($rel->images) && isset($rel->images[0]))
                                        <img src="{{ $rel->images[0] }}" alt="{{ $rel->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    @endif
                                </a>

                                @if($rel->is_bestseller) <span class="glass-pill absolute top-3 left-3 text-[#1C1917] text-[8.5px] uppercase tracking-[0.18em] font-medium px-3 py-1 rounded-full pointer-events-none">BESTSELLER</span> @endif
                                @if($rel->is_featured) <span class="glass-pill absolute top-3 left-3 text-[#1C1917] text-[8.5px] uppercase tracking-[0.18em] font-medium px-3 py-1 rounded-full pointer-events-none">FEATURED</span> @endif
                                @if($rel->is_new) <span class="glass-pill absolute top-3 left-3 text-[#1C1917] text-[8.5px] uppercase tracking-[0.18em] font-medium px-3 py-1 rounded-full pointer-events-none">NEW</span> @endif

                                {{-- Action Buttons (Hover Animated with Glass Border) --}}
                                @php
                                    $relWishlisted = false;
                                    if (Auth::check()) {
                                        $relWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())->where('product_id', $rel->id)->exists();
                                    } else {
                                        $relWishlisted = in_array($rel->id, session('guest_wishlist', []));
                                    }
                                @endphp
                                <div class="absolute top-3 right-3 flex flex-col space-y-1.5 z-20 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300 ease-out">
                                    <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $rel->id }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                        <button type="submit" title="{{ $relWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                                class="wishlist-btn w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs {{ $relWishlisted ? 'bg-[#1C1917] text-white' : 'glass-pill border border-white/70 text-[#1C1917] hover:bg-[#1C1917] hover:text-white' }}"
                                                data-product-id="{{ $rel->id }}"
                                                data-wishlisted="{{ $relWishlisted ? 'true' : 'false' }}"
                                                data-style-type="related-item">
                                            <svg class="wishlist-icon w-3.5 h-3.5 stroke-[1.75] transition-all duration-200 {{ $relWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" title="Add to Bag" class="glass-pill border border-white/70 w-8 h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-2 px-1">
                                <div class="min-w-0">
                                    <h3 class="font-editorial text-[1.1rem] text-[#1C1917] font-normal leading-snug">
                                        <a href="{{ route('shop.show', $rel->slug) }}" class="hover:text-[#8E877D] transition-colors">{{ $rel->name }}</a>
                                    </h3>
                                    <p class="text-[10px] uppercase tracking-[0.12em] text-[#8E877D] font-medium mt-0.5 truncate">{{ $rel->category?->name ?? 'Handcrafted Art' }}</p>
                                </div>
                                <span class="text-[12.5px] font-semibold text-[#1C1917] whitespace-nowrap shrink-0 pt-0.5">₹ {{ number_format($rel->effective_price) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── RECENTLY VIEWED ──────────────────────────────────── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 2)
            <div class="pt-14 mb-16">
                <h2 class="font-editorial text-3xl sm:text-4xl text-[#1C1917] font-light mb-8">Recently viewed.</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($relatedProducts->take(3) as $recent)
                        <div class="group">
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-3 group/card">
                                <a href="{{ route('shop.show', $recent->slug) }}" class="block w-full h-full">
                                    @if(!empty($recent->images) && isset($recent->images[0]))
                                        <img src="{{ $recent->images[0] }}" alt="{{ $recent->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    @endif
                                </a>

                                @if($recent->is_bestseller) <span class="glass-pill absolute top-3 left-3 text-[#1C1917] text-[8.5px] uppercase tracking-[0.18em] font-medium px-3 py-1 rounded-full pointer-events-none">BESTSELLER</span> @endif
                                @if($recent->is_featured) <span class="glass-pill absolute top-3 left-3 text-[#1C1917] text-[8.5px] uppercase tracking-[0.18em] font-medium px-3 py-1 rounded-full pointer-events-none">FEATURED</span> @endif
                                @if($recent->is_new) <span class="glass-pill absolute top-3 left-3 text-[#1C1917] text-[8.5px] uppercase tracking-[0.18em] font-medium px-3 py-1 rounded-full pointer-events-none">NEW</span> @endif

                                {{-- Action Buttons --}}
                                <div class="absolute top-3 right-3 flex flex-col space-y-1.5 z-20 opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300 ease-out">
                                    <form method="POST" action="{{ route('wishlist.toggle') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $recent->id }}">
                                        <button type="submit" title="Add to Wishlist" class="glass-pill w-8 h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $recent->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" title="Add to Bag" class="glass-pill w-8 h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-2 px-1">
                                <div class="min-w-0">
                                    <h3 class="font-editorial text-[1.1rem] text-[#1C1917] font-normal leading-snug">
                                        <a href="{{ route('shop.show', $recent->slug) }}" class="hover:text-[#8E877D] transition-colors">{{ $recent->name }}</a>
                                    </h3>
                                    <p class="text-[10px] uppercase tracking-[0.12em] text-[#8E877D] font-medium mt-0.5 truncate">{{ $recent->category?->name ?? 'Handcrafted Art' }}</p>
                                </div>
                                <span class="text-[12.5px] font-semibold text-[#1C1917] whitespace-nowrap shrink-0 pt-0.5">₹ {{ number_format($recent->effective_price) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

</x-app-layout>
