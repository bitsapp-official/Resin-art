<x-account-layout title="Request Return">
    <div class="bg-white/80 border border-[#E6E1D7] rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
        <div class="border-b border-[#E6E1D7] pb-3">
            <h2 class="font-editorial text-2xl italic font-light text-[#1C1917]">Submit Return Request</h2>
            <p class="text-xs text-[#78716C]">Please provide the reason for your artwork return request.</p>
        </div>

        <form method="POST" action="{{ route('account.returns.store') }}" class="space-y-4 text-xs max-w-lg">
            @csrf

            <div>
                <label class="block font-medium text-[#1C1917] mb-1">Eligible Delivered Order *</label>
                <select name="order_id" required class="w-full px-4 py-2.5 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]">
                    @foreach($orders as $ord)
                        <option value="{{ $ord->id }}" {{ isset($selectedOrder) && $selectedOrder->id == $ord->id ? 'selected' : '' }}>
                            {{ $ord->order_reference }} ({{ $ord->created_at->format('M d, Y') }}) — ? {{ number_format($ord->grand_total, 2) }}
                        </option>
                    @endforeach
                </select>
                @if($orders->count() === 0)
                    <span class="text-red-700 text-[11px] block mt-1">Note: Only orders marked as 'DELIVERED' are eligible for online return requests.</span>
                @endif
            </div>

            <div>
                <label class="block font-medium text-[#1C1917] mb-1">Return Reason *</label>
                <select name="reason" required class="w-full px-4 py-2.5 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]">
                    <option value="Damaged in transit">Damaged during transit</option>
                    <option value="Defect in resin casting">Defect in resin casting</option>
                    <option value="Incorrect item received">Incorrect item received</option>
                    <option value="Size or dimension mismatch">Size or dimension mismatch</option>
                    <option value="Other">Other reason</option>
                </select>
            </div>

            <div>
                <label class="block font-medium text-[#1C1917] mb-1">Detailed Explanation *</label>
                <textarea name="description" rows="4" required placeholder="Describe any issues with your received piece..." class="w-full px-4 py-2.5 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]"></textarea>
            </div>

            <div class="flex items-center space-x-3 pt-2">
                <button type="submit" class="bg-[#1C1917] text-white text-xs uppercase tracking-[0.2em] font-semibold py-3 px-6 rounded-full transition-all">
                    Submit Return Request
                </button>
                <a href="{{ route('account.returns.index') }}" class="border border-[#E6E1D7] text-[#78716C] text-xs uppercase tracking-wider py-3 px-6 rounded-full">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-account-layout>
