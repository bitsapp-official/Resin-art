<x-account-layout 
    title="Account Settings" 
    header-title="Account" 
    header-italic=" settings." 
    header-subtitle="Manage preferences and communication options.">
    <div class="space-y-6">
        
        <!-- Preferences Card (Lovable Design) -->
        <div class="glass rounded-[1.75rem] p-7 sm:p-9 space-y-6">
            <div class="border-b border-[#E6E1D7]/60 pb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#8E877D]">PREFERENCES</span>
            </div>

            <form method="POST" action="{{ route('account.settings.update') }}" class="space-y-4 text-xs max-w-lg">
                @csrf
                @method('PUT')

                <div class="space-y-3">
                    <label class="flex items-center space-x-3.5 cursor-pointer p-4 border border-[#E6E1D7]/60 rounded-2xl bg-white/40 hover:bg-white/70 transition-all">
                        <input type="checkbox" name="order_updates_email" value="1" {{ $settings->order_updates_email ? 'checked' : '' }} class="accent-[#1C1917] rounded">
                        <div>
                            <span class="font-normal text-[#1C1917] block" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">Order Dispatch Email Alerts</span>
                            <span class="text-[#78716C] text-[11px] font-light">Receive tracking & dispatch updates via email.</span>
                        </div>
                    </label>

                    <label class="flex items-center space-x-3.5 cursor-pointer p-4 border border-[#E6E1D7]/60 rounded-2xl bg-white/40 hover:bg-white/70 transition-all">
                        <input type="checkbox" name="promotional_email" value="1" {{ $settings->promotional_email ? 'checked' : '' }} class="accent-[#1C1917] rounded">
                        <div>
                            <span class="font-normal text-[#1C1917] block" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">Atelier Journal & Drop Previews</span>
                            <span class="text-[#78716C] text-[11px] font-light">Early access invitations to new resin drops.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-6 py-3 rounded-full transition-all duration-300 cursor-pointer">
                        SAVE PREFERENCES
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-account-layout>
