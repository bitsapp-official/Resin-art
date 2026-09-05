<x-app-layout title="Collections — Maison Résine Atelier">
<div class="min-h-screen bg-transparent">
    <div class="max-w-[1240px] mx-auto px-6 lg:px-10 pt-12 pb-32 space-y-24 md:space-y-36">

        {{-- ── HERO HEADER SECTION ──────────────────────────────── --}}
        <div class="space-y-4 max-w-3xl pt-6">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.3em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#C5BEB2] inline-block"></span>
                <span>COLLECTIONS</span>
            </div>

            <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                Four ways to <em class="italic font-normal">live with resin.</em>
            </h1>

            <p class="text-[14px] sm:text-[15px] text-[#78716C] font-light leading-relaxed max-w-xl">
                Each collection is released once. Once poured, once signed, never reissued.
            </p>
        </div>

        {{-- ── COLLECTIONS LOOP (EQUAL 2-COLUMN GRID) ────────────────── --}}
        <div class="space-y-28 md:space-y-40">
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
                    
                    {{-- Image Column --}}
                    <div class="order-1 {{ $isEven ? 'lg:order-1' : 'lg:order-2' }}">
                        <a href="{{ route('collections.show', $collection->slug) }}" 
                           class="block group overflow-hidden rounded-[2rem] aspect-[4/3] bg-[#EBE6DD] relative shadow-xs">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $collection->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]"
                                 loading="lazy">
                        </a>
                    </div>
                    
                    {{-- Text Column --}}
                    <div class="order-2 {{ $isEven ? 'lg:order-2 lg:pl-8' : 'lg:order-1 lg:pr-8' }} flex flex-col items-start space-y-4">
                        
                        <div class="flex items-center gap-2 text-[9.5px] uppercase tracking-[0.25em] text-[#8E877D] font-bold">
                            <span>N°0{{ $loop->iteration }}</span>
                            <span class="text-[#C5BEB2]">•</span>
                            <span>COLLECTION</span>
                        </div>
                        
                        <h2 class="font-editorial text-4xl sm:text-5xl lg:text-[50px] font-light tracking-tight leading-[1.05] text-[#1C1917] hover:text-[#AD9575] transition-colors duration-300">
                            <a href="{{ route('collections.show', $collection->slug) }}">{{ $collection->name }}</a>
                        </h2>
                        
                        @if(!empty($collection->subtitle))
                            <p class="text-[10px] uppercase tracking-[0.22em] font-medium text-[#8E877D] pt-0.5">
                                {{ $collection->subtitle }}
                            </p>
                        @endif

                        <p class="text-[14px] sm:text-[14.5px] text-[#524C46] font-light leading-relaxed max-w-md pt-1">
                            {{ $collection->effective_short_description }}
                        </p>

                        <div class="pt-3">
                            <a href="{{ route('collections.show', $collection->slug) }}" 
                               class="inline-flex items-center gap-2 rounded-full bg-[#1C1917] px-8 py-3.5 text-[10.5px] font-medium uppercase tracking-[0.26em] text-[#FAF8F5] hover:bg-[#AD9575] transition-colors duration-300 shadow-xs group">
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
