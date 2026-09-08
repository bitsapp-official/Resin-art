<x-account-layout 
    title="Downloads" 
    header-title="Down" 
    header-italic="loads." 
    header-subtitle="Papers that travel with your pieces.">
    
    <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] p-4 sm:p-7 md:p-9 space-y-6 w-full min-w-0">
        <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D] pb-2">
            YOUR DOCUMENTS
        </div>

        <div class="divide-y divide-[#E6E1D7]/60 font-sans">
            @if(isset($orders) && $orders->count() > 0)
                @foreach($orders as $order)
                    {{-- 1. Invoice Document --}}
                    <div class="py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-5 first:pt-2 last:pb-2">
                        <div class="space-y-0.5 sm:space-y-1 min-w-0">
                            <h5 class="text-sm font-normal text-[#1C1917] truncate">
                                Invoice {{ $order->order_reference }}
                            </h5>
                            <p class="text-[10px] sm:text-[10.5px] tracking-wide text-[#8E877D] font-light uppercase">
                                PDF &bull; {{ $order->created_at ? $order->created_at->format('d F Y') : '' }}
                            </p>
                        </div>
                        <a href="{{ route('account.orders.invoice', $order->order_reference) }}" 
                           target="_blank"
                           class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 sm:px-6 py-2 rounded-full transition-all duration-200 whitespace-nowrap self-start sm:self-auto">
                            DOWNLOAD
                        </a>
                    </div>
                @endforeach
            @else
                <div class="py-14 text-center">
                    <p class="text-xs text-[#78716C] font-light max-w-md mx-auto leading-relaxed">
                        No documents available yet. Your acquired art piece invoices and certificates will appear here.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-account-layout>
