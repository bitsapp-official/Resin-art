<x-app-layout :title="$product->name . ' — Maison Résine Atelier'">
<div class="min-h-screen bg-transparent w-full min-w-0">
    <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 pt-6 sm:pt-8 pb-16 sm:pb-24 w-full min-w-0"
         x-data="{ 
            activeImage: 0, 
            selectedSize: '{{ !empty($product->attributes['size_variants']) ? ($product->attributes['size_variants'][0]['size'] ?? '') : (!empty($product->attributes['sizes']) ? $product->attributes['sizes'][0] : '') }}', 
            qty: 1, 
            showLightbox: false, 
            productImages: {{ json_encode($product->images ?? []) }},
            sizeVariants: {{ json_encode($product->attributes['size_variants'] ?? []) }},
            basePrice: {{ (float) $product->effective_price }},
            
            // Lightbox cut-free zoom & pan
            lightboxZoom: 1,
            lightboxPanX: 0,
            lightboxPanY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            
            init() {
                window.addEventListener('touchmove', (e) => {
                    if (this.showLightbox && (this.lightboxZoom <= 1 || !e.target.closest('[x-ref="lightboxImg"]'))) {
                        e.preventDefault();
                    }
                }, { passive: false });
            },
            
            openLightbox() {
                this.showLightbox = true;
                this.resetLightboxZoom();
                document.documentElement.classList.add('overflow-hidden', 'touch-none');
                document.body.classList.add('overflow-hidden', 'touch-none');
                document.documentElement.style.overflow = 'hidden';
                document.documentElement.style.overscrollBehavior = 'none';
                document.body.style.overflow = 'hidden';
                document.body.style.overscrollBehavior = 'none';
                document.body.style.touchAction = 'none';
            },
            closeLightbox() {
                this.showLightbox = false;
                this.resetLightboxZoom();
                document.documentElement.classList.remove('overflow-hidden', 'touch-none');
                document.body.classList.remove('overflow-hidden', 'touch-none');
                document.documentElement.style.overflow = '';
                document.documentElement.style.overscrollBehavior = '';
                document.body.style.overflow = '';
                document.body.style.overscrollBehavior = '';
                document.body.style.touchAction = '';
            },
            resetLightboxZoom() {
                this.lightboxZoom = 1;
                this.lightboxPanX = 0;
                this.lightboxPanY = 0;
                this.isDragging = false;
            },
            zoomIn() {
                if (this.lightboxZoom < 3) {
                    this.lightboxZoom = +(this.lightboxZoom + 0.5).toFixed(1);
                }
            },
            zoomOut() {
                if (this.lightboxZoom > 1) {
                    this.lightboxZoom = +(this.lightboxZoom - 0.5).toFixed(1);
                    if (this.lightboxZoom <= 1) {
                        this.resetLightboxZoom();
                    }
                }
            },
            toggleLightboxZoom() {
                if (this.lightboxZoom === 1) {
                    this.lightboxZoom = 2;
                } else {
                    this.resetLightboxZoom();
                }
            },
            handleWheel(e) {
                if (!this.showLightbox) return;
                e.preventDefault();
                if (e.deltaY < 0) {
                    this.zoomIn();
                } else {
                    this.zoomOut();
                }
            },
            startDrag(e) {
                if (this.lightboxZoom <= 1) return;
                this.isDragging = true;
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                this.dragStartX = clientX - this.lightboxPanX;
                this.dragStartY = clientY - this.lightboxPanY;
            },
            onDrag(e) {
                if (!this.isDragging || this.lightboxZoom <= 1) return;
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                const img = this.$refs.lightboxImg;
                const w = img ? img.offsetWidth : 600;
                const h = img ? img.offsetHeight : 600;
                // Generous pan limits so every edge and corner is easily reachable without cutting off
                const maxPanX = Math.max(120, ((w * this.lightboxZoom) - w) / 2 + 100);
                const maxPanY = Math.max(120, ((h * this.lightboxZoom) - h) / 2 + 100);
                const newX = clientX - this.dragStartX;
                const newY = clientY - this.dragStartY;
                this.lightboxPanX = Math.max(-maxPanX, Math.min(maxPanX, newX));
                this.lightboxPanY = Math.max(-maxPanY, Math.min(maxPanY, newY));
            },
            stopDrag() {
                this.isDragging = false;
            },
            
            get currentPrice() {
                if (this.sizeVariants.length > 0) {
                    const variant = this.sizeVariants.find(v => v.size === this.selectedSize);
                    return variant ? variant.price : this.basePrice;
                }
                return this.basePrice;
            },
            formatPrice(price) {
                return new Intl.NumberFormat('en-IN').format(price);
            },
            nextImage() {
                if (this.productImages.length > 0) {
                    this.activeImage = (this.activeImage + 1) % this.productImages.length;
                    this.resetLightboxZoom();
                }
            },
            prevImage() {
                if (this.productImages.length > 0) {
                    this.activeImage = (this.activeImage - 1 + this.productImages.length) % this.productImages.length;
                    this.resetLightboxZoom();
                }
            },
            selectImage(idx) {
                this.activeImage = idx;
                this.resetLightboxZoom();
            }
         }">

        {{-- ── FULL-SCREEN LIGHTBOX MODAL WITH CUT-FREE ZOOM & PAN ── --}}
        <div x-show="showLightbox" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="closeLightbox()"
             @keydown.arrow-left.window="prevImage()"
             @keydown.arrow-right.window="nextImage()"
             @keydown.equal.window="zoomIn()"
             @keydown.minus.window="zoomOut()"
             @keydown.digit0.window="resetLightboxZoom()"
             class="fixed inset-0 z-[99999] bg-[#FAF8F5]/96 backdrop-blur-2xl flex flex-col justify-between p-3 sm:p-6 select-none overflow-hidden overscroll-none touch-none"
             @wheel.prevent="handleWheel($event)"
             @touchmove="if (lightboxZoom <= 1 || !$event.target.closest('[x-ref=\'lightboxImg\']')) $event.preventDefault()">
            
            {{-- TOP BAR: Close Button (Right) --}}
            <div class="w-full flex items-center justify-end z-50 shrink-0 min-h-[44px]">
                {{-- Right: Close Button (✕) --}}
                <button type="button" @click.stop="closeLightbox()"
                        class="text-[#1C1917] bg-white hover:bg-[#1C1917] hover:text-white border border-[#DFD9CE] w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-sm shrink-0"
                        aria-label="Close lightbox">
                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- MIDDLE CANVAS: Zoomable & Draggable Image (100% Fit at 1x, Zero Cutting on Zoom) --}}
            <div class="relative flex-1 w-full flex items-center justify-center overflow-hidden my-auto"
                 @click.self="closeLightbox()">

                {{-- Previous Arrow --}}
                <template x-if="productImages.length > 1">
                    <button type="button" @click.stop="prevImage()"
                            class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 text-[#1C1917] bg-white/95 hover:bg-[#1C1917] hover:text-white border border-[#DFD9CE] w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-md z-40"
                            aria-label="Previous image">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                </template>

                {{-- Next Arrow --}}
                <template x-if="productImages.length > 1">
                    <button type="button" @click.stop="nextImage()"
                            class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 text-[#1C1917] bg-white/95 hover:bg-[#1C1917] hover:text-white border border-[#DFD9CE] w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center transition-all cursor-pointer shadow-md z-40"
                            aria-label="Next image">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>

                {{-- Image Container --}}
                <div class="relative flex items-center justify-center select-none"
                     @mousedown="startDrag($event)"
                     @mousemove="onDrag($event)"
                     @mouseup="stopDrag()"
                     @mouseleave="stopDrag()"
                     @touchstart="startDrag($event)"
                     @touchmove="onDrag($event)"
                     @touchend="stopDrag()"
                     @dblclick.stop="toggleLightboxZoom()">
                    <img x-ref="lightboxImg"
                         :src="productImages[activeImage]"
                         alt="{{ $product->name }}"
                         draggable="false"
                         class="max-w-[86vw] max-h-[66vh] sm:max-h-[70vh] object-contain rounded-2xl shadow-xl border border-white/60 will-change-transform select-none"
                         :style="'transform: translate(' + lightboxPanX + 'px, ' + lightboxPanY + 'px) scale(' + lightboxZoom + '); transform-origin: center center; transition: ' + (isDragging ? 'none' : 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)') + '; cursor: ' + (lightboxZoom > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in') + ';'">
                </div>
            </div>

            {{-- Lightbox Thumbnails Navigation --}}
            <template x-if="productImages.length > 1">
                <div @click.stop="" class="flex items-center justify-center gap-2 sm:gap-3 pt-2 pb-1 z-50 cursor-default max-w-full overflow-x-auto no-scrollbar px-4 shrink-0">
                    <template x-for="(img, idx) in productImages" :key="idx">
                        <button type="button" @click="selectImage(idx)"
                                class="shrink-0 w-11 h-11 sm:w-13 sm:h-13 rounded-xl overflow-hidden border-2 transition-all cursor-pointer p-0.5 bg-white"
                                :class="activeImage === idx ? 'border-[#1C1917] ring-2 ring-[#1C1917]/25 scale-110 shadow-md opacity-100' : 'border-[#DFD9CE] opacity-60 hover:opacity-100'">
                            <img :src="img" class="w-full h-full object-cover rounded-lg">
                        </button>
                    </template>
                </div>
            </template>
        </div>

        {{-- ── BREADCRUMB ──────────────────────────────────────── --}}
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-[10px] uppercase tracking-[0.16em] sm:tracking-[0.2em] text-[#8E877D] mb-6 sm:mb-8 font-medium">
            <a href="{{ url('/') }}" class="hover:text-[#1C1917] transition-colors shrink-0">Home</a>
            <span>·</span>
            <a href="{{ route('shop.index') }}" class="hover:text-[#1C1917] transition-colors shrink-0">Shop</a>
            @if($product->category)
            <span>·</span>
            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-[#1C1917] transition-colors shrink-0">{{ $product->category->name }}</a>
            @endif
            <span>·</span>
            <span class="text-[#1C1917] font-semibold truncate max-w-[180px] sm:max-w-none">{{ $product->name }}</span>
        </nav>

        {{-- ── MAIN PRODUCT LAYOUT ─────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-12 lg:gap-16 xl:gap-20 mb-16 sm:mb-20">

            {{-- LEFT: Gallery --}}
            <div class="space-y-3.5 sm:space-y-4">
                {{-- Main Gallery Image (Clean Steady Display - No Zoom Glitch + Click to Open Lightbox) --}}
                <div class="relative overflow-hidden aspect-square rounded-[1.75rem] sm:rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs cursor-pointer select-none"
                     @click="openLightbox()">

                    @if(!empty($product->images))
                        @foreach($product->images as $index => $img)
                            <img x-show="activeImage === {{ $index }}"
                                 src="{{ $img }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover absolute inset-0 transition-opacity duration-300"
                                 :class="activeImage === {{ $index }} ? 'opacity-100' : 'opacity-0'"
                                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                        @endforeach
                    @else
                        <div class="w-full h-full flex items-center justify-center text-[#8E877D] text-sm">
                            No image available
                        </div>
                    @endif

                    {{-- Navigation Arrows (Visible on mobile, hover-revealed on desktop) --}}
                    @if(!empty($product->images) && count($product->images) > 1)
                        <button type="button" @click.stop="prevImage()"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/85 backdrop-blur-md border border-[#DFD9CE]/80 text-[#1C1917] flex items-center justify-center shadow-sm opacity-90 sm:opacity-0 hover:!opacity-100 transition-all duration-200 cursor-pointer hover:bg-white z-10"
                                aria-label="Previous image">
                            <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" @click.stop="nextImage()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/85 backdrop-blur-md border border-[#DFD9CE]/80 text-[#1C1917] flex items-center justify-center shadow-sm opacity-90 sm:opacity-0 hover:!opacity-100 transition-all duration-200 cursor-pointer hover:bg-white z-10"
                                aria-label="Next image">
                            <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- Dot Indicators for mobile --}}
                        <div class="absolute bottom-3.5 left-1/2 -translate-x-1/2 flex items-center gap-1.5 sm:hidden z-10 pointer-events-none">
                            @foreach($product->images as $index => $img)
                                <span class="h-1.5 rounded-full transition-all duration-300"
                                      :class="activeImage === {{ $index }} ? 'bg-[#1C1917] w-4' : 'bg-[#1C1917]/35 w-1.5'"></span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Thumbnails: Horizontal scroll on mobile, flex row on desktop with instant hover & click selection --}}
                @if(!empty($product->images) && count($product->images) > 1)
                    <div class="flex items-center gap-2.5 sm:gap-3 overflow-x-auto no-scrollbar py-1 px-0.5">
                        @foreach($product->images as $index => $img)
                            <button type="button" 
                                    @click="selectImage({{ $index }})"
                                    class="shrink-0 w-16 h-16 sm:w-20 sm:h-20 aspect-square overflow-hidden rounded-xl sm:rounded-2xl bg-[#F5F2EB] border-2 transition-all duration-200 cursor-pointer relative p-0.5"
                                    :class="activeImage === {{ $index }} ? 'border-[#1C1917] ring-2 ring-[#1C1917]/25 scale-105 opacity-100 shadow-xs' : 'border-[#DFD9CE]/70 opacity-60 hover:opacity-100 hover:border-[#1C1917]/50'">
                                <img src="{{ $img }}" alt="{{ $product->name }} thumbnail {{ $index + 1 }}" class="w-full h-full object-cover rounded-lg sm:rounded-xl transition-transform duration-300">
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
                    <h1 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light leading-[1.12] sm:leading-[1.08] tracking-tight">
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
                <div class="flex items-baseline flex-wrap gap-x-4 gap-y-2 pt-1">
                    <div class="flex items-baseline gap-2.5 sm:gap-3">
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
                       class="text-[10px] uppercase tracking-[0.16em] sm:tracking-[0.18em] text-[#8E877D] hover:text-[#1C1917] font-light flex items-center gap-1.5 transition-colors cursor-pointer">
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
                                        class="px-4 sm:px-5 py-2 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-200 cursor-pointer">
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </template>

                {{-- Quantity + Add to Bag + Wishlist Row (Un-nested Clean Forms) --}}
                <div class="space-y-2.5 pt-1" x-data="{ showShareModal: false, copied: false }">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        {{-- Add to Bag Form --}}
                        <form method="POST" action="{{ route('cart.add') }}" class="flex items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            {{-- Pass selected size only when product has size variants --}}
                            <template x-if="sizeVariants.length > 0">
                                <input type="hidden" name="options[size]" :value="selectedSize">
                            </template>

                            {{-- Qty Counter Pill --}}
                            <div class="flex items-center justify-between border border-[#DFD9CE] rounded-full px-3 sm:px-4 bg-white w-24 sm:w-28 h-12 shrink-0">
                                <button type="button" @click="if(qty > 1) qty--"
                                        class="w-6 h-6 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] text-base leading-none font-light cursor-pointer select-none">−</button>
                                <span class="text-xs font-semibold text-[#1C1917]" x-text="qty"></span>
                                <button type="button" @click="qty++"
                                        class="w-6 h-6 flex items-center justify-center text-[#78716C] hover:text-[#1C1917] text-base leading-none font-light cursor-pointer select-none">+</button>
                                <input type="hidden" name="quantity" :value="qty">
                            </div>

                            {{-- Add to Bag Button --}}
                            <button type="submit"
                                    class="flex-1 h-12 bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] sm:text-[11px] uppercase tracking-[0.16em] sm:tracking-[0.25em] font-semibold rounded-full transition-all duration-300 shadow-xs cursor-pointer flex items-center justify-center text-center px-2">
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
                        <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form inline shrink-0" data-product-id="{{ $product->id }}">
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
                        <form method="POST" action="{{ route('cart.buy-now') }}" class="flex-1 min-w-0">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" :value="qty">
                            {{-- Only send size when product has size variants --}}
                            <template x-if="sizeVariants.length > 0">
                                <input type="hidden" name="options[size]" :value="selectedSize">
                            </template>
                            <button type="submit"
                                    class="w-full h-12 flex items-center justify-center text-center border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] sm:text-[11px] uppercase tracking-[0.16em] sm:tracking-[0.25em] font-semibold rounded-full transition-all duration-300 shadow-2xs cursor-pointer">
                                BUY NOW
                            </button>
                        </form>

                        {{-- Native Share Button / Modal Trigger --}}
                        <button type="button"
                                @click="
                                    const sharePayload = {
                                        title: {{ Js::from($product->name) }},
                                        text: {{ Js::from('Discover ' . $product->name . ' — handcrafted resin art at Maison Résine.') }},
                                        url: window.location.href
                                    };
                                    if (navigator.share) {
                                        navigator.share(sharePayload).catch(err => {
                                            if (err.name !== 'AbortError') {
                                                showShareModal = true;
                                            }
                                        });
                                    } else {
                                        showShareModal = true;
                                    }
                                "
                                class="h-12 shrink-0 px-5 sm:px-6 flex items-center justify-center border border-[#DFD9CE] hover:border-[#1C1917] bg-white text-[#1C1917] text-[10px] sm:text-[11px] uppercase tracking-[0.16em] sm:tracking-[0.25em] font-semibold rounded-full transition-all duration-300 shadow-2xs cursor-pointer">
                            SHARE
                        </button>
                    </div>

                    {{-- ── SHARE POPUP MODAL (Clean Atelier Luxury Design) ────────────────────────── --}}
                    <div x-show="showShareModal" x-cloak
                         @keydown.escape.window="showShareModal = false"
                         class="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
                        <div @click.away="showShareModal = false"
                             class="bg-[#FAF8F5] border border-[#E6E1D7] rounded-[2rem] p-6 sm:p-7 max-w-sm w-full space-y-4 shadow-2xl text-left">
                            
                            {{-- Modal Header --}}
                            <div class="flex items-center justify-between pb-3 border-b border-[#E6E1D7]">
                                <div>
                                    <span class="text-[9px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] block">SHARE PIECE</span>
                                    <h3 class="font-editorial text-xl text-[#1C1917] font-normal mt-0.5">Share this <em class="italic">artwork</em></h3>
                                </div>
                                <button type="button" @click="showShareModal = false" 
                                        class="w-7 h-7 rounded-full border border-[#DFD9CE] flex items-center justify-center text-[#78716C] hover:text-[#1C1917] hover:border-[#1C1917] transition-colors text-sm font-light cursor-pointer">
                                    &times;
                                </button>
                            </div>

                            {{-- Social Share Options --}}
                            <div class="grid grid-cols-2 gap-2.5 pt-1">
                                <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('Check out ' + {{ Js::from($product->name) }} + ' on Maison Résine: ' + window.location.href)"
                                   target="_blank" 
                                   class="flex items-center justify-center gap-2 bg-white hover:bg-[#F5F2EB] border border-[#DFD9CE] text-[#1C1917] text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs">
                                    <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.83.81 2.805.81h.001c3.182 0 5.768-2.587 5.769-5.766.001-3.182-2.585-5.796-5.77-5.796zm0 10.427c-.881 0-1.683-.243-2.378-.669l-.17-.104-1.58.414.422-1.54-.112-.178c-.469-.747-.717-1.464-.716-2.584.001-2.433 1.979-4.411 4.414-4.411 2.435 0 4.413 1.979 4.413 4.412 0 2.434-1.977 4.66-4.413 4.66z"/></svg>
                                    <span>WhatsApp</span>
                                </a>

                                <a :href="'https://twitter.com/intent/tweet?text=' + encodeURIComponent({{ Js::from($product->name) }}) + '&url=' + encodeURIComponent(window.location.href)"
                                   target="_blank" 
                                   class="flex items-center justify-center gap-2 bg-white hover:bg-[#F5F2EB] border border-[#DFD9CE] text-[#1C1917] text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-[#1C1917]" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    <span>Twitter / X</span>
                                </a>

                                <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href)"
                                   target="_blank" 
                                   class="flex items-center justify-center gap-2 bg-white hover:bg-[#F5F2EB] border border-[#DFD9CE] text-[#1C1917] text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs">
                                    <svg class="w-4 h-4 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    <span>Facebook</span>
                                </a>

                                <button type="button" 
                                        @click="
                                            navigator.clipboard.writeText(window.location.href);
                                            copied = true;
                                            setTimeout(() => { copied = false; showShareModal = false; }, 1400);
                                        "
                                        class="flex items-center justify-center gap-2 bg-[#1A1615] hover:bg-[#2C2724] text-white text-xs font-semibold py-3 rounded-xl transition-all shadow-2xs cursor-pointer">
                                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Make it Bespoke Accordion Card (Subtle Luxury Hover Effect) --}}
                <a href="{{ route('custom.index') }}"
                   class="flex items-center justify-between bg-[#F7F4EE] border border-[#DFD9CE]/60 rounded-[1.5rem] px-5 sm:px-6 py-3.5 sm:py-4 hover:border-[#8E877D]/40 hover:bg-[#F2EDE4] transition-all duration-300 group shadow-2xs">
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
                <div class="grid grid-cols-3 gap-2 sm:gap-3.5 pt-1">
                    <div class="text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3 px-1.5 sm:py-3.5 sm:px-2 shadow-2xs overflow-hidden">
                        <p class="font-editorial text-sm sm:text-base lg:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b1Title }}</p>
                        <p class="text-[7px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.1em] text-[#8E877D] font-medium mt-1.5 truncate">{{ $b1Sub }}</p>
                    </div>
                    <div class="text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3 px-1.5 sm:py-3.5 sm:px-2 shadow-2xs overflow-hidden">
                        <p class="font-editorial text-sm sm:text-base lg:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b2Title }}</p>
                        <p class="text-[7px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.1em] text-[#8E877D] font-medium mt-1.5 truncate">{{ $b2Sub }}</p>
                    </div>
                    <div class="text-center glass border border-[#DFD9CE]/80 rounded-[1.25rem] sm:rounded-[1.5rem] py-3 px-1.5 sm:py-3.5 sm:px-2 shadow-2xs overflow-hidden">
                        <p class="font-editorial text-sm sm:text-base lg:text-lg font-normal text-[#1C1917] leading-none truncate">{{ $b3Title }}</p>
                        <p class="text-[7px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.1em] text-[#8E877D] font-medium mt-1.5 truncate">{{ $b3Sub }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DETAIL TABS & POLICY SECTION ── --}}
        <div class="pt-8 sm:pt-12 mb-16 sm:mb-20" x-data="{ activeTab: 'description' }">
            <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap pb-2 sm:pb-4 mb-6 sm:mb-8">
                <button type="button" @click="activeTab = 'description'"
                        :class="activeTab === 'description' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#78716C] border-[#DFD9CE] hover:border-[#1C1917] hover:text-[#1C1917]'"
                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-300 cursor-pointer shadow-2xs">
                    PRODUCT DETAILS
                </button>
                <button type="button" @click="activeTab = 'shipping'"
                        :class="activeTab === 'shipping' ? 'bg-[#1C1917] text-white border-[#1C1917]' : 'bg-transparent text-[#78716C] border-[#DFD9CE] hover:border-[#1C1917] hover:text-[#1C1917]'"
                        class="px-4 sm:px-5 py-2 sm:py-2.5 rounded-full border text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-medium transition-all duration-300 cursor-pointer shadow-2xs">
                    SHIPPING &amp; POLICY
                </button>
            </div>

            {{-- Single Clean Full-Width Content Container --}}
            <div class="glass rounded-[1.5rem] sm:rounded-[2rem] border border-[#DFD9CE] p-4 sm:p-8 lg:p-12 shadow-xs">
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
                        <div class="grid grid-cols-3 gap-2 sm:gap-2.5 pt-1">
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-2 sm:p-3 text-center overflow-hidden">
                                <p class="text-[10px] sm:text-[11px] font-semibold text-[#1C1917] truncate">{{ $c1Title }}</p>
                                <p class="text-[8px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.12em] text-[#8E877D] mt-0.5 truncate">{{ $c1Sub }}</p>
                            </div>
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-2 sm:p-3 text-center overflow-hidden">
                                <p class="text-[10px] sm:text-[11px] font-semibold text-[#1C1917] truncate">{{ $c2Title }}</p>
                                <p class="text-[8px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.12em] text-[#8E877D] mt-0.5 truncate">{{ $c2Sub }}</p>
                            </div>
                            <div class="bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl p-2 sm:p-3 text-center overflow-hidden">
                                <p class="text-[10px] sm:text-[11px] font-semibold text-[#1C1917] truncate">{{ $c3Title }}</p>
                                <p class="text-[8px] sm:text-[9px] uppercase tracking-[0.08em] sm:tracking-[0.12em] text-[#8E877D] mt-0.5 truncate">{{ $c3Sub }}</p>
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
        <div id="customer-reviews" class="pt-10 sm:pt-14 mb-16 sm:mb-20" x-data="{ showReviewForm: false, newRating: 5, visibleReviews: 6 }">
            @if(session('success'))
                <div class="mb-6">
                    <x-alert type="success" :message="session('success')" />
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6 mb-8 sm:mb-10">
                <div>
                    <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light mb-2">Customer Reviews.</h2>
                    <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                        <span class="text-[#C8A96E] text-sm sm:text-base tracking-widest flex items-center">
                            @php
                                $avgRating = $product->average_rating ?? 5.0;
                                $fullStars = floor($avgRating);
                                $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                            @endphp
                            {!! str_repeat('★', $fullStars) !!}{!! $hasHalfStar ? '★' : '' !!}{!! str_repeat('☆', $emptyStars) !!}
                        </span>
                        <span class="text-xs sm:text-sm font-semibold text-[#1C1917]">{{ number_format($avgRating, 1) }} out of 5</span>
                        <span class="text-xs text-[#8E877D] font-light">({{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }})</span>
                    </div>
                </div>

                @auth
                    <button type="button" @click="showReviewForm = !showReviewForm"
                            class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-semibold px-5 sm:px-6 py-2.5 sm:py-3 rounded-full transition-all cursor-pointer shadow-xs self-start sm:self-auto">
                        <span x-text="showReviewForm ? 'Cancel Review' : '★ Write a Review'"></span>
                    </button>
                @else
                    <a href="{{ route('login', ['redirect' => request()->url()]) }}"
                       class="bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.18em] sm:tracking-[0.2em] font-semibold px-5 sm:px-6 py-2.5 sm:py-3 rounded-full transition-all cursor-pointer shadow-xs self-start sm:self-auto">
                        ★ Write a Review
                    </a>
                @endauth
            </div>

            {{-- Write Review Form (Glass Card Styling matching Auth & About pages) --}}
            <div x-show="showReviewForm" x-cloak
                 class="glass rounded-[1.5rem] sm:rounded-[2rem] p-4 sm:p-6 lg:p-8 mb-10 sm:mb-12 border border-[#DFD9CE] shadow-sm transition-all duration-300">
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
                                   class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] px-4 sm:px-5 py-3 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all">
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] mb-1">Review Title (Optional)</label>
                            <input type="text" name="title" placeholder="e.g. Breathtaking finish!"
                                   class="w-full bg-white/80 border border-[#DFD9CE] rounded-[1.125rem] px-4 sm:px-5 py-3 text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] transition-all">
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

            {{-- Reviews Grid (Responsive 1 col mobile, 2 col tablet, 3 col desktop) --}}
            @if($product->reviews && $product->reviews->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($product->reviews as $index => $review)
                        <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] border border-[#DFD9CE] p-4 sm:p-6 space-y-3.5 sm:space-y-4 shadow-xs hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between"
                             x-show="{{ $index }} < visibleReviews" x-cloak>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="text-[#C8A96E] text-xs tracking-widest">
                                        {{ str_repeat('★', $review->rating) }}
                                    </div>
                                </div>

                                @if($review->title)
                                    <p class="font-editorial text-base sm:text-lg text-[#1C1917] font-normal leading-snug">{{ $review->title }}</p>
                                @endif

                                <p class="text-xs sm:text-[12.5px] text-[#78716C] font-light leading-relaxed italic">"{{ $review->comment }}"</p>
                            </div>

                            <div class="pt-3 border-t border-[#DFD9CE]/60 flex items-center justify-between">
                                <span class="text-[9px] sm:text-[9.5px] uppercase tracking-[0.18em] text-[#1C1917] font-semibold truncate max-w-[140px]">
                                    {{ strtoupper($review->reviewer_name) }}
                                </span>
                                <span class="text-[8.5px] sm:text-[9px] uppercase tracking-[0.15em] text-[#8E877D] font-light shrink-0">
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
                                class="bg-white border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.25em] font-semibold px-6 sm:px-8 py-3 sm:py-3.5 rounded-full transition-all cursor-pointer shadow-2xs">
                            <span x-text="visibleReviews >= {{ $product->reviews->count() }} ? 'SHOW LESS REVIEWS' : 'LOAD MORE REVIEWS ({{ $product->reviews->count() - 6 }} MORE)'"></span>
                        </button>
                    </div>
                @endif
            @else
                <div class="glass rounded-[1.5rem] sm:rounded-[1.75rem] border border-[#DFD9CE] p-6 sm:p-8 text-center space-y-2">
                    <p class="font-editorial text-lg sm:text-xl italic text-[#1C1917]">No reviews yet.</p>
                    <p class="text-xs text-[#8E877D] font-light">Be the first collector to share your experience with this piece.</p>
                </div>
            @endif
        </div>

        {{-- ── YOU MAY ALSO LOVE ────────────────────────────────── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="pt-10 sm:pt-14 mb-16 sm:mb-20">
                <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light mb-6 sm:mb-8">You may also love.</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach($relatedProducts->take(4) as $rel)
                        <div class="group">
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[1.25rem] sm:rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-2.5 sm:mb-3 group/card">
                                <a href="{{ route('shop.show', $rel->slug) }}" class="block w-full h-full">
                                    @if(!empty($rel->images) && isset($rel->images[0]))
                                        <img src="{{ $rel->images[0] }}" alt="{{ $rel->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    @endif
                                </a>

                                @if($rel->is_bestseller) <span class="glass-pill absolute top-2 sm:top-3 left-2 sm:left-3 text-[#1C1917] text-[7.5px] sm:text-[8.5px] uppercase tracking-[0.14em] sm:tracking-[0.18em] font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full pointer-events-none">BESTSELLER</span> @endif
                                @if($rel->is_featured) <span class="glass-pill absolute top-2 sm:top-3 left-2 sm:left-3 text-[#1C1917] text-[7.5px] sm:text-[8.5px] uppercase tracking-[0.14em] sm:tracking-[0.18em] font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full pointer-events-none">FEATURED</span> @endif
                                @if($rel->is_new) <span class="glass-pill absolute top-2 sm:top-3 left-2 sm:left-3 text-[#1C1917] text-[7.5px] sm:text-[8.5px] uppercase tracking-[0.14em] sm:tracking-[0.18em] font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full pointer-events-none">NEW</span> @endif

                                {{-- Action Buttons (Visible on mobile, hover-animated on desktop) --}}
                                @php
                                    $relWishlisted = false;
                                    if (Auth::check()) {
                                        $relWishlisted = \App\Models\Wishlist::where('user_id', Auth::id())->where('product_id', $rel->id)->exists();
                                    } else {
                                        $relWishlisted = in_array($rel->id, session('guest_wishlist', []));
                                    }
                                @endphp
                                <div class="absolute top-2 sm:top-3 right-2 sm:right-3 flex flex-col space-y-1 sm:space-y-1.5 z-20 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:translate-x-2 sm:group-hover:translate-x-0 transition-all duration-300 ease-out">
                                    <form method="POST" action="{{ route('wishlist.toggle') }}" class="wishlist-toggle-form" data-product-id="{{ $rel->id }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                        <button type="submit" title="{{ $relWishlisted ? 'Remove from Wishlist' : 'Add to Wishlist' }}"
                                                class="wishlist-btn w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs {{ $relWishlisted ? 'bg-[#1C1917] text-white' : 'glass-pill border border-white/70 text-[#1C1917] hover:bg-[#1C1917] hover:text-white' }}"
                                                data-product-id="{{ $rel->id }}"
                                                data-wishlisted="{{ $relWishlisted ? 'true' : 'false' }}"
                                                data-style-type="related-item">
                                            <svg class="wishlist-icon w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75] transition-all duration-200 {{ $relWishlisted ? 'fill-current' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $rel->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" title="Add to Bag" class="glass-pill border border-white/70 w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer">
                                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-1.5 px-0.5 sm:px-1">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-editorial text-xs sm:text-[1.1rem] text-[#1C1917] font-normal leading-snug truncate">
                                        <a href="{{ route('shop.show', $rel->slug) }}" class="hover:text-[#8E877D] transition-colors">{{ $rel->name }}</a>
                                    </h3>
                                    <p class="text-[8.5px] sm:text-[10px] uppercase tracking-[0.1em] sm:tracking-[0.12em] text-[#8E877D] font-medium mt-0.5 truncate">{{ $rel->category?->name ?? 'Handcrafted Art' }}</p>
                                </div>
                                <span class="text-[11px] sm:text-[12.5px] font-semibold text-[#1C1917] whitespace-nowrap shrink-0 pt-0.5">₹ {{ number_format($rel->effective_price) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── RECENTLY VIEWED ──────────────────────────────────── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 2)
            <div class="pt-10 sm:pt-14 mb-12 sm:mb-16">
                <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light mb-6 sm:mb-8">Recently viewed.</h2>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                    @foreach($relatedProducts->take(3) as $recent)
                        <div class="group">
                            <div class="relative overflow-hidden aspect-[4/5] rounded-[1.25rem] sm:rounded-[2rem] bg-[#F5F2EB] border border-[#DFD9CE]/60 shadow-2xs mb-2.5 sm:mb-3 group/card">
                                <a href="{{ route('shop.show', $recent->slug) }}" class="block w-full h-full">
                                    @if(!empty($recent->images) && isset($recent->images[0]))
                                        <img src="{{ $recent->images[0] }}" alt="{{ $recent->name }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                    @endif
                                </a>

                                @if($recent->is_bestseller) <span class="glass-pill absolute top-2 sm:top-3 left-2 sm:left-3 text-[#1C1917] text-[7.5px] sm:text-[8.5px] uppercase tracking-[0.14em] sm:tracking-[0.18em] font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full pointer-events-none">BESTSELLER</span> @endif
                                @if($recent->is_featured) <span class="glass-pill absolute top-2 sm:top-3 left-2 sm:left-3 text-[#1C1917] text-[7.5px] sm:text-[8.5px] uppercase tracking-[0.14em] sm:tracking-[0.18em] font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full pointer-events-none">FEATURED</span> @endif
                                @if($recent->is_new) <span class="glass-pill absolute top-2 sm:top-3 left-2 sm:left-3 text-[#1C1917] text-[7.5px] sm:text-[8.5px] uppercase tracking-[0.14em] sm:tracking-[0.18em] font-medium px-2 sm:px-3 py-0.5 sm:py-1 rounded-full pointer-events-none">NEW</span> @endif

                                {{-- Action Buttons --}}
                                <div class="absolute top-2 sm:top-3 right-2 sm:right-3 flex flex-col space-y-1 sm:space-y-1.5 z-20 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:translate-x-2 sm:group-hover:translate-x-0 transition-all duration-300 ease-out">
                                    <form method="POST" action="{{ route('wishlist.toggle') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $recent->id }}">
                                        <button type="submit" title="Add to Wishlist" class="glass-pill w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs">
                                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $recent->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" title="Add to Bag" class="glass-pill w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[#1C1917] hover:bg-[#1C1917] hover:text-white flex items-center justify-center transition-all duration-200 cursor-pointer shadow-xs">
                                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-1.5 px-0.5 sm:px-1">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-editorial text-xs sm:text-[1.1rem] text-[#1C1917] font-normal leading-snug truncate">
                                        <a href="{{ route('shop.show', $recent->slug) }}" class="hover:text-[#8E877D] transition-colors">{{ $recent->name }}</a>
                                    </h3>
                                    <p class="text-[8.5px] sm:text-[10px] uppercase tracking-[0.1em] sm:tracking-[0.12em] text-[#8E877D] font-medium mt-0.5 truncate">{{ $recent->category?->name ?? 'Handcrafted Art' }}</p>
                                </div>
                                <span class="text-[11px] sm:text-[12.5px] font-semibold text-[#1C1917] whitespace-nowrap shrink-0 pt-0.5">₹ {{ number_format($recent->effective_price) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

</x-app-layout>
