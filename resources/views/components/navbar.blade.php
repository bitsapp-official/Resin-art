<div x-data="{ mobileMenuOpen: false, cartOpen: {{ session('cart_open') ? 'true' : 'false' }}, searchModalOpen: false }" 
     @open-cart.window="cartOpen = true"
     class="fixed top-0 left-0 right-0 z-50 w-full pointer-events-none">

    {{-- Dimmed Backdrop when Mobile Menu is Open --}}
    <div x-show="mobileMenuOpen" 
         x-cloak 
         @click="mobileMenuOpen = false"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="lg:hidden fixed inset-0 bg-black/50 backdrop-blur-xs -z-10 cursor-pointer pointer-events-auto">
    </div>

    <div class="w-full px-4 sm:px-6 lg:px-12 xl:px-16 py-3 pointer-events-auto">
        {{-- Main Header Capsule --}}
        <header class="max-w-[1400px] mx-auto glass-nav rounded-full px-5 sm:px-6 lg:px-8 py-2.5 sm:py-3 border border-[#EBE6DD]/80 shadow-sm backdrop-blur-md">
            <div class="flex items-center justify-between">

                <!-- LEFT: Brand Logo Area (M Script + MAISON RÉSINE) -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="group flex items-center space-x-2 sm:space-x-2.5">
                        <span class="font-editorial italic text-2xl font-light text-[oklch(18%_0.012_50)] group-hover:opacity-75 transition-opacity leading-none">
                            M
                        </span>
                        <span class="font-sans text-[10.5px] sm:text-[11px] tracking-[0.25em] font-medium uppercase text-[oklch(18%_0.012_50)] group-hover:opacity-75 transition-opacity">
                            Maison Résine
                        </span>
                    </a>
                </div>

                <!-- CENTER: Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center space-x-7">
                    @php
                        $navLinks = [
                            ['route' => 'shop.index', 'label' => 'Shop', 'active' => 'shop.*'],
                            ['route' => 'collections.index', 'label' => 'Collections', 'active' => 'collections.*'],
                            ['route' => 'custom.index', 'label' => 'Custom', 'active' => 'custom.*'],
                            ['route' => 'gallery.index', 'label' => 'Gallery', 'active' => 'gallery.index'],
                            ['route' => 'blog.index', 'label' => 'Journal', 'active' => 'blog.*'],
                            ['route' => 'about.index', 'label' => 'About', 'active' => 'about.index'],
                            ['route' => 'contact.index', 'label' => 'Contact', 'active' => 'contact.index'],
                        ];
                    @endphp

                    @foreach($navLinks as $link)
                        <a href="{{ route($link['route']) }}" class="text-[10.5px] uppercase tracking-[0.22em] {{ request()->routeIs($link['active']) ? 'text-[oklch(18%_0.012_50)] font-bold' : 'text-[oklch(18%_0.012_50)]/70 hover:text-[oklch(18%_0.012_50)] font-medium' }} transition-colors duration-200">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <!-- RIGHT: Desktop Utility Controls (Search, Heart Badge, User Profile, Bag Pill) -->
                <div class="hidden lg:flex items-center space-x-3.5">
                    <!-- Search Icon Trigger -->
                    <button type="button" @click="searchModalOpen = true" title="Search" class="text-[oklch(18%_0.012_50)] hover:opacity-60 transition-opacity p-1 cursor-pointer">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>

                    <!-- Wishlist / Heart with Top-Right Circle Badge -->
                    @php
                        $wishlistCount = 0;
                        if (Auth::check()) {
                            $wishlistCount = \App\Models\Wishlist::where('user_id', Auth::id())->count();
                        } else {
                            $wishlistCount = count(session('guest_wishlist', []));
                        }
                    @endphp
                    <a href="{{ route('wishlist.index') }}" aria-label="Wishlist" title="Wishlist" class="relative text-[oklch(18%_0.012_50)] hover:opacity-60 transition-opacity p-1">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <span id="nav-wishlist-badge" class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full bg-[#1C1917] text-white font-semibold text-[9px] items-center justify-center leading-none shadow-2xs {{ $wishlistCount > 0 ? 'flex' : 'hidden' }}">{{ $wishlistCount }}</span>
                    </a>

                    <!-- Account / User Profile Link -->
                    <a href="{{ Auth::check() ? route('account.dashboard') : route('login') }}" title="{{ Auth::check() ? 'My Account' : 'Sign In' }}" class="text-[oklch(18%_0.012_50)] hover:opacity-60 transition-opacity p-1">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </a>

                    <!-- Shopping Bag Pill (with Teal Badge) -->
                    @php
                        $cartItemCount = 0;
                        if (Auth::check()) {
                            $cart = \App\Models\Cart::where('user_id', Auth::id())->withCount('items')->first();
                            $cartItemCount = $cart ? $cart->items_count : 0;
                        } else {
                            $cart = \App\Models\Cart::where('session_id', session()->getId())->whereNull('user_id')->withCount('items')->first();
                            $cartItemCount = $cart ? $cart->items_count : 0;
                        }
                    @endphp
                    <button @click="cartOpen = true" type="button" class="flex items-center space-x-2 border border-[#DFD9CE] rounded-full px-3.5 py-1 text-[10.5px] tracking-[0.18em] font-medium text-[oklch(18%_0.012_50)] bg-white/80 hover:bg-white hover:border-[#1C1917] transition-all duration-200 cursor-pointer">
                        <svg class="w-3.5 h-3.5 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span>BAG</span>
                        <span class="w-[18px] h-[18px] min-w-[18px] rounded-full bg-[#0E5E6F] text-white font-bold text-[9.5px] tracking-normal inline-flex items-center justify-center shrink-0 shadow-2xs {{ $cartItemCount > 9 ? 'px-1' : '' }}">
                            <span id="nav-cart-count" class="inline-block translate-y-[0.75px] leading-none">{{ $cartItemCount }}</span>
                        </span>
                    </button>
                </div>

                <!-- RIGHT: Mobile Top Controls (Quick Search, Quick Bag Pill & Luxury Menu Toggle) -->
                <div class="flex items-center lg:hidden space-x-1 sm:space-x-2">
                    <!-- Mobile Search Trigger -->
                    <button type="button" @click="searchModalOpen = true" title="Search Atelier" class="text-[oklch(18%_0.012_50)] p-2 hover:opacity-60 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>

                    <!-- Mobile Bag Trigger (Clean Luxury Icon with Conditional Top-Right Badge) -->
                    <button @click="cartOpen = true" type="button" title="View Bag" class="relative text-[oklch(18%_0.012_50)] p-2 hover:opacity-60 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span id="mobile-header-cart-count" class="absolute top-0.5 right-0.5 w-4 h-4 rounded-full bg-[#1C1917] text-white font-semibold text-[8.5px] items-center justify-center leading-none shadow-2xs {{ $cartItemCount > 0 ? 'flex' : 'hidden' }}">
                            {{ $cartItemCount }}
                        </span>
                    </button>

                    <!-- Mobile Hamburger / Close Toggle Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                            class="text-[oklch(18%_0.012_50)] p-2 hover:opacity-60 transition-opacity cursor-pointer"
                            aria-label="Toggle menu">
                        <svg x-show="!mobileMenuOpen" class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        {{-- Luxury Atelier Mobile Menu Dropdown Panel (Smooth Slide-Down Directly Under Header) --}}
        <div x-show="mobileMenuOpen" 
             x-cloak
             @keydown.escape.window="mobileMenuOpen = false"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 -translate-y-3 scale-[0.98]"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-3 scale-[0.98]"
             class="lg:hidden mt-2 mx-auto max-w-[1400px] rounded-3xl border border-[#DFD9CE] shadow-2xl flex flex-col overflow-hidden"
             style="background-color: #FAF8F5 !important; max-height: calc(100dvh - 85px);">

            <!-- Integrated Search Bar -->
            <div class="px-5 pt-5 pb-2 shrink-0" style="background-color: #FAF8F5 !important;">
                <form method="GET" action="{{ route('shop.index') }}" class="relative">
                    <svg class="w-4 h-4 text-[#8E877D] absolute left-4 top-1/2 -translate-y-1/2 stroke-[1.75] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="search" placeholder="Search coasters, clocks, tables..." required value="{{ request('search') ?: request('q') }}"
                           class="w-full pr-4 py-2.5 rounded-full text-xs text-[#1C1917] placeholder:text-[#8E877D] border border-[#DFD9CE] focus:outline-none focus:border-[#1C1917] transition-all shadow-2xs"
                           style="background-color: #FFFFFF !important; padding-left: 2.75rem !important;">
                </form>
            </div>

            <!-- Scrollable Navigation Body -->
            <div class="flex-1 overflow-y-auto px-5 pt-3 pb-6 space-y-5" style="background-color: #FAF8F5 !important;">

                <!-- Primary Navigation Links (Clean, Direct & Simple) -->
                <nav class="space-y-0.5">
                    @php
                        $primaryLinks = [
                            ['route' => 'shop.index', 'label' => 'Shop', 'active' => 'shop.index'],
                            ['route' => 'collections.index', 'label' => 'Collections', 'active' => 'collections.*'],
                            ['route' => 'custom.index', 'label' => 'Custom Orders', 'active' => 'custom.*'],
                            ['route' => 'gallery.index', 'label' => 'Gallery', 'active' => 'gallery.index'],
                            ['route' => 'our-process.index', 'label' => 'Our Process', 'active' => 'our-process.index'],
                            ['route' => 'blog.index', 'label' => 'Blog', 'active' => 'blog.*'],
                            ['route' => 'about.index', 'label' => 'About Us', 'active' => 'about.index'],
                            ['route' => 'contact.index', 'label' => 'Contact Us', 'active' => 'contact.index'],
                        ];
                    @endphp

                    @foreach($primaryLinks as $link)
                        <a href="{{ route($link['route']) }}" @click="mobileMenuOpen = false"
                           class="group flex items-center justify-between py-3 border-b border-[#EBE6DD] hover:translate-x-1 transition-all">
                            <span class="font-editorial text-[1.4rem] text-[#1C1917] font-light group-hover:text-[#B87333] transition-colors {{ request()->routeIs($link['active']) ? 'italic font-normal text-[#B87333]' : '' }}">
                                {{ $link['label'] }}
                            </span>
                            <span class="text-xs text-[#8E877D] group-hover:text-[#1C1917] group-hover:translate-x-1 transition-all">
                                →
                            </span>
                        </a>
                    @endforeach
                </nav>

                <!-- Quick Action Cards (Wishlist, Bag, Track Order, Contact) -->
                <div class="space-y-2 pt-1">
                    <p class="text-[8.5px] uppercase tracking-[0.25em] font-semibold text-[#8E877D]">Quick Actions</p>
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Wishlist Card -->
                        <a href="{{ route('wishlist.index') }}" @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 rounded-2xl border border-[#DFD9CE] hover:border-[#1C1917] transition-all group shadow-2xs"
                           style="background-color: #FFFFFF !important;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-3.5 h-3.5 text-[#1C1917] stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                                <span class="text-[9.5px] uppercase tracking-[0.16em] font-semibold text-[#1C1917]">Wishlist</span>
                            </div>
                            <span id="mobile-nav-wishlist-count" class="w-4 h-4 rounded-full bg-[#1C1917] text-white font-bold text-[8.5px] inline-flex items-center justify-center shrink-0 {{ $wishlistCount > 0 ? '' : 'hidden' }}">
                                {{ $wishlistCount }}
                            </span>
                        </a>

                        <!-- Bag Card -->
                        <button @click="mobileMenuOpen = false; cartOpen = true" type="button"
                                class="flex items-center justify-between p-3 rounded-2xl border border-[#DFD9CE] hover:border-[#1C1917] transition-all group shadow-2xs cursor-pointer text-left"
                                style="background-color: #FFFFFF !important;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-3.5 h-3.5 text-[#1C1917] stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span class="text-[9.5px] uppercase tracking-[0.16em] font-semibold text-[#1C1917]">Your Bag</span>
                            </div>
                            <span id="mobile-nav-cart-count" class="w-4 h-4 rounded-full bg-[#0E5E6F] text-white font-bold text-[8.5px] inline-flex items-center justify-center shrink-0">
                                {{ $cartItemCount }}
                            </span>
                        </button>

                        <!-- Track Order Card -->
                        <a href="{{ route('tracking.index') }}" @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 rounded-2xl border border-[#DFD9CE] hover:border-[#1C1917] transition-all group shadow-2xs"
                           style="background-color: #FFFFFF !important;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-3.5 h-3.5 text-[#1C1917] stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V3.75m0 3.75l-4.5-4.5m4.5 4.5l4.5-4.5" />
                                </svg>
                                <span class="text-[9.5px] uppercase tracking-[0.16em] font-semibold text-[#1C1917]">Track Order</span>
                            </div>
                            <span class="text-[9px] text-[#8E877D] group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>

                        <!-- Contact Us Card -->
                        <a href="{{ route('contact.index') }}" @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 rounded-2xl border border-[#DFD9CE] hover:border-[#1C1917] transition-all group shadow-2xs"
                           style="background-color: #FFFFFF !important;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-3.5 h-3.5 text-[#1C1917] stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                <span class="text-[9.5px] uppercase tracking-[0.16em] font-semibold text-[#1C1917]">Contact Us</span>
                            </div>
                            <span class="text-[9px] text-[#8E877D] group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Pinned Drawer Footer (Account & Atelier Info) -->
            <div class="border-t border-[#EBE6DD] px-5 py-4 shrink-0 space-y-3"
                 style="background-color: #FAF8F5 !important;">
                @if(Auth::check())
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-8 h-8 rounded-full bg-[#1C1917] text-white text-xs font-semibold flex items-center justify-center shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <div>
                                <p class="text-xs font-medium text-[#1C1917] leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[8.5px] uppercase tracking-wider text-[#8E877D]">Atelier Patron</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 text-xs">
                            <a href="{{ route('account.dashboard') }}" @click="mobileMenuOpen = false" class="text-[9.5px] uppercase tracking-[0.2em] font-semibold text-[#1C1917] hover:underline">
                                Account
                            </a>
                            <span class="text-[#DFD9CE]">·</span>
                            <a href="{{ route('logout') }}" class="text-[9.5px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] hover:text-red-700">
                                Sign Out
                            </a>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('login') }}" @click="mobileMenuOpen = false"
                           class="flex-1 bg-[#1C1917] hover:bg-[#2D2825] text-white text-center py-3 rounded-full text-[9.5px] uppercase tracking-[0.25em] font-semibold transition-all shadow-xs">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" @click="mobileMenuOpen = false"
                           class="flex-1 border border-[#DFD9CE] hover:border-[#1C1917] text-[#1C1917] text-center py-3 rounded-full text-[9.5px] uppercase tracking-[0.25em] font-semibold transition-all shadow-2xs"
                           style="background-color: #FFFFFF !important;">
                            Register
                        </a>
                    </div>
                @endif

                <p class="text-[8px] uppercase tracking-[0.25em] text-[#8E877D] text-center font-light">
                    Maison Résine Atelier · Surat, India
                </p>
            </div>

        </div>
    </div>

    <!-- Slide-over Cart Drawer Component -->
    <x-cart-drawer />

    {{-- Search Modal Overlay (At Root level to avoid Header CSS backdrop-filter containing block scroll & margin/gap bugs) --}}
    <div x-show="searchModalOpen" 
         x-cloak 
         x-effect="document.body.classList.toggle('overflow-hidden', searchModalOpen)"
         @keydown.escape.window="searchModalOpen = false" 
         class="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-start justify-center pt-24 px-4 pointer-events-auto">
        <div @click.away="searchModalOpen = false" class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-1">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-[#1C1917]">Search Atelier</h3>
                <button type="button" @click="searchModalOpen = false" class="text-gray-400 hover:text-black text-xl">&times;</button>
            </div>
            <form method="GET" action="{{ route('shop.index') }}" class="flex gap-2">
                <input type="text" name="search" placeholder="Search coasters, clocks, tables..." required value="{{ request('search') ?: request('q') }}" class="flex-1 border border-[#DFD9CE] rounded-full px-5 py-2.5 text-xs text-[#1C1917] focus:outline-none focus:border-[#1C1917]">
                <button type="submit" class="bg-[#1C1917] text-white text-xs px-6 py-2.5 rounded-full font-semibold">SEARCH</button>
            </form>
        </div>
    </div>
</div>
