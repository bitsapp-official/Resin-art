<x-account-layout 
    title="Profile Details" 
    header-title="Profile" 
    header-italic=" details." 
    header-subtitle="Quiet details, kept only for your orders.">
    
    <!-- YOUR DETAILS Card (Lovable Design) -->
    <div class="glass rounded-[1.25rem] sm:rounded-[1.75rem] p-4 sm:p-7 md:p-9 space-y-5 sm:space-y-6 w-full min-w-0" x-data="{ editMode: @js($errors->any()) }">
        
        <!-- Header row -->
        <div class="flex items-center justify-between border-b border-[#E6E1D7]/60 pb-3">
            <span class="text-[10px] font-bold uppercase tracking-[0.22em] text-[#8E877D]">YOUR DETAILS</span>
            <button @click="editMode = !editMode" type="button" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-4 sm:px-5 py-1.5 rounded-full transition-all duration-300 cursor-pointer">
                <span x-text="editMode ? 'CANCEL' : 'EDIT'">EDIT</span>
            </button>
        </div>

        <!-- View Mode -->
        <div x-show="!editMode" class="space-y-3.5 text-xs">
            <!-- Row 1: Name -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50 gap-3 min-w-0">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] shrink-0">NAME</span>
                <span class="text-[#1C1917] font-normal truncate text-right" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->name }}
                </span>
            </div>

            <!-- Row 2: Email -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50 gap-3 min-w-0">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] shrink-0">EMAIL</span>
                <span class="text-[#1C1917] font-normal break-all text-right text-[11.5px] sm:text-xs" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->email }}
                </span>
            </div>

            <!-- Row 3: Phone -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50 gap-3 min-w-0">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] shrink-0">PHONE</span>
                <span class="text-[#1C1917] font-normal truncate text-right" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->phone ?: ($user->addresses()->where('is_default', true)->value('phone') ?: 'Not set') }}
                </span>
            </div>

            <!-- Row 4: Member Since -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50 gap-3 min-w-0">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] shrink-0">MEMBER SINCE</span>
                <span class="text-[#1C1917] font-normal shrink-0" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif;">
                    {{ $user->created_at ? $user->created_at->format('Y') : '2026' }}
                </span>
            </div>

            <!-- Row 5: Default Shipping -->
            <div class="flex items-center justify-between py-2 border-b border-[#E6E1D7]/50 gap-3 min-w-0">
                <span class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] shrink-0">DEFAULT SHIPPING</span>
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
            <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 pt-3 sm:pt-4">
                <a href="{{ route('account.password.index') }}" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9px] sm:text-[9.5px] uppercase tracking-[0.2em] font-semibold px-4 sm:px-5 py-2 sm:py-2.5 rounded-full transition-all duration-300 whitespace-nowrap">
                    CHANGE PASSWORD
                </a>
                <a href="{{ route('account.addresses.index') }}" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9px] sm:text-[9.5px] uppercase tracking-[0.2em] font-semibold px-4 sm:px-5 py-2 sm:py-2.5 rounded-full transition-all duration-300 whitespace-nowrap">
                    SAVED ADDRESSES
                </a>
            </div>
        </div>

        <!-- Edit Mode Form -->
        <div x-show="editMode" x-cloak>
            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-4 text-xs max-w-lg">
                @csrf
                @method('PUT')

                <div class="space-y-1.5">
                    <label class="block text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">FULL NAME *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                           class="w-full px-5 py-3.5 bg-[#FAF8F5] border @error('name') border-red-400 @else border-[#DFD9CE] @enderror rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                    @error('name')
                        <p class="text-[10px] text-red-600 pl-2 pt-0.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">EMAIL ADDRESS (CANNOT BE CHANGED)</label>
                    <input type="email" value="{{ $user->email }}" readonly disabled class="w-full px-5 py-3.5 bg-[#E6E1D7]/20 border border-[#DFD9CE]/60 rounded-[1.125rem] text-xs text-[#78716C] focus:outline-none opacity-70 cursor-not-allowed">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">PHONE NUMBER</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+91 98201 45678" 
                           class="w-full px-5 py-3.5 bg-[#FAF8F5] border @error('phone') border-red-400 @else border-[#DFD9CE] @enderror rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                    @error('phone')
                        <p class="text-[10px] text-red-600 pl-2 pt-0.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center space-x-3">
                    <button type="submit" class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[10px] uppercase tracking-[0.25em] font-semibold py-3.5 px-7 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                        SAVE CHANGES
                    </button>
                    <button @click="editMode = false" type="button" class="text-[9.5px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] hover:text-[#1C1917] transition-colors cursor-pointer px-3 py-2">
                        CANCEL
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-account-layout>
