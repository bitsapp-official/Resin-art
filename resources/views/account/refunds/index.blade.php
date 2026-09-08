<x-account-layout 
    title="Refund Requests" 
    header-title="Refund" 
    header-italic=" requests." 
    header-subtitle="Track processing of cancellation refunds & financial adjustments.">
    <div class="space-y-6">

        @if($refundRequests->count() > 0)
            <div class="space-y-4">
                @foreach($refundRequests as $ref)
                    <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] p-4 sm:p-6 space-y-3 text-xs w-full min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#E6E1D7]/60 pb-3 gap-2.5">
                            <div class="min-w-0">
                                <span class="font-semibold text-[#1C1917] block sm:inline">Order Ref: {{ $ref->order?->order_reference }}</span>
                                <span class="text-[#78716C] sm:ml-2 block sm:inline text-[11px] sm:text-xs">• Submitted {{ $ref->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="flex items-center space-x-3 shrink-0">
                                <span class="font-normal text-sm text-[#1C1917]">₹ {{ number_format($ref->amount) }}</span>
                                <span class="bg-[#1C1917] text-white px-2.5 sm:px-3 py-1 rounded-full text-[8.5px] sm:text-[9px] uppercase font-semibold tracking-widest">
                                    {{ $ref->status }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <span class="font-medium text-[#1C1917]">Reason:</span> {{ $ref->reason }}
                        </div>

                        @if($ref->admin_notes)
                            <div class="p-3 bg-white/60 border border-[#E6E1D7]/60 rounded-2xl text-[11px] text-[#78716C]">
                                <strong class="text-[#1C1917]">Atelier Admin Note:</strong> {{ $ref->admin_notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $refundRequests->links() }}
            </div>
        @else
            <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] p-8 sm:p-12 text-center text-xs text-[#78716C] font-light">
                No refund requests recorded for your account.
            </div>
        @endif

    </div>
</x-account-layout>
