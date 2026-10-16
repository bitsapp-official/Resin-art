<x-app-layout :title="($title ?? 'Customer Account') . ' — Solitaire Fine Jewels'">
    <div class="max-w-[1360px] mx-auto w-full min-w-0 px-3.5 sm:px-6 lg:px-12 pt-2 sm:pt-4 pb-12 sm:pb-16">
        
        <!-- Dynamic Header (Lovable Design) -->
        <div class="space-y-1.5 sm:space-y-2 mb-6 sm:mb-10 min-w-0">
            <div class="text-[9.5px] sm:text-[10px] uppercase tracking-[0.2em] sm:tracking-[0.25em] font-medium text-[#8E877D] truncate">
                MY ACCOUNT — {{ strtoupper(explode(' ', Auth::user()->name)[0] ?? 'CUSTOMER') }}
            </div>
            <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light leading-[1.08] tracking-tight">
                {{ $headerTitle }}<em class="italic font-normal">{{ $headerItalic }}</em>
            </h1>
            <p class="text-xs sm:text-[13.5px] text-[#78716C] font-light break-words">
                @if($headerSubtitle)
                    {!! $headerSubtitle !!}
                @else
                    Signed in as <span class="text-[#1C1917] font-normal break-all">{{ Auth::user()->email }}</span>.
                @endif
            </p>
        </div>

        @php
            $unreadCount = Auth::user()->customerNotifications()->where('is_read', false)->count();
            $ordersCount = Auth::user()->orders()->count();
            $customCount = \App\Models\CustomRequest::where('user_id', Auth::id())->orWhere('email', Auth::user()->email)->count();

            $mobileTabs = [
                ['route' => 'account.dashboard', 'label' => 'Dashboard', 'active' => 'account.dashboard', 'badge' => null],
                ['route' => 'account.orders.index', 'label' => 'Orders', 'active' => 'account.orders.*', 'badge' => $ordersCount],
                ['route' => 'account.custom-requests.index', 'label' => 'Custom Requests', 'active' => 'account.custom-requests.*', 'badge' => $customCount],
                ['route' => 'wishlist.index', 'label' => 'Wishlist', 'active' => 'wishlist.*', 'badge' => null],
                ['route' => 'account.profile.index', 'label' => 'Profile', 'active' => 'account.profile.*', 'badge' => null],
                ['route' => 'account.addresses.index', 'label' => 'Addresses', 'active' => 'account.addresses.*', 'badge' => null],
                ['route' => 'tracking.index', 'label' => 'Track Order', 'active' => 'tracking.*', 'badge' => null],
                ['route' => 'account.notifications.index', 'label' => 'Notifications', 'active' => 'account.notifications.*', 'badge' => $unreadCount > 0 ? $unreadCount : null],
                ['route' => 'account.password.index', 'label' => 'Password', 'active' => 'account.password.*', 'badge' => null],
                ['route' => 'account.refunds.index', 'label' => 'Refunds', 'active' => 'account.refunds.*', 'badge' => null],
                ['route' => 'account.recently-viewed.index', 'label' => 'Recent', 'active' => 'account.recently-viewed.*', 'badge' => null],
                ['route' => 'account.downloads.index', 'label' => 'Downloads', 'active' => 'account.downloads.*', 'badge' => null],
            ];
        @endphp

        <!-- Mobile Account Horizontal Pills Navigation (Sticky On Scroll) -->
        <div class="mobile-account-sticky lg:hidden sticky top-[66px] sm:top-[74px] z-30 mb-6 -mx-3.5 sm:-mx-6 px-3.5 sm:px-6 py-2.5 bg-[#FAF8F5]/90 backdrop-blur-md border-b border-[#E6E1D7]/70 shadow-2xs transition-all"
             x-data="{
                 scrollProgress: 0,
                 canScroll: false,
                 updateScroll(el) {
                     if (!el) return;
                     const max = el.scrollWidth - el.clientWidth;
                     this.canScroll = max > 5;
                     this.scrollProgress = max > 0 ? Math.min(100, Math.max(0, (el.scrollLeft / max) * 100)) : 0;
                 }
             }"
             @resize.window.passive="if ($refs.pillsNav) updateScroll($refs.pillsNav)">
            <div class="relative">
                <nav x-ref="pillsNav"
                     @scroll.passive="updateScroll($el)"
                     class="flex items-center space-x-2 overflow-x-auto no-scrollbar py-1 scroll-smooth" 
                     x-init="$nextTick(() => { 
                         updateScroll($el);
                         const activeTab = $el.querySelector('[data-active=true]'); 
                         if (activeTab) {
                             activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                             setTimeout(() => updateScroll($el), 350);
                         }
                     })">
                    @foreach($mobileTabs as $tab)
                        @php $isActive = request()->routeIs($tab['active']); @endphp
                        <a href="{{ route($tab['route']) }}" 
                           data-active="{{ $isActive ? 'true' : 'false' }}"
                           class="inline-flex items-center shrink-0 px-4 py-2 rounded-full text-[11.5px] font-medium tracking-wider uppercase transition-all duration-200 {{ $isActive 
                               ? 'bg-[#1C1917] text-white shadow-xs font-semibold' 
                               : 'bg-white/80 text-[#66615C] hover:text-[#1C1917] hover:bg-white border border-[#E6E1D7] shadow-2xs' }}">
                            <span>{{ $tab['label'] }}</span>
                            @if(!empty($tab['badge']))
                                <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold leading-none {{ $isActive ? 'bg-white/20 text-white' : 'bg-[#1C1917]/10 text-[#1C1917]' }}">
                                    {{ $tab['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}" class="inline-flex shrink-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center shrink-0 px-4 py-2 rounded-full text-[11.5px] font-semibold tracking-wider uppercase bg-red-50 hover:bg-red-100 text-red-700 border border-red-200/70 shadow-2xs transition-colors cursor-pointer">
                            Sign Out
                        </button>
                    </form>
                </nav>

                <!-- Visual Scrollbar Indicator Track -->
                <div x-show="canScroll" class="flex items-center justify-center pt-2.5 pb-0.5 select-none" aria-hidden="true">
                    <div class="w-24 h-[3px] bg-[#E6E1D7] rounded-full overflow-hidden relative shadow-2xs">
                        <div class="h-full w-8 bg-[#1C1917] rounded-full absolute left-0 transition-transform duration-100 ease-out"
                             :style="`transform: translateX(${ (scrollProgress / 100) * (96 - 32) }px);`">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            @media (min-width: 1024px) {
                .account-sidebar {
                    width: 270px !important;
                    min-width: 270px !important;
                    max-width: 270px !important;
                    flex: 0 0 270px !important;
                    position: -webkit-sticky !important;
                    position: sticky !important;
                    top: 96px !important;
                    align-self: flex-start !important;
                    z-index: 30 !important;
                }
                .account-sidebar-card {
                    max-height: calc(100vh - 120px) !important;
                    overflow-y: auto !important;
                    scrollbar-width: none !important;
                    -ms-overflow-style: none !important;
                }
                .account-sidebar-card::-webkit-scrollbar {
                    display: none !important;
                }
            }
            .mobile-account-sticky {
                position: -webkit-sticky !important;
                position: sticky !important;
                top: 66px !important;
                z-index: 35 !important;
            }
        </style>

        <!-- Flexbox Layout Container (Optimal 100% Zoom Desktop & Mobile Alignment) -->
        <div class="flex flex-col lg:flex-row items-stretch lg:items-start gap-6 lg:gap-8 w-full min-w-0">
            
            <!-- Left Sidebar Navigation Card (Sticky on Desktop) -->
            <aside class="account-sidebar hidden lg:block w-[270px] shrink-0 sticky top-24 self-start">
                <div class="account-sidebar-card glass rounded-[1.75rem] p-6 space-y-6 max-h-[calc(100vh-7.5rem)] overflow-y-auto no-scrollbar shadow-sm border border-[#E6E1D7]/70">
                    
                    <!-- Group 1: OVERVIEW -->
                    <div class="space-y-2">
                        <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] px-3">OVERVIEW</div>
                        <div class="space-y-1">
                            <a href="{{ route('account.dashboard') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.dashboard') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('account.notifications.index') }}" 
                               class="flex items-center justify-between px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.notifications.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                <span>Notifications</span>
                                @php $unread = Auth::user()->customerNotifications()->where('is_read', false)->count(); @endphp
                                <span class="text-[10.5px] opacity-75 font-normal">({{ $unread }})</span>
                            </a>
                            <a href="{{ route('account.recently-viewed.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.recently-viewed.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Recently viewed
                            </a>
                        </div>
                    </div>

                    <!-- Group 2: ORDERS -->
                    <div class="space-y-2 pt-2 border-t border-[#E6E1D7]/60">
                        <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] px-3">ORDERS</div>
                        <div class="space-y-1">
                            <a href="{{ route('account.orders.index') }}" 
                               class="flex items-center justify-between px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.orders.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                <span>Order history</span>
                                <span class="text-[10.5px] opacity-75 font-normal">({{ Auth::user()->orders()->count() }})</span>
                            </a>
                            <a href="{{ route('account.custom-requests.index') }}" 
                               class="flex items-center justify-between px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.custom-requests.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                <span>Custom requests</span>
                                @php $customCount = \App\Models\CustomRequest::where('user_id', Auth::id())->orWhere('email', Auth::user()->email)->count(); @endphp
                                <span class="text-[10.5px] opacity-75 font-normal">({{ $customCount }})</span>
                            </a>
                            <a href="{{ route('tracking.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium">
                                Track order
                            </a>
                            <a href="{{ route('account.refunds.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.refunds.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Refund requests
                            </a>
                            <a href="{{ route('account.downloads.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.downloads.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Downloads
                            </a>
                        </div>
                    </div>

                    <!-- Group 3: DETAILS -->
                    <div class="space-y-2 pt-2 border-t border-[#E6E1D7]/60">
                        <div class="text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] px-3">DETAILS</div>
                        <div class="space-y-1">
                            <a href="{{ route('account.profile.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.profile.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Profile
                            </a>
                            <a href="{{ route('account.password.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.password.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Change password
                            </a>
                            <a href="{{ route('account.addresses.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all {{ request()->routeIs('account.addresses.*') ? 'bg-[#1C1917] text-white font-semibold shadow-sm' : 'text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium' }}">
                                Saved addresses
                            </a>
                            <a href="{{ route('wishlist.index') }}" 
                               class="block px-4 py-2 rounded-full text-[13px] transition-all text-[#66615C] hover:text-[#1C1917] hover:bg-white/70 font-medium">
                                Wishlist
                            </a>
                        </div>
                    </div>

                    <!-- Sign Out -->
                    <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-[#E6E1D7]/60">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 rounded-full text-[13px] font-semibold text-red-700 hover:bg-red-50/80 transition-all cursor-pointer">
                            Sign Out
                        </button>
                    </form>

                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 min-w-0 w-full space-y-5 sm:space-y-6">
                @if(session('success'))
                    <x-alert type="success" :message="session('success')" />
                @endif
                @if(session('error'))
                    <x-alert type="error" :message="session('error')" />
                @endif
                @if(session('status'))
                    <x-alert type="info" :message="session('status')" />
                @endif

                {{ $slot }}
            </div>

        </div>

        <!-- Floating Back to Top / Quick Jump Action -->
        <div x-data="{ showTop: false }"
             @scroll.window.passive="showTop = window.pageYOffset > 400"
             x-show="showTop"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-3"
             class="fixed bottom-24 right-5 sm:right-8 z-40 pointer-events-auto">
            <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                    type="button"
                    aria-label="Scroll back to top"
                    class="group flex items-center space-x-2 bg-white/95 hover:bg-[#1C1917] text-[#1C1917] hover:text-white border border-[#E6E1D7] shadow-xl backdrop-blur-md px-3.5 py-2.5 rounded-full text-xs font-medium transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer">
                <svg class="w-3.5 h-3.5 stroke-[2.2] group-hover:-translate-y-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                </svg>
                <span class="text-[10px] sm:text-[10.5px] uppercase tracking-wider font-semibold pr-0.5">Top</span>
            </button>
        </div>
    </div>
</x-app-layout>
