<x-app-layout :title="$page->meta_title ?? ($page->title . ' — Maison Résine')">
    <div class="min-h-[70vh] max-w-4xl mx-auto px-6 py-16 sm:py-24 space-y-12">
        
        {{-- Header block --}}
        <div class="space-y-4 border-b border-[#E5DFD3]/80 pb-8">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                <span>{{ $page->hero_badge ?? 'LEGAL NOTICE' }}</span>
            </div>
            <h1 class="font-editorial text-4xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-tight">
                {{ $page->title }}<span class="text-[#8E877D]">.</span>
            </h1>
            @if($page->hero_label)
                <p class="text-xs sm:text-sm text-[#8E877D] font-light tracking-wide mt-1">{{ $page->hero_label }}</p>
            @endif
        </div>

        {{-- Rich Content Body --}}
        <div class="prose prose-[#1C1917] max-w-none text-[14px] text-[#524C46] font-light leading-relaxed space-y-8
                    prose-h2:font-editorial prose-h2:text-2xl sm:prose-h2:text-3xl prose-h2:text-[#1C1917] prose-h2:font-light prose-h2:tracking-tight prose-h2:mt-10 prose-h2:mb-4
                    prose-p:text-[#524C46] prose-p:text-[14.5px] prose-p:leading-[1.85] prose-p:font-light
                    prose-a:text-[#1C1917] prose-a:underline prose-a:underline-offset-2 hover:prose-a:opacity-70
                    prose-strong:text-[#1C1917] prose-strong:font-semibold
                    prose-ul:list-disc prose-ul:pl-5 prose-ul:space-y-2
                    prose-li:my-1">
            {!! $page->content !!}
        </div>

        {{-- Footer details --}}
        <div class="pt-6 border-t border-[#E5DFD3]/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-[11px] text-[#8E877D]/80 font-sans">
            <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                </svg>
                Last updated: {{ $page->updated_at?->format('d F Y') ?? now()->format('d F Y') }}
            </span>
            <div class="flex items-center gap-4">
                <a href="{{ route('legal.shipping') }}" class="hover:text-[#1C1917] transition-colors {{ $page->slug === 'shipping' ? 'underline font-medium text-[#1C1917]' : '' }}">Shipping</a>
                <a href="{{ route('legal.return') }}" class="hover:text-[#1C1917] transition-colors {{ $page->slug === 'return' ? 'underline font-medium text-[#1C1917]' : '' }}">Returns & Cancellations</a>
                <a href="{{ route('legal.privacy') }}" class="hover:text-[#1C1917] transition-colors {{ $page->slug === 'privacy' ? 'underline font-medium text-[#1C1917]' : '' }}">Privacy</a>
                <a href="{{ route('legal.terms') }}" class="hover:text-[#1C1917] transition-colors {{ $page->slug === 'terms' ? 'underline font-medium text-[#1C1917]' : '' }}">Terms</a>
            </div>
        </div>

    </div>
</x-app-layout>
