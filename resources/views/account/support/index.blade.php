<x-account-layout title="Support Tickets">
    <div class="space-y-6" x-data="{ showForm: false }">
        
        <div class="flex items-center justify-between border-b border-[#E6E1D7] pb-3">
            <div>
                <h2 class="font-editorial text-2xl italic font-light text-[#1C1917]">Support & Inquiries</h2>
                <p class="text-xs text-[#78716C]">Communicate directly with our customer concierge team.</p>
            </div>
            <button @click="showForm = !showForm" class="bg-[#1C1917] text-white text-xs uppercase tracking-widest px-4 py-2 rounded-full font-semibold">
                + Open New Ticket
            </button>
        </div>

        <!-- New Ticket Form Drawer -->
        <div x-show="showForm" x-cloak class="bg-white border border-[#E6E1D7] rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="font-editorial text-xl italic text-[#1C1917] border-b pb-2">New Support Ticket</h3>
            <form method="POST" action="{{ route('account.support.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-[#1C1917] mb-1">Subject *</label>
                        <input type="text" name="subject" required placeholder="e.g. Question about my order" class="w-full px-4 py-2.5 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]">
                    </div>
                    <div>
                        <label class="block font-medium text-[#1C1917] mb-1">Category *</label>
                        <select name="category" required class="w-full px-4 py-2.5 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]">
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Order Status">Order Status</option>
                            <option value="Shipping & Delivery">Shipping & Delivery</option>
                            <option value="Custom Artwork">Custom Artwork</option>
                            <option value="Returns & Refunds">Returns & Refunds</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Detailed Message *</label>
                    <textarea name="message" rows="4" required placeholder="Type your message to the atelier team..." class="w-full px-4 py-2.5 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]"></textarea>
                </div>

                <div class="flex items-center space-x-3 pt-2">
                    <button type="submit" class="bg-[#1C1917] text-white px-6 py-2.5 rounded-full uppercase tracking-widest font-semibold text-[10px]">
                        Submit Support Ticket
                    </button>
                    <button type="button" @click="showForm = false" class="border border-[#E6E1D7] text-[#78716C] px-6 py-2.5 rounded-full uppercase tracking-widest font-medium text-[10px]">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Tickets List -->
        @if($tickets->count() > 0)
            <div class="space-y-4">
                @foreach($tickets as $tck)
                    <div class="bg-white/80 border border-[#E6E1D7] rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 text-xs">
                        <div>
                            <div class="flex items-center space-x-3 mb-1">
                                <span class="font-mono font-bold text-[#1C1917]">{{ $tck->ticket_number }}</span>
                                <span class="text-[#8E7558] uppercase font-semibold text-[10px] tracking-wider">{{ $tck->category }}</span>
                            </div>
                            <h4 class="font-semibold text-sm text-[#1C1917]">{{ $tck->subject }}</h4>
                            <span class="text-[#78716C]">Opened on {{ $tck->created_at->format('M d, Y H:i') }}</span>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span class="bg-[#1C1917] text-white px-3 py-0.5 rounded-full text-[10px] uppercase font-semibold tracking-widest">
                                {{ $tck->status }}
                            </span>
                            <a href="{{ route('account.support.show', $tck->id) }}" class="border border-[#1C1917] text-[#1C1917] hover:bg-[#1C1917] hover:text-white px-4 py-1.5 rounded-full font-semibold transition-all">
                                View Conversation →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-white/40 border border-[#E6E1D7] rounded-3xl p-6 text-xs text-[#78716C]">
                No support tickets found.
            </div>
        @endif

    </div>
</x-account-layout>
