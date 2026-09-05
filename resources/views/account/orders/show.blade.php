<x-account-layout 
    title="Order {{ $order->order_reference }}"
    header-title="{{ $order->order_reference }}" 
    header-italic="" 
    header-subtitle="Placed {{ $order->created_at->format('d F Y') }} &middot; Order {{ $order->status_label }} &middot; Payment {{ ucfirst(strtolower($order->payment_status)) }}">

    <div class="space-y-8" x-data="{ cancelModal: false }">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: ORDER TIMELINE & AVAILABLE ACTIONS (6 cols) -->
            <div class="lg:col-span-6 space-y-8">
                
                @if($order->status === 'CANCELLED')
                    <!-- 1. ORDER CANCELLED & REFUND STATUS Card (Shown when order is Cancelled) -->
                    <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-6 border border-red-200/60 bg-red-50/10">
                        <div class="flex items-center justify-between">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-red-800">
                                ORDER CANCELLATION DETAILS
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[9.5px] font-bold uppercase tracking-wider bg-red-800 text-white">
                                CANCELLED
                            </span>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-[#E6E1D7] space-y-3.5 shadow-xs">
                            <div class="flex items-baseline justify-between border-b border-[#E6E1D7]/60 pb-3">
                                <span class="text-xs font-semibold text-[#1C1917]">Cancelled Date</span>
                                <span class="text-xs text-[#78716C]">
                                    {{ $order->canceled_at ? $order->canceled_at->format('d F Y, h:i A') : $order->updated_at->format('d F Y') }}
                                </span>
                            </div>

                            @if($order->cancel_reason)
                                <div class="space-y-1 border-b border-[#E6E1D7]/60 pb-3">
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-[#8E877D]">Reason for Cancellation:</span>
                                    <p class="text-xs text-[#1C1917] italic leading-relaxed">
                                        "{{ $order->cancel_reason }}"
                                    </p>
                                </div>
                            @endif

                            {{-- Refund Request Breakdown --}}
                            @php
                                $refund = $order->refundRequests->first();
                            @endphp
                            <div class="space-y-2 pt-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-[#1C1917]">Refund Status</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[9.5px] font-bold uppercase tracking-wider {{ in_array($refund?->status, ['APPROVED', 'COMPLETED', 'PROCESSED']) ? 'bg-emerald-800 text-white' : 'bg-amber-700 text-white' }}">
                                        {{ $refund?->status ?? 'REQUESTED' }}
                                    </span>
                                </div>
                                <div class="flex items-baseline justify-between text-xs">
                                    <span class="text-[#78716C]">Refund Amount</span>
                                    <span class="font-bold text-[#1C1917]">₹ {{ number_format($refund?->amount ?? $order->grand_total, 2) }}</span>
                                </div>
                                <p class="text-[11.5px] text-[#78716C] font-light leading-relaxed pt-1">
                                    A full 100% refund has been logged for this order. Our atelier accounts team will credit the amount back to your original payment method.
                                </p>
                                <div class="pt-2">
                                    <a href="{{ route('account.refunds.index') }}" class="inline-flex items-center text-xs font-semibold text-[#1C1917] hover:underline">
                                        Track in Refund Requests &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- 1. ORDER TIMELINE Card (Continuous Stepper for Active Orders) -->
                    <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-6">
                        <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                            ORDER TIMELINE
                        </div>

                        @php
                            $timelineSteps = [
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

                            $activeIdx = 0;
                            foreach ($timelineSteps as $idx => $step) {
                                if ($step['completed']) {
                                    $activeIdx = $idx;
                                }
                            }
                        @endphp

                        <div>
                            @foreach($timelineSteps as $index => $step)
                                @php
                                    $isDone     = $index <= $activeIdx && $order->status !== 'CANCELLED';
                                    $isCurrent  = $index === $activeIdx && $order->status !== 'CANCELLED';
                                    $isNextDone = ($index + 1) <= $activeIdx && $order->status !== 'CANCELLED';
                                @endphp
                                {{-- Step row: relative so the connector line can be positioned absolute --}}
                                <div style="position: relative; display: flex; align-items: flex-start; gap: 16px; {{ !$loop->last ? 'padding-bottom: 28px;' : '' }}">

                                    {{-- Vertical connector line from bottom of this circle to top of next --}}
                                    @if(!$loop->last)
                                        <div style="
                                            position: absolute;
                                            left: 11px;
                                            top: 24px;
                                            bottom: 0;
                                            width: 2px;
                                            background-color: {{ $isNextDone ? '#1C1917' : '#DFD9CE' }};
                                            z-index: 0;
                                        "></div>
                                    @endif

                                    {{-- Circle --}}
                                    <div style="
                                        position: relative;
                                        z-index: 1;
                                        flex-shrink: 0;
                                        width: 24px;
                                        height: 24px;
                                        border-radius: 50%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-size: 10px;
                                        font-weight: 600;
                                        line-height: 1;
                                        text-align: center;
                                        background-color: {{ $isDone ? '#1C1917' : '#FAF8F5' }};
                                        color: {{ $isDone ? '#ffffff' : '#78716C' }};
                                        border: {{ $isDone ? '2px solid #1C1917' : '1.5px solid #C4BCAC' }};
                                    ">
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
                @endif

                <!-- 2. AVAILABLE ACTIONS Card -->
                <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-6">
                    <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                        AVAILABLE ACTIONS
                    </div>

                    <div class="space-y-3">
                        {{-- Button 1: Cancel Order (Only visible if within cancellation window) --}}
                        @if($order->is_cancellable)
                            <button type="button" 
                                     @click="cancelModal = true"
                                     class="w-full text-center border border-[#DC2626] text-[#DC2626] hover:bg-[#DC2626] hover:text-white text-[9.5px] uppercase tracking-[0.2em] font-semibold py-3.5 px-4 rounded-full transition-all duration-200 cursor-pointer shadow-2xs">
                                Cancel order ({{ config('atelier.cancellation_hours', env('ORDER_CANCELLATION_HOURS', 3)) }}h window)
                            </button>
                        @endif

                        {{-- Button 2: Contact Support --}}
                        <a href="{{ route('contact.index') }}" 
                           class="w-full text-center border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold py-3.5 px-4 rounded-full transition-all duration-200 block">
                            Contact support
                        </a>

                        {{-- Button 3: Download Invoice (PDF) --}}
                        <a href="{{ route('account.orders.invoice', $order->order_reference) }}" 
                           target="_blank"
                           class="w-full text-center border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold py-3.5 px-4 rounded-full transition-all duration-200 block">
                            Download invoice (PDF)
                        </a>

                        {{-- Button 4: Back to Orders --}}
                        <a href="{{ route('account.orders.index') }}" 
                           class="w-full text-center border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold py-3.5 px-4 rounded-full transition-all duration-200 block">
                            Back to orders
                        </a>
                    </div>

                    <div class="text-[11px] text-[#8E877D] font-light leading-relaxed pt-1">
                        @if($order->is_cancellable)
                            Bespoke handcrafted piece. Cancellation allowed within {{ config('atelier.cancellation_hours', env('ORDER_CANCELLATION_HOURS', 3)) }} hours of order placement.
                        @else
                            For any order inquiries or custom adjustments, please contact our atelier support.
                        @endif
                    </div>
                </div>

            </div>

            <!-- Right Column: PRODUCTS & DELIVERY & PAYMENT (6 cols) -->
            <div class="lg:col-span-6 space-y-8">
                
                <!-- 1. PRODUCTS Card -->
                <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-5">
                    <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                        PRODUCTS
                    </div>

                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="flex items-center justify-between gap-4 pb-4 border-b border-[#E6E1D7]/50 last:border-b-0 last:pb-0">
                                <div class="flex items-center space-x-3.5 min-w-0">
                                    {{-- Strictly bounded thumbnail image --}}
                                    <div class="w-12 h-12 min-w-[48px] max-w-[48px] h-[48px] rounded-xl bg-white border border-[#E6E1D7] overflow-hidden shrink-0 flex items-center justify-center">
                                        @if(!empty($item->product_snapshot['images']) && isset($item->product_snapshot['images'][0]))
                                            <img src="{{ $item->product_snapshot['images'][0] }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover rounded-xl shrink-0">
                                        @elseif($item->product && $item->product->primary_image_url)
                                            <img src="{{ $item->product->primary_image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover rounded-xl shrink-0">
                                        @else
                                            <span class="text-[8px] font-mono text-[#8E877D]">R&Eacute;SINE</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-sans text-xs sm:text-[13px] font-medium text-[#1C1917] leading-snug truncate">
                                            {{ $item->product_name }} &times; {{ $item->quantity }}
                                        </h4>
                                        <span class="text-[10px] font-mono text-[#8E877D] block pt-0.5">
                                            {{ $item->sku ?? ('MR-TBL-' . str_pad($item->id, 3, '0', STR_PAD_LEFT)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-[13.5px] font-medium text-[#1C1917] shrink-0 font-sans">
                                    &#8377; {{ number_format($item->subtotal) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Price Summary --}}
                    <div class="border-t border-[#E6E1D7]/60 pt-4 space-y-2.5 text-[13px]">
                        <div class="flex justify-between text-[#78716C]">
                            <span>Subtotal</span>
                            <span class="font-medium text-[#1C1917]">&#8377; {{ number_format($order->subtotal) }}</span>
                        </div>
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

                <!-- 2. DELIVERY & PAYMENT Card (Dynamic Data from Database) -->
                <div class="glass rounded-[2rem] p-7 sm:p-8 space-y-4">
                    <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D] pb-1">
                        DELIVERY &amp; PAYMENT
                    </div>

                    <div class="space-y-3 text-xs">
                        {{-- Row 1: Order Status --}}
                        <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                            <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">ORDER STATUS</span>
                            <span class="text-[#1C1917] font-normal">
                                {{ $order->status_label }}
                            </span>
                        </div>

                        {{-- Row 2: Payment Status --}}
                        <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                            <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">PAYMENT STATUS</span>
                            <span class="text-[#1C1917] font-normal capitalize">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>

                        {{-- Row 3: Payment Method --}}
                        <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                            <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">PAYMENT METHOD</span>
                            <span class="text-[#1C1917] font-normal capitalize">
                                @if($order->payment_method === 'card' || empty($order->payment_method))
                                    Visa &bull;&bull;&bull;&bull; 4242
                                @else
                                    {{ ucfirst($order->payment_method) }}
                                @endif
                            </span>
                        </div>

                        {{-- Row 4: Courier --}}
                        @if(!empty($order->courier))
                            <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">COURIER</span>
                                <span class="text-[#1C1917] font-normal">
                                    {{ $order->courier }}
                                </span>
                            </div>
                        @endif

                        {{-- Row 5: Tracking Number --}}
                        @if(!empty($order->tracking_number))
                            <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">TRACKING NUMBER</span>
                                <span class="text-[#1C1917] font-mono text-[11px]">
                                    {{ $order->tracking_number }}
                                </span>
                            </div>
                        @endif

                        {{-- Row 6: Shipment Date --}}
                        @if($order->shipped_at)
                            <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">SHIPMENT DATE</span>
                                <span class="text-[#1C1917] font-normal">
                                    {{ $order->shipped_at->format('d F Y') }}
                                </span>
                            </div>
                        @endif

                        {{-- Row 7: Estimated Delivery --}}
                        @if($order->estimated_delivery_at)
                            <div class="flex items-center justify-between py-1.5 border-b border-[#E6E1D7]/40">
                                <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">ESTIMATED DELIVERY</span>
                                <span class="text-[#1C1917] font-normal">
                                    {{ $order->estimated_delivery_at->format('d F Y') }}
                                </span>
                            </div>
                        @endif

                        {{-- Row 8: Shipping Address --}}
                        <div class="flex items-start justify-between py-2 border-b border-[#E6E1D7]/40 gap-4">
                            <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D] shrink-0 pt-0.5">SHIPPING ADDRESS</span>
                            <span class="text-[#1C1917] font-normal text-right text-[11.5px] leading-relaxed max-w-xs">
                                {{ $order->shipping_address_snapshot['full_name'] ?? ($order->user->name ?? '') }}<br>
                                {{ $order->shipping_address_snapshot['address_line_1'] ?? '' }}@if(!empty($order->shipping_address_snapshot['address_line_2'])), {{ $order->shipping_address_snapshot['address_line_2'] }}@endif<br>
                                {{ $order->shipping_address_snapshot['city'] ?? '' }} {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}
                            </span>
                        </div>

                        {{-- Row 9: Billing Address --}}
                        <div class="flex items-start justify-between py-2 gap-4">
                            <span class="text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D] shrink-0 pt-0.5">BILLING ADDRESS</span>
                            <span class="text-[#1C1917] font-normal text-right text-[11.5px] leading-relaxed max-w-xs">
                                {{ $order->billing_address_snapshot['full_name'] ?? ($order->shipping_address_snapshot['full_name'] ?? ($order->user->name ?? '')) }}<br>
                                {{ $order->billing_address_snapshot['address_line_1'] ?? ($order->shipping_address_snapshot['address_line_1'] ?? '') }}<br>
                                {{ $order->billing_address_snapshot['city'] ?? ($order->shipping_address_snapshot['city'] ?? '') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- Cancel Confirmation Modal (Luxury Maison Résine Signature Solid Palette) --}}
        <template x-teleport="body">
            <div x-show="cancelModal" 
                 x-cloak 
                 x-effect="document.body.classList.toggle('overflow-hidden', cancelModal)"
                 class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 overflow-y-auto min-h-screen w-screen"
                 style="background-color: rgba(17, 14, 13, 0.65); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
                
                {{-- Modal Box Container (Solid Warm Atelier Background) --}}
                <div x-show="cancelModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     @click.outside="cancelModal = false"
                     class="max-w-lg w-full rounded-[2rem] p-8 space-y-6 shadow-2xl border border-[#E6E1D7] relative my-auto bg-[#FAF8F5]">
                    
                    {{-- Header --}}
                    <div class="space-y-1.5 border-b border-[#E6E1D7] pb-4">
                        <div class="text-[10px] uppercase tracking-[0.25em] font-bold text-[#8E877D]">
                            ORDER CANCELLATION
                        </div>
                        <h3 class="font-editorial text-3xl text-[#1C1917] font-normal leading-tight">
                            Cancel <em class="italic font-normal">Order.</em>
                        </h3>
                    </div>

                    {{-- Policy Notice Pill --}}
                    <div class="p-5 rounded-2xl bg-white border border-[#E6E1D7] space-y-2 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-editorial text-lg text-[#1C1917] font-normal">{{ $order->order_reference }}</span>
                            <span class="text-[9.5px] uppercase tracking-[0.15em] font-bold px-3 py-1 rounded-full bg-[#1C1917] text-white">
                                3H Window Active
                            </span>
                        </div>
                        <p class="text-xs text-[#57534E] leading-relaxed">
                            Handcrafted pieces can be cancelled within {{ config('atelier.cancellation_hours', env('ORDER_CANCELLATION_HOURS', 3)) }} hours of order placement. A <strong>100% refund request (₹ {{ number_format($order->grand_total, 2) }})</strong> will be automatically initiated for processing.
                        </p>
                    </div>

                    {{-- Cancellation Form --}}
                    <form method="POST" action="{{ route('account.orders.cancel', $order->id) }}" class="space-y-5">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">
                                REASON FOR CANCELLATION *
                            </label>
                            <textarea name="reason" rows="3" required 
                                      placeholder="Please let our atelier know why you wish to cancel this order..." 
                                      class="w-full p-4 rounded-2xl bg-white border border-[#DFD9CE] text-xs text-[#1C1917] placeholder:text-[#A89F91] focus:border-[#1C1917] focus:ring-1 focus:ring-[#1C1917] transition-all outline-none leading-relaxed shadow-xs"></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-3.5 pt-3 border-t border-[#E6E1D7]">
                            <button type="button" @click="cancelModal = false" 
                                    class="px-6 py-3 rounded-full border border-[#DFD9CE] bg-white hover:border-[#1C1917] hover:bg-[#FAF8F5] text-[10px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] transition-all cursor-pointer shadow-xs">
                                KEEP ORDER
                            </button>
                            <button type="submit" 
                                    class="px-7 py-3 rounded-full bg-[#1C1917] hover:bg-red-700 text-white text-[10px] uppercase tracking-[0.2em] font-semibold transition-all shadow-md cursor-pointer">
                                CONFIRM CANCELLATION
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
</x-account-layout>
