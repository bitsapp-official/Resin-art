<footer class="bg-[#12100E] text-[#F5F2EC] mt-20 pt-12 pb-10 font-sans border-t border-[#241F1A]">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-12 xl:px-16">

        <!-- 5-Column Grid: Brand + 4 Link Columns Evenly Distributed -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8 lg:gap-10 pb-12 border-b border-[#241F1A]">
            
            <!-- Col 1: Maison & Origin + Direct Contact -->
            @php
                $footerContact = \App\Models\ContactPageContent::getContent();
            @endphp
            <div class="col-span-2 md:col-span-1 space-y-3.5">
                <a href="{{ route('home') }}" class="group inline-block">
                    <span class="font-editorial text-3xl text-[#FDFBF7] font-normal tracking-tight block leading-none group-hover:text-[#DFD4C0] group-hover:translate-x-0.5 transition-all duration-200">
                        Maison Résine
                    </span>
                    <span class="font-sans text-[9.5px] uppercase tracking-[0.28em] font-semibold text-[#C7B69C] block mt-1.5">
                        Bordeaux Atelier
                    </span>
                </a>
                <p class="text-[13px] text-[#B8B0A2] font-normal leading-relaxed">
                    {{ \App\Models\SiteSetting::get('footer_tagline', 'Poured by hand — One piece at a time. Natural timber and crystalline resin forged into timeless collectors works.') }}
                </p>

                <div class="space-y-1.5 text-[13px] pt-1">
                    <p>
                        <a href="mailto:{{ $footerContact->studio_email }}" class="font-medium text-[#FDFBF7] hover:text-[#C7B69C] transition-colors block truncate">
                            {{ $footerContact->studio_email }}
                        </a>
                    </p>
                    <p>
                        <a href="tel:{{ $footerContact->studio_phone }}" class="font-medium text-[#FDFBF7] hover:text-[#C7B69C] transition-colors block">
                            {{ $footerContact->studio_phone }}
                        </a>
                    </p>
                    @if(!empty($footerContact->studio_hours))
                    <p class="text-[#9C9488] text-[12px] pt-1 leading-relaxed">
                        {!! nl2br(e($footerContact->studio_hours)) !!}
                    </p>
                    @endif
                </div>
            </div>

            <!-- Col 2: SHOP -->
            <div class="space-y-3.5">
                <h4 class="text-[11px] uppercase tracking-[0.22em] font-semibold text-[#C7B69C]">Shop</h4>
                <ul class="space-y-2.5 text-[13.5px] font-normal">
                    <li><a href="{{ route('shop.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">All pieces</a></li>
                    <li><a href="{{ route('shop.new-arrivals') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">New arrivals</a></li>
                    <li><a href="{{ route('shop.best-sellers') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Best sellers</a></li>
                    <li><a href="{{ route('collections.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">All collections</a></li>
                    <li><a href="{{ route('custom.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Custom Requirements</a></li>
                </ul>
            </div>

            <!-- Col 3: HOUSE -->
            <div class="space-y-3.5">
                <h4 class="text-[11px] uppercase tracking-[0.22em] font-semibold text-[#C7B69C]">House</h4>
                <ul class="space-y-2.5 text-[13.5px] font-normal">
                    <li><a href="{{ route('gallery.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Gallery</a></li>
                    <li><a href="{{ route('about.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">About</a></li>
                    <li><a href="{{ route('our-process.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Our process</a></li>
                    <li><a href="{{ route('blog.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Journal</a></li>
                    <li><a href="{{ route('contact.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Contact</a></li>
                </ul>
            </div>

            <!-- Col 4: CUSTOMER CARE -->
            <div class="space-y-3.5">
                <h4 class="text-[11px] uppercase tracking-[0.22em] font-semibold text-[#C7B69C]">Customer Care</h4>
                <ul class="space-y-2.5 text-[13.5px] font-normal">
                    <li><a href="{{ route('contact.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Contact</a></li>
                    <li><a href="{{ route('tracking.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Track order</a></li>
                    <li><a href="{{ route('wishlist.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Wishlist</a></li>
                    <li><a href="{{ route('cart.index') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Your bag</a></li>
                    <li><a href="{{ Auth::check() ? route('account.dashboard') : route('login') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Account</a></li>
                </ul>
            </div>

            <!-- Col 5: POLICIES -->
            <div class="space-y-3.5">
                <h4 class="text-[11px] uppercase tracking-[0.22em] font-semibold text-[#C7B69C]">Policies</h4>
                <ul class="space-y-2.5 text-[13.5px] font-normal">
                    <li><a href="{{ route('legal.shipping') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Shipping policy</a></li>
                    <li><a href="{{ route('legal.return') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Return policy</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Terms & conditions</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="text-[#CCC5B9] hover:text-white hover:translate-x-1 transition-all duration-200 inline-block">Privacy policy</a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom Bar: Copyright & Socials -->
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[12px] text-[#8E877D]">
            
            <!-- Left: Copyright -->
            <div class="text-[11.5px] text-[#9C9488] font-medium tracking-wider text-center sm:text-left">
                {{ \App\Models\SiteSetting::get('footer_copyright_text', '© 2026 MAISON RÉSINE · BORDEAUX') }}
            </div>

            <!-- Right: E-Commerce Social Media Icons with Luxury Hover Effects -->
            <div class="flex items-center space-x-3">
                <!-- Instagram -->
                <a href="{{ \App\Models\SiteSetting::get('footer_instagram_url', 'https://instagram.com') }}" 
                   target="_blank" 
                   rel="noopener" 
                   aria-label="Instagram"
                   title="Follow on Instagram"
                   class="group w-9 h-9 rounded-full bg-[#1A1612] border border-[#2E261F] flex items-center justify-center text-[#A8A29A] hover:text-[#12100E] hover:bg-[#C7B69C] hover:border-[#C7B69C] hover:-translate-y-1 hover:shadow-[0_6px_16px_rgba(199,182,156,0.35)] active:scale-95 transition-all duration-300 ease-out">
                    <svg class="w-3.5 h-3.5 fill-current transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>

                <!-- YouTube -->
                <a href="{{ \App\Models\SiteSetting::get('footer_youtube_url', 'https://youtube.com') }}" 
                   target="_blank" 
                   rel="noopener" 
                   aria-label="YouTube"
                   title="Watch on YouTube"
                   class="group w-9 h-9 rounded-full bg-[#1A1612] border border-[#2E261F] flex items-center justify-center text-[#A8A29A] hover:text-[#12100E] hover:bg-[#C7B69C] hover:border-[#C7B69C] hover:-translate-y-1 hover:shadow-[0_6px_16px_rgba(199,182,156,0.35)] active:scale-95 transition-all duration-300 ease-out">
                    <svg class="w-3.5 h-3.5 fill-current transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>


                <!-- Facebook -->
                <a href="{{ \App\Models\SiteSetting::get('footer_facebook_url', 'https://facebook.com') }}" 
                   target="_blank" 
                   rel="noopener" 
                   aria-label="Facebook"
                   title="Join on Facebook"
                   class="group w-9 h-9 rounded-full bg-[#1A1612] border border-[#2E261F] flex items-center justify-center text-[#A8A29A] hover:text-[#12100E] hover:bg-[#C7B69C] hover:border-[#C7B69C] hover:-translate-y-1 hover:shadow-[0_6px_16px_rgba(199,182,156,0.35)] active:scale-95 transition-all duration-300 ease-out">
                    <svg class="w-3.5 h-3.5 fill-current transition-transform duration-300 group-hover:scale-110" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
            </div>

        </div>

    </div>
</footer>
