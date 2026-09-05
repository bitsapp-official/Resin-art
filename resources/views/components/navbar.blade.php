<div x-data="{ isScrolled: false, mobileMenuOpen: false, cartOpen: {{ session('cart_open') ? 'true' : 'false' }}, searchModalOpen: false }" 
     @open-cart.window="cartOpen = true"
     @scroll.window="isScrolled = (window.pageYOffset > 20)"
     class="fixed top-0 left-0 right-0 z-50 w-full transition-all duration-300">

    <div class="w-full transition-all duration-300 px-4 sm:px-6 lg:px-12 xl:px-16 py-3">
        <header class="max-w-[1400px] mx-auto glass-nav rounded-full px-6 lg:px-8 py-3 border border-[#EBE6DD]/80 shadow-sm backdrop-blur-md transition-all duration-300 ease-in-out">
            <div class="flex items-center justify-between">

                <!-- LEFT: Brand Logo Area (M Script + MAISON RÉSINE) -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="group flex items-center space-x-2.5">
                        <span class="font-editorial italic text-2xl font-light text-[oklch(18%_0.012_50)] group-hover:opacity-75 transition-opacity leading-none">
                            M
                        </span>
                        <span class="font-sans text-[11px] tracking-[0.25em] font-medium uppercase text-[oklch(18%_0.012_50)] group-hover:opacity-75 transition-opacity">
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

                <!-- RIGHT: Utility Controls (Search, Heart Badge, User Profile, Bag Pill) -->
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
                        @if($wishlistCount > 0)
                            <span class="absolute -top-1.5 -right-2 w-4 h-4 rounded-full bg-[#1C1917] text-white font-semibold text-[9px] flex items-center justify-center leading-none shadow-2xs">{{ $wishlistCount }}</span>
                        @endif
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
                            <span class="inline-block translate-y-[0.75px] leading-none">{{ $cartItemCount }}</span>
                        </span>
                    </button>

                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center lg:hidden space-x-3">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-[#1C1917] p-2" aria-label="Toggle menu">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>
    </div>

    <!-- Mobile Drawer -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="lg:hidden bg-[#FAF8F5]/95 backdrop-blur-lg border border-[#E6E1D7] rounded-3xl mx-4 mt-2 px-8 py-6 space-y-4 shadow-xl">
        <nav class="flex flex-col space-y-3">
            @php
                $mobileNavLinks = [
                    ['route' => 'shop.index', 'label' => 'Shop', 'active' => 'shop.*'],
                    ['route' => 'collections.index', 'label' => 'Collections', 'active' => 'collections.*'],
                    ['route' => 'tracking.index', 'label' => 'Track Order', 'active' => 'tracking.*'],
                    ['route' => 'our-process.index', 'label' => 'Process', 'active' => 'our-process.index'],
                    ['route' => 'custom.index', 'label' => 'Custom', 'active' => 'custom.*'],
                    ['route' => 'gallery.index', 'label' => 'Gallery', 'active' => 'gallery.index'],
                    ['route' => 'blog.index', 'label' => 'Journal', 'active' => 'blog.*'],
                    ['route' => 'about.index', 'label' => 'About', 'active' => 'about.index'],
                    ['route' => 'contact.index', 'label' => 'Contact', 'active' => 'contact.index'],
                ];
            @endphp
            @foreach($mobileNavLinks as $link)
                <a href="{{ route($link['route']) }}" class="text-xs uppercase tracking-[0.25em] {{ request()->routeIs($link['active']) ? 'text-[#1C1917] font-bold' : 'text-[#78716C]' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('wishlist.index') }}" class="text-xs uppercase tracking-[0.25em] text-[#78716C]">Wishlist</a>
            <a href="{{ route('cart.index') }}" class="text-xs uppercase tracking-[0.25em] text-[#78716C]">Bag ({{ $cartItemCount }})</a>
            <a href="{{ Auth::check() ? route('account.dashboard') : route('login') }}" class="text-xs uppercase tracking-[0.25em] text-[#1C1917] font-semibold pt-2 border-t border-[#E6E1D7]">{{ Auth::check() ? 'My Account' : 'Sign In' }}</a>
        </nav>
    </div>

    <!-- Slide-over Cart Drawer Component -->
    <x-cart-drawer />

    {{-- Search Modal Overlay (At Root level to avoid Header CSS backdrop-filter containing block scroll & margin/gap bugs) --}}
    <div x-show="searchModalOpen" 
         x-cloak 
         x-effect="document.body.classList.toggle('overflow-hidden', searchModalOpen)"
         @keydown.escape.window="searchModalOpen = false" 
         class="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-xs flex items-start justify-center pt-24 px-4">
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

