<x-account-layout 
    title="Saved Addresses" 
    header-title="Saved" 
    header-italic=" addresses." 
    header-subtitle="Locations for crate dispatch and billing.">
    <div class="space-y-6" x-data="{ 
        showAddForm: @js($errors->any() && !old('is_edit')), 
        editingId: @js(old('is_edit') ? old('address_id') : null),
        editData: {
            id: '',
            full_name: '',
            phone: '',
            address_line_1: '',
            address_line_2: '',
            city: '',
            state: '',
            postal_code: '',
            country: 'India',
            is_default: false
        },
        startEdit(addr) {
            this.showAddForm = false;
            this.editingId = addr.id;
            this.editData = {
                id: addr.id,
                full_name: addr.full_name || '',
                phone: addr.phone || '',
                address_line_1: addr.address_line_1 || '',
                address_line_2: addr.address_line_2 || '',
                city: addr.city || '',
                state: addr.state || '',
                postal_code: addr.postal_code || '',
                country: addr.country || 'India',
                is_default: Boolean(addr.is_default)
            };
            $nextTick(() => {
                const el = document.getElementById('edit-form-anchor');
                if (el) el.scrollIntoView({ behavior: 'smooth' });
            });
        },
        cancelEdit() {
            this.editingId = null;
        }
    }">
        
        <!-- Header Actions -->
        <div class="flex items-center justify-between pb-2">
            <p class="text-xs text-[#78716C]">Manage your atelier delivery locations</p>
            <button @click="showAddForm = !showAddForm; editingId = null" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.2em] font-semibold px-5 py-2.5 rounded-full transition-all duration-300 cursor-pointer">
                <span x-text="showAddForm ? '✕ Close Form' : '+ ADD NEW ADDRESS'"></span>
            </button>
        </div>

        <!-- Add Address Form -->
        <div x-show="showAddForm" x-cloak class="glass border border-[#DFD9CE]/80 rounded-[1.75rem] p-7 shadow-sm space-y-6">
            <h3 class="font-editorial text-xl italic text-[#1C1917] pb-1">New Address Details</h3>
            <form method="POST" action="{{ route('account.addresses.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                @csrf
                <input type="hidden" name="type" value="shipping">
                
                {{-- Recipient Full Name --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Recipient Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', Auth::user()->name) }}" required placeholder="e.g. {{ Auth::user()->name }} (or Gift Recipient)" 
                           class="w-full px-4 py-2.5 bg-transparent border @error('full_name') border-red-400 @else border-[#DFD9CE] @enderror rounded-full focus:outline-none focus:border-[#1C1917]">
                    @error('full_name')
                        <p class="text-[10px] text-red-500 pl-3 pt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone Number --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" required placeholder="e.g. +91 98201 45678" 
                           class="w-full px-4 py-2.5 bg-transparent border @error('phone') border-red-400 @else border-[#DFD9CE] @enderror rounded-full focus:outline-none focus:border-[#1C1917]">
                    @error('phone')
                        <p class="text-[10px] text-red-500 pl-3 pt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Street Address --}}
                <div class="sm:col-span-2">
                    <label class="block font-medium text-[#1C1917] mb-1">Flat / House No., Building, Society *</label>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1') }}" required placeholder="e.g. Flat 402, Royal Palms, MG Road" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- Landmark / Locality --}}
                <div class="sm:col-span-2">
                    <label class="block font-medium text-[#1C1917] mb-1">Area, Locality, Landmark (Optional)</label>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2') }}" placeholder="e.g. Near Phoenix Mall, Andheri West" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- City & State --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">City / Town *</label>
                    <input type="text" name="city" value="{{ old('city') }}" required placeholder="e.g. Mumbai" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">State *</label>
                    <input type="text" name="state" value="{{ old('state') }}" required placeholder="e.g. Maharashtra" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- PIN Code & Country --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">PIN Code *</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code') }}" required placeholder="e.g. 400 053" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Country *</label>
                    <input type="text" name="country" value="India" required placeholder="e.g. India" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>
                <div class="sm:col-span-2 flex items-center space-x-2">
                    <input type="checkbox" name="is_default" value="1" id="is_default" class="accent-[#1C1917] rounded">
                    <label for="is_default" class="text-[#78716C]">Set as my default delivery address</label>
                </div>
                <div class="sm:col-span-2 flex items-center space-x-3 pt-2">
                    <button type="submit" class="bg-[#1C1917] text-white px-6 py-2.5 rounded-full uppercase tracking-widest font-semibold text-[10px] cursor-pointer">
                        Save Address
                    </button>

                    <button type="button" @click="showAddForm = false" class="border border-[#E6E1D7] text-[#78716C] px-6 py-2.5 rounded-full uppercase tracking-widest font-medium text-[10px] cursor-pointer">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Edit Address Modal / Form -->
        <div id="edit-form-anchor" x-show="editingId !== null" x-cloak class="glass border border-[#DFD9CE]/80 rounded-[1.75rem] p-7 shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="font-editorial text-xl italic text-[#1C1917]">Edit Address Details</h3>
                <button type="button" @click="cancelEdit()" class="text-[#8E877D] hover:text-[#1C1917] text-xs">✕ Cancel</button>
            </div>
            
            <form method="POST" :action="'/account/addresses/' + editingId" class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="is_edit" value="1">
                <input type="hidden" name="address_id" :value="editingId">
                <input type="hidden" name="type" value="shipping">
                
                {{-- Recipient Full Name --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Recipient Full Name *</label>
                    <input type="text" name="full_name" x-model="editData.full_name" required placeholder="e.g. Sandip Sharma" 
                           class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- Phone Number --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Phone Number *</label>
                    <input type="text" name="phone" x-model="editData.phone" required placeholder="e.g. +91 98201 45678" 
                           class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- Street Address --}}
                <div class="sm:col-span-2">
                    <label class="block font-medium text-[#1C1917] mb-1">Flat / House No., Building, Society *</label>
                    <input type="text" name="address_line_1" x-model="editData.address_line_1" required placeholder="e.g. Flat 402, Royal Palms, MG Road" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- Landmark / Locality --}}
                <div class="sm:col-span-2">
                    <label class="block font-medium text-[#1C1917] mb-1">Area, Locality, Landmark (Optional)</label>
                    <input type="text" name="address_line_2" x-model="editData.address_line_2" placeholder="e.g. Near Phoenix Mall, Andheri West" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- City & State --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">City / Town *</label>
                    <input type="text" name="city" x-model="editData.city" required placeholder="e.g. Mumbai" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">State *</label>
                    <input type="text" name="state" x-model="editData.state" required placeholder="e.g. Maharashtra" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>

                {{-- PIN Code & Country --}}
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">PIN Code *</label>
                    <input type="text" name="postal_code" x-model="editData.postal_code" required placeholder="e.g. 400 053" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>
                <div>
                    <label class="block font-medium text-[#1C1917] mb-1">Country *</label>
                    <input type="text" name="country" x-model="editData.country" required placeholder="e.g. India" class="w-full px-4 py-2.5 bg-transparent border border-[#DFD9CE] rounded-full focus:outline-none focus:border-[#1C1917]">
                </div>
                <div class="sm:col-span-2 flex items-center space-x-2">
                    <input type="checkbox" name="is_default" value="1" id="edit_is_default" :checked="editData.is_default" class="accent-[#1C1917] rounded">
                    <label for="edit_is_default" class="text-[#78716C]">Set as my default delivery address</label>
                </div>
                <div class="sm:col-span-2 flex items-center space-x-3 pt-2">
                    <button type="submit" class="bg-[#1C1917] text-white px-6 py-2.5 rounded-full uppercase tracking-widest font-semibold text-[10px] cursor-pointer">
                        Update Address
                    </button>

                    <button type="button" @click="cancelEdit()" class="border border-[#E6E1D7] text-[#78716C] px-6 py-2.5 rounded-full uppercase tracking-widest font-medium text-[10px] cursor-pointer">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Addresses List -->
        @if($addresses->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach($addresses as $address)
                    <div class="glass rounded-[1.75rem] p-6 shadow-xs flex flex-col justify-between space-y-4 relative transition-all duration-300 {{ $address->is_default ? 'bg-white/70 shadow-sm' : 'bg-white/50 hover:bg-white/70' }}">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-editorial text-base italic text-[#1C1917] font-normal">{{ $address->full_name }}</h4>
                                @if($address->is_default)
                                    <span class="bg-[#1C1917] text-white text-[9px] font-medium uppercase tracking-wider px-2.5 py-0.5 rounded-full">Default</span>
                                @endif
                            </div>
                            <p class="text-xs text-[#78716C] leading-relaxed font-light">
                                {{ $address->address_line_1 }}@if($address->address_line_2), {{ $address->address_line_2 }}@endif<br>
                                {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>
                                {{ $address->country }}<br>
                                <span class="text-[#78716C] block mt-0.5">Phone: {{ $address->phone }}</span>
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-xs font-normal">
                            <div class="flex items-center space-x-3">
                                <button type="button" 
                                        @click="startEdit({{ json_encode($address) }})" 
                                        class="text-[#1C1917] hover:underline cursor-pointer transition-colors">
                                    Edit
                                </button>

                                @if(!$address->is_default)
                                    <span class="text-[#D3CBBF]">&bull;</span>
                                    <form method="POST" action="{{ route('account.addresses.default', $address->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-[#8E7558] hover:underline cursor-pointer transition-colors">
                                            Set as Default
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[#D3CBBF]">&bull;</span>
                                    <span class="text-emerald-800 font-medium">Primary</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('account.addresses.destroy', $address->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Remove address from your atelier book?')" class="text-red-700 hover:underline cursor-pointer transition-colors">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="glass text-center py-12 rounded-[1.75rem] p-6 text-xs text-[#78716C]">
                No saved addresses yet. Add an address to speed up checkout.
            </div>
        @endif

    </div>
</x-account-layout>
