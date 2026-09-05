<x-account-layout 
    title="Custom Requests" 
    header-title="Custom" 
    header-italic=" requests." 
    header-subtitle="Every bespoke fluid resin &amp; woodwork piece crafted to your specifications.">

    <div x-data="{
        nextPageUrl: '{{ $customRequests->nextPageUrl() }}',
        loading: false,
        hasMore: {{ $customRequests->hasMorePages() ? 'true' : 'false' }},
        init() {
            if (!this.hasMore || !this.nextPageUrl) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.loading && this.hasMore) {
                        this.loadMore();
                    }
                });
            }, {
                rootMargin: '300px'
            });

            if (this.$refs.sentinel) {
                observer.observe(this.$refs.sentinel);
            }
        },
        async loadMore() {
            if (!this.nextPageUrl || this.loading) return;
            this.loading = true;

            try {
                const response = await fetch(this.nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const htmlText = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, 'text/html');

                const newCards = doc.querySelectorAll('#custom-requests-list > div.custom-request-card');
                const container = document.getElementById('custom-requests-list');

                newCards.forEach(card => {
                    container.appendChild(card);
                });

                const newNextUrlInput = doc.querySelector('#next-page-url-data');
                if (newNextUrlInput && newNextUrlInput.value) {
                    this.nextPageUrl = newNextUrlInput.value;
                    this.hasMore = true;
                } else {
                    this.nextPageUrl = null;
                    this.hasMore = false;
                }
            } catch (error) {
                console.error('Error loading more requests:', error);
                this.hasMore = false;
            } finally {
                this.loading = false;
            }
        }
    }" class="space-y-6">
        
        <!-- Top Action Header -->
        <div class="flex items-center justify-between pb-1">
            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D]">
                REQUEST HISTORY ({{ $customRequests->total() }})
            </span>
            <a href="{{ route('custom.index') }}" 
               class="inline-flex items-center justify-center border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-2.5 rounded-full transition-all duration-300 shadow-sm shrink-0">
                + SUBMIT NEW REQUEST
            </a>
        </div>

        @if($customRequests->count() > 0)
            <!-- Infinite Scroll Container -->
            <div id="custom-requests-list" class="space-y-5">
                @foreach($customRequests as $req)
                    @php
                        $statusEnum = $req->status instanceof \App\Enums\CustomRequestStatus 
                            ? $req->status 
                            : \App\Enums\CustomRequestStatus::tryFrom($req->status) ?? \App\Enums\CustomRequestStatus::SUBMITTED;
                        
                        $stepIndex = $statusEnum->stepIndex();
                        $customerLabel = $statusEnum->customerLabel();
                        $isClosed = in_array($statusEnum, [\App\Enums\CustomRequestStatus::DECLINED, \App\Enums\CustomRequestStatus::EXPIRED]);
                        $invoice = $req->invoice;
                    @endphp

                    <div class="custom-request-card rounded-[2rem] p-6 sm:p-7 space-y-4 shadow-[0_20px_50px_rgba(28,25,23,0.04)] border-none" style="background: oklch(98.5% .008 85);">
                        
                        <!-- Header Row: Reference, Date, Status Badge & Price -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-[#E6E1D7]/60 pb-3 gap-2 text-xs">
                            <div>
                                <span class="font-editorial text-xl text-[#1C1917] block">{{ $req->public_reference }}</span>
                                <span class="text-[10px] uppercase tracking-[0.15em] text-[#8E877D] block mt-0.5">
                                    Submitted on {{ ($req->submitted_at ?? $req->created_at)->format('d M Y') }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-3">
                                @if($isClosed)
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-[9px] uppercase font-semibold tracking-widest border border-red-200">
                                        {{ $customerLabel }}
                                    </span>
                                @elseif($stepIndex >= 5)
                                    <span class="bg-emerald-100 text-emerald-900 px-3 py-1 rounded-full text-[9px] uppercase font-semibold tracking-widest border border-emerald-300">
                                        {{ $customerLabel }}
                                    </span>
                                @elseif($stepIndex >= 3)
                                    <span class="bg-[#1C1917] text-white px-3 py-1 rounded-full text-[9px] uppercase font-semibold tracking-widest">
                                        {{ $customerLabel }}
                                    </span>
                                @else
                                    <span class="bg-[#EFECE6] text-[#1C1917] border border-[#DDD6CA] px-3 py-1 rounded-full text-[9px] uppercase font-semibold tracking-widest">
                                        {{ $customerLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- 5-Segment Progress Bar (Universal Flexbox) -->
                        @if(!$isClosed)
                            <div class="py-1">
                                <div class="flex items-center gap-1.5 w-full">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="flex-1 h-1.5 rounded-full transition-colors {{ $i <= $stepIndex ? 'bg-[#1C1917]' : 'bg-[#E5DFD3]' }}"></div>
                                    @endfor
                                </div>
                            </div>
                        @endif

                        <!-- Requirement Description & Reference Images Preview -->
                        <div class="space-y-2 text-xs">
                            <p class="text-[#57534E] font-normal leading-relaxed line-clamp-2" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                                {{ $req->idea_description }}
                            </p>

                            @if($req->images && $req->images->count() > 0)
                                <div class="flex items-center gap-2 pt-1">
                                    <span class="text-[10px] uppercase tracking-wider text-[#8E877D] font-medium mr-1">References:</span>
                                    @foreach($req->images as $img)
                                        <a href="{{ asset('storage/' . $img->file_path) }}" 
                                           target="_blank" 
                                           class="w-10 h-10 rounded-lg overflow-hidden border border-[#E6E1D7] bg-white shrink-0 hover:scale-105 transition-transform inline-block"
                                           title="View reference photo">
                                            <img src="{{ asset('storage/' . $img->file_path) }}" 
                                                 alt="Reference" 
                                                 class="w-full h-full object-cover">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-3 border-t border-[#E6E1D7]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="text-[10px] text-[#8E877D] font-light">
                                Destination: <span class="text-[#1C1917] font-medium">{{ $req->phone ?: 'Custom Delivery' }}</span>
                                @if($req->whatsapp)
                                    &bull; WhatsApp: <span class="text-[#1C1917] font-medium">{{ $req->whatsapp }}</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                @if($invoice)
                                    <a href="{{ route('admin.invoices.pdf', $invoice) }}" 
                                       target="_blank"
                                       class="border border-[#DFD9CE] text-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[9.5px] uppercase tracking-[0.2em] font-semibold px-4 py-2 rounded-full transition-all duration-300">
                                        Download Invoice PDF
                                    </a>
                                @endif

                                @php
                                    $whatsappNumber = config('atelier.whatsapp.number', '919876543210');
                                    $msg = rawurlencode("Hello Maison Résine, I am inquiring regarding my custom request ({$req->public_reference}).");
                                @endphp
                                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $msg }}" 
                                   target="_blank"
                                   class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors">
                                    Contact Us &rarr;
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Hidden Next Page URL Tracker -->
            <input type="hidden" id="next-page-url-data" value="{{ $customRequests->nextPageUrl() }}">

            <!-- Infinite Scroll Sentinel & Loading Indicator -->
            <div x-ref="sentinel" class="py-6 text-center">
                <div x-show="loading" class="inline-flex items-center space-x-2 text-xs text-[#8E877D] font-light">
                    <svg class="animate-spin h-4 w-4 text-[#1C1917]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Loading more requests...</span>
                </div>
                <div x-show="!hasMore && !loading && {{ $customRequests->total() > 5 ? 'true' : 'false' }}" class="text-[10px] uppercase tracking-[0.2em] text-[#A8A29E] font-medium">
                    All requests loaded
                </div>
            </div>
        @else
            <div class="text-center py-16 rounded-[2rem] p-8 space-y-4 shadow-[0_20px_50px_rgba(28,25,23,0.04)] glass">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#FAF8F5] border border-[#E6E1D7] flex items-center justify-center text-[#8E877D]">
                    <svg class="w-6 h-6 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l2.846-.813a3.75 3.75 0 001.072-.516l8.808-8.808a2.25 2.25 0 000-3.182l-1.06-1.06a2.25 2.25 0 00-3.182 0l-8.808 8.808a3.75 3.75 0 00-.516 1.072z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold text-[#1C1917]">No custom requests submitted yet</h3>
                    <p class="text-xs text-[#78716C] font-light max-w-sm mx-auto">
                        Looking for a bespoke ocean table, resin clock, or wall art? Submit your vision and receive a personalized quote.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('custom.index') }}" 
                       class="inline-block bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-7 rounded-full transition-all duration-300 shadow-xs">
                        + COMMISSION BESPOKE ARTWORK
                    </a>
                </div>
            </div>
        @endif

    </div>

</x-account-layout>
