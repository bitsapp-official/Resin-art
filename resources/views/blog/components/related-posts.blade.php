
@if(isset($posts) && $posts->count() > 0)
    <div class="py-16 border-t border-[#E5DFD3] mt-20 space-y-10">
        <div class="space-y-2">
            <span class="text-[10px] uppercase tracking-[0.25em] font-semibold text-[#8E877D] block">More Essays</span>
            <h2 class="font-editorial text-3xl sm:text-4xl lg:text-[42px] text-[#1C1917] font-light">
                Continue reading.
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-10">
            @foreach($posts as $relatedPost)
                @include('blog.components.post-card', ['post' => $relatedPost, 'featured' => false])
            @endforeach
        </div>
    </div>
@endif
