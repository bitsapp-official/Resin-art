<x-app-layout title="Journal — Maison Résine">

    <!-- Structured Data (JSON-LD BlogPosting Schema) -->
    @php
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
            'datePublished' => $post->published_at ? $post->published_at->toIso8601String() : null,
            'dateModified' => $post->updated_at ? $post->updated_at->toIso8601String() : null,
            'author' => [
                '@type' => 'Person',
                'name' => $post->author_name ?? 'Atelier Artisan',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Maison Résine',
            ],
        ]);
    @endphp
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    <div class="py-10 lg:py-16">
        <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16 space-y-12 lg:space-y-16">

            <!-- Back to Journal Link -->
            <div class="animate-fade-up">
                <a href="{{ route('blog.index') }}" 
                   class="inline-flex items-center space-x-2 text-[11px] uppercase tracking-[0.25em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors group">
                    <svg class="w-3.5 h-3.5 stroke-[2] transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Back to Journal</span>
                </a>
            </div>

            <!-- Centered Header -->
            <div class="max-w-4xl mx-auto text-center space-y-5 animate-fade-up delay-100">
                <div class="flex items-center justify-center space-x-2 text-[11px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                    <a href="{{ route('blog.index', ['category' => $post->category?->slug]) }}" class="hover:text-[#1C1917] transition-colors">
                        {{ $post->category?->name ?? 'Journal' }}
                    </a>
                    <span>·</span>
                    <span>{{ $post->reading_time ?? '5 MIN' }}</span>
                </div>

                <h1 class="font-editorial text-4xl sm:text-5xl lg:text-[60px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    {{ $post->title }}
                </h1>

                @if($post->published_at)
                    <div class="text-[11px] font-mono uppercase tracking-[0.2em] text-[#8E877D]">
                        · {{ strtoupper($post->published_at->format('d F Y')) }}
                    </div>
                @endif
            </div>

            <!-- Featured Image -->
            @if(!empty($post->featured_image))
                <div class="max-w-5xl mx-auto rounded-[2rem] overflow-hidden border border-[#E5DFD3] shadow-[0_15px_45px_rgba(0,0,0,0.04)] bg-[oklch(98.5%_0.008_85)] animate-fade-up delay-150">
                    <img src="{{ asset('storage/' . $post->featured_image) }}" 
                         alt="{{ $post->title }}" 
                         class="w-full h-[400px] sm:h-[520px] lg:h-[620px] object-cover"
                         loading="eager"
                         decoding="async">
                </div>
            @endif

            <!-- Article Body Typography Container (Optimal Reading Width ~720px) -->
            <div class="max-w-[720px] mx-auto space-y-8 animate-fade-up delay-200">

                <!-- Excerpt Lead Paragraph -->
                @if(!empty($post->excerpt))
                    <p class="font-editorial text-xl sm:text-2xl text-[#1C1917] italic leading-relaxed border-l-2 border-[#D9D2C5] pl-6 py-1">
                        {{ $post->excerpt }}
                    </p>
                @endif

                <!-- Rich Article Content -->
                <div class="prose prose-stone max-w-none text-[16px] text-[#44403C] font-light leading-[1.85] space-y-6">
                    {!! $post->content !!}
                </div>

                <!-- Author Signature Footer Card -->
                <div class="pt-8 border-t border-[#E5DFD3] flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-[#1C1917] text-[#FAF8F5] font-editorial italic font-light text-lg flex items-center justify-center">
                            {{ substr($post->author_name ?? 'M', 0, 1) }}
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">Written By</span>
                            <span class="font-editorial text-base text-[#1C1917] font-medium">{{ $post->author_name ?? 'Elène Marchand' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('blog.index') }}" class="text-[10.5px] uppercase tracking-[0.22em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors">
                        ← All Journal Entries
                    </a>
                </div>

            </div>

            <!-- Related Posts Section -->
            @include('blog.components.related-posts', ['posts' => $relatedPosts])

        </div>
    </div>

</x-app-layout>
