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
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-8 sm:space-y-10 lg:space-y-14 w-full min-w-0">

            <!-- Hero Header Section -->
            <div class="max-w-3xl space-y-3 sm:space-y-4 animate-fade-up">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>GALLERY</span>
                </div>

                <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl xl:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    Pieces in place.
                </h1>

                <p class="text-[14px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
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
                <div class="max-w-2xl mx-auto bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[1.5rem] sm:rounded-[2.5rem] p-6 sm:p-12 lg:p-16 text-center space-y-5 sm:space-y-6 my-10 sm:my-16 shadow-sm">
                    <span class="text-[10px] uppercase tracking-[0.3em] font-bold text-[#8E877D]">
                        MAISON RÉSINE GALLERY
                    </span>
                    <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl text-[#1C1917] font-light">
                        No pieces in this category yet.
                    </h2>
                    <p class="text-xs sm:text-sm text-[#78716C] font-light leading-relaxed max-w-md mx-auto">
                        We are currently capturing new photography for this curation. Please explore our full collection or write to the atelier.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('gallery.index') }}" 
                           class="inline-block bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-[10px] sm:text-[10.5px] uppercase tracking-[0.25em] font-semibold px-6 sm:px-8 py-3 sm:py-3.5 rounded-full transition-all duration-300 whitespace-nowrap">
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
             @keydown.window.equal="zoom = Math.min(3.5, +(zoom + 0.5).toFixed(2))"
             @keydown.window.minus="zoom = Math.max(1, +(zoom - 0.5).toFixed(2)); if(zoom===1){panX=0;panY=0;}"
             @keydown.window.digit0="resetZoom()"
             @wheel.prevent="handleWheel($event)"
             @touchmove="if (zoom <= 1 || !$event.target.closest('[x-ref=\'lightboxImg\']')) $event.preventDefault()"
             class="fixed inset-0 z-50 flex flex-col items-center justify-center p-3 sm:p-6 lg:p-10 bg-black/90 backdrop-blur-xl select-none overflow-hidden overscroll-none touch-none"
             role="dialog"
             aria-modal="true"
             aria-label="Gallery Image Lightbox">

            <!-- Backdrop Click Close -->
            <div class="absolute inset-0 z-10" @click="closeLightbox()" @touchmove.prevent></div>

            <!-- Close Button (Top Right) -->
            <button type="button" 
                    @click="closeLightbox()"
                    class="absolute top-4 right-4 sm:top-6 sm:right-6 z-30 w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md flex items-center justify-center transition-all focus:outline-none cursor-pointer"
                    aria-label="Close lightbox">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Navigation Arrow Left -->
            <button type="button" 
                    @click="prevImage()"
                    class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 z-30 w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md flex items-center justify-center transition-all focus:outline-none cursor-pointer"
                    aria-label="Previous image">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <!-- Navigation Arrow Right -->
            <button type="button" 
                    @click="nextImage()"
                    class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 z-30 w-9 h-9 sm:w-12 sm:h-12 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md flex items-center justify-center transition-all focus:outline-none cursor-pointer"
                    aria-label="Next image">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <!-- Main Image & Title Container -->
            <div class="relative z-20 flex flex-col items-center justify-center max-w-full max-h-full select-none" @click.stop>
                <div class="relative overflow-hidden flex items-center justify-center rounded-2xl sm:rounded-3xl"
                     @mousedown="startDrag($event)"
                     @mousemove="onDrag($event)"
                     @mouseup="stopDrag()"
                     @mouseleave="stopDrag()"
                     @touchstart="startDrag($event)"
                     @touchmove="onDrag($event)"
                     @touchend="stopDrag()">
                    <template x-if="currentItem">
                        <img x-ref="lightboxImg"
                             :src="currentItem.image" 
                             :alt="currentItem.alt" 
                             draggable="false"
                             @click="onImageClick($event)"
                             @dblclick.prevent="toggleZoom($event)"
                             class="max-h-[75vh] sm:max-h-[79vh] max-w-[90vw] sm:max-w-[85vw] object-contain rounded-2xl sm:rounded-3xl shadow-2xl will-change-transform select-none"
                             :style="'transform: translate(' + panX + 'px, ' + panY + 'px) scale(' + zoom + '); transform-origin: center center; transition: ' + (isDragging ? 'none' : 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)') + '; cursor: ' + (zoom > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in') + ';'">
                    </template>
                </div>

                <!-- Clean Minimal Title Underneath Image (Matching Reference Screenshot) -->
                <div class="text-center pt-5 sm:pt-6 pointer-events-none">
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

            // Zero-clutter Zoom & Pan State
            zoom: 1,
            panX: 0,
            panY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            hasMoved: false,

            init() {
                this.setupInfiniteScroll();
                window.addEventListener('touchmove', (e) => {
                    if (this.isOpen && (this.zoom <= 1 || !e.target.closest('[x-ref="lightboxImg"]'))) {
                        e.preventDefault();
                    }
                }, { passive: false });
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

            toggleZoom() {
                if (this.zoom > 1) {
                    this.resetZoom();
                } else {
                    this.zoom = 2;
                    this.panX = 0;
                    this.panY = 0;
                }
            },

            resetZoom() {
                this.zoom = 1;
                this.panX = 0;
                this.panY = 0;
                this.isDragging = false;
                this.hasMoved = false;
            },

            handleWheel(e) {
                if (!this.isOpen) return;
                const delta = e.deltaY > 0 ? -0.25 : 0.25;
                const newZoom = Math.min(Math.max(1, +(this.zoom + delta).toFixed(2)), 3.5);
                if (newZoom !== this.zoom) {
                    this.zoom = newZoom;
                    if (this.zoom === 1) {
                        this.panX = 0;
                        this.panY = 0;
                    } else {
                        this.clampPan();
                    }
                }
            },

            startDrag(e) {
                if (this.zoom <= 1) return;
                this.isDragging = true;
                this.hasMoved = false;
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                this.startX = clientX - this.panX;
                this.startY = clientY - this.panY;
            },

            onDrag(e) {
                if (!this.isDragging || this.zoom <= 1) return;
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                const newX = clientX - this.startX;
                const newY = clientY - this.startY;
                if (Math.abs(newX - this.panX) > 3 || Math.abs(newY - this.panY) > 3) {
                    this.hasMoved = true;
                }
                this.panX = newX;
                this.panY = newY;
                this.clampPan();
            },

            stopDrag() {
                this.isDragging = false;
            },

            clampPan() {
                const img = this.$refs.lightboxImg;
                if (!img) return;
                const w = img.offsetWidth || 500;
                const h = img.offsetHeight || 500;
                const maxPanX = Math.max(0, ((w * this.zoom) - w) / 2 + 100);
                const maxPanY = Math.max(0, ((h * this.zoom) - h) / 2 + 100);
                this.panX = Math.max(-maxPanX, Math.min(maxPanX, this.panX));
                this.panY = Math.max(-maxPanY, Math.min(maxPanY, this.panY));
            },

            onImageClick(e) {
                if (this.hasMoved) {
                    this.hasMoved = false;
                    return;
                }
                this.toggleZoom();
            },

            openLightbox(index) {
                this.resetZoom();
                this.activeIndex = index;
                this.isOpen = true;
                document.documentElement.classList.add('overflow-hidden', 'touch-none');
                document.body.classList.add('overflow-hidden', 'touch-none');
                document.documentElement.style.overflow = 'hidden';
                document.documentElement.style.overscrollBehavior = 'none';
                document.body.style.overflow = 'hidden';
                document.body.style.overscrollBehavior = 'none';
                document.body.style.touchAction = 'none';
            },

            closeLightbox() {
                this.resetZoom();
                this.isOpen = false;
                document.documentElement.classList.remove('overflow-hidden', 'touch-none');
                document.body.classList.remove('overflow-hidden', 'touch-none');
                document.documentElement.style.overflow = '';
                document.documentElement.style.overscrollBehavior = '';
                document.body.style.overflow = '';
                document.body.style.overscrollBehavior = '';
                document.body.style.touchAction = '';
            },

            nextImage() {
                if (this.items.length === 0) return;
                this.resetZoom();
                this.activeIndex = (this.activeIndex + 1) % this.items.length;
            },

            prevImage() {
                if (this.items.length === 0) return;
                this.resetZoom();
                this.activeIndex = (this.activeIndex - 1 + this.items.length) % this.items.length;
            }
        }));
    });
    </script>

</x-app-layout>
