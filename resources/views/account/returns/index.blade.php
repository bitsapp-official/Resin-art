<x-account-layout 
    title="Returns & Refunds" 
    header-title="Returns" 
    header-italic=" & refunds." 
    header-subtitle="Atelier return requests and studio exchange policies.">
    <div class="space-y-6">
        
        <div class="flex items-center justify-end pb-2">
            <a href="{{ route('account.returns.create') }}" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-2.5 rounded-full transition-all duration-300">
                + NEW RETURN REQUEST
            </a>
        </div>

        @if($returnRequests->count() > 0)
            <div class="space-y-4">
                @foreach($returnRequests as $req)
                    <div class="bg-white/80 border border-[#E6E1D7] rounded-3xl p-6 shadow-sm space-y-3 text-xs">
                        <div class="flex items-center justify-between border-b border-[#E6E1D7] pb-2">
                            <div>
                                <span class="font-semibold text-[#1C1917]">Order Ref: {{ $req->order?->order_reference }}</span>
                                <span class="text-[#78716C] ml-2">• Requested on {{ $req->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="bg-[#1C1917] text-white px-3 py-0.5 rounded-full text-[10px] uppercase font-semibold tracking-widest">
                                {{ $req->status }}
                            </span>
                        </div>

                        <div>
                            <span class="font-medium text-[#1C1917]">Reason:</span> {{ $req->reason }}
                            <p class="text-[#78716C] mt-1">{{ $req->description }}</p>
                        </div>

                        @if($req->admin_notes)
                            <div class="p-3 bg-[#FAF8F5] border border-[#E6E1D7] rounded-2xl text-[11px] text-[#78716C]">
                                <strong class="text-[#1C1917]">Atelier Response:</strong> {{ $req->admin_notes }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $returnRequests->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-white/40 border border-[#E6E1D7] rounded-3xl p-6 text-xs text-[#78716C]">
                No return requests found.
            </div>
        @endif

    </div>
</x-account-layout>
