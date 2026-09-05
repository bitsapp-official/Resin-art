<x-app-layout title="Gallery — Maison Résine Atelier">

    <div class="py-10 lg:py-16" 
         x-data="galleryLightbox({
            nextPageUrl: @js($items->nextPageUrl()),
            hasMore: @js($items->hasMorePages()),
            items: [
                @foreach($items as $index => $item)
                {
                    id: {{ $item->id }},
                    title: @js($item->title),
                    category: @js($item->galleryCategory ? $item->galleryCategory->name : 'Gallery'),
                    image: @js(asset('storage/' . $item->image_path)),
                    alt: @js($item->image_alt),
                    location: @js($item->location ?? '')
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
         })">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-10 lg:space-y-14">

            <!-- Hero Header Section -->
            <div class="max-w-3xl space-y-4 animate-fade-up">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>GALLERY</span>
                </div>

                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    Pieces in place.
                </h1>

                <p class="text-[15px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                    Our work photographed in the homes, offices and spaces it now lives in.
                </p>
            </div>

            <!-- Category Filter Pills Bar (No Border Line) -->
            @if(isset($categories) && $categories->count() > 0)
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3.5 pb-2 animate-fade-up delay-100">
                    <!-- ALL Category Pill -->
                    <a href="{{ route('gallery.index') }}" 
                       class="px-5 py-2.5 rounded-full text-[10.5px] uppercase tracking-[0.2em] font-semibold transition-all duration-300 {{ is_null($activeCategory) ? 'bg-[#1C1917] text-[#FAF8F5] shadow-sm' : 'bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] text-[#78716C] hover:text-[#1C1917] hover:border-[#1C1917]' }}">
                        All
                    </a>

                    <!-- Dynamic Categories Pills -->
                    @foreach($categories as $category)
                        <a href="{{ route('gallery.index', ['category' => $category->slug]) }}" 
                           class="px-5 py-2.5 rounded-full text-[10.5px] uppercase tracking-[0.2em] font-semibold transition-all duration-300 {{ ($activeCategory && $activeCategory->id === $category->id) ? 'bg-[#1C1917] text-[#FAF8F5] shadow-sm' : 'bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] text-[#78716C] hover:text-[#1C1917] hover:border-[#1C1917]' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Instagram / Pinterest-Style Dynamic Masonry Gallery Grid -->
            @if(isset($items) && $items->count() > 0)
                <div id="gallery-grid-container" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6 pt-2">
                    @foreach($items as $index => $item)
                        @include('gallery.partials.item-card', [
                            'item' => $item,
                            'index' => $index,
                            'globalIndex' => $index
                        ])
                    @endforeach
                </div>

                <!-- Infinite Scroll Loading Trigger Sentinel -->
                <div id="infinite-scroll-trigger" 
                     x-ref="scrollTrigger"
                     x-show="hasMore" 
                     class="py-14 flex flex-col items-center justify-center space-y-3">
                    <div x-show="isLoading" class="w-8 h-8 rounded-full border-2 border-[#1C1917] border-t-transparent animate-spin"></div>
                    <span class="text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]" 
                          x-text="isLoading ? 'Loading more atelier pieces...' : 'Scroll to discover more'"></span>
                </div>

                <!-- SEO Fallback Pagination -->
                <noscript>
                    <div class="pt-12 flex justify-center">
                        {{ $items->links() }}
                    </div>
                </noscript>

            @else
                <!-- Empty State -->
                <div class="max-w-2xl mx-auto bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2.5rem] p-12 lg:p-16 text-center space-y-6 my-16 shadow-sm">
                    <span class="text-[10px] uppercase tracking-[0.3em] font-bold text-[#8E877D]">
                        MAISON RÉSINE GALLERY
                    </span>
                    <h2 class="font-editorial text-3xl lg:text-4xl text-[#1C1917] font-light">
                        No pieces in this category yet.
                    </h2>
                    <p class="text-sm text-[#78716C] font-light leading-relaxed max-w-md mx-auto">
                        We are currently capturing new photography for this curation. Please explore our full collection or write to the atelier.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('gallery.index') }}" 
                           class="inline-block bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[10.5px] uppercase tracking-[0.25em] font-semibold px-8 py-3.5 rounded-full transition-all duration-300">
                            View All Pieces →
                        </a>
                    </div>
                </div>
            @endif

        </div>

        <!-- Fullscreen Reference Lightbox Modal (Matching User Screenshot) -->
        <div x-show="isOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.window.escape="closeLightbox()"
             @keydown.window.arrow-left="prevImage()"
             @keydown.window.arrow-right="nextImage()"
             class="fixed inset-0 z-50 flex flex-col items-center justify-center p-4 sm:p-6 lg:p-10 bg-black/90 backdrop-blur-xl"
             role="dialog"
             aria-modal="true"
             aria-label="Gallery Image Lightbox">

            <!-- Backdrop Click Close -->
            <div class="absolute inset-0" @click="closeLightbox()"></div>

            <!-- Close Button (Top Right) -->
            <button type="button" 
                    @click="closeLightbox()"
                    class="absolute top-6 right-6 z-30 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md flex items-center justify-center transition-all focus:outline-none"
                    aria-label="Close lightbox">
                <svg class="w-5 h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Navigation Arrow Left -->
            <button type="button" 
                    @click="prevImage()"
                    class="absolute left-6 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md flex items-center justify-center transition-all focus:outline-none"
                    aria-label="Previous image">
                <svg class="w-6 h-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <!-- Navigation Arrow Right -->
            <button type="button" 
                    @click="nextImage()"
                    class="absolute right-6 top-1/2 -translate-y-1/2 z-30 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md flex items-center justify-center transition-all focus:outline-none"
                    aria-label="Next image">
                <svg class="w-6 h-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <!-- Main Image & Title Container -->
            <div class="relative z-20 flex flex-col items-center justify-center max-w-full max-h-full" @click.stop>
                <template x-if="currentItem">
                    <img :src="currentItem.image" 
                         :alt="currentItem.alt" 
                         class="max-h-[78vh] sm:max-h-[82vh] max-w-full object-contain rounded-2xl sm:rounded-3xl shadow-2xl transition-all duration-300">
                </template>

                <!-- Clean Minimal Title Underneath Image (Matching Reference Screenshot) -->
                <div class="text-center pt-5 sm:pt-6">
                    <span class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-medium text-[#FAF8F5] opacity-90" 
                          x-text="currentItem?.title"></span>
                </div>
            </div>

        </div>

    </div>

    <!-- Alpine.js Lightbox & Infinite Scroll Script -->
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('galleryLightbox', (config) => ({
            isOpen: false,
            activeIndex: 0,
            items: config.items || [],
            nextPageUrl: config.nextPageUrl || null,
            hasMore: config.hasMore || false,
            isLoading: false,

            init() {
                this.setupInfiniteScroll();
            },

            setupInfiniteScroll() {
                this.$nextTick(() => {
                    const trigger = this.$refs.scrollTrigger;
                    if (!trigger) return;

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && this.hasMore && !this.isLoading) {
                                this.loadMore();
                            }
                        });
                    }, { rootMargin: '300px' });

                    observer.observe(trigger);
                });
            },

            loadMore() {
                if (!this.hasMore || this.isLoading || !this.nextPageUrl) return;

                this.isLoading = true;

                fetch(this.nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.nextPageUrl = data.next_page_url;
                    this.hasMore = data.has_more;

                    if (data.items && data.items.length > 0) {
                        this.items.push(...data.items);
                    }

                    const grid = document.getElementById('gallery-grid-container');
                    if (grid && data.html) {
                        grid.insertAdjacentHTML('beforeend', data.html);
                    }
                })
                .catch(err => {
                    console.error('Infinite scroll loading error:', err);
                })
                .finally(() => {
                    this.isLoading = false;
                });
            },

            get currentItem() {
                return this.items[this.activeIndex] || null;
            },

            openLightbox(index) {
                this.activeIndex = index;
                this.isOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeLightbox() {
                this.isOpen = false;
                document.body.style.overflow = '';
            },

            nextImage() {
                if (this.items.length === 0) return;
                this.activeIndex = (this.activeIndex + 1) % this.items.length;
            },

            prevImage() {
                if (this.items.length === 0) return;
                this.activeIndex = (this.activeIndex - 1 + this.items.length) % this.items.length;
            }
        }));
    });
    </script>

</x-app-layout>
