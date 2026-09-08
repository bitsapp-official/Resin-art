<x-app-layout title="Terms & Conditions — Maison Résine">
    <div class="min-h-[70vh] max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-20 space-y-8 sm:space-y-12 w-full min-w-0">
        
        <div class="space-y-3 sm:space-y-4 border-b border-[#E5DFD3]/80 pb-6 sm:pb-8">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                <span>ATELIER AGREEMENTS</span>
            </div>
            <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-tight">
                Terms & <em class="italic font-normal">conditions.</em>
            </h1>
        </div>

        <div class="prose prose-[#1C1917] max-w-none text-[14px] text-[#524C46] font-light leading-relaxed space-y-8">
            {!! \App\Models\SiteSetting::get('terms_and_conditions', '<p>Terms and conditions content will appear here.</p>') !!}
        </div>

    </div>
</x-app-layout>
