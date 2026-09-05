<x-app-layout title="Maison Résine — Handcrafted Resin Art & Objects">

    <div class="min-h-screen bg-transparent space-y-24 md:space-y-36 pb-24 overflow-hidden">

        {{-- ══════════════════════════════════════════════════════════════════
             1. HERO SECTION (Full 100vh Widescreen Carousel Slider)
             ══════════════════════════════════════════════════════════════════ --}}
        @php
            $slidesData = $homeSlides->isNotEmpty() ? $homeSlides->map(function($slide) {
                return [
                    'tag' => $slide->tag ?: 'HANDCRAFTED & MADE TO ORDER',
                    'title' => $slide->title,
                    'desc' => $slide->description ?: 'Poured slowly, one piece at a time. Each work is a still moment — a river held between hands, a landscape suspended in glass.',
                    'image' => $slide->image_url,
                    'link' => $slide->link ?: route('collections.index'),
                ];
            })->values()->toArray() : [
                [
                    'tag' => 'HANDCRAFTED & MADE TO ORDER',
                    'title' => 'The quiet language of resin.',
                    'desc' => 'Poured slowly, one piece at a time. Each work is a still moment — a river held between hands, a landscape suspended in glass.',
                    'image' => asset('storage/gallery/segre_river_table.png'),
                    'link' => route('collections.show', 'river-tables'),
                ]
            ];
        @endphp

        <section class="relative w-full overflow-hidden bg-[#1C1917] group" x-data="{
            currentSlide: 0,
            slides: {{ json_encode($slidesData) }},
            timer: null,
            init() {
                if (this.slides.length > 1) {
                    this.startTimer();
                }
            },
            startTimer() {
                if (this.timer) clearInterval(this.timer);
                this.timer = setInterval(() => { this.next(); }, 6000);
            },
            next() {
                this.currentSlide = (this.currentSlide + 1) % this.slides.length;
                this.startTimer();
            },
            prev() {
                this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
                this.startTimer();
            },
            goTo(index) {
                this.currentSlide = index;
                this.startTimer();
            }
        }">
            {{-- Widescreen 100vh Hero Banner Height Container --}}
            <div class="relative w-full h-screen min-h-[650px] flex items-center">
                
                {{-- Slide Background Images --}}
                <template x-for="(slide, index) in slides" :key="index">
                    <div x-show="currentSlide === index"
                         x-transition:enter="transition duration-1000 ease-out"
                         x-transition:enter-start="opacity-0 scale-105"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition duration-700 ease-in"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-0 w-full h-full">
                        <img :src="slide.image" 
                             :alt="slide.title" 
                             class="w-full h-full object-cover object-center">
                    </div>
                </template>

                {{-- Cinematic Dark Gradient Vignette Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-black/20 lg:via-black/45 lg:to-transparent pointer-events-none"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/30 pointer-events-none"></div>

                {{-- Hero Content Overlay (Aligned with Site Container) --}}
                <div class="relative z-10 max-w-[1400px] w-full mx-auto px-6 lg:px-12 xl:px-16 pt-24 sm:pt-28 lg:pt-32">
                    <div class="max-w-2xl lg:max-w-3xl space-y-6 text-white">
                        
                        {{-- Tag Badge --}}
                        <div class="inline-flex items-center space-x-2.5 bg-white/15 backdrop-blur-md border border-white/20 px-4 py-1.5 rounded-full shadow-sm animate-fade-up">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.25em] font-semibold text-white" x-text="slides[currentSlide].tag"></span>
                        </div>

                        {{-- Big Editorial Headline --}}
                        <h1 class="font-editorial text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-light leading-[1.08] tracking-tight text-white drop-shadow-sm min-h-[2.2em] sm:min-h-[2em] flex items-center" x-text="slides[currentSlide].title">
                        </h1>

                        {{-- Subtitle Paragraph --}}
                        <p class="text-sm sm:text-base lg:text-lg text-white/85 font-light leading-relaxed max-w-xl drop-shadow-xs" x-text="slides[currentSlide].desc">
                        </p>

                        {{-- Action Buttons --}}
                        <div class="pt-4 flex flex-wrap items-center gap-4 sm:gap-5">
                            <a :href="slides[currentSlide].link" 
                               class="inline-flex items-center justify-center rounded-full bg-white text-[#1C1917] hover:bg-[#AD9575] hover:text-white px-8 sm:px-10 py-4 text-[10.5px] uppercase tracking-[0.25em] font-semibold transition-all duration-300 shadow-xl group/btn">
                                <span>Explore Collection</span>
                                <svg class="w-3.5 h-3.5 ml-2.5 group-hover/btn:translate-x-1 transition-transform stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                </svg>
                            </a>

                            <a href="{{ route('custom.index') }}" 
                               class="inline-flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/30 backdrop-blur-md px-8 sm:px-9 py-4 text-[10.5px] uppercase tracking-[0.25em] font-semibold transition-all duration-300 shadow-lg">
                                <span>Custom Request</span>
                            </a>
                        </div>

                    </div>
                </div>

                {{-- Navigation Controls (Prev/Next Arrows + Progress Dots) --}}
                @if(count($slidesData) > 1)
                    {{-- Left Prev Arrow Button (Appears on Hover) --}}
                    <button type="button" 
                            @click.stop.prevent="prev()" 
                            aria-label="Previous Slide"
                            class="absolute top-1/2 -translate-y-1/2 left-6 lg:left-10 z-30 w-12 h-12 rounded-full bg-white/20 hover:bg-white text-white hover:text-[#1C1917] backdrop-blur-md border border-white/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-xl cursor-pointer hover:scale-110 active:scale-95">
                        <svg class="w-5 h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>

                    {{-- Right Next Arrow Button (Appears on Hover) --}}
                    <button type="button" 
                            @click.stop.prevent="next()" 
                            aria-label="Next Slide"
                            class="absolute top-1/2 -translate-y-1/2 right-6 lg:right-10 z-30 w-12 h-12 rounded-full bg-white/20 hover:bg-white text-white hover:text-[#1C1917] backdrop-blur-md border border-white/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-xl cursor-pointer hover:scale-110 active:scale-95">
                        <svg class="w-5 h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>

                    {{-- Bottom Pagination Dots: Aligned Exactly with Content Text Container --}}
                    <div class="absolute bottom-8 sm:bottom-10 inset-x-0 z-30 pointer-events-none">
                        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16">
                            <div class="pointer-events-auto inline-flex items-center space-x-2.5 bg-black/45 backdrop-blur-md border border-white/20 px-3.5 py-2 rounded-full shadow-lg">
                                @foreach($slidesData as $sIndex => $sItem)
                                    <button type="button" 
                                            @click.stop.prevent="goTo({{ $sIndex }})" 
                                            :class="currentSlide === {{ $sIndex }} ? 'w-8 bg-[#DFD4C0]' : 'w-2.5 bg-white/40 hover:bg-white/80'"
                                            aria-label="Go to slide {{ $sIndex + 1 }}"
                                            class="h-2 rounded-full transition-all duration-300 focus:outline-none cursor-pointer">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </section>


        {{-- ══════════════════════════════════════════════════════════════════
             1.5 EXPLORE BY CATEGORY (Horizontal Scroll Carousel with Side Arrows)
             ══════════════════════════════════════════════════════════════════ --}}
        @if($categories->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 py-8"
                 x-data="{
                     scrollLeft() { this.$refs.catSlider.scrollBy({ left: -300, behavior: 'smooth' }); },
                     scrollRight() { this.$refs.catSlider.scrollBy({ left: 300, behavior: 'smooth' }); },
                     canScrollLeft: false,
                     canScrollRight: false,
                     checkScroll() {
                         const el = this.$refs.catSlider;
                         this.canScrollLeft = el.scrollLeft > 10;
                         this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
                     }
                 }"
                 x-init="$nextTick(() => checkScroll())">
            
            <div class="relative">

                {{-- Left Side Floating Arrow --}}
                <button type="button" 
                        x-show="canScrollLeft"
                        x-transition
                        @click.prevent="scrollLeft()"
                        class="absolute -left-4 sm:-left-6 top-[42%] -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white shadow-[0_2px_16px_rgba(0,0,0,0.12)] border border-[#E8E2D8] flex items-center justify-center text-[#1C1917] hover:bg-[#1C1917] hover:text-white hover:border-[#1C1917] active:scale-95 transition-all duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                    </svg>
                </button>

                {{-- Right Side Floating Arrow --}}
                <button type="button" 
                        x-show="canScrollRight"
                        x-transition
                        @click.prevent="scrollRight()"
                        class="absolute -right-4 sm:-right-6 top-[42%] -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white shadow-[0_2px_16px_rgba(0,0,0,0.12)] border border-[#E8E2D8] flex items-center justify-center text-[#1C1917] hover:bg-[#1C1917] hover:text-white hover:border-[#1C1917] active:scale-95 transition-all duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </button>

                {{-- Scrollable Row (No visible scrollbar) --}}
                <div x-ref="catSlider"
                     @scroll.passive="checkScroll()"
                     style="-ms-overflow-style:none; scrollbar-width:none;"
                     class="flex items-start gap-8 sm:gap-10 lg:gap-12 overflow-x-auto scroll-smooth py-4 [&::-webkit-scrollbar]:hidden">
                    @foreach($categories as $category)
                        @php
                            $imageUrl = $category->image_url;
                        @endphp

                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}" 
                           class="group flex flex-col items-center shrink-0 text-center cursor-pointer">
                            
                            {{-- Uniform Round Circle (Bigger - same for ALL) --}}
                            <div class="w-40 h-40 sm:w-44 sm:h-44 lg:w-52 lg:h-52 rounded-full bg-[#F3ECE2] p-3 sm:p-3.5 group-hover:bg-[#E6DCD0] group-hover:-translate-y-1.5 group-hover:shadow-lg transition-all duration-300 ease-out">
                                <div class="w-full h-full rounded-full overflow-hidden bg-white">
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $category->name }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-500 ease-out">
                                </div>
                            </div>

                            {{-- Category Name --}}
                            <h3 class="mt-3.5 text-[14px] sm:text-[15px] lg:text-base font-semibold text-[#1C1917] group-hover:text-[#8E7558] transition-colors leading-snug">
                                {{ $category->name }}
                            </h3>
                        </a>
                    @endforeach
                </div>

            </div>

        </section>
        @endif


         {{-- ══════════════════════════════════════════════════════════════════
             5. MOST LOVED (Curated Published Products & Bestsellers)
             ══════════════════════════════════════════════════════════════════ --}}
        @if($mostLoved->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-10">
            
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-[#E5DFD3]/80 pb-6">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        Most Loved
                    </span>
                    <h2 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light">
                        Small pieces, <em class="italic text-[#AD9575]">quietly loved.</em>
                    </h2>
                </div>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] hover:text-[#AD9575] transition-colors">
                    <span>View all objects</span>
                    <span>&rarr;</span>
                </a>
            </div>

            {{-- 4-Column Product Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                @foreach($mostLoved as $product)
                    @include('components.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
            </div>

        </section>
        @endif


        {{-- ══════════════════════════════════════════════════════════════════
             5b. BESTSELLERS (Products marked as bestseller in admin)
             ══════════════════════════════════════════════════════════════════ --}}
        @if($bestsellers->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-10">
            
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-[#E5DFD3]/80 pb-6">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        BESTSELLERS
                    </span>
                    <h2 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light">
                        Pieces the world <em class="italic text-[#AD9575]">keeps choosing.</em>
                    </h2>
                </div>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] hover:text-[#AD9575] transition-colors group">
                    <span>Shop bestsellers</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
            </div>

            {{-- 4-Column Product Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                @foreach($bestsellers as $product)
                    @include('components.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
            </div>

        </section>
        @endif


        {{-- ══════════════════════════════════════════════════════════════════
             5c. NEW ARRIVALS (Recently added or marked as new)
             ══════════════════════════════════════════════════════════════════ --}}
        @if($newArrivals->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-10">
            
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-[#E5DFD3]/80 pb-6">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        NEW ARRIVALS
                    </span>
                    <h2 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light">
                        Fresh from the <em class="italic text-[#AD9575]">atelier.</em>
                    </h2>
                </div>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] hover:text-[#AD9575] transition-colors group">
                    <span>See what's new</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
            </div>

            {{-- 4-Column Product Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                @foreach($newArrivals as $product)
                    @include('components.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds])
                @endforeach
            </div>

        </section>
        @endif


        {{-- ══════════════════════════════════════════════════════════════════
             2. FEATURED COLLECTIONS (Dynamic from Admin Panel with Collection Links)
             ══════════════════════════════════════════════════════════════════ --}}
        @if($featuredCollections->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-12">
            
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5DFD3]/80 pb-6">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        CURATED COLLECTIONS
                    </span>
                    <h2 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light">
                        From centrepiece tables to <em class="italic text-[#AD9575]">quiet objects.</em>
                    </h2>
                </div>
                <a href="{{ route('collections.index') }}" class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] hover:text-[#AD9575] transition-colors group">
                    <span>View all collections</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
            </div>

            {{-- Asymmetric Dynamic Collections Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
                
                {{-- Card 1: Tall Featured Collection (Left 7 Cols) --}}
                @if(isset($featuredCollections[0]))
                    @php 
                        $col1 = $featuredCollections[0]; 
                        $col1Img = $col1->effective_cover_image ? (str_starts_with($col1->effective_cover_image, 'http') || str_starts_with($col1->effective_cover_image, '/') ? $col1->effective_cover_image : asset('storage/' . $col1->effective_cover_image)) : asset('storage/gallery/segre_river_table.png');
                    @endphp
                    <a href="{{ route('collections.show', $col1->slug) }}" 
                       class="lg:col-span-7 group relative rounded-[2.5rem] overflow-hidden min-h-[480px] lg:min-h-[580px] bg-[#EBE5DB] border border-[#DFD9CE]/60 flex flex-col justify-between p-8 sm:p-10 shadow-sm transition-all duration-500 hover:shadow-xl">
                        
                        <img src="{{ $col1Img }}" 
                             alt="{{ $col1->name }}" 
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10"></div>

                        {{-- Top Tag --}}
                        <div class="relative z-10 flex justify-between items-start">
                            <span class="glass-pill bg-white/20 backdrop-blur-md border border-white/30 text-white text-[9.5px] uppercase tracking-[0.22em] font-semibold px-4 py-1.5 rounded-full">
                                N°01 · COLLECTION
                            </span>
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-md border border-white/40 text-white flex items-center justify-center group-hover:bg-white group-hover:text-[#1C1917] transition-all duration-300">
                                <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </div>
                        </div>

                        {{-- Bottom Description --}}
                        <div class="relative z-10 space-y-1.5 text-white">
                            <h3 class="font-editorial text-3xl sm:text-4xl text-white font-normal">
                                {{ $col1->name }}
                            </h3>
                            <p class="text-sm text-white/80 font-light tracking-wide">
                                {{ $col1->subtitle ?: $col1->effective_short_description ?: 'Handcrafted Atelier Collection' }}
                            </p>
                        </div>
                    </a>
                @endif

                {{-- Right Stacked Cards (5 Cols) --}}
                <div class="lg:col-span-5 grid grid-cols-1 gap-6 sm:gap-8">
                    
                    {{-- Card 2: Top Right --}}
                    @if(isset($featuredCollections[1]))
                        @php 
                            $col2 = $featuredCollections[1]; 
                            $col2Img = $col2->effective_cover_image ? (str_starts_with($col2->effective_cover_image, 'http') || str_starts_with($col2->effective_cover_image, '/') ? $col2->effective_cover_image : asset('storage/' . $col2->effective_cover_image)) : asset('storage/gallery/mira_wall.png');
                        @endphp
                        <a href="{{ route('collections.show', $col2->slug) }}" 
                           class="group relative rounded-[2.25rem] overflow-hidden min-h-[260px] sm:min-h-[275px] bg-[#EBE5DB] border border-[#DFD9CE]/60 flex flex-col justify-between p-7 sm:p-8 shadow-sm transition-all duration-500 hover:shadow-lg">
                            
                            <img src="{{ $col2Img }}" 
                                 alt="{{ $col2->name }}" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10"></div>

                            <div class="relative z-10 flex justify-between items-start">
                                <span class="glass-pill bg-white/20 backdrop-blur-md border border-white/30 text-white text-[9px] uppercase tracking-[0.22em] font-semibold px-3.5 py-1.5 rounded-full">
                                    N°02 · COLLECTION
                                </span>
                                <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-md border border-white/40 text-white flex items-center justify-center group-hover:bg-white group-hover:text-[#1C1917] transition-all duration-300">
                                    <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </div>
                            </div>

                            <div class="relative z-10 space-y-1 text-white">
                                <h3 class="font-editorial text-2xl sm:text-3xl text-white font-normal">
                                    {{ $col2->name }}
                                </h3>
                                <p class="text-xs text-white/80 font-light">
                                    {{ $col2->subtitle ?: $col2->effective_short_description ?: 'Unique Resin Artwork Series' }}
                                </p>
                            </div>
                        </a>
                    @endif

                    {{-- Card 3: Bottom Right --}}
                    @if(isset($featuredCollections[2]))
                        @php 
                            $col3 = $featuredCollections[2]; 
                            $col3Img = $col3->effective_cover_image ? (str_starts_with($col3->effective_cover_image, 'http') || str_starts_with($col3->effective_cover_image, '/') ? $col3->effective_cover_image : asset('storage/' . $col3->effective_cover_image)) : asset('storage/gallery/tray.png');
                        @endphp
                        <a href="{{ route('collections.show', $col3->slug) }}" 
                           class="group relative rounded-[2.25rem] overflow-hidden min-h-[260px] sm:min-h-[275px] bg-[#EBE5DB] border border-[#DFD9CE]/60 flex flex-col justify-between p-7 sm:p-8 shadow-sm transition-all duration-500 hover:shadow-lg">
                            
                            <img src="{{ $col3Img }}" 
                                 alt="{{ $col3->name }}" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10"></div>

                            <div class="relative z-10 flex justify-between items-start">
                                <span class="glass-pill bg-white/20 backdrop-blur-md border border-white/30 text-white text-[9px] uppercase tracking-[0.22em] font-semibold px-3.5 py-1.5 rounded-full">
                                    N°03 · COLLECTION
                                </span>
                                <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-md border border-white/40 text-white flex items-center justify-center group-hover:bg-white group-hover:text-[#1C1917] transition-all duration-300">
                                    <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </div>
                            </div>

                            <div class="relative z-10 space-y-1 text-white">
                                <h3 class="font-editorial text-2xl sm:text-3xl text-white font-normal">
                                    {{ $col3->name }}
                                </h3>
                                <p class="text-xs text-white/80 font-light">
                                    {{ $col3->subtitle ?: $col3->effective_short_description ?: 'Bespoke Home Objects & Decor' }}
                                </p>
                            </div>
                        </a>
                    @endif

                </div>

            </div>
        </section>
        @endif


        {{-- ══════════════════════════════════════════════════════════════════
             3. BRAND QUOTE BANNER (Dynamic from Admin Settings)
             ══════════════════════════════════════════════════════════════════ --}}
        <section class="max-w-4xl mx-auto px-6 text-center py-4">
            <div class="space-y-6">
                <span class="inline-block w-12 h-[1px] bg-[#AD9575]"></span>
                <blockquote class="font-editorial text-2xl sm:text-3xl md:text-4xl text-[#1C1917] font-light italic leading-relaxed">
                    &ldquo;{{ $homeQuoteText }}&rdquo;
                </blockquote>
                <p class="text-[11px] uppercase tracking-[0.28em] font-semibold text-[#8E877D]">
                    — {{ $homeQuoteAuthor }}
                </p>
            </div>
        </section>


        {{-- ══════════════════════════════════════════════════════════════════
             4. THE HOUSE: MADE TO ORDER (Dynamic from Admin Settings)
             ══════════════════════════════════════════════════════════════════ --}}
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                
                {{-- Left Image --}}
                <div class="lg:col-span-6 relative">
                    <div class="relative aspect-[4/3.2] max-h-[440px] rounded-[2.25rem] lg:rounded-[2.5rem] overflow-hidden bg-[#F0EBE1] border border-[#DFD9CE]/70 shadow-lg">
                        @php
                            $storyImgSrc = $homeStoryImage ? (str_starts_with($homeStoryImage, 'http') || str_starts_with($homeStoryImage, '/') ? $homeStoryImage : asset('storage/' . $homeStoryImage)) : asset('storage/about/artist_workshop.png');
                        @endphp
                        <img src="{{ $storyImgSrc }}" 
                             alt="{{ $homeStoryTitle }}" 
                             class="w-full h-full object-cover">
                        <div class="absolute bottom-6 left-6 z-10">
                            <span class="glass-pill bg-white/90 backdrop-blur-md border border-white/80 text-[#1C1917] text-[9.5px] uppercase tracking-[0.24em] font-semibold px-4 py-2 rounded-full shadow-sm">
                                {{ $homeStoryBadge }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right Narrative --}}
                <div class="lg:col-span-6 space-y-7">
                    <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        <span class="w-6 h-[1px] bg-[#D9D2C5] inline-block"></span>
                        <span>{{ $homeStoryTag }}</span>
                    </div>

                    <h2 class="font-editorial text-4xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-[1.1]">
                        {{ $homeStoryTitle }}
                    </h2>

                    <p class="text-[15px] sm:text-[16px] text-[#78716C] font-light leading-relaxed">
                        {{ $homeStoryParagraph1 }}
                    </p>

                    @if($homeStoryParagraph2)
                    <p class="text-[14px] text-[#8E877D] font-light leading-relaxed italic">
                        {{ $homeStoryParagraph2 }}
                    </p>
                    @endif

                    <div class="pt-3">
                        <a href="{{ $homeStoryLinkUrl }}" 
                           class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.24em] font-semibold text-[#1C1917] hover:text-[#AD9575] transition-colors border-b border-[#1C1917] pb-1 hover:border-[#AD9575] group">
                            <span>{{ $homeStoryLinkText }}</span>
                            <span class="group-hover:translate-x-1.5 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>

            </div>
        </section>


       


     


        {{-- ══════════════════════════════════════════════════════════════════
             7. CUSTOM RESIN ARTWORK (Custom Request Process Showcase)
             ══════════════════════════════════════════════════════════════════ --}}
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16">
            <div class="rounded-[2.5rem] bg-[#1C1917] text-white p-8 sm:p-12 lg:p-16 relative overflow-hidden shadow-2xl">
                
                {{-- Atmospheric Background Glow --}}
                <div class="absolute -top-32 -right-32 w-96 h-96 bg-[#AD9575]/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-[#0D5C75]/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 space-y-12">
                    
                    {{-- Header Row --}}
                    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 border-b border-white/10 pb-8">
                        <div class="space-y-4 max-w-2xl">
                            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-bold text-[#AD9575]">
                                <span class="w-6 h-[1px] bg-[#AD9575] inline-block"></span>
                                <span>{{ $homeCustomTag }}</span>
                            </div>
                            <h2 class="font-editorial text-4xl sm:text-5xl lg:text-6xl font-light leading-tight">
                                {!! str_replace('your space.', '<em class="italic text-[#AD9575]">your space.</em>', e($homeCustomTitle)) !!}
                            </h2>
                            <p class="text-[14.5px] text-white/70 font-light leading-relaxed">
                                {{ $homeCustomDesc }}
                            </p>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ $homeCustomBtnUrl }}" 
                               class="inline-flex items-center justify-center rounded-full bg-[#AD9575] hover:bg-white text-[#1C1917] px-9 py-4 text-[10.5px] uppercase tracking-[0.24em] font-semibold transition-all duration-300 shadow-md">
                                {{ $homeCustomBtnText }}
                            </a>
                        </div>
                    </div>

                    {{-- 3-Step Custom Artwork Process Lifecycle --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-2">
                        
                        {{-- Step 1 --}}
                        <div class="rounded-[2rem] bg-white/5 border border-white/10 p-7 sm:p-8 space-y-4 backdrop-blur-sm">
                            <div class="flex items-center justify-between text-[#AD9575]">
                                <span class="font-mono text-sm">01</span>
                                <span class="text-[9px] uppercase tracking-[0.2em] font-semibold text-white/40">Step 1</span>
                            </div>
                            <h3 class="font-editorial text-2xl text-white font-normal">{{ $homeCustomStep1Title }}</h3>
                            <p class="text-xs text-white/60 font-light leading-relaxed">
                                {{ $homeCustomStep1Desc }}
                            </p>
                        </div>

                        {{-- Step 2 --}}
                        <div class="rounded-[2rem] bg-white/5 border border-white/10 p-7 sm:p-8 space-y-4 backdrop-blur-sm">
                            <div class="flex items-center justify-between text-[#AD9575]">
                                <span class="font-mono text-sm">02</span>
                                <span class="text-[9px] uppercase tracking-[0.2em] font-semibold text-white/40">Step 2</span>
                            </div>
                            <h3 class="font-editorial text-2xl text-white font-normal">{{ $homeCustomStep2Title }}</h3>
                            <p class="text-xs text-white/60 font-light leading-relaxed">
                                {{ $homeCustomStep2Desc }}
                            </p>
                        </div>

                        {{-- Step 3 --}}
                        <div class="rounded-[2rem] bg-white/5 border border-white/10 p-7 sm:p-8 space-y-4 backdrop-blur-sm">
                            <div class="flex items-center justify-between text-[#AD9575]">
                                <span class="font-mono text-sm">03</span>
                                <span class="text-[9px] uppercase tracking-[0.2em] font-semibold text-white/40">Step 3</span>
                            </div>
                            <h3 class="font-editorial text-2xl text-white font-normal">{{ $homeCustomStep3Title }}</h3>
                            <p class="text-xs text-white/60 font-light leading-relaxed">
                                {{ $homeCustomStep3Desc }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>
        </section>


        {{-- ══════════════════════════════════════════════════════════════════
             8. CLIENT FEEDBACK (Dynamic Reviews Auto-Scrolling Carousel - Exactly 3 Per View)
             ══════════════════════════════════════════════════════════════════ --}}
        @if(isset($featuredReviews) && $featuredReviews->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-10"
                 x-data="{
                     scrollLeft() {
                         const el = this.$refs.reviewSlider;
                         const card = el.querySelector('.review-card');
                         const step = card ? (card.offsetWidth + 24) : 400;
                         el.scrollBy({ left: -step, behavior: 'smooth' });
                     },
                     scrollRight() {
                         const el = this.$refs.reviewSlider;
                         const card = el.querySelector('.review-card');
                         const step = card ? (card.offsetWidth + 24) : 400;
                         el.scrollBy({ left: step, behavior: 'smooth' });
                     },
                     canScrollLeft: false,
                     canScrollRight: true,
                     checkScroll() {
                         const el = this.$refs.reviewSlider;
                         this.canScrollLeft = el.scrollLeft > 10;
                         this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 10);
                     },
                     autoScrollTimer: null,
                     startAutoScroll() {
                         this.stopAutoScroll();
                         this.autoScrollTimer = setInterval(() => {
                             const el = this.$refs.reviewSlider;
                             if (!el) return;
                             const card = el.querySelector('.review-card');
                             const step = card ? (card.offsetWidth + 24) : 400;
                             if (el.scrollLeft >= (el.scrollWidth - el.clientWidth - 15)) {
                                 el.scrollTo({ left: 0, behavior: 'smooth' });
                             } else {
                                 el.scrollBy({ left: step, behavior: 'smooth' });
                             }
                         }, 4000);
                     },
                     stopAutoScroll() {
                         if (this.autoScrollTimer) clearInterval(this.autoScrollTimer);
                     }
                 }"
                 x-init="$nextTick(() => { checkScroll(); startAutoScroll(); })"
                 @mouseenter="stopAutoScroll()"
                 @mouseleave="startAutoScroll()">
            
            {{-- Header with Nav Arrows --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-[#E5DFD3]/80 pb-6">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        Words from our clients
                    </span>
                    <h2 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light">
                        Trusted by <em class="italic text-[#AD9575]">collectors.</em>
                    </h2>
                </div>

                {{-- Interactive Arrows --}}
                <div class="flex items-center space-x-2.5">
                    <button type="button" 
                            @click="scrollLeft()"
                            :class="canScrollLeft ? 'opacity-100 cursor-pointer hover:bg-[#1C1917] hover:text-white hover:border-[#1C1917]' : 'opacity-40 cursor-not-allowed'"
                            aria-label="Previous review"
                            class="w-10 h-10 rounded-full border border-[#D5CFC4] bg-white flex items-center justify-center text-[#1C1917] transition-all duration-200 shadow-xs active:scale-95">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                        </svg>
                    </button>
                    <button type="button" 
                            @click="scrollRight()"
                            :class="canScrollRight ? 'opacity-100 cursor-pointer hover:bg-[#1C1917] hover:text-white hover:border-[#1C1917]' : 'opacity-40 cursor-not-allowed'"
                            aria-label="Next review"
                            class="w-10 h-10 rounded-full border border-[#D5CFC4] bg-white flex items-center justify-center text-[#1C1917] transition-all duration-200 shadow-xs active:scale-95">
                        <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Exactly 3 Cards Per View Container (Zero Cutting) --}}
            <div class="relative overflow-hidden w-full">
                <div x-ref="reviewSlider"
                     @scroll.passive="checkScroll()"
                     style="-ms-overflow-style:none; scrollbar-width:none;"
                     class="flex items-stretch gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory py-4 px-1 [&::-webkit-scrollbar]:hidden">
                    
                    @foreach($featuredReviews as $review)
                    @php
                        $initials = collect(explode(' ', $review->reviewer_name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('');
                    @endphp
                    <div class="review-card shrink-0 snap-start w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] rounded-[2rem] bg-[#FBF9F5] border border-[#E7E1D6] p-7 sm:p-8 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_32px_rgba(142,117,88,0.12)] hover:border-[#D5C7B2] hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between space-y-6">
                        
                        <div class="space-y-4">
                            {{-- Rating Stars --}}
                            <div class="flex items-center space-x-1 text-[#AD9575]">
                                @for($i = 0; $i < ($review->rating ?: 5); $i++)
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                @endfor
                            </div>

                            {{-- Title & Body --}}
                            <h4 class="font-editorial text-xl sm:text-2xl text-[#1C1917] font-normal leading-snug">
                                {{ $review->title ?: 'Unbelievable craftsmanship' }}
                            </h4>
                            <p class="text-sm text-[#5C554E] font-light leading-relaxed italic line-clamp-4">
                                &ldquo;{{ $review->comment }}&rdquo;
                            </p>
                        </div>

                        {{-- Reviewer Info & Purchased Item --}}
                        <div class="pt-5 border-t border-[#EAE3D8]/80 space-y-2.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-[#EFE9DF] text-[#8E7558] font-semibold text-xs flex items-center justify-center border border-[#DFD7CB] shrink-0">
                                    {{ $initials ?: 'MR' }}
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-xs uppercase tracking-wider font-semibold text-[#1C1917] truncate">
                                        {{ $review->reviewer_name }}
                                    </p>
                                    @if($review->created_at)
                                        <p class="text-[10px] text-[#A8A29E] tracking-wide">
                                            {{ $review->created_at->format('M Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if($review->product)
                                <a href="{{ route('shop.show', $review->product->slug) }}" class="inline-flex items-center space-x-1 text-[10.5px] text-[#8E7558] hover:text-[#1C1917] font-medium tracking-wide transition-colors group/item truncate max-w-full">
                                    <span class="truncate">Purchased: {{ $review->product->name }}</span>
                                    <span class="group-hover/item:translate-x-0.5 transition-transform">&rarr;</span>
                                </a>
                            @endif
                        </div>

                    </div>
                    @endforeach

                </div>
            </div>

        </section>
        @endif


        {{-- ══════════════════════════════════════════════════════════════════
             9. INTERIOR INSPIRATION (Dynamic Random Gallery from Database)
             ══════════════════════════════════════════════════════════════════ --}}
        @if($galleryItems->isNotEmpty())
        <section class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-10">
            
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-[#E5DFD3]/80 pb-6">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase tracking-[0.28em] font-bold text-[#8E877D]">
                        Interior Inspiration
                    </span>
                    <h2 class="font-editorial text-3xl sm:text-4xl lg:text-5xl text-[#1C1917] font-light">
                        Homes that hold <em class="italic">our work.</em>
                    </h2>
                </div>
                <a href="{{ route('gallery.index') }}" class="inline-flex items-center space-x-2 text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#1C1917] hover:text-[#AD9575] transition-colors">
                    <span>Enter the gallery</span>
                    <span>&rarr;</span>
                </a>
            </div>

            {{-- Asymmetric Random Luxury Gallery Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 sm:gap-8">
                
                {{-- 1. Hero Feature Item (Span 7) --}}
                @if(isset($galleryItems[0]))
                    @php $first = $galleryItems[0]; @endphp
                    <div class="lg:col-span-7 group relative rounded-[2.25rem] overflow-hidden min-h-[420px] lg:min-h-[500px] bg-[#EBE5DB] border border-[#DFD9CE]/60 shadow-sm">
                        <img src="{{ asset('storage/' . $first->image_path) }}" 
                             alt="{{ $first->title }}" 
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        
                        <div class="absolute top-6 left-6 z-10">
                            <span class="glass-pill bg-white/80 backdrop-blur-md border border-white/60 text-[#1C1917] text-[9px] uppercase tracking-[0.22em] font-semibold px-3.5 py-1.5 rounded-full shadow-sm">
                                {{ $first->galleryCategory->name ?? 'Interior Feature' }}
                            </span>
                        </div>

                        <div class="absolute bottom-6 left-6 right-6 text-white space-y-1">
                            <p class="text-[9.5px] uppercase tracking-widest text-[#AD9575] font-semibold">
                                {{ $first->location ?? 'Private Residence' }}
                            </p>
                            <h3 class="font-editorial text-2xl sm:text-3xl font-normal text-white">
                                {{ $first->title }}
                            </h3>
                        </div>
                    </div>
                @endif

                {{-- 2. Side Item (Span 5) --}}
                @if(isset($galleryItems[1]))
                    @php $second = $galleryItems[1]; @endphp
                    <div class="lg:col-span-5 group relative rounded-[2.25rem] overflow-hidden min-h-[420px] lg:min-h-[500px] bg-[#EBE5DB] border border-[#DFD9CE]/60 shadow-sm">
                        <img src="{{ asset('storage/' . $second->image_path) }}" 
                             alt="{{ $second->title }}" 
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        
                        <div class="absolute top-6 left-6 z-10">
                            <span class="glass-pill bg-white/80 backdrop-blur-md border border-white/60 text-[#1C1917] text-[9px] uppercase tracking-[0.22em] font-semibold px-3.5 py-1.5 rounded-full shadow-sm">
                                {{ $second->galleryCategory->name ?? 'Atelier Piece' }}
                            </span>
                        </div>

                        <div class="absolute bottom-6 left-6 right-6 text-white space-y-1">
                            <p class="text-[9.5px] uppercase tracking-widest text-[#AD9575] font-semibold">
                                {{ $second->location ?? 'Architectural Suite' }}
                            </p>
                            <h3 class="font-editorial text-2xl sm:text-3xl font-normal text-white">
                                {{ $second->title }}
                            </h3>
                        </div>
                    </div>
                @endif

                {{-- 3 & 4. Bottom 3-Card Row (4 Cols Each) --}}
                @foreach($galleryItems->slice(2, 3) as $item)
                    <div class="lg:col-span-4 group relative rounded-[2rem] overflow-hidden aspect-[4/5] bg-[#EBE5DB] border border-[#DFD9CE]/60 shadow-sm">
                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                             alt="{{ $item->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>
                        
                        <div class="absolute bottom-6 left-6 right-6 text-white space-y-0.5">
                            <p class="text-[9px] uppercase tracking-widest text-[#AD9575] font-semibold">
                                {{ $item->location ?? 'Private Collection' }}
                            </p>
                            <h4 class="font-editorial text-xl font-normal text-white truncate">
                                {{ $item->title }}
                            </h4>
                        </div>
                    </div>
                @endforeach

            </div>

        </section>
        @endif

    </div>

</x-app-layout>
