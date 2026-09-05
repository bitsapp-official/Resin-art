<x-account-layout 
    title="Recently Viewed" 
    header-title="Recently" 
    header-italic=" viewed." 
    header-subtitle="Masterpieces and bespoke creations you recently browsed in the atelier.">
    <div class="space-y-6">

        @if($recentlyViewed->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($recentlyViewed as $rv)
                    @php $p = $rv->product; @endphp
                    @if($p)
                        <div class="space-y-3 group">
                            <!-- Image Container with Badge -->
                            <a href="{{ route('shop.show', $p->slug) }}" class="block relative aspect-square bg-white rounded-[1.5rem] overflow-hidden border border-[#E6E1D7]/40 shadow-sm">
                                @if(!empty($p->badge_text))
                                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-[0.18em] text-[#1C1917]">
                                        {{ $p->badge_text }}
                                    </span>
                                @elseif($loop->first)
                                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-[0.18em] text-[#1C1917]">
                                        FEATURED
                                    </span>
                                @elseif($loop->iteration == 2)
                                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-[0.18em] text-[#1C1917]">
                                        FEATURED
                                    </span>
                                @elseif($loop->iteration == 3)
                                    <span class="absolute top-3 left-3 bg-[#1C1917] text-white px-3 py-1 rounded-full text-[9px] font-bold uppercase tracking-[0.18em]">
                                        BESTSELLER
                                    </span>
                                @endif

                                @if(!empty($p->images) && isset($p->images[0]))
                                    <img src="{{ $p->images[0] }}" alt="{{ $p->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @endif
                            </a>

                            <!-- Title, Price & Spec -->
                            <div class="space-y-0.5 pt-1">
                                <div class="flex items-baseline justify-between gap-2">
                                    <a href="{{ route('shop.show', $p->slug) }}" class="font-editorial text-lg text-[#1C1917] font-normal leading-snug line-clamp-1 hover:underline">
                                        {{ $p->name }}
                                    </a>
                                    <span class="text-xs font-semibold text-[#1C1917] shrink-0">
                                        ₹ {{ number_format($p->effective_price) }}
                                    </span>
                                </div>
                                <div class="text-[9.5px] uppercase tracking-[0.15em] font-semibold text-[#8E877D]">
                                    {{ $p->dimensions ?? $p->category?->name ?? 'BESPOKE RESIN CREATION' }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="glass rounded-[1.75rem] p-12 text-center text-xs text-[#78716C] font-light space-y-4">
                <p>You haven't viewed any products yet.</p>
                <div>
                    <a href="{{ route('shop.index') }}" 
                       class="inline-block bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-6 rounded-full transition-all duration-300 shadow-xs">
                        EXPLORE PRODUCTS
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-account-layout>
