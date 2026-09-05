<x-app-layout title="Privacy Policy — Maison Résine">
    <div class="min-h-[70vh] max-w-4xl mx-auto px-6 py-16 sm:py-24 space-y-12">
        
        <div class="space-y-4 border-b border-[#E5DFD3]/80 pb-8">
            <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.28em] font-medium text-[#8E877D]">
                <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                <span>DATA INTEGRITY</span>
            </div>
            <h1 class="font-editorial text-4xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-tight">
                Privacy & data <em class="italic font-normal">protection.</em>
            </h1>
        </div>

        <div class="prose prose-[#1C1917] max-w-none text-[14px] text-[#524C46] font-light leading-relaxed space-y-8">
            {!! \App\Models\SiteSetting::get('privacy_policy', '<p>Privacy policy content will appear here.</p>') !!}
        </div>

    </div>
</x-app-layout>
