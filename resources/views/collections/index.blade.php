<x-app-layout title="Collections — Maison Résine Atelier">
<div class="min-h-screen bg-transparent w-full min-w-0">
    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 lg:px-10 pt-6 sm:pt-10 pb-16 sm:pb-28 space-y-12 sm:space-y-16 lg:space-y-24 w-full min-w-0">

        {{-- ── BREADCRUMB ──────────────────────────────────────── --}}
        <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-[10px] uppercase tracking-[0.16em] sm:tracking-[0.2em] text-[#8E877D] font-medium">
            <a href="{{ url('/') }}" class="hover:text-[#1C1917] transition-colors shrink-0">Home</a>
            <span>·</span>
            <span class="text-[#1C1917] font-semibold">Collections</span>
        </nav>

        {{-- ── HERO HEADER SECTION ──────────────────────────────── --}}
        <div class="space-y-3 sm:space-y-4 max-w-3xl">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] sm:tracking-[0.3em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#C5BEB2] inline-block"></span>
                <span>COLLECTIONS</span>
                <span class="text-[#C5BEB2]">•</span>
                <span class="text-[#1C1917] font-semibold">{{ $collections->count() }} SERIES</span>
            </div>

            <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl xl:text-[68px] text-[#1C1917] font-light leading-[1.1] sm:leading-[1.08] tracking-tight">
                Four ways to <em class="italic font-normal">live with resin.</em>
            </h1>

            <p class="text-xs sm:text-[14px] md:text-[15px] text-[#78716C] font-light leading-relaxed max-w-xl pt-0.5">
                Each collection is released once. Once poured, once signed, never reissued.
            </p>
        </div>

        {{-- ── COLLECTIONS LOOP (RESPONSIVE 2-COLUMN GRID) ────────────── --}}
        <div class="space-y-14 sm:space-y-20 lg:space-y-28">
            @foreach($collections as $collection)
                @php
                    $isEven = $loop->index % 2 == 0;
                    $rawImg = $collection->cover_image ?: $collection->image;
                    if (!empty($rawImg)) {
                        if (Illuminate\Support\Str::startsWith($rawImg, ['http://', 'https://']) || Illuminate\Support\Str::startsWith($rawImg, '/')) {
                            $imageUrl = $rawImg;
                        } else {
                            $imageUrl = asset('storage/' . $rawImg);
                        }
                    } else {
                        $imageUrl = 'https://images.unsplash.com/photo-1541701494587-cb58502866ab?auto=format&fit=crop&w=1200&q=80';
                    }
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-10 lg:gap-16 xl:gap-20 items-center">
                    
                    {{-- Image Column --}}
                    <div class="order-1 {{ $isEven ? 'lg:order-1' : 'lg:order-2' }}">
                        <a href="{{ route('collections.show', $collection->slug) }}" 
                           class="block group overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] aspect-[16/10] sm:aspect-[4/3] bg-[#EBE6DD] relative shadow-xs border border-[#DFD9CE]/60">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $collection->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]"
                                 loading="lazy">

                            {{-- Piece Count Glass Badge --}}
                            <div class="glass-pill absolute top-3 left-3 sm:top-4 sm:left-4 text-[#1C1917] text-[8.5px] sm:text-[9px] uppercase tracking-[0.16em] sm:tracking-[0.18em] font-semibold px-3 sm:px-3.5 py-1 sm:py-1.5 rounded-full pointer-events-none shadow-xs">
                                {{ $collection->products_count }} {{ Str::plural('PIECE', $collection->products_count) }}
                            </div>
                        </a>
                    </div>
                    
                    {{-- Text Column --}}
                    <div class="order-2 {{ $isEven ? 'lg:order-2 lg:pl-6 xl:lg:pl-8' : 'lg:order-1 lg:pr-6 xl:lg:pr-8' }} flex flex-col items-start space-y-2.5 sm:space-y-3.5">
                        
                        <div class="flex items-center gap-2 text-[9px] sm:text-[9.5px] uppercase tracking-[0.2em] sm:tracking-[0.25em] text-[#8E877D] font-bold">
                            <span>N°0{{ $loop->iteration }}</span>
                            <span class="text-[#C5BEB2]">•</span>
                            <span>CURATED SERIES</span>
                        </div>
                        
                        <h2 class="font-editorial text-2xl sm:text-3xl lg:text-4xl xl:text-[46px] font-light tracking-tight leading-[1.12] sm:leading-[1.08] text-[#1C1917] hover:text-[#AD9575] transition-colors duration-300">
                            <a href="{{ route('collections.show', $collection->slug) }}">{{ $collection->name }}</a>
                        </h2>
                        
                        @if(!empty($collection->subtitle))
                            <p class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.18em] sm:tracking-[0.22em] font-medium text-[#8E877D] pt-0.5">
                                {{ $collection->subtitle }}
                            </p>
                        @endif

                        <p class="text-xs sm:text-[14px] text-[#524C46] font-light leading-relaxed max-w-lg pt-0.5">
                            {{ $collection->effective_short_description }}
                        </p>

                        <div class="pt-2 sm:pt-3 w-full sm:w-auto">
                            <a href="{{ route('collections.show', $collection->slug) }}" 
                               class="inline-flex items-center justify-center gap-2 rounded-full bg-[#1C1917] px-6 sm:px-8 py-3 sm:py-3.5 text-[10px] sm:text-[10.5px] font-medium uppercase tracking-[0.2em] sm:tracking-[0.26em] text-[#FAF8F5] hover:bg-[#AD9575] transition-colors duration-300 shadow-xs group w-full sm:w-auto text-center">
                                <span>SHOP THE COLLECTION</span>
                                <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
</x-app-layout>
