<x-app-layout title="Journal — Solitaire Fine Jewels Atelier">

    <div class="pt-2 sm:pt-4 pb-12 sm:pb-16 lg:pb-20">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 space-y-6 sm:space-y-8 lg:space-y-10 w-full min-w-0">

            <!-- Hero Header Section -->
            <div class="max-w-3xl space-y-2.5 sm:space-y-3 animate-fade-up">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>JOURNAL</span>
                </div>

                <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl xl:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    Notes from <em class="italic font-normal">the atelier.</em>
                </h1>

                <p class="text-[14px] sm:text-base text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                    Essays on craftsmanship, gemstones, gold and the timeless art of fine jewellery.
                </p>
            </div>

            <!-- Category Filter Bar -->
            <div class="animate-fade-up delay-100">
                @include('blog.components.category-filter', [
                    'categories' => $categories,
                    'selectedCategorySlug' => $selectedCategorySlug
                ])
            </div>

            <!-- Main Content Area -->
            @if($posts->count() > 0)

                <!-- Featured Card (Only on Page 1 when no category filter is selected) -->
                @if($featuredPost)
                    <div class="animate-fade-up delay-150">
                        @include('blog.components.post-card', ['post' => $featuredPost, 'featured' => true])
                    </div>
                @endif

                <!-- 3-Column Articles Grid -->
                @if($gridPosts->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 pt-4 animate-fade-up delay-200">
                        @foreach($gridPosts as $post)
                            @include('blog.components.post-card', ['post' => $post, 'featured' => false])
                        @endforeach
                    </div>
                @endif

                <!-- Pagination Links -->
                <div class="pt-8 flex justify-center">
                    {{ $posts->links() }}
                </div>

            @else

                <!-- Elegant Empty State -->
                <div class="max-w-2xl mx-auto bg-[oklch(98.5%_0.008_85)] border border-[#E5DFD3] rounded-[2rem] p-12 lg:p-16 text-center space-y-5 my-12 shadow-sm animate-fade-in">
                    <span class="text-[10px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                        Solitaire Fine Jewels Journal
                    </span>
                    <h2 class="font-editorial text-3xl lg:text-4xl text-[#1C1917] font-light">
                        No pieces in this journal category yet.
                    </h2>
                    <p class="text-sm text-[#78716C] font-light leading-relaxed max-w-md mx-auto">
                        The atelier team is preparing new essays on fine jewellery, diamonds, and precious metals.
                    </p>
                    <div class="pt-3">
                        <a href="{{ route('blog.index') }}" 
                           class="inline-block bg-[#1C1917] hover:bg-[#2D2825] text-[#FAF8F5] text-xs uppercase tracking-[0.2em] font-medium px-8 py-3 rounded-full transition-all duration-300">
                            View All Journal Entries →
                        </a>
                    </div>
                </div>

            @endif

        </div>
    </div>

</x-app-layout>
