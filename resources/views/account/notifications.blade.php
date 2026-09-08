<x-account-layout 
    title="Notifications" 
    header-title="Notifi" 
    header-italic="cations." 
    header-subtitle="Everything the atelier has told you lately.">
    
    <!-- INBOX Card (Lovable Design) -->
    <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] p-4 sm:p-7 md:p-9 space-y-5 sm:space-y-6 w-full min-w-0">
        
        <div class="flex items-center justify-between gap-3">
            <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#8E877D]">INBOX</span>
            
            @if($notifications->count() > 0 && $notifications->where('is_read', false)->count() > 0)
                <form method="POST" action="{{ route('account.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9px] sm:text-[9.5px] uppercase tracking-[0.2em] font-semibold px-3 sm:px-4 py-1.5 sm:py-2 rounded-full transition-all duration-300 cursor-pointer whitespace-nowrap">
                        MARK ALL READ
                    </button>
                </form>
            @endif
        </div>

        <div class="space-y-4 sm:space-y-5 pt-2">
            @forelse($notifications as $notif)
                <div class="flex items-start justify-between gap-3 pb-4 border-b border-[#E6E1D7]/60 last:border-none last:pb-0 w-full min-w-0">
                    <div class="flex items-start space-x-3 sm:space-x-3.5 min-w-0 flex-1">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ !$notif->is_read ? 'bg-[#8E7558]' : 'bg-[#DFD9CE]' }}"></span>
                        <div class="space-y-1 min-w-0 flex-1">
                            <h4 class="text-xs font-semibold text-[#1C1917] leading-snug">
                                {{ $notif->title }}
                            </h4>
                            <p class="text-[11.5px] text-[#78716C] font-light leading-relaxed">
                                {{ $notif->message }}
                            </p>
                            <div class="text-[9px] uppercase tracking-[0.15em] font-semibold text-[#A89F91] pt-0.5">
                                {{ $notif->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    @if(!$notif->is_read)
                        <form method="POST" action="{{ route('account.notifications.read', $notif->id) }}" class="shrink-0 whitespace-nowrap pl-2 sm:pl-4">
                            @csrf
                            <button type="submit" class="text-[9px] uppercase tracking-[0.15em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors cursor-pointer">
                                Mark read
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="py-12 text-center space-y-3">
                    <div class="w-10 h-10 mx-auto rounded-full bg-[#FAF8F5] border border-[#E6E1D7] flex items-center justify-center text-[#8E877D]">
                        <svg class="w-5 h-5 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs font-semibold text-[#1C1917]">No notifications yet</h4>
                        <p class="text-xs text-[#78716C] font-light max-w-sm mx-auto">
                            You will receive genuine updates here regarding your order progress, bespoke artwork inquiries, and atelier news.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($notifications->count() > 0)
            <div class="mt-4 pt-2">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</x-account-layout>
