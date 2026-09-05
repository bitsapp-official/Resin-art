<x-account-layout title="Ticket {{ $ticket->ticket_number }}">
    <div class="space-y-6">
        
        <div class="flex items-center justify-between border-b border-[#E6E1D7] pb-4">
            <div>
                <span class="font-mono text-xs text-[#8E7558] font-bold block mb-1">{{ $ticket->ticket_number }} • {{ $ticket->category }}</span>
                <h2 class="font-editorial text-2xl italic font-light text-[#1C1917]">{{ $ticket->subject }}</h2>
            </div>
            <span class="bg-[#1C1917] text-white px-4 py-1 rounded-full text-xs uppercase font-semibold tracking-widest">
                {{ $ticket->status }}
            </span>
        </div>

        <!-- Thread Messages -->
        <div class="space-y-4">
            @foreach($ticket->messages as $msg)
                <div class="p-5 rounded-3xl border text-xs space-y-2 {{ $msg->is_admin ? 'bg-[#FAF8F5] border-[#E6E1D7] ml-4 sm:ml-8' : 'bg-white border-[#E6E1D7] mr-4 sm:mr-8' }}">
                    <div class="flex items-center justify-between text-[#78716C]">
                        <span class="font-semibold text-[#1C1917]">
                            {{ $msg->is_admin ? 'Maison Résine Concierge' : ($msg->user?->name ?? 'You') }}
                        </span>
                        <span>{{ $msg->created_at->format('M d, Y \a\t h:i A') }}</span>
                    </div>
                    <div class="text-[#1C1917] font-light leading-relaxed whitespace-pre-line">
                        {{ $msg->message }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Reply Form -->
        @if(!in_array($ticket->status, ['CLOSED', 'RESOLVED']))
            <div class="bg-white/80 border border-[#E6E1D7] rounded-3xl p-6 shadow-sm space-y-4">
                <h3 class="font-editorial text-lg italic text-[#1C1917]">Post a Reply</h3>
                <form method="POST" action="{{ route('account.support.reply', $ticket->id) }}" class="space-y-4 text-xs">
                    @csrf
                    <textarea name="message" rows="4" required placeholder="Type your response..." class="w-full px-4 py-3 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]"></textarea>
                    
                    <button type="submit" class="bg-[#1C1917] hover:bg-[#8E7558] text-white text-xs uppercase tracking-[0.2em] font-semibold py-3 px-6 rounded-full transition-all">
                        Send Reply
                    </button>
                </form>
            </div>
        @else
            <div class="text-center py-4 bg-gray-50 border border-gray-200 rounded-2xl text-xs text-gray-600">
                This ticket has been marked as {{ strtolower($ticket->status) }}.
            </div>
        @endif

    </div>
</x-account-layout>
