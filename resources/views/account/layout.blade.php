<x-app-layout :title="($title ?? 'Customer Account') . ' — Maison Résine'">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-12 py-10" x-data="{ mobileAccountNavOpen: false }">
        
        <!-- Dynamic Header (Lovable Design) -->
        <div class="space-y-2 mb-10">
            <div class="text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                MY ACCOUNT — {{ strtoupper(explode(' ', Auth::user()->name)[0] ?? 'CUSTOMER') }}
            </div>
            <h1 class="font-editorial text-5xl sm:text-6xl text-[#1C1917] font-light leading-[1.05] tracking-tight">
                {{ $headerTitle }}<em class="italic font-normal">{{ $headerItalic }}</em>
            </h1>
            <p class="text-[13.5px] text-[#78716C] font-light">
                @if($headerSubtitle)
                    {!! $headerSubtitle !!}
                @else
                    Signed in as <span class="text-[#1C1917] font-normal">{{ Auth::user()->email }}</span>.
                @endif
            </p>
        </div>

        <!-- Mobile Account Nav Toggle -->
        <div class="lg:hidden mb-6">
            <button @click="mobileAccountNavOpen = !mobileAccountNavOpen" class="w-full flex items-center justify-between border border-[#E6E1D7] rounded-full px-5 py-3 text-xs sm:text-sm font-semibold uppercase tracking-wider text-[#1C1917]" style="background: oklch(98.5% .008 85);">
                <span>Account Menu Navigation</span>
                <span x-text="mobileAccountNavOpen ? '▲' : '▼'">▼</span>
            </button>
        </div>

        <!-- Flash Notifications -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl text-xs">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-900 rounded-2xl text-xs">
                {{ session('error') }}
            </div>
        @endif

        <style>
            @media (min-width: 1024px) {
                .account-sidebar {
                    width: 270px !important;
                    min-width: 270px !important;
                    max-width: 270px !important;
                    flex: 0 0 270px !important;
                }
            }
        </style>

        <!-- Flexbox Layout Container (Optimal 100% Zoom Desktop & Mobile Alignment) -->
        <div class="flex flex-col lg:flex-row items-start gap-8">
            
            <!-- Left Sidebar Navigation Card (Lovable Design) -->
            <aside class="account-sidebar w-full lg:w-[270px] shrink-0" :class="mobileAccountNavOpen ? 'block' : 'hidden lg:block'">
                <div class="glass rounded-[1.75rem] p-6 space-y-6">
                    
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
                                @if($unread > 0)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-600 text-white font-bold text-[9.5px]">{{ $unread }}</span>
                                @endif
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
            <main class="flex-1 min-w-0 w-full">
                {{ $slot }}
            </main>

        </div>
    </div>
</x-app-layout>
