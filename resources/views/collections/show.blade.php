<x-app-layout :title="$collection->meta_title ?? ($collection->name . ' Collection — Maison Résine Atelier')">
    @push('head')
        <meta name="description" content="{{ $collection->meta_description ?? ($collection->short_description ?? Str::limit(strip_tags($collection->description), 160)) }}">
        <meta property="og:title" content="{{ $collection->meta_title ?? ($collection->name . ' Collection — Maison Résine') }}">
        <meta property="og:description" content="{{ $collection->meta_description ?? ($collection->short_description ?? Str::limit(strip_tags($collection->description), 160)) }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ route('collections.show', $collection->slug) }}">
        @if($collection->effective_cover_image)
            <meta property="og:image" content="{{ Str::startsWith($collection->effective_cover_image, ['http://', 'https://']) ? $collection->effective_cover_image : asset('storage/' . $collection->effective_cover_image) }}">
        @endif
    @endpush

    <div class="min-h-screen bg-transparent">
        <div class="max-w-[1360px] mx-auto px-6 lg:px-12 xl:px-16 pt-12 pb-28 space-y-12">

            {{-- ── EDITORIAL HERO HEADER SECTION ──────────────────────────────── --}}
            <div class="pb-10 space-y-4 animate-fade-up">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>{{ !empty($collection->subtitle) ? $collection->subtitle : 'CURATED COLLECTION' }}</span>
                </div>

                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[70px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    {{ $collection->name }}
                </h1>

                @if(!empty($collection->description))
                    <div class="text-[14.5px] sm:text-[15.5px] text-[#78716C] font-light leading-relaxed max-w-3xl pt-1">
                        {!! $collection->description !!}
                    </div>
                @endif
            </div>

            {{-- ── PRODUCTS SECTION HEADER ──────────────────────────────── --}}
            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span>COLLECTION PIECES</span>
                    <span class="text-[#D9D2C5]">•</span>
                    <span>({{ $products->total() }} {{ Str::plural('ITEM', $products->total()) }})</span>
                </div>
            </div>

            {{-- ── PRODUCTS GRID (MATCHING SHOP CATALOG) ──────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12 pt-2 pb-16">
                @forelse($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full py-20 text-center glass rounded-[2.25rem] p-12 sm:p-16 space-y-5 border border-[#E5DFD3]/80">
                        <div class="w-12 h-12 rounded-full bg-white/80 border border-[#E5DFD3] flex items-center justify-center mx-auto text-[#8E877D] shadow-xs">
                            <svg class="w-5 h-5 stroke-[1.5] text-[#8E877D]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                            </svg>
                        </div>
                        <h3 class="font-editorial text-3xl text-[#1C1917] font-light tracking-tight">This collection is currently being curated.</h3>
                        <p class="text-xs text-[#78716C] max-w-md mx-auto font-light leading-relaxed">
                            New one-of-a-kind resin art pieces are actively being poured in our studio for this series.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-full bg-[#1C1917] px-8 py-3.5 text-[10.5px] uppercase tracking-[0.24em] font-medium text-[#FAF8F5] hover:bg-[#AD9575] transition-colors shadow-xs">
                                EXPLORE AVAILABLE SHOP PIECES
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- ── PAGINATION ────────────────────────────────── --}}
            @if($products->hasPages())
                <div class="pt-8 border-t border-[#E5DFD3]">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
