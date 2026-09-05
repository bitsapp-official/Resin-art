<x-app-layout title="Order Confirmed — Maison Résine">
    <div class="max-w-[920px] mx-auto px-6 py-20 text-center space-y-10">

        <!-- Top Centered Hero Header (Matching Screenshot 3) -->
        <div class="space-y-4 max-w-xl mx-auto">
            <span class="text-[10px] uppercase tracking-[0.28em] font-semibold text-[#8E877D] block">
                ORDER CONFIRMED
            </span>
            
            <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[76px] font-light text-[#1C1917] tracking-tight leading-none">
                Thank you, <em class="italic font-normal">truly.</em>
            </h1>
            
            <p class="text-xs sm:text-[13.5px] text-[#78716C] font-light leading-relaxed max-w-md mx-auto pt-2">
                Your piece is being wrapped in the atelier. You will receive an email with tracking within two working days.
            </p>
        </div>

        <!-- Reference Pill Badge (Screenshot 3) -->
        <div>
            <span class="inline-block bg-white/80 border border-[#DFD9CE] rounded-full px-6 py-2 text-[10.5px] font-mono tracking-widest font-semibold text-[#1C1917] shadow-2xs">
                REFERENCE {{ $order->order_reference }}
            </span>
        </div>

        <!-- Action Buttons (Pill Buttons Layout matching Screenshot 3) -->
        <div class="space-y-3 pt-2">
            {{-- Top Row Buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-3">
                @if(Auth::check())
                    <a href="{{ route('account.orders.show', $order->order_reference) }}" 
                       class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.22em] font-semibold px-7 py-3.5 rounded-full transition-all duration-200 shadow-xs">
                        VIEW ORDER
                    </a>
                @endif

                <a href="{{ route('orders.invoice.public', ['order' => $order->order_reference, 'email' => $order->email]) }}" 
                   target="_blank"
                   class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.22em] font-semibold px-7 py-3.5 rounded-full transition-all duration-200 shadow-xs">
                    DOWNLOAD INVOICE
                </a>

                <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}" 
                   class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.22em] font-semibold px-7 py-3.5 rounded-full transition-all duration-200">
                    TRACK ORDER
                </a>

                <a href="{{ route('shop.index') }}" 
                   class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.22em] font-semibold px-7 py-3.5 rounded-full transition-all duration-200">
                    CONTINUE BROWSING
                </a>
            </div>

            {{-- Bottom Row Button --}}
            <div class="flex items-center justify-center">
                <a href="{{ route('contact.index') }}" 
                   class="bg-white/70 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-[9.5px] uppercase tracking-[0.22em] font-semibold px-8 py-3 rounded-full transition-all duration-200">
                    CONTACT THE ATELIER
                </a>
            </div>
        </div>

        <!-- Order Summary Card (Preserved in clean luxury glass style) -->
        <div class="glass rounded-[2rem] p-7 sm:p-9 text-left space-y-6 max-w-xl mx-auto shadow-sm mt-12">
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
