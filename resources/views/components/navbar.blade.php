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
                        $cart = \App\Models\Cart::current();
                        $cartItemCount = $cart ? $cart->items->count() : 0;
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

                <!-- RIGHT: Mobile Top Controls (Quick Search, User Profile, Quick Bag & Menu Toggle) -->
                <div class="flex items-center lg:hidden space-x-1 sm:space-x-1.5 shrink-0">
                    <!-- Mobile Search Trigger -->
                    <button type="button" @click="searchModalOpen = true" title="Search Atelier" class="text-[oklch(18%_0.012_50)] p-1.5 sm:p-2 hover:opacity-60 transition-opacity cursor-pointer">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>

                    <!-- Mobile Account / User Profile Link -->
                    <a href="{{ Auth::check() ? route('account.dashboard') : route('login') }}" 
                       title="{{ Auth::check() ? 'My Account (' . Auth::user()->name . ')' : 'Sign In / Account' }}" 
                       class="text-[oklch(18%_0.012_50)] p-1.5 sm:p-2 hover:opacity-60 transition-opacity flex items-center justify-center cursor-pointer"
                       aria-label="{{ Auth::check() ? 'My Account' : 'Sign In' }}">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </a>

                    <!-- Mobile Bag Trigger (Clean Luxury Icon with Floating Top-Right Badge) -->
                    <button @click="cartOpen = true" type="button" title="View Bag" class="text-[oklch(18%_0.012_50)] p-1.5 sm:p-2 hover:opacity-60 transition-opacity flex items-center justify-center cursor-pointer">
                        <span class="relative inline-flex items-center justify-center">
                            <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <span id="mobile-header-cart-count" class="absolute -top-1.5 -right-2 min-w-[15px] h-[15px] px-0.5 rounded-full bg-[#1C1917] text-white font-bold text-[8px] items-center justify-center leading-none shadow-2xs {{ $cartItemCount > 0 ? 'flex' : 'hidden' }}">
                                {{ $cartItemCount }}
                            </span>
                        </span>
                    </button>

                    <!-- Mobile Hamburger / Close Toggle Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                            class="text-[oklch(18%_0.012_50)] p-1.5 sm:p-2 hover:opacity-60 transition-opacity cursor-pointer"
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

                        <!-- Account / Profile Card -->
                        <a href="{{ Auth::check() ? route('account.dashboard') : route('login') }}" @click="mobileMenuOpen = false"
                           class="flex items-center justify-between p-3 rounded-2xl border border-[#DFD9CE] hover:border-[#1C1917] transition-all group shadow-2xs"
                           style="background-color: #FFFFFF !important;">
                            <div class="flex items-center space-x-2">
                                <svg class="w-3.5 h-3.5 text-[#1C1917] stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                <span class="text-[9.5px] uppercase tracking-[0.16em] font-semibold text-[#1C1917]">
                                    {{ Auth::check() ? 'My Account' : 'Sign In' }}
                                </span>
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
                        <a href="{{ route('account.dashboard') }}" @click="mobileMenuOpen = false" class="flex items-center space-x-2.5 group hover:opacity-85 transition-opacity">
                            <span class="w-8 h-8 rounded-full bg-[#1C1917] text-white text-xs font-semibold flex items-center justify-center shadow-xs shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-[#1C1917] leading-tight truncate group-hover:text-[#B87333] transition-colors">{{ Auth::user()->name }}</p>
                                <p class="text-[8.5px] uppercase tracking-wider text-[#8E877D]">Atelier Patron</p>
                            </div>
                        </a>
                        <div class="shrink-0 pl-2">
                            <a href="{{ route('logout') }}" class="text-[9.5px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] hover:text-red-700 transition-colors">
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
         x-effect="document.body.classList.toggle('overflow-hidden', searchModalOpen); if (searchModalOpen) { $nextTick(() => $refs.searchInput && $refs.searchInput.focus()); }"
         @keydown.escape.window="searchModalOpen = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-start justify-center pt-20 sm:pt-28 px-3.5 sm:px-4 pointer-events-auto">
        <div @click.away="searchModalOpen = false" 
             x-show="searchModalOpen"
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
             class="bg-white rounded-3xl p-5 sm:p-7 max-w-lg w-full shadow-2xl space-y-4 border border-[#DFD9CE]/60">
            <div class="flex items-center justify-between pb-1 border-b border-[#F0ECE1]">
                <div>
                    <h3 class="text-xs sm:text-sm font-semibold uppercase tracking-[0.2em] text-[#1C1917]">Search Atelier</h3>
                    <p class="text-[10px] sm:text-xs text-[#8E877D] mt-0.5">Explore handcrafted resin art, clocks & custom decor</p>
                </div>
                <button type="button" 
                        @click="searchModalOpen = false" 
                        class="w-8 h-8 rounded-full bg-[#FAF8F5] hover:bg-[#EBE6DD] text-[#1C1917] flex items-center justify-center transition-colors cursor-pointer shrink-0"
                        aria-label="Close search">
                    <svg class="w-4 h-4 stroke-[2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="GET" action="{{ route('shop.index') }}" class="relative w-full">
                <div class="relative flex items-center w-full">
                    <div class="absolute left-3.5 sm:left-4 text-[#8E877D] pointer-events-none flex items-center">
                        <svg class="w-4 h-4 stroke-[1.75]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           x-ref="searchInput"
                           placeholder="Search coasters, clocks, tables..." 
                           required 
                           value="{{ request('search') ?: request('q') }}" 
                           class="w-full bg-[#FAF8F5] border border-[#DFD9CE] rounded-full pl-10 sm:pl-11 pr-24 sm:pr-28 py-3 text-xs sm:text-sm text-[#1C1917] placeholder:text-[#8E877D] focus:outline-none focus:border-[#1C1917] focus:bg-white transition-all shadow-inner/10">
                    <button type="submit" 
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10.5px] sm:text-xs tracking-wider uppercase font-semibold px-4 py-2 rounded-full transition-all cursor-pointer shadow-xs">
                        Search
                    </button>
                </div>
            </form>
            <div class="pt-1">
                <p class="text-[9px] uppercase tracking-[0.2em] font-semibold text-[#8E877D] mb-2">Popular Searches</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['Coasters', 'Wall Clocks', 'Resin Tables', 'Serving Trays', 'Geode Art', 'Bookmarks'] as $tag)
                        <a href="{{ route('shop.index', ['search' => $tag]) }}" 
                           class="text-[10px] sm:text-xs px-3 py-1 rounded-full bg-[#FAF8F5] hover:bg-[#1C1917] text-[#1C1917] hover:text-white border border-[#DFD9CE] transition-all cursor-pointer">
                            {{ $tag }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
