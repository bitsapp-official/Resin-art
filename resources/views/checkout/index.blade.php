<x-app-layout title="Checkout — Maison Résine">
    @push('head')
    <style>
        body {
            background: oklch(98.5% .008 85) !important;
        }
    </style>
    @endpush

    @php
        $initFullName = old('full_name', $defaultAddress?->full_name ?? Auth::user()?->name ?? '');
        $initEmail = old('email', Auth::user()?->email ?? '');
        $initPhone = old('phone', $defaultAddress?->phone ?? Auth::user()?->phone ?? '');
        $initAddress1 = old('address_line_1', $defaultAddress?->address_line_1 ?? '');
        $initAddress2 = old('address_line_2', $defaultAddress?->address_line_2 ?? '');
        $initCity = old('city', $defaultAddress?->city ?? '');
        $initState = old('state', $defaultAddress?->state ?? '');
        $initPostal = old('postal_code', $defaultAddress?->postal_code ?? '');
        $initCountry = old('country', $defaultAddress?->country ?? 'India');
        $isLoggedIn = Auth::check();
    @endphp

    <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-12 py-10" 
         x-data="{ 
            currentStep: 1, 
            isLoggedIn: @js($isLoggedIn),
            selectedAddressId: @js($defaultAddress?->id ?? null),
            
            // Step 1: Contact & Delivery Recipient
            fullName: @js($initFullName),
            email: @js($initEmail),
            phone: @js($initPhone),
            
            // Step 2: Shipping Delivery Address
            address1: @js($initAddress1),
            address2: @js($initAddress2),
            city: @js($initCity),
            state: @js($initState),
            postalCode: @js($initPostal),
            country: @js($initCountry),
            
            // Step 2: Billing Address (For Tax Invoice)
            sameAsShipping: true,
            billingFullName: @js($initFullName),
            billingAddress1: '',
            billingAddress2: '',
            billingCity: '',
            billingState: '',
            billingPostalCode: '',
            billingCountry: 'India',
            
            // Step 3: Payment Card Details (Independent of Recipient Full Name)
            cardName: @js($initFullName),
            cardNumber: '',
            cardExpiry: '',
            cardCvc: '',
            paymentMethod: 'stripe',

            errors: {},

            selectSavedAddress(addr) {
                this.selectedAddressId = addr.id;
                this.fullName = addr.full_name || this.fullName;
                this.phone = addr.phone || this.phone;
                this.address1 = addr.address_line_1 || '';
                this.address2 = addr.address_line_2 || '';
                this.city = addr.city || '';
                this.state = addr.state || '';
                this.postalCode = addr.postal_code || '';
                this.country = addr.country || 'India';
                this.errors = {};
            },

            clearAddressForNew() {
                this.selectedAddressId = null;
                this.address1 = '';
                this.address2 = '';
                this.city = '';
                this.state = '';
                this.postalCode = '';
                this.country = 'India';
                this.errors = {};
            },

            validateStep1() {
                this.errors = {};
                if (!this.fullName || !this.fullName.trim()) {
                    this.errors.fullName = 'Recipient full name is required for delivery.';
                }
                if (!this.email || !this.email.trim()) {
                    this.errors.email = 'Email address is required for invoice & tracking.';
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email.trim())) {
                    this.errors.email = 'Please enter a valid email address.';
                }
                
                const phoneVal = (this.phone || '').trim();
                let mobile10 = '';
                if (phoneVal.startsWith('+91')) {
                    mobile10 = phoneVal.substring(3).replace(/[^0-9]/g, '');
                } else if (phoneVal.startsWith('+')) {
                    const digits = phoneVal.replace(/[^0-9]/g, '');
                    mobile10 = (digits.startsWith('91') && digits.length > 10) ? digits.substring(2) : digits;
                } else {
                    const rawDigits = phoneVal.replace(/[^0-9]/g, '');
                    if (rawDigits.length === 12 && rawDigits.startsWith('91')) {
                        mobile10 = rawDigits.substring(2);
                    } else if (rawDigits.length === 11 && rawDigits.startsWith('0')) {
                        mobile10 = rawDigits.substring(1);
                    } else {
                        mobile10 = rawDigits;
                    }
                }

                const dummyNumbers = [
                    '1234567890', '0123456789', '9876543210', '8765432109', '7890123456',
                    '9876598765', '1234512345', '9876512345', '9123456780', '9898989898',
                    '9797979797', '9696969696', '9595959595', '9191919191'
                ];

                if (!phoneVal) {
                    this.errors.phone = 'Mobile number is required for courier delivery.';
                } else if (mobile10.length !== 10) {
                    this.errors.phone = 'Please enter a valid 10-digit mobile number.';
                } else if (!/^[6-9]/.test(mobile10)) {
                    this.errors.phone = 'Please enter a valid mobile number starting with 6, 7, 8, or 9.';
                } else if (/(.)\1{5,}/.test(mobile10) || dummyNumbers.includes(mobile10)) {
                    this.errors.phone = 'Please provide a genuine, reachable contact number for courier coordination.';
                }

                if (Object.keys(this.errors).length === 0) {
                    // Pre-fill cardholder name if not yet customized
                    if (!this.cardName) {
                        this.cardName = this.fullName;
                    }
                    if (!this.billingFullName) {
                        this.billingFullName = this.fullName;
                    }
                    this.currentStep = 2;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            validateStep2() {
                this.errors = {};
                if (!this.address1 || !this.address1.trim()) {
                    this.errors.address1 = 'Please enter your street address for delivery.';
                }
                if (!this.city || !this.city.trim()) {
                    this.errors.city = 'Please enter your city.';
                }
                if (!this.state || !this.state.trim()) {
                    this.errors.state = 'Please enter your state or province.';
                }
                if (!this.postalCode || !this.postalCode.trim()) {
                    this.errors.postalCode = 'Please enter a valid PIN / postal code.';
                }
                if (!this.country || !this.country.trim()) {
                    this.errors.country = 'Please enter your country.';
                }

                // If billing address is different, validate billing fields
                if (!this.sameAsShipping) {
                    if (!this.billingFullName || !this.billingFullName.trim()) {
                        this.errors.billingFullName = 'Please enter the billing full name for your Tax Invoice.';
                    }
                    if (!this.billingAddress1 || !this.billingAddress1.trim()) {
                        this.errors.billingAddress1 = 'Please enter the billing street address.';
                    }
                    if (!this.billingCity || !this.billingCity.trim()) {
                        this.errors.billingCity = 'Please enter billing city.';
                    }
                    if (!this.billingState || !this.billingState.trim()) {
                        this.errors.billingState = 'Please enter billing state.';
                    }
                    if (!this.billingPostalCode || !this.billingPostalCode.trim()) {
                        this.errors.billingPostalCode = 'Please enter billing postal code.';
                    }
                }

                if (Object.keys(this.errors).length === 0) {
                    this.currentStep = 3;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }
         }">

        <!-- Title Header -->
        <div class="mb-8">
            <h1 class="font-editorial text-5xl sm:text-6xl text-[#1C1917] font-light">Checkout.</h1>
        </div>

        <form method="POST" action="{{ route('checkout.process') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- LEFT COLUMN: Step Bar + Multi-Step Form -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Step Indicator Bar -->
                    <div class="flex items-center space-x-6 text-[11px] uppercase tracking-[0.2em] mb-8">
                        <div class="flex items-center space-x-3 transition-colors duration-300" :class="currentStep >= 1 ? 'text-[#1C1917] font-medium' : 'text-[#A8A29E]'">
                            <span class="w-6 h-6 rounded-full border flex items-center justify-center text-[10px] transition-colors duration-300 bg-transparent"
                                  :class="currentStep >= 1 ? 'border-[#1C1917] text-[#1C1917] font-semibold' : 'border-[#E6E1D7] text-[#A8A29E]'">1</span>
                            <span>CONTACT</span>
                        </div>
                        <span class="w-12 h-[1px] bg-[#E6E1D7]"></span>
                        <div class="flex items-center space-x-3 transition-colors duration-300" :class="currentStep >= 2 ? 'text-[#1C1917] font-medium' : 'text-[#A8A29E]'">
                            <span class="w-6 h-6 rounded-full border flex items-center justify-center text-[10px] transition-colors duration-300 bg-transparent"
                                  :class="currentStep >= 2 ? 'border-[#1C1917] text-[#1C1917] font-semibold' : 'border-[#E6E1D7] text-[#A8A29E]'">2</span>
                            <span>DELIVERY &amp; BILLING</span>
                        </div>
                        <span class="w-12 h-[1px] bg-[#E6E1D7]"></span>
                        <div class="flex items-center space-x-3 transition-colors duration-300" :class="currentStep >= 3 ? 'text-[#1C1917] font-medium' : 'text-[#A8A29E]'">
                            <span class="w-6 h-6 rounded-full border flex items-center justify-center text-[10px] transition-colors duration-300 bg-transparent"
                                  :class="currentStep >= 3 ? 'border-[#1C1917] text-[#1C1917] font-semibold' : 'border-[#E6E1D7] text-[#A8A29E]'">3</span>
                            <span>PAYMENT</span>
                        </div>
                    </div>

                    <!-- STEP 1: Contact & Delivery Recipient Details -->
                    <div x-show="currentStep === 1" class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">RECIPIENT FULL NAME *</label>
                            <input type="text" name="full_name" x-model="fullName" placeholder="e.g. Jean Dupont" required 
                                   :class="errors.fullName ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                   class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                            <p class="text-[10px] text-[#8E877D] pl-3 font-light">Name of the person who will receive the delivery parcel.</p>
                            <template x-if="errors.fullName">
                                <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.fullName"></p>
                            </template>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">EMAIL ADDRESS (FOR INVOICE &amp; TRACKING) *</label>
                            @if(Auth::check())
                                <input type="email" name="email" value="{{ Auth::user()->email }}" readonly 
                                       class="w-full px-5 py-3.5 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium text-[#1C1917] opacity-80 cursor-default focus:outline-none select-none">
                            @else
                                <input type="email" name="email" x-model="email" placeholder="e.g. your@email.com" required 
                                       :class="errors.email ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                       class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                <template x-if="errors.email">
                                    <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.email"></p>
                                </template>
                            @endif
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">CONTACT PHONE NUMBER *</label>
                            <input type="tel" name="phone" x-model="phone" placeholder="e.g. +91 98201 45678" required 
                                   :class="errors.phone ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                   class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                            <p class="text-[10px] text-[#8E877D] pl-3 font-light">Courier partner will call this number before protective crate delivery.</p>
                            <template x-if="errors.phone">
                                <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.phone"></p>
                            </template>
                        </div>

                        <div class="pt-4">
                            <button type="button" @click="validateStep1()" class="w-full bg-[#1A1615] hover:bg-[#2C2724] text-white text-xs uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-md cursor-pointer">
                                CONTINUE TO DELIVERY
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Delivery Address & Optional Billing Address -->
                    <div x-show="currentStep === 2" x-cloak class="space-y-6">
                        
                        {{-- Saved Address Picker (For Logged in Users) --}}
                        @if(Auth::check() && $savedAddresses->count() > 0)
                            <div class="p-5 sm:p-6 rounded-[1.75rem] glass space-y-3">
                                <div class="flex items-center justify-between pb-1">
                                    <label class="block text-[9.5px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">SAVED DELIVERY ADDRESSES</label>
                                    <button type="button" 
                                            @click="clearAddressForNew()" 
                                            class="text-[9.5px] uppercase tracking-wider text-[#8E7558] hover:text-[#1C1917] font-semibold cursor-pointer underline">
                                        + Enter New Address
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($savedAddresses as $addr)
                                        <button type="button" 
                                                @click="selectSavedAddress(@js($addr))" 
                                                :class="selectedAddressId === {{ $addr->id }} ? 'ring-1.5 ring-[#1C1917] bg-white shadow-sm' : 'bg-white/50 hover:bg-white/80 border border-[#DFD9CE]/60'"
                                                class="text-left p-4 rounded-[1.25rem] text-xs transition-all duration-200 cursor-pointer relative flex flex-col justify-between">
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-semibold text-[#1C1917] block text-xs">{{ $addr->full_name }}</span>
                                                    @if($addr->is_default)
                                                        <span class="text-[8.5px] uppercase tracking-wider bg-[#1C1917] text-white px-2.5 py-0.5 rounded-full font-bold">Default</span>
                                                    @endif
                                                </div>
                                                <span class="text-[#78716C] block line-clamp-2 text-[11px] leading-relaxed">{{ $addr->address_line_1 }}@if($addr->address_line_2), {{ $addr->address_line_2 }}@endif, {{ $addr->city }}, {{ $addr->state }} {{ $addr->postal_code }}</span>
                                            </div>
                                            <span class="text-[10px] text-[#A89F90] block mt-2 font-mono">{{ $addr->phone }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Section: Shipping Destination --}}
                        <div class="space-y-4">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D] border-b border-[#E6E1D7] pb-2">
                                1. SHIPPING / DELIVERY DESTINATION CRATE
                            </div>

                            {{-- Flat / House No, Building, Society --}}
                            <div class="space-y-1.5">
                                <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">STREET ADDRESS / BUILDING / FLAT NO. *</label>
                                <input type="text" name="address_line_1" x-model="address1"
                                       placeholder="e.g. Flat 402, Royal Palms, MG Road"
                                       required
                                       :class="errors.address1 ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                       class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                <template x-if="errors.address1">
                                    <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.address1"></p>
                                </template>
                            </div>

                            {{-- Area / Locality / Landmark --}}
                            <div class="space-y-1.5">
                                <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">AREA / LOCALITY / LANDMARK (OPTIONAL)</label>
                                <input type="text" name="address_line_2" x-model="address2"
                                       placeholder="e.g. Near Phoenix Mall, Andheri West"
                                       class="w-full px-5 py-3.5 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
                            </div>

                            {{-- City & State --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">CITY / TOWN *</label>
                                    <input type="text" name="city" x-model="city"
                                           placeholder="e.g. Mumbai"
                                           required
                                           :class="errors.city ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                           class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                    <template x-if="errors.city">
                                        <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.city"></p>
                                    </template>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">STATE *</label>
                                    <input type="text" name="state" x-model="state"
                                           placeholder="e.g. Maharashtra"
                                           required
                                           :class="errors.state ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                           class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                    <template x-if="errors.state">
                                        <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.state"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- PIN Code & Country --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">PIN CODE *</label>
                                    <input type="text" name="postal_code" x-model="postalCode"
                                           placeholder="e.g. 400 053"
                                           required maxlength="10"
                                           :class="errors.postalCode ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                           class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                    <template x-if="errors.postalCode">
                                        <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.postalCode"></p>
                                    </template>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">COUNTRY *</label>
                                    <input type="text" name="country" x-model="country"
                                           placeholder="e.g. India"
                                           required
                                           :class="errors.country ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                           class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                    <template x-if="errors.country">
                                        <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.country"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Billing Address Selection --}}
                        <div class="p-5 sm:p-6 rounded-[1.75rem] glass space-y-4 border border-[#DFD9CE]/70">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                                2. BILLING ADDRESS (FOR TAX INVOICE)
                            </div>

                            <label class="flex items-start space-x-3 cursor-pointer select-none">
                                <input type="checkbox" name="same_as_shipping" value="1" x-model="sameAsShipping" class="mt-0.5 accent-[#1C1917] rounded w-4 h-4">
                                <div class="text-xs">
                                    <span class="font-medium text-[#1C1917] block">Billing address is same as shipping delivery address</span>
                                    <span class="text-[#78716C] font-light block text-[11px] pt-0.5">Recommended for 95% of personal acquisitions. Uncheck if gifting or purchasing on company/corporate name.</span>
                                </div>
                            </label>

                            {{-- Unfolded Custom Billing Address Fields --}}
                            <div x-show="!sameAsShipping" x-cloak x-transition.opacity class="space-y-4 pt-3 border-t border-[#E6E1D7]/70">
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING NAME (PERSON OR COMPANY NAME) *</label>
                                    <input type="text" name="billing_full_name" x-model="billingFullName"
                                           placeholder="e.g. Jean Dupont / Dupont Design Studio"
                                           :class="errors.billingFullName ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                           class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                    <template x-if="errors.billingFullName">
                                        <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.billingFullName"></p>
                                    </template>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING STREET ADDRESS *</label>
                                    <input type="text" name="billing_address_line_1" x-model="billingAddress1"
                                           placeholder="e.g. 101 Corporate Park, Nariman Point"
                                           :class="errors.billingAddress1 ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                           class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                    <template x-if="errors.billingAddress1">
                                        <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.billingAddress1"></p>
                                    </template>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING AREA / SUITE (OPTIONAL)</label>
                                    <input type="text" name="billing_address_line_2" x-model="billingAddress2"
                                           placeholder="e.g. Suite 400"
                                           class="w-full px-5 py-3.5 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING CITY *</label>
                                        <input type="text" name="billing_city" x-model="billingCity"
                                               placeholder="e.g. Mumbai"
                                               :class="errors.billingCity ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                               class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                        <template x-if="errors.billingCity">
                                            <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.billingCity"></p>
                                        </template>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING STATE *</label>
                                        <input type="text" name="billing_state" x-model="billingState"
                                               placeholder="e.g. Maharashtra"
                                               :class="errors.billingState ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                               class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                        <template x-if="errors.billingState">
                                            <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.billingState"></p>
                                        </template>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING PIN CODE *</label>
                                        <input type="text" name="billing_postal_code" x-model="billingPostalCode"
                                               placeholder="e.g. 400 021"
                                               :class="errors.billingPostalCode ? 'border-red-400 focus:border-red-500' : 'border-[#DFD9CE] focus:border-[#1C1917]'"
                                               class="w-full px-5 py-3.5 bg-transparent border rounded-full text-xs font-medium focus:outline-none transition-colors">
                                        <template x-if="errors.billingPostalCode">
                                            <p class="text-[10px] text-red-500 pl-3 font-medium" x-text="errors.billingPostalCode"></p>
                                        </template>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">BILLING COUNTRY *</label>
                                        <input type="text" name="billing_country" x-model="billingCountry"
                                               placeholder="e.g. India"
                                               class="w-full px-5 py-3.5 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(Auth::check())
                            <div class="pt-1">
                                <label class="flex items-center space-x-3 cursor-pointer select-none">
                                    <input type="checkbox" name="save_to_address_book" value="1" checked class="accent-[#1C1917] rounded w-4 h-4">
                                    <span class="text-xs text-[#78716C]">Save delivery address to my account for faster future checkout</span>
                                </label>
                            </div>
                        @endif

                        <div class="flex items-center space-x-4 pt-4">
                            <button type="button" @click="currentStep = 1" class="border border-[#E6E1D7] bg-transparent hover:bg-[#FAF8F5] text-[#1C1917] text-xs uppercase tracking-[0.2em] font-semibold px-8 py-4 rounded-full transition-colors cursor-pointer">
                                BACK
                            </button>
                            <button type="button" @click="validateStep2()" class="flex-1 bg-[#1A1615] hover:bg-[#2C2724] text-white text-xs uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-md cursor-pointer">
                                CONTINUE TO PAYMENT
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Payment Details -->
                    <div x-show="currentStep === 3" x-cloak class="space-y-6">
                        
                        {{-- Payment Method Selection --}}
                        <div class="space-y-4">
                            <div class="text-[10px] uppercase tracking-[0.22em] font-bold text-[#8E877D]">
                                SECURE ONLINE PAYMENT (STRIPE GATEWAY)
                            </div>

                            <input type="hidden" name="payment_method" value="stripe">

                            {{-- Stripe Official Payment Card --}}
                            <div class="p-7 sm:p-8 rounded-[2rem] bg-white border-2 border-[#1C1917] shadow-sm space-y-6 relative overflow-hidden">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-[#22C55E] animate-pulse"></span>
                                            <span class="font-semibold text-sm text-[#1C1917]">Stripe 256-Bit Encrypted Checkout</span>
                                        </div>
                                        <p class="text-xs text-[#78716C] font-light leading-relaxed">
                                            Cards (Visa, Mastercard, RuPay, Amex), UPI (Google Pay, PhonePe), and Netbanking with bank 3D Secure OTP.
                                        </p>
                                    </div>
                                    <div class="p-2.5 bg-[#F9F8F6] rounded-xl shrink-0 border border-[#E6E1D7]">
                                        <svg class="w-6 h-6 text-[#1C1917]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                </div>

                                {{-- Supported Payment Badges Grid --}}
                                <div class="pt-2 border-t border-[#F2EFE9] grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center text-[10px] uppercase tracking-wider font-semibold text-[#57534E]">
                                    <div class="p-2.5 bg-[#FAF8F5] rounded-xl border border-[#EBE6DD]">
                                        <span>VISA / MC / RUPAY</span>
                                    </div>
                                    <div class="p-2.5 bg-[#FAF8F5] rounded-xl border border-[#EBE6DD]">
                                        <span>AMEX &amp; DINERS</span>
                                    </div>
                                    <div class="p-2.5 bg-[#FAF8F5] rounded-xl border border-[#EBE6DD]">
                                        <span>UPI &amp; NETBANKING</span>
                                    </div>
                                    <div class="p-2.5 bg-[#FAF8F5] rounded-xl border border-[#EBE6DD]">
                                        <span>APPLE / GPAY</span>
                                    </div>
                                </div>

                                {{-- Security Guarantees Note --}}
                                <div class="p-4 bg-[#FAF8F5] rounded-xl border border-[#EBE6DD] flex items-start space-x-3 text-[11px] text-[#78716C] leading-relaxed">
                                    <svg class="w-4 h-4 text-[#1C1917] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>
                                        <strong>Zero Card Data Stored:</strong> When you click continue, you will be securely redirected to Stripe's bank-level hosted gateway to enter your card details and OTP. Strictly 100% online authorization.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 pt-4">
                            <button type="button" @click="currentStep = 2" class="border border-[#E6E1D7] bg-transparent hover:bg-[#FAF8F5] text-[#1C1917] text-xs uppercase tracking-[0.2em] font-semibold px-8 py-4 rounded-full transition-colors cursor-pointer">
                                BACK
                            </button>
                            <button type="submit" class="flex-1 bg-[#1A1615] hover:bg-[#2C2724] text-white text-xs uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-md cursor-pointer flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>PROCEED TO STRIPE SECURE PAYMENT (&#8377; {{ number_format($subtotal) }}) &rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Order Summary Card -->
                <div class="lg:col-span-5 sticky top-8">
                    <div class="glass rounded-[2.5rem] p-8 sm:p-10 space-y-6">
                        <h3 class="text-[10px] font-semibold uppercase tracking-[0.22em] pb-1 text-[#78716C]">YOUR ORDER SUMMARY</h3>

                        <div class="space-y-5">
                            @foreach($cartItems as $item)
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-14 h-14 bg-white rounded-2xl overflow-hidden shrink-0 border border-[#DFD9CE]">
                                            @if(!empty($item->product?->images) && isset($item->product->images[0]))
                                                <img src="{{ $item->product->images[0] }}" alt="" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-normal leading-snug text-[#1C1917]">{{ $item->product_name }} &times; {{ $item->quantity }}</h4>
                                        </div>
                                    </div>
                                    <span class="text-sm font-normal shrink-0 pl-4 text-[#1C1917] font-sans">&#8377; {{ number_format($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-[#DFD9CE]/60 pt-4 space-y-2 text-xs">
                            <div class="flex justify-between text-[#78716C]">
                                <span>Subtotal</span>
                                <span class="font-medium text-[#1C1917]">&#8377; {{ number_format($subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-[#78716C]">
                                <span>White-Glove Crated Shipping</span>
                                <span class="uppercase text-[10px] font-semibold text-[#0E5E6F]">Complimentary</span>
                            </div>
                            <div class="border-t border-[#1C1917]/10 pt-3 flex justify-between text-sm font-medium text-[#1C1917]">
                                <span>Estimated Total</span>
                                <span class="font-editorial text-lg font-bold font-sans">&#8377; {{ number_format($subtotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
