<x-app-layout title="Order Tracking — Maison Résine">
    <div class="min-h-[75vh] py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-[1060px] mx-auto space-y-10">

        {{-- Header --}}
        <div class="space-y-3">
            <span class="text-[10px] uppercase tracking-[0.25em] font-semibold text-[#8E877D] block">
                ORDER TRACKING
            </span>
            <h1 class="font-editorial text-4xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light tracking-tight leading-none">
                Track your <em class="italic font-normal">order.</em>
            </h1>
            <p class="text-xs sm:text-[13px] text-[#78716C] font-light max-w-lg leading-relaxed pt-1">
                Enter your order number and the email used at checkout. Each piece is followed by hand from the studio to your door.
            </p>
        </div>

        {{-- Search Form (Supports POST and GET) --}}
        <div class="glass rounded-[2rem] p-5 sm:p-6 shadow-xs w-full">
            <form method="POST" action="{{ route('tracking.search') }}" class="flex flex-col sm:flex-row items-end gap-3 sm:gap-4">
                @csrf
                {{-- Order Number --}}
                <div class="w-full sm:flex-1 space-y-1.5">
                    <label class="block text-[9px] uppercase tracking-[0.22em] font-bold text-[#8E877D] pl-4">ORDER NUMBER</label>
                    <input type="text" name="order_reference"
                           value="{{ old('order_reference', request('order_reference', request('order', request('reference')))) }}"
                           placeholder="MR-2026-4821"
                           required
                           class="w-full px-6 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-full text-xs font-mono focus:outline-none focus:border-[#1C1917] text-[#1C1917] placeholder-[#A89F90] transition-colors @error('order_reference') border-red-400 @enderror">
                    @error('order_reference')
                        <p class="text-[10px] text-red-500 pl-4">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email Address --}}
                <div class="w-full sm:flex-1 space-y-1.5">
                    <label class="block text-[9px] uppercase tracking-[0.22em] font-bold text-[#8E877D] pl-4">EMAIL ADDRESS</label>
                    <input type="email" name="email"
                           value="{{ old('email', request('email')) }}"
                           placeholder="test@test.com"
                           required
                           class="w-full px-6 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-full text-xs focus:outline-none focus:border-[#1C1917] text-[#1C1917] placeholder-[#A89F90] transition-colors @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="text-[10px] text-red-500 pl-4">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Track Button --}}
                <div class="w-full sm:w-auto shrink-0">
                    <button type="submit"
                            class="w-full sm:w-auto bg-[#1C1917] hover:bg-[#2C2724] text-white text-[10px] uppercase tracking-[0.28em] font-semibold py-3.5 px-9 rounded-full transition-all duration-200 cursor-pointer text-center flex items-center justify-center">
                        TRACK
                    </button>
                </div>
            </form>
        </div>

        {{-- Error Alert --}}
        @if(session('tracking_error'))
            <div class="glass rounded-2xl p-6 w-full border border-[#E6C9C9]">
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                    </svg>
                    <p class="text-xs text-[#78716C] leading-relaxed">{!! session('tracking_error') !!}</p>
                </div>
            </div>
        @endif

        {{-- Order Result --}}
        @if(isset($searched) && $searched)
            @if($order)
                @php
                    $trackingSteps = [
                        [
                            'key'       => 'CONFIRMED',
                            'label'     => 'Confirmed',
                            'subtitle'  => 'Order placed & payment verified',
                            'detail'    => $order->created_at ? 'Placed on ' . $order->created_at->format('d M Y, h:i A') : null,
                            'completed' => true,
                        ],
                        [
                            'key'       => 'PROCESSING',
                            'label'     => 'Processing',
                            'subtitle'  => 'Order is being prepared & packed',
                            'detail'    => null,
                            'completed' => in_array(strtoupper((string) $order->status), ['PROCESSING', 'CRAFTING', 'QUALITY_CHECK', 'PACKED', 'SHIPPED', 'DELIVERED']),
                        ],
                        [
                            'key'       => 'SHIPPED',
                            'label'     => 'Shipped',
                            'subtitle'  => 'Handed over to courier partner',
                            'detail'    => !empty($order->tracking_number) ? ($order->courier ? $order->courier . ' &bull; AWB: ' . $order->tracking_number : 'AWB: ' . $order->tracking_number) : ($order->shipped_at ? 'Shipped on ' . $order->shipped_at->format('d M Y') : null),
                            'completed' => in_array(strtoupper((string) $order->status), ['SHIPPED', 'DELIVERED']),
                        ],
                        [
                            'key'       => 'DELIVERED',
                            'label'     => 'Delivered',
                            'subtitle'  => 'Package safely delivered to destination',
                            'detail'    => $order->estimated_delivery_at ? (strtoupper((string) $order->status) === 'DELIVERED' ? 'Delivered on ' : 'Expected by ') . $order->estimated_delivery_at->format('d M Y') : null,
                            'completed' => strtoupper((string) $order->status) === 'DELIVERED',
                        ],
                    ];

                    $activeStepIndex = 0;
                    foreach ($trackingSteps as $idx => $step) {
                        if ($step['completed']) {
                            $activeStepIndex = $idx;
                        }
                    }
                @endphp

                {{-- 2-Column Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {{-- Left: PROGRESS --}}
                    <div class="lg:col-span-5 space-y-6">
                        <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-6">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                                PROGRESS &mdash; {{ $order->order_reference }}
                            </div>

                            <div>
                                @foreach($trackingSteps as $index => $step)
                                    @php
                                        $isDone     = $index <= $activeStepIndex && $order->status !== 'CANCELLED';
                                        $isCurrent  = $index === $activeStepIndex && $order->status !== 'CANCELLED';
                                        $isNextDone = ($index + 1) <= $activeStepIndex && $order->status !== 'CANCELLED';
                                    @endphp
                                    <div style="position: relative; display: flex; align-items: flex-start; gap: 16px; {{ !$loop->last ? 'padding-bottom: 28px;' : '' }}">

                                        {{-- Connector line --}}
                                        @if(!$loop->last)
                                            <div style="position: absolute; left: 11px; top: 24px; bottom: 0; width: 2px; background-color: {{ $isNextDone ? '#1C1917' : '#DFD9CE' }}; z-index: 0;"></div>
                                        @endif

                                        {{-- Circle --}}
                                        <div style="position: relative; z-index: 1; flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 600; line-height: 1; text-align: center; background-color: {{ $isDone ? '#1C1917' : '#FAF8F5' }}; color: {{ $isDone ? '#ffffff' : '#78716C' }}; border: {{ $isDone ? '2px solid #1C1917' : '1.5px solid #C4BCAC' }};">
                                            @if($isDone)
                                                <svg style="width:13px;height:13px;stroke-width:2.5; display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            @else
                                                <span style="display:flex; align-items:center; justify-content:center; width:100%; height:100%; line-height:1; text-align:center;">{{ $index + 1 }}</span>
                                            @endif
                                        </div>

                                        {{-- Label & Description --}}
                                        <div style="padding-top: 2px; min-width: 0; flex: 1;">
                                            <div style="font-size: 13.5px; font-weight: {{ $isDone ? '600' : '500' }}; line-height: 1.3; color: {{ $isDone ? '#1C1917' : '#78716C' }};">
                                                {{ $step['label'] }}
                                            </div>

                                            {{-- Subtitle is ALWAYS visible for every step --}}
                                            <div style="font-size: 11px; color: {{ $isDone ? '#57534E' : '#A89F90' }}; font-weight: 400; padding-top: 2px; line-height: 1.4;">
                                                {{ $step['subtitle'] }}
                                            </div>

                                            {{-- Live details (like date, tracking info) if present --}}
                                            @if(!empty($step['detail']))
                                                <div style="font-size: 10px; color: #8E877D; font-family: monospace; padding-top: 3px;">
                                                    {!! $step['detail'] !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Right: DELIVERY & ORDER SUMMARY --}}
                    <div class="lg:col-span-7 space-y-8">

                        {{-- Delivery Card --}}
                        <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-4">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D] pb-1">
                                DELIVERY
                            </div>

                            <div class="space-y-3 text-xs">
                                {{-- Estimated Delivery --}}
                                @if($order->estimated_delivery_at)
                                    <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                        <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">ESTIMATED DELIVERY</span>
                                        <span class="text-[#1C1917] font-normal">{{ $order->estimated_delivery_at->format('d F Y') }}</span>
                                    </div>
                                @endif

                                {{-- Shipment Date --}}
                                @if($order->shipped_at)
                                    <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                        <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">SHIPMENT DATE</span>
                                        <span class="text-[#1C1917] font-normal">{{ $order->shipped_at->format('d F Y') }}</span>
                                    </div>
                                @endif

                                {{-- Courier --}}
                                @if(!empty($order->courier))
                                    <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                        <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">COURIER</span>
                                        <span class="text-[#1C1917] font-normal">{{ $order->courier }}</span>
                                    </div>
                                @endif

                                {{-- Tracking Number --}}
                                @if(!empty($order->tracking_number))
                                    <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                        <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">TRACKING NUMBER</span>
                                        <span class="text-[#1C1917] font-mono text-[11px]">{{ $order->tracking_number }}</span>
                                    </div>
                                @endif

                                {{-- Tracking Status --}}
                                <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                    <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">TRACKING STATUS</span>
                                    <span class="text-[#1C1917] font-normal">
                                        {{ $order->status_label }}
                                    </span>
                                </div>

                                {{-- Shipping Address --}}
                                <div class="flex items-start justify-between py-2 gap-4">
                                    <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D] shrink-0 pt-0.5">SHIPPING ADDRESS</span>
                                    <span class="text-[#1C1917] font-normal text-right text-[11.5px] leading-relaxed max-w-xs">
                                        {{ $order->shipping_address_snapshot['full_name'] ?? '' }}<br>
                                        {{ $order->shipping_address_snapshot['address_line_1'] ?? '' }}@if(!empty($order->shipping_address_snapshot['address_line_2'])), {{ $order->shipping_address_snapshot['address_line_2'] }}@endif<br>
                                        {{ $order->shipping_address_snapshot['city'] ?? '' }}@if(!empty($order->shipping_address_snapshot['state'])), {{ $order->shipping_address_snapshot['state'] }}@endif {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Order Summary Card --}}
                        <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-5">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                                ORDER SUMMARY
                            </div>

                            <div class="space-y-4">
                                @foreach($order->items as $item)
                                    <div class="flex items-center justify-between gap-4 pb-3 border-b border-[#E6E1D7]/50 last:border-b-0 last:pb-0">
                                        <div class="flex items-center space-x-3.5 min-w-0">
                                            <div class="w-12 h-12 min-w-[48px] max-w-[48px] rounded-xl bg-white border border-[#E6E1D7] overflow-hidden shrink-0 flex items-center justify-center">
                                                @if(!empty($item->product_snapshot['images']) && isset($item->product_snapshot['images'][0]))
                                                    <img src="{{ $item->product_snapshot['images'][0] }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover rounded-xl">
                                                @elseif($item->product && $item->product->primary_image_url)
                                                    <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover rounded-xl">
                                                @else
                                                    <span class="text-[8px] font-mono text-[#8E877D]">R&Eacute;SINE</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="font-sans text-xs sm:text-[13px] font-medium text-[#1C1917] leading-snug truncate">
                                                    {{ $item->product_name }}
                                                </h4>
                                                <span class="text-[10px] font-mono text-[#8E877D] block pt-0.5">
                                                    QTY {{ $item->quantity }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-[13.5px] font-medium text-[#1C1917] shrink-0 font-sans">
                                            &#8377; {{ number_format($item->subtotal) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Totals --}}
                            <div class="border-t border-[#E6E1D7]/60 pt-4 space-y-2.5 text-[13px]">
                                <div class="flex justify-between text-[#78716C]">
                                    <span>Shipping</span>
                                    <span class="text-[#1C1917] font-medium">Complimentary</span>
                                </div>
                                <div class="flex justify-between items-center border-t border-[#E6E1D7]/60 pt-3">
                                    <span class="text-[11px] uppercase tracking-[0.2em] font-bold text-[#1C1917]">TOTAL</span>
                                    <span class="font-medium text-base sm:text-lg text-[#1C1917] font-sans">&#8377; {{ number_format($order->grand_total) }}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Bottom Action Bar --}}
                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                    @if($order->tracking_url)
                        <a href="{{ $order->tracking_url }}" target="_blank"
                           class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200">
                            Track on Courier Website
                        </a>
                    @endif

                    <a href="{{ route('orders.invoice.public', ['order' => $order->order_reference, 'email' => $order->email]) }}" 
                       target="_blank"
                       class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.2em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200 shadow-2xs">
                        Download Invoice
                    </a>

                    @if(Auth::check() && Auth::id() === $order->user_id)
                        <a href="{{ route('account.orders.show', $order->order_reference) }}"
                           class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200">
                            Order Details
                        </a>
                        <a href="{{ route('account.orders.index') }}"
                           class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200">
                            Back to Orders
                        </a>
                    @endif

                    <a href="{{ route('shop.index') }}"
                       class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200">
                        Continue Shopping
                    </a>
                </div>

            @else
                {{-- Nothing Found --}}
                <div class="glass rounded-[2rem] p-12 text-center max-w-2xl mx-auto space-y-4">
                    <h3 class="font-editorial text-2xl italic text-[#1C1917]">Nothing to follow yet.</h3>
                    <p class="text-xs text-[#78716C] leading-relaxed max-w-sm mx-auto">
                        Enter an order reference above, or open an order from your order history.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                        @auth
                            <a href="{{ route('account.orders.index') }}"
                               class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200">
                                YOUR ORDERS
                            </a>
                        @endauth
                        <a href="{{ route('shop.index') }}"
                           class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.25em] font-semibold px-6 py-3.5 rounded-full transition-all duration-200">
                            CONTINUE SHOPPING
                        </a>
                    </div>
                </div>
            @endif
        @endif

    </div>
</x-app-layout>
