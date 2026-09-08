<x-account-layout 
    title="Order History" 
    header-title="Order" 
    header-italic=" history." 
    header-subtitle="View your past orders, track dispatch status, and download tax invoices.">
    <div class="space-y-6">

        @if($orders->count() > 0)
            <div class="space-y-4 sm:space-y-5 w-full min-w-0">
                @foreach($orders as $order)
                    <div class="rounded-[1.25rem] sm:rounded-[2rem] p-4 sm:p-6 lg:p-7 space-y-3.5 sm:space-y-4 shadow-[0_10px_30px_rgba(28,25,23,0.04)] border border-[#E6E1D7]/60 w-full min-w-0" style="background: oklch(98.5% .008 85);">
                        
                        <!-- Header Row: Order Ref, Placed Date, Status Badge & Grand Total -->
                        <div class="flex items-center justify-between border-b border-[#E6E1D7]/60 pb-3 gap-2 text-xs">
                            <div class="min-w-0 flex-1">
                                <span class="font-editorial text-base sm:text-xl text-[#1C1917] block truncate leading-snug">{{ $order->order_reference }}</span>
                                <span class="text-[9.5px] sm:text-[10px] uppercase tracking-wider sm:tracking-[0.15em] text-[#8E877D] block mt-0.5">Placed on {{ $order->created_at ? $order->created_at->format('d M Y') : 'Recent' }}</span>
                            </div>

                            <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
                                @if(strtoupper((string) $order->status) === 'CANCELLED')
                                    <span class="bg-red-50 text-red-800 border border-red-200/80 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[8.5px] sm:text-[9px] uppercase font-semibold tracking-wider">
                                        {{ $order->status_label }}
                                    </span>
                                @else
                                    <span class="bg-[#EFECE6] text-[#1C1917] border border-[#DDD6CA] px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-[8.5px] sm:text-[9px] uppercase font-semibold tracking-wider">
                                        {{ $order->status_label }}
                                    </span>
                                @endif
                                <span class="font-medium font-sans text-xs sm:text-sm text-[#1C1917] whitespace-nowrap">₹ {{ number_format($order->grand_total) }}</span>
                            </div>
                        </div>

                        <!-- Items Preview -->
                        <div class="space-y-2 text-xs">
                            @foreach($order->items as $item)
                                <div class="flex items-baseline justify-between gap-3 min-w-0">
                                    <span class="text-[#1C1917] font-normal truncate min-w-0 flex-1 text-xs" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                                        {{ $item->product_name }} <span class="text-[#8E877D] text-[11px]">(×{{ $item->quantity }})</span>
                                    </span>
                                    <span class="text-[#78716C] shrink-0 font-sans text-xs whitespace-nowrap font-medium">₹ {{ number_format($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Footer Actions: Track Dispatch & View Order Details -->
                        <div class="pt-3 border-t border-[#E6E1D7]/60 flex items-center justify-between gap-2 text-xs">
                            <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}" 
                               class="text-[9.5px] sm:text-[10px] uppercase tracking-wider sm:tracking-[0.2em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors py-1 flex items-center shrink-0">
                                <span>Track Dispatch</span>
                                <span class="ml-1">&rarr;</span>
                            </a>

                            <a href="{{ route('account.orders.show', $order->order_reference) }}" 
                               class="border border-[#DFD9CE] text-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[9px] sm:text-[9.5px] uppercase tracking-wider sm:tracking-[0.2em] font-semibold px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-full transition-all duration-300 text-center shrink-0 whitespace-nowrap shadow-2xs">
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
