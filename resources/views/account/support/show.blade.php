<x-account-layout title="Ticket {{ $ticket->ticket_number }}">
    <div class="space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#E6E1D7] pb-4 gap-3">
            <div>
                <span class="font-mono text-xs text-[#8E7558] font-bold block mb-1">{{ $ticket->ticket_number }} • {{ $ticket->category }}</span>
                <h2 class="font-editorial text-xl sm:text-2xl italic font-light text-[#1C1917]">{{ $ticket->subject }}</h2>
            </div>
            <span class="bg-[#1C1917] text-white px-3.5 sm:px-4 py-1 rounded-full text-[9.5px] sm:text-xs uppercase font-semibold tracking-widest shrink-0 self-start sm:self-auto">
                {{ $ticket->status }}
            </span>
        </div>

        <!-- Thread Messages -->
        <div class="space-y-4">
            @foreach($ticket->messages as $msg)
                <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl border text-xs space-y-2 {{ $msg->is_admin ? 'bg-[#FAF8F5] border-[#E6E1D7] ml-2 sm:ml-8' : 'bg-white border-[#E6E1D7] mr-2 sm:mr-8' }}">
                    <div class="flex items-center justify-between text-[#78716C] gap-2">
                        <span class="font-semibold text-[#1C1917] truncate">
                            {{ $msg->is_admin ? 'Maison Résine Concierge' : ($msg->user?->name ?? 'You') }}
                        </span>
                        <span class="shrink-0 text-[10.5px] sm:text-xs">{{ $msg->created_at->format('M d, Y \a\t h:i A') }}</span>
                    </div>
                    <div class="text-[#1C1917] font-light leading-relaxed whitespace-pre-line break-words">
                        {{ $msg->message }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Reply Form -->
        @if(!in_array($ticket->status, ['CLOSED', 'RESOLVED']))
            <div class="bg-white/80 border border-[#E6E1D7] rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-sm space-y-4">
                <h3 class="font-editorial text-base sm:text-lg italic text-[#1C1917]">Post a Reply</h3>
                <form method="POST" action="{{ route('account.support.reply', $ticket->id) }}" class="space-y-4 text-xs">
                    @csrf
                    <textarea name="message" rows="4" required placeholder="Type your response..." class="w-full px-4 py-3 bg-white border border-[#E6E1D7] rounded-xl focus:outline-none focus:border-[#1C1917]"></textarea>
                    
                    <button type="submit" class="bg-[#1C1917] hover:bg-[#8E7558] text-white text-xs uppercase tracking-[0.2em] font-semibold py-3 px-6 rounded-full transition-all cursor-pointer">
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
