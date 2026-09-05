<x-account-layout 
    title="Profile Details" 
    header-title="Profile" 
    header-italic=" details." 
    header-subtitle="Quiet details, kept only for your orders.">
    
    <!-- YOUR DETAILS Card (Lovable Design) -->
    <div class="glass rounded-[1.75rem] p-7 sm:p-9 space-y-6" x-data="{ editMode: @js($errors->any()) }">
        
        <!-- Header row -->
        <div class="flex items-center justify-between border-b border-[#E6E1D7]/60 pb-3">
            <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#8E877D]">YOUR DETAILS</span>
            <button @click="editMode = !editMode" type="button" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-1.5 rounded-full transition-all duration-300 cursor-pointer">
                <span x-text="editMode ? 'CANCEL' : 'EDIT'">EDIT</span>
            </button>
        </div>

        <!-- View Mode -->
        <div x-show="!editMode" class="space-y-4 text-xs">
            <!-- Row 1: Name -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">NAME</span>
                <span class="text-[#1C1917] font-normal" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->name }}
                </span>
            </div>

            <!-- Row 2: Email -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">EMAIL</span>
                <span class="text-[#1C1917] font-normal" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->email }}
                </span>
            </div>

            <!-- Row 3: Phone -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">PHONE</span>
                <span class="text-[#1C1917] font-normal" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->phone ?: ($user->addresses()->where('is_default', true)->value('phone') ?: 'Not set') }}
                </span>
            </div>

            <!-- Row 4: Member Since -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">MEMBER SINCE</span>
                <span class="text-[#1C1917] font-normal" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->created_at ? $user->created_at->format('Y') : '2026' }}
                </span>
            </div>

            <!-- Row 5: Default Shipping -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">DEFAULT SHIPPING</span>
                <span class="text-[#1C1917] font-normal line-clamp-1 max-w-xs text-right" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    @php
                        $defaultAddr = $user->addresses()->where('is_default', true)->first();
                    @endphp
                    @if($defaultAddr)
                        {{ $defaultAddr->address_line_1 }}, {{ $defaultAddr->city }}
                    @else
                        Primary address on file
                    @endif
                </span>
            </div>

            <!-- Bottom Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 pt-4">
                <a href="{{ route('account.password.index') }}" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-2.5 rounded-full transition-all duration-300">
                    CHANGE PASSWORD
                </a>
                <a href="{{ route('account.addresses.index') }}" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-2.5 rounded-full transition-all duration-300">
                    SAVED ADDRESSES
                </a>
            </div>
        </div>

        <!-- Edit Mode Form -->
        <div x-show="editMode" x-cloak>
            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-4 text-xs max-w-lg">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] mb-1.5">FULL NAME *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                           class="w-full px-5 py-3 bg-transparent border @error('name') border-red-400 @else border-[#DFD9CE] @enderror rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
                    @error('name')
                        <p class="text-[10px] text-red-500 pl-3 pt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] mb-1.5">EMAIL ADDRESS (CANNOT BE CHANGED)</label>
                    <input type="email" value="{{ $user->email }}" readonly disabled class="w-full px-5 py-3 bg-[#E6E1D7]/20 border border-[#DFD9CE]/60 rounded-full text-xs font-medium focus:outline-none opacity-70 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] mb-1.5">PHONE NUMBER</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98201 45678" 
                           class="w-full px-5 py-3 bg-transparent border @error('phone') border-red-400 @else border-[#DFD9CE] @enderror rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
                    @error('phone')
                        <p class="text-[10px] text-red-500 pl-3 pt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center space-x-3">
                    <button type="submit" class="border border-[#1C1917] bg-[#1C1917] text-white text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3 px-6 rounded-full transition-all duration-300 cursor-pointer">
                        SAVE CHANGES
                    </button>
                    <button @click="editMode = false" type="button" class="text-[9.5px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors cursor-pointer">
                        CANCEL
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-account-layout>
