<x-account-layout title="Overview Dashboard">
    <div class="space-y-8">
        
        <!-- 1. Top Row: 4 Metric Stat Cards (Balanced 4-Column Grid) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
            
            <!-- Card 1: Orders -->
            <a href="{{ route('account.orders.index') }}" class="glass rounded-[1.75rem] p-7 hover:bg-white/80 transition-all block">
                <div class="font-editorial text-4xl text-[#1C1917] font-light leading-none mb-2">
                    {{ $ordersCount }}
                </div>
                <div class="text-[9px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">
                    ORDERS
                </div>
            </a>

            <!-- Card 2: Custom Requests -->
            <a href="{{ route('account.custom-requests.index') }}" class="glass rounded-[1.75rem] p-7 hover:bg-white/80 transition-all block">
                <div class="font-editorial text-4xl text-[#1C1917] font-light leading-none mb-2">
                    {{ $customRequestsCount }}
                </div>
                <div class="text-[9px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">
                    CUSTOM REQUESTS
                </div>
            </a>

            <!-- Card 3: Wishlist -->
            <a href="{{ route('wishlist.index') }}" class="glass rounded-[1.75rem] p-7 hover:bg-white/80 transition-all block">
                <div class="font-editorial text-4xl text-[#1C1917] font-light leading-none mb-2">
                    {{ $wishlistCount }}
                </div>
                <div class="text-[9px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">
                    WISHLIST
                </div>
            </a>

            <!-- Card 4: Unread Notifications -->
            <a href="{{ route('account.notifications.index') }}" class="glass rounded-[1.75rem] p-7 hover:bg-white/80 transition-all block">
                <div class="font-editorial text-4xl text-[#1C1917] font-light leading-none mb-2">
                    {{ $unreadNotificationsCount }}
                </div>
                <div class="text-[9px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">
                    UNREAD
                </div>
            </a>

        </div>

        <!-- 2. Middle Row: Latest Order & Notifications Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start w-full">
            
            <!-- Left Column: LATEST ORDER Card (7-Step Progress Bar) -->
            <div class="w-full lg:col-span-7 glass rounded-[1.75rem] p-7 space-y-6">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.2em]">
                    <span class="text-[#8E877D]">LATEST ORDER</span>
                    <a href="{{ route('account.orders.index') }}" class="text-[#8E877D] hover:text-[#1C1917] transition-colors">ALL ORDERS</a>
                </div>

                @if($latestOrder)
                    <div class="space-y-4">
                        <!-- Order Reference & Date -->
                        <div class="flex items-baseline justify-between border-b border-[#E6E1D7]/60 pb-3">
                            <span class="font-editorial text-2xl text-[#1C1917] font-normal tracking-tight">
                                {{ $latestOrder->order_reference }}
                            </span>
                            <span class="text-[10px] uppercase tracking-[0.15em] font-semibold text-[#8E877D]">
                                {{ strtoupper($latestOrder->created_at->format('d F Y')) }}
                            </span>
                        </div>

                        <!-- 4-Segment Progress Bar (Matching 4 Workflow Steps) -->
                        <div class="space-y-2 pt-1">
                            <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; height: 6px; width: 100%;">
                                @php
                                    $statusMap = [
                                        'CONFIRMED'     => 1,
                                        'PROCESSING'    => 2,
                                        'CRAFTING'      => 2,
                                        'QUALITY_CHECK' => 2,
                                        'PACKED'        => 2,
                                        'SHIPPED'       => 3,
                                        'DELIVERED'     => 4,
                                        'CANCELLED'     => 0,
                                    ];
                                    $activeStep  = $statusMap[strtoupper((string) $latestOrder->status)] ?? 1;
                                    $statusLabel = strtoupper((string) $latestOrder->status) === 'CONFIRMED' 
                                                   ? 'Order Confirmed' 
                                                   : $latestOrder->status_label;
                                @endphp
                                @for($i = 1; $i <= 4; $i++)
                                    <div style="height: 6px; border-radius: 9999px; background-color: {{ $latestOrder->status === 'CANCELLED' ? '#fca5a5' : ($i <= $activeStep ? '#1C1917' : '#E6E1D7') }};"></div>
                                @endfor
                            </div>
                            <div class="text-xs text-[#1C1917] font-semibold tracking-wide pt-1">
                                {{ $statusLabel }}
                            </div>
                        </div>

                        <!-- Order Products Preview -->
                        <div class="space-y-3.5 pt-2">
                            @foreach($latestOrder->items->take(2) as $item)
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-3.5">
                                        <div class="w-12 h-12 bg-white rounded-xl overflow-hidden shrink-0 border border-[#E6E1D7]/40">
                                            @if(!empty($item->product?->images) && isset($item->product->images[0]))
                                                <img src="{{ $item->product->images[0] }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-[#FAF8F5] flex items-center justify-center text-[10px] text-[#8E877D]">Art</div>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-xs text-[#1C1917] font-normal" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                                                {{ $item->product_name }} × {{ $item->quantity }}
                                            </h4>
                                        </div>
                                    </div>
                                    <span class="text-xs font-normal text-[#1C1917]">
                                        ₹ {{ number_format($item->subtotal) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Track Order Action Button -->
                        <div class="pt-3">
                            <a href="{{ route('tracking.index', ['order_reference' => $latestOrder->order_reference, 'email' => $latestOrder->email]) }}" 
                               class="inline-block border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-6 rounded-full transition-all duration-300">
                                TRACK THIS ORDER
                            </a>
                        </div>
                    </div>
                @else
                    <div class="py-14 text-center">
                        <p class="text-xs text-[#78716C] font-light">You haven't placed any orders yet.</p>
                        <p class="text-[11px] text-[#A8A29E] mt-1.5 font-light">Your newest acquisition details will appear here.</p>
                    </div>
                @endif
            </div>

            <!-- Right Column: NOTIFICATIONS Card -->
            <div class="w-full lg:col-span-5 glass rounded-[1.75rem] p-7 space-y-6">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.2em]">
                    <span class="text-[#8E877D]">NOTIFICATIONS</span>
                    <a href="{{ route('account.notifications.index') }}" class="text-[#8E877D] hover:text-[#1C1917] transition-colors">ALL</a>
                </div>

                <div class="space-y-4">
                    @forelse($notifications as $notification)
                        <div class="space-y-1 pb-3 border-b border-[#E6E1D7]/50 last:border-none last:pb-0">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-semibold text-[#1C1917]">
                                    {{ $notification->title }}
                                </h4>
                                @if(!$notification->is_read)
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#8E7558]"></span>
                                @endif
                            </div>
                            <p class="text-[11.5px] text-[#78716C] font-light leading-relaxed">
                                {{ $notification->message }}
                            </p>
                            <div class="text-[9px] uppercase tracking-[0.15em] font-semibold text-[#A89F91] pt-0.5">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#8E877D]">
                            No notifications yet. You will receive updates here as your orders progress.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- 3. Active Custom Request Banner (Full Width Standalone Card - Only When Active) -->
        @if($latestCustomRequest)
            @php
                $crStatusEnum = $latestCustomRequest->status instanceof \App\Enums\CustomRequestStatus 
                    ? $latestCustomRequest->status 
                    : \App\Enums\CustomRequestStatus::tryFrom($latestCustomRequest->status) ?? \App\Enums\CustomRequestStatus::SUBMITTED;
                $crStep = $crStatusEnum->stepIndex();
            @endphp
            <div class="w-full glass rounded-[1.75rem] p-7 space-y-5 border border-[#E6E1D7]">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.2em]">
                    <span class="text-[#8E877D]">ACTIVE CUSTOM REQUEST</span>
                    <a href="{{ route('account.custom-requests.index') }}" class="text-[#8E877D] hover:text-[#1C1917] transition-colors">ALL REQUESTS</a>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E6E1D7]/60 pb-3">
                    <div>
                        <span class="font-editorial text-2xl text-[#1C1917] font-normal tracking-tight">
                            {{ $latestCustomRequest->public_reference }}
                        </span>
                        <span class="text-xs text-[#78716C] ml-3">&bull; {{ ($latestCustomRequest->submitted_at ?? $latestCustomRequest->created_at)->format('d M Y') }}</span>
                    </div>
                    <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-[#1C1917] text-white">
                        {{ $crStatusEnum->customerLabel() }}
                    </span>
                </div>

                <!-- 5 Segment Progress Bar (Universal Flexbox) -->
                <div class="py-1">
                    <div class="flex items-center gap-1.5 w-full">
                        @for($i = 1; $i <= 5; $i++)
                            <div class="flex-1 h-1.5 rounded-full transition-colors {{ $i <= $crStep ? 'bg-[#1C1917]' : 'bg-[#E5DFD3]' }}"></div>
                        @endfor
                    </div>
                </div>

                <div class="pt-1">
                    <a href="{{ route('account.custom-requests.index') }}" 
                       class="inline-block border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-6 rounded-full transition-all duration-300">
                        TRACK REQUEST DETAILS
                    </a>
                </div>
            </div>
        @endif

        <!-- 3. Bottom Row: RECENTLY VIEWED Card -->
        <div class="glass rounded-[1.75rem] p-7 space-y-4">
            <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-[0.2em]">
                <span class="text-[#8E877D]">RECENTLY VIEWED</span>
                @if($recentlyViewedProducts->count() > 0)
                    <a href="{{ route('account.recently-viewed.index') }}" class="text-[#8E877D] hover:text-[#1C1917] transition-colors">SEE ALL</a>
                @endif
            </div>

            @if($recentlyViewedProducts->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                    @foreach($recentlyViewedProducts as $viewed)
                        @if($viewed->product)
                            <a href="{{ route('shop.show', $viewed->product->slug) }}" class="group flex items-center space-x-3 p-2 rounded-2xl hover:bg-white/60 transition-all">
                                <div class="w-12 h-12 bg-white rounded-xl overflow-hidden shrink-0 border border-[#E6E1D7]/40">
                                    @if(!empty($viewed->product->images) && isset($viewed->product->images[0]))
                                        <img src="{{ $viewed->product->images[0] }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full bg-[#FAF8F5] flex items-center justify-center text-[10px] text-[#8E877D]">Art</div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="text-xs font-normal text-[#1C1917] group-hover:underline truncate">{{ $viewed->product->name }}</h5>
                                    <span class="text-[11px] text-[#78716C]">₹ {{ number_format($viewed->product->effective_price ?? $viewed->product->price) }}</span>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center space-y-4">
                    <p class="text-xs text-[#78716C] font-light">
                        You haven't viewed any products yet.
                    </p>
                    <div>
                        <a href="{{ route('shop.index') }}" 
                           class="inline-block bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-6 rounded-full transition-all duration-300 shadow-xs">
                            EXPLORE PRODUCTS
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-account-layout>
