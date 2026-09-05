<!-- Category Filter Navigation Pills -->
<div class="flex items-center space-x-2.5 overflow-x-auto py-2 scrollbar-none">
    <!-- ALL Pill -->
    <a href="{{ route('blog.index') }}" 
       class="px-5 py-2 rounded-full text-[11px] font-semibold uppercase tracking-[0.2em] transition-all duration-300 shrink-0 
              {{ empty($selectedCategorySlug) ? 'bg-[#1C1917] text-[#FAF8F5] shadow-sm' : 'bg-[#FAF8F5] border border-[#E5DFD3] text-[#78716C] hover:text-[#1C1917] hover:border-[#1C1917]' }}">
        All
    </a>

    @foreach($categories as $cat)
        <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" 
           class="px-5 py-2 rounded-full text-[11px] font-semibold uppercase tracking-[0.2em] transition-all duration-300 shrink-0 
                  {{ $selectedCategorySlug === $cat->slug ? 'bg-[#1C1917] text-[#FAF8F5] shadow-sm' : 'bg-[#FAF8F5] border border-[#E5DFD3] text-[#78716C] hover:text-[#1C1917] hover:border-[#1C1917]' }}">
            {{ $cat->name }}
        </a>
    @endforeach
</div>
