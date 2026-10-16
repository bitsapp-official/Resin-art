<x-app-layout title="Bespoke Jewellery & Bulk Inquiry — Fine Diamond & Gold Jewellery">

    <div class="pt-2 sm:pt-4 lg:pt-5 pb-12 sm:pb-16 lg:pb-20 bg-transparent w-full min-w-0">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 w-full min-w-0">

            {{-- ══════════════════════════════════════════════════════════════════
                 1. HERO SECTION
                 ══════════════════════════════════════════════════════════════════ --}}
            <div class="max-w-3xl mb-6 sm:mb-8 lg:mb-10 space-y-2.5 sm:space-y-3 animate-fade-up min-w-0">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>BESPOKE JEWELLERY &amp; BULK INQUIRIES</span>
                </div>

                <h1 class="font-editorial text-3xl sm:text-5xl lg:text-[72px] text-[#1C1917] font-light leading-[1.08] tracking-tight">
                    Crafted for your signature elegance.
                </h1>

                <p class="text-xs sm:text-[15px] md:text-base text-[#78716C] font-light leading-relaxed max-w-xl pt-1">
                    From bespoke solitaire engagement rings to fine gold necklaces, custom diamond sourcing, and wholesale B2B bulk orders — share your requirements with our master jewellery artisans.
                </p>
            </div>


            {{-- ══════════════════════════════════════════════════════════════════
                 2. MAIN LAYOUT GRID
                 ══════════════════════════════════════════════════════════════════ --}}
            <div class="flex flex-col lg:flex-row gap-7 lg:gap-10 items-start">

                {{-- LEFT COLUMN: Direct Consultation & Touchpoint Card --}}
                <div class="w-full lg:w-[400px] xl:w-[420px] shrink-0 space-y-6">

                    @php
                        $whatsappNum = preg_replace('/[^0-9]/', '', \App\Models\SiteSetting::get('contact_whatsapp') ?: \App\Models\SiteSetting::get('contact_phone', config('atelier.studio.whatsapp', '919876543210')));
                        $studioEmail = \App\Models\SiteSetting::get('contact_email', config('atelier.studio.email', 'concierge@solitairefinejewels.com'));
                    @endphp

                    {{-- Direct Contact Box --}}
                    <div class="bg-[#1C1917] text-white rounded-[1.75rem] p-7 lg:p-9 space-y-7 border border-white/10 shadow-sm relative overflow-hidden">
                        {{-- Subtle background gold aura glow --}}
                        <div class="absolute -right-16 -top-16 w-32 h-32 bg-[#D4AF37]/15 blur-2xl rounded-full"></div>
                        
                        <div class="space-y-3 relative z-10">
                            <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#D4AF37] block">DIRECT CONCIERGE</span>
                            <h3 class="font-editorial text-[26px] text-white font-normal leading-[1.25]">
                                Master Jeweller Advisory
                            </h3>
                        </div>

                        <div class="text-[13px] text-white/80 font-light leading-[1.6] relative z-10">
                            Prefer an immediate conversation? Connect directly with our lead jewellery consultant on WhatsApp or send an email inquiry.
                        </div>

                        <div class="space-y-3 pt-1 relative z-10">
                            <a href="https://wa.me/{{ $whatsappNum }}?text=Hello,%20I%20am%20inquiring%20about%20bespoke%20jewellery%20and%20bulk%20orders." 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="w-full inline-flex items-center justify-center gap-3 rounded-xl px-5 py-4 text-xs font-bold uppercase tracking-[0.2em] cursor-pointer text-center bg-[#25D366] hover:bg-[#1fa951] text-white transition-all shadow-md">
                                <svg class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                <span>CHAT ON WHATSAPP</span>
                            </a>

                            <a href="mailto:{{ $studioEmail }}?subject=Bespoke%20Jewellery%20Inquiry" 
                               class="w-full inline-flex items-center justify-center gap-3 rounded-xl border border-white/30 hover:border-white hover:bg-white hover:text-[#1C1917] px-5 py-3.5 text-xs font-semibold uppercase tracking-[0.2em] transition-all cursor-pointer text-center text-white">
                                <svg class="w-4 h-4 stroke-[2] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                                <span>SEND EMAIL INQUIRY</span>
                            </a>
                        </div>
                    </div>

                    {{-- Guarantees Box --}}
                    <div class="bg-[oklch(98.5%_0.008_85)] rounded-[1.75rem] p-7 space-y-4 border border-[#E5DFD3] shadow-sm">
                        <span class="text-[10px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] block">JEWELLERY GUARANTEES</span>
                        <ul class="text-xs text-[#78716C] font-light space-y-2.5">
                            <li class="flex items-center space-x-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1C1917]"></span>
                                <span>100% Certified Diamonds (IGI / SGL / GIA)</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1C1917]"></span>
                                <span>BIS Hallmarked 14KT, 18KT &amp; 22KT Gold</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1C1917]"></span>
                                <span>100% Insured Worldwide Express Delivery</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1C1917]"></span>
                                <span>Transparent Diamond Pricing &amp; Certification</span>
                            </li>
                        </ul>
                    </div>

                </div>


                {{-- RIGHT COLUMN: Custom Requirement & Inquiry Form --}}
                <div class="flex-1 w-full">
                    <div class="bg-[oklch(98.5%_0.008_85)] rounded-[2.5rem] p-8 sm:p-12 border border-[#E5DFD3] shadow-sm space-y-8">
                        
                        <div class="space-y-2 border-b border-[#E5DFD3]/80 pb-6">
                            <span class="text-[10px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">
                                INQUIRY FORM
                            </span>
                            <h2 class="font-editorial text-3xl sm:text-4xl text-[#1C1917] font-light">
                                Share your requirement.
                            </h2>
                        </div>

                        {{-- Optional Product Reference Banner --}}
                        @if(isset($product) && $product)
                            <div class="p-4 bg-white border border-[#DFD9CE] rounded-2xl flex items-center justify-between gap-4 shadow-2xs">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    @if($product->primary_image_url)
                                        <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-14 h-14 object-cover rounded-xl border border-[#EBE6DD] shrink-0">
                                    @endif
                                    <div class="min-w-0">
                                        <span class="text-[9.5px] uppercase tracking-wider text-[#8B1E3F] font-bold block">Inquiring About Piece:</span>
                                        <h4 class="text-sm font-semibold text-[#1C1917] truncate">{{ $product->name }}</h4>
                                        <p class="text-xs text-[#78716C]">₹ {{ number_format($product->effective_price) }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('shop.show', $product->slug) }}" target="_blank" class="text-xs text-[#8B1E3F] underline font-medium hover:text-[#1C1917] shrink-0">View Piece ↗</a>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="p-6 bg-red-50/90 border border-red-200 rounded-2xl space-y-2">
                                <div class="flex items-center space-x-2 text-red-800 font-semibold text-xs uppercase tracking-wider">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Please check the form inputs:</span>
                                </div>
                                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ (request()->routeIs('inquiry.*') || request()->is('inquiry*')) ? route('inquiry.store') : route('custom.store') }}" method="POST" enctype="multipart/form-data" class="space-y-7">
                            @csrf

                            {{-- Honeypot & Anti-Spam Security Fields --}}
                            <div class="opacity-0 absolute -z-50 pointer-events-none w-0 h-0 overflow-hidden" aria-hidden="true">
                                <input type="text" name="website_url_honey" tabindex="-1" autocomplete="off">
                            </div>
                            <input type="hidden" name="form_time_token" value="{{ $timeToken ?? '' }}">
                            <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                            {{-- 1. Contact & Inquiry Details in a Balanced 2-Column Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                {{-- Full Name --}}
                                <div class="space-y-1.5">
                                    <label for="name" class="block text-xs uppercase tracking-[0.22em] font-semibold text-[#1C1917]">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           required 
                                           value="{{ old('name', Auth::check() ? Auth::user()->name : '') }}" 
                                           placeholder="e.g. Sandip Rathod" 
                                           class="input-pill w-full @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email Address --}}
                                <div class="space-y-1.5">
                                    <label for="email" class="block text-xs uppercase tracking-[0.22em] font-semibold text-[#1C1917]">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           required 
                                           value="{{ old('email', Auth::check() ? Auth::user()->email : '') }}" 
                                           placeholder="sandip@example.com" 
                                           class="input-pill w-full @error('email') border-red-500 @enderror">
                                    @error('email')
                                        <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Phone / WhatsApp --}}
                                <div class="space-y-1.5">
                                    <label for="whatsapp" class="block text-xs uppercase tracking-[0.22em] font-semibold text-[#1C1917]">
                                        Phone / WhatsApp <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" 
                                           id="whatsapp" 
                                           name="whatsapp" 
                                           required
                                           value="{{ old('whatsapp', Auth::check() ? Auth::user()->phone : '') }}" 
                                           placeholder="e.g. +91 98201 45678" 
                                           class="input-pill w-full @error('whatsapp') border-red-500 @enderror">
                                    @error('whatsapp')
                                        <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Inquiry Type Selector --}}
                                <div class="space-y-1.5">
                                    <label for="project_type" class="block text-xs uppercase tracking-[0.22em] font-semibold text-[#1C1917]">
                                        Inquiry Type <span class="text-red-500">*</span>
                                    </label>
                                    <select id="project_type" name="project_type" required class="input-pill w-full bg-white text-xs sm:text-sm font-medium">
                                        <option value="Bespoke Jewellery Design" {{ old('project_type', $inquiryType ?? '') === 'Bespoke Jewellery Design' || old('project_type', $inquiryType ?? '') === 'bespoke' || old('project_type', $inquiryType ?? '') === 'Bespoke Handcrafted Design' ? 'selected' : '' }}>
                                            Bespoke / Custom Jewellery Design
                                        </option>
                                        <option value="Bulk & Wholesale B2B Order" {{ old('project_type', $inquiryType ?? '') === 'Bulk & Wholesale B2B Order' || old('project_type', $inquiryType ?? '') === 'bulk' || old('project_type', $inquiryType ?? '') === 'Bulk & Wholesale Inquiries' ? 'selected' : '' }}>
                                            Bulk &amp; Wholesale B2B Order
                                        </option>
                                        <option value="General Inquiry & Consultation" {{ old('project_type', $inquiryType ?? '') === 'General Inquiry & Consultation' || old('project_type', $inquiryType ?? '') === 'general' ? 'selected' : '' }}>
                                            General Inquiry &amp; Consultation
                                        </option>
                                    </select>
                                    @error('project_type')
                                        <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- 3. Main Requirement Description Textarea --}}
                            <div class="space-y-2">
                                <label for="idea_description" class="block text-xs uppercase tracking-[0.22em] font-semibold text-[#1C1917]">
                                    Describe Your Requirement In Your Own Words <span class="text-red-500">*</span>
                                </label>
                                @php
                                    $defaultDesc = old('idea_description');
                                    if (!$defaultDesc && isset($product) && $product) {
                                        $defaultDesc = "Inquiring regarding {$product->name} (SKU: {$product->sku}). ";
                                    }
                                @endphp
                                <textarea id="idea_description" 
                                          name="idea_description" 
                                          rows="5" 
                                          required
                                          placeholder="Describe what you have in mind — diamond carat, gold purity, preferred metal tone, or custom concept requirements..." 
                                          class="textarea-rounded w-full leading-relaxed resize-y @error('idea_description') border-[#D97706] @enderror">{{ $defaultDesc }}</textarea>
                                <p class="text-[11px] text-[#8E877D] font-light">Describe what you have in mind — diamond specifications, gold purity, sizing, or volume requirements. Our atelier team will contact you with options.</p>
                            </div>

                            {{-- 4. Custom Reference File Uploader with Live Image Previews & Working Remove (X) Button (Max 5 Images) --}}
                            <div class="pt-1">
                                <div x-data="{
                                    files: [],
                                    previews: [],
                                    handleFileSelect(event) {
                                        const newFiles = Array.from(event.target.files);
                                        this.files = [...this.files, ...newFiles].slice(0, 5);
                                        this.updatePreviewsAndInput();
                                    },
                                    removeFile(index) {
                                        this.files.splice(index, 1);
                                        this.updatePreviewsAndInput();
                                    },
                                    updatePreviewsAndInput() {
                                        this.previews = this.files.map(file => ({
                                            name: file.name,
                                            url: URL.createObjectURL(file)
                                        }));
                                        const dt = new DataTransfer();
                                        this.files.forEach(file => dt.items.add(file));
                                        if (this.$refs.fileInput) {
                                            this.$refs.fileInput.files = dt.files;
                                        }
                                    }
                                }" class="space-y-2">
                                    <label class="block text-xs uppercase tracking-[0.22em] font-semibold text-[#1C1917]">
                                        Inspiration Photos (Optional)
                                    </label>

                                    {{-- Hidden real file input --}}
                                    <input type="file" 
                                           id="reference_images" 
                                           name="reference_images[]" 
                                           x-ref="fileInput"
                                           multiple 
                                           accept="image/jpeg,image/png,image/webp,image/jpg" 
                                           @change="handleFileSelect($event)"
                                           class="hidden">

                                    {{-- Custom Dropzone Container --}}
                                    <div class="border border-dashed border-[#DFD9CE] rounded-2xl bg-[#FAF8F5] p-4 text-center transition-colors">
                                        
                                        {{-- Empty State (Click to Upload) --}}
                                        <template x-if="previews.length === 0">
                                            <button type="button" 
                                                    @click="$refs.fileInput.click()" 
                                                    class="w-full flex items-center justify-center space-x-2 text-xs text-[#78716C] font-light py-2 cursor-pointer hover:text-[#1C1917] transition-colors">
                                                <svg class="w-4 h-4 text-[#8E877D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                <span>Click or drop photos to upload (Max 5, 5MB each)</span>
                                            </button>
                                        </template>

                                        {{-- Uploaded Previews List with High-Visibility Luxury Delete (X) Button --}}
                                        <template x-if="previews.length > 0">
                                            <div class="flex flex-wrap items-center justify-center gap-5 py-2 overflow-visible">
                                                <template x-for="(file, index) in previews" :key="index">
                                                    <div class="relative w-20 h-20 rounded-2xl overflow-visible border-2 border-[#E5DFD3] shadow-md bg-white p-0.5">
                                                        <img :src="file.url" class="w-full h-full object-cover rounded-xl">
                                                        
                                                        {{-- High-Visibility Close Button Badge (Guaranteed Top-Right Position) --}}
                                                        <button type="button" 
                                                                @click.prevent.stop="removeFile(index)" 
                                                                title="Remove photo"
                                                                style="top: -8px; right: -8px; position: absolute;"
                                                                class="bg-[#1C1917] hover:bg-red-600 text-white border-2 border-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg z-30 transition-all transform hover:scale-110 cursor-pointer">
                                                            <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>

                                                {{-- Add More Button if less than 5 --}}
                                                <template x-if="previews.length < 5">
                                                    <button type="button" 
                                                            @click="$refs.fileInput.click()" 
                                                            class="w-20 h-20 rounded-2xl border-2 border-dashed border-[#DFD9CE] bg-white flex flex-col items-center justify-center text-[#8E877D] hover:border-[#1C1917] hover:text-[#1C1917] transition-all cursor-pointer shadow-sm">
                                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                        <span class="text-[10px] font-medium mt-0.5">Add Photo</span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>

                                    </div>
                                    <p class="text-[11px] text-[#8E877D] font-light">Upload up to 5 reference photos to help our atelier understand your design vision (5MB each).</p>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-6">
                                <button type="submit" 
                                        id="inquiry-submit-btn"
                                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-full bg-[#1C1917] hover:bg-[#AD9575] text-white px-9 py-4 text-xs font-semibold uppercase tracking-[0.25em] transition-all duration-300 shadow-md group cursor-pointer">
                                    <span>SUBMIT INQUIRY</span>
                                    <svg class="w-4 h-4 ml-3 group-hover:translate-x-1 transition-transform stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                </button>

                                <p class="text-[11px] text-[#8E877D] font-light text-center sm:text-right">
                                    Free atelier consultation • No upfront obligation
                                </p>
                            </div>

                        </form>

                        @if(config('services.recaptcha.site_key'))
                            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const form = document.querySelector('form');
                                    if (!form) return;
                                    form.addEventListener('submit', function (e) {
                                        if (window.grecaptcha && '{{ config('services.recaptcha.site_key') }}') {
                                            e.preventDefault();
                                            grecaptcha.ready(function () {
                                                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', { action: 'inquiry_submit' }).then(function (token) {
                                                    document.getElementById('recaptcha_token').value = token;
                                                    form.submit();
                                                });
                                            });
                                        }
                                    });
                                });
                            </script>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>
