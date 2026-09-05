<x-account-layout 
    title="Order History" 
    header-title="Order" 
    header-italic=" history." 
    header-subtitle="View your past orders, track dispatch status, and download tax invoices.">
    <div class="space-y-6">

        @if($orders->count() > 0)
            <div class="space-y-5">
                @foreach($orders as $order)
                    <div class="rounded-[2rem] p-6 sm:p-7 space-y-4 shadow-[0_20px_50px_rgba(28,25,23,0.04)] border-none" style="background: oklch(98.5% .008 85);">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-[#E6E1D7]/60 pb-3 gap-2 text-xs">
                            <div>
                                <span class="font-editorial text-xl text-[#1C1917] block">{{ $order->order_reference }}</span>
                                <span class="text-[10px] uppercase tracking-[0.15em] text-[#8E877D]">Placed on {{ $order->created_at->format('d M Y') }}</span>
                            </div>

                            <div class="flex items-center space-x-3">
                                @php
                                    $badgeStyle = match(strtoupper((string) $order->status)) {
                                        'CONFIRMED'  => 'background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;',
                                        'PROCESSING', 'CRAFTING', 'QUALITY_CHECK', 'PACKED' => 'background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                                        'SHIPPED'    => 'background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;',
                                        'DELIVERED'  => 'background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                        'CANCELLED'  => 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                                        default      => 'background-color: #f5f5f4; color: #44403c; border: 1px solid #e7e5e4;',
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[9.5px] uppercase font-bold tracking-wider inline-block" style="{{ $badgeStyle }}">
                                    {{ $order->status_label }}
                                </span>
                                <span class="font-normal text-sm text-[#1C1917]">₹ {{ number_format($order->grand_total) }}</span>
                            </div>
                        </div>

                        <!-- Items Preview -->
                        <div class="space-y-2.5 text-xs">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between">
                                    <span class="text-[#1C1917] font-normal line-clamp-1" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                                        {{ $item->product_name }} (×{{ $item->quantity }})
                                    </span>
                                    <span class="text-[#78716C]">₹ {{ number_format($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t border-[#E6E1D7]/60 flex items-center justify-between text-xs">
                            <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}" class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors">
                                Track Dispatch →
                            </a>

                            <a href="{{ route('account.orders.show', $order->order_reference) }}" class="border border-[#DFD9CE] text-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-2.5 rounded-full transition-all duration-300">
                                View Order Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @else
            <div class="text-center py-16 rounded-[2rem] p-8 space-y-4 shadow-[0_20px_50px_rgba(28,25,23,0.04)] glass">
                <div class="w-12 h-12 mx-auto rounded-full bg-[#FAF8F5] border border-[#E6E1D7] flex items-center justify-center text-[#8E877D]">
                    <svg class="w-6 h-6 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-sm font-semibold text-[#1C1917]">You haven't placed any orders yet</h3>
                    <p class="text-xs text-[#78716C] font-light max-w-md mx-auto leading-relaxed">
                        Once you place an order, you can track real-time status updates, follow dispatch and delivery, and download tax invoices here.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('shop.index') }}" 
                       class="inline-block bg-[#1C1917] hover:bg-[#2C2724] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-7 rounded-full transition-all duration-300 shadow-xs">
                        EXPLORE PRODUCTS
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-account-layout>
