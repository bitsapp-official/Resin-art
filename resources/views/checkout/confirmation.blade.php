<x-app-layout title="Order Confirmed — Maison Résine">
    <div class="max-w-[920px] mx-auto w-full min-w-0 px-4 sm:px-6 py-10 sm:py-20 text-center space-y-8 sm:space-y-10">

        <!-- Top Centered Hero Header -->
        <div class="space-y-3 sm:space-y-4 max-w-xl mx-auto min-w-0">
            <span class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.25em] sm:tracking-[0.28em] font-semibold text-[#8E877D] block">
                ORDER CONFIRMED
            </span>
            
            <h1 class="font-editorial text-3xl sm:text-6xl lg:text-[76px] font-light text-[#1C1917] tracking-tight leading-tight sm:leading-none">
                Thank you, <em class="italic font-normal">truly.</em>
            </h1>
            
            <p class="text-xs sm:text-[13.5px] text-[#78716C] font-light leading-relaxed max-w-md mx-auto pt-1 sm:pt-2">
                Your piece is being wrapped in the atelier. You will receive an email with tracking within two working days.
            </p>
        </div>

        <!-- Reference Pill Badge -->
        <div>
            <span class="inline-block bg-white/80 border border-[#DFD9CE] rounded-full px-4 sm:px-6 py-2 text-[10px] sm:text-[10.5px] font-mono tracking-wider sm:tracking-widest font-semibold text-[#1C1917] shadow-2xs max-w-full truncate">
                REFERENCE {{ $order->order_reference }}
            </span>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3 pt-2">
            {{-- Top Row Buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                @if(Auth::check())
                    <a href="{{ route('account.orders.show', $order->order_reference) }}" 
                       class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9px] sm:text-[9.5px] uppercase tracking-wider sm:tracking-[0.22em] font-semibold px-5 sm:px-7 py-3 sm:py-3.5 rounded-full transition-all duration-200 shadow-2xs shrink-0">
                        VIEW ORDER
                    </a>
                @endif

                <a href="{{ route('orders.invoice.public', ['order' => $order->order_reference, 'email' => $order->email]) }}" 
                   target="_blank"
                   class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9px] sm:text-[9.5px] uppercase tracking-wider sm:tracking-[0.22em] font-semibold px-5 sm:px-7 py-3 sm:py-3.5 rounded-full transition-all duration-200 shadow-2xs shrink-0">
                    DOWNLOAD INVOICE
                </a>

                <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}" 
                   class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9px] sm:text-[9.5px] uppercase tracking-wider sm:tracking-[0.22em] font-semibold px-5 sm:px-7 py-3 sm:py-3.5 rounded-full transition-all duration-200 shrink-0">
                    TRACK ORDER
                </a>

                <a href="{{ route('shop.index') }}" 
                   class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9px] sm:text-[9.5px] uppercase tracking-wider sm:tracking-[0.22em] font-semibold px-5 sm:px-7 py-3 sm:py-3.5 rounded-full transition-all duration-200 shrink-0">
                    CONTINUE BROWSING
                </a>
            </div>

            {{-- Bottom Row Button --}}
            <div class="flex items-center justify-center">
                <a href="{{ route('contact.index') }}" 
                   class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9px] sm:text-[9.5px] uppercase tracking-wider sm:tracking-[0.22em] font-semibold px-6 sm:px-8 py-2.5 sm:py-3 rounded-full transition-all duration-200">
                    CONTACT THE ATELIER
                </a>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="glass rounded-[1.5rem] sm:rounded-[2rem] p-4 sm:p-7 md:p-9 text-left space-y-5 sm:space-y-6 max-w-xl mx-auto shadow-sm mt-8 sm:mt-12 w-full min-w-0">
            <div class="flex items-center justify-between border-b border-[#E6E1D7]/60 pb-4">
                <div>
                    <span class="text-[9.5px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">ORDER SUMMARY</span>
                    <h3 class="font-mono font-bold text-sm text-[#1C1917] pt-0.5">{{ $order->order_reference }}</h3>
                </div>
                <div class="text-right">
                    <span class="text-[9.5px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">GRAND TOTAL</span>
                    <span class="font-editorial text-lg text-[#1C1917] block pt-0.5 font-sans">&#8377; {{ number_format($order->grand_total) }}</span>
                </div>
            </div>

            <!-- Items -->
            <div class="space-y-3.5">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between text-xs pb-3 border-b border-[#E6E1D7]/40 last:border-b-0 last:pb-0">
                        <div class="min-w-0 pr-4">
                            <span class="font-medium text-[#1C1917] block truncate">{{ $item->product_name }}</span>
                            <span class="text-[10px] text-[#78716C] font-mono">Qty: {{ $item->quantity }} &bull; &#8377; {{ number_format($item->unit_price) }}</span>
                        </div>
                        <span class="font-medium text-[#1C1917] shrink-0 font-sans">&#8377; {{ number_format($item->subtotal) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Destination Crate Address -->
            <div class="border-t border-[#E6E1D7]/60 pt-4 text-xs">
                <span class="text-[9.5px] uppercase tracking-[0.2em] font-bold text-[#8E877D] block mb-1">DESTINATION CRATE</span>
                <p class="text-[#78716C] leading-relaxed font-light text-[11.5px]">
                    {{ $order->shipping_address_snapshot['full_name'] ?? '' }}<br>
                    {{ $order->shipping_address_snapshot['address_line_1'] ?? '' }}@if(!empty($order->shipping_address_snapshot['address_line_2'])), {{ $order->shipping_address_snapshot['address_line_2'] }}@endif<br>
                    {{ $order->shipping_address_snapshot['city'] ?? '' }} {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}<br>
                    {{ $order->shipping_address_snapshot['country'] ?? '' }} &bull; Phone: {{ $order->shipping_address_snapshot['phone'] ?? '' }}
                </p>
            </div>
        </div>

    </div>
</x-app-layout>
