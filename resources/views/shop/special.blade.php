<x-app-layout :title="$title . ' — Maison Résine Atelier'">
<div class="min-h-screen bg-transparent">
    <div class="max-w-[1360px] mx-auto px-6 lg:px-12 xl:px-16 pt-8 pb-24">

        {{-- ── HERO HEADER SECTION ──────────────────────────────── --}}
        <div class="py-10 border-b border-[#E5DFD3]/80 space-y-3.5 animate-fade-up">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                <span>{{ $eyebrow }}</span>
            </div>

            <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                {!! $title !!}
            </h1>

            <p class="text-[14px] sm:text-[15px] text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                {{ $subtitle }}
            </p>
        </div>

        {{-- ── FILTER & SORT BAR (CLEAN) ────────────────────────── --}}
        <form method="GET" class="flex flex-wrap items-center justify-between gap-4 py-4 mb-6">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                <span>AVAILABLE PIECES</span>
                <span class="text-[#D9D2C5]">•</span>
                <span>({{ $products->total() }} {{ Str::plural('ITEM', $products->total()) }})</span>
            </div>

            <div class="relative">
                <select name="sort" onchange="this.form.submit()"
                        class="appearance-none [appearance:none] [-webkit-appearance:none] [-moz-appearance:none] bg-white/80 border border-[#DFD9CE] rounded-full pl-5 pr-10 py-2.5 text-[10.5px] uppercase tracking-[0.18em] font-medium text-[#1C1917] hover:border-[#1C1917] focus:outline-none cursor-pointer transition-all bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%231C1917%22%20stroke-width%3D%222%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22M19%209l-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[length:12px_12px] bg-[right_14px_center] bg-no-repeat">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Sort: Price Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Sort: Price High to Low</option>
                </select>
            </div>
        </form>

        {{-- ── PRODUCT GRID ─────────────────────────────────────── --}}
        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12 pt-4 pb-16">
                @foreach($products as $product)
                    <x-product-card :product="$product" :wishlistIds="$wishlistIds" />
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($products->hasPages())
                <div class="pt-8 border-t border-[#E5DFD3]">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="py-24 text-center border border-dashed border-[#DFD9CE] rounded-[2.25rem] bg-white/40 p-12 space-y-4">
                <div class="w-12 h-12 rounded-full bg-white/85 border border-[#E5DFD3] flex items-center justify-center mx-auto text-[#8E877D] shadow-xs">
                    <svg class="w-5 h-5 stroke-[1.5] text-[#8E877D]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <h3 class="font-editorial text-2xl text-[#1C1917]">No pieces are currently listed under this selection.</h3>
                <p class="text-xs text-[#78716C] max-w-md mx-auto font-light leading-relaxed">
                    Explore our main boutique gallery for other active resin art listings.
                </p>
                <div class="pt-2">
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 rounded-full bg-[#1C1917] px-8 py-3.5 text-[10.5px] uppercase tracking-[0.24em] font-medium text-[#FAF8F5] hover:bg-[#AD9575] transition-colors shadow-xs">
                        EXPLORE MAIN SHOP
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
</x-app-layout>
