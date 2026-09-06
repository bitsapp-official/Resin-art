<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Maison Résine — Handcrafted Resin Art & Objects' }}</title>
    <meta name="description" content="Handcrafted made-to-order resin art, fluid wall clocks, bespoke tables and artisan objects.">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph SEO Metadata -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? 'Maison Résine — Handcrafted Resin Art & Objects' }}">
    <meta property="og:description" content="Handcrafted made-to-order resin art, fluid wall clocks, bespoke tables and artisan objects.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Maison Résine">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    @if(request()->is('/'))
        <link rel="preload" as="image" href="{{ asset('storage/gallery/segre_river_table.webp') }}" type="image/webp" fetchpriority="high">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full flex flex-col bg-[#FAF8F5] text-[#1C1917] antialiased relative overflow-x-clip">
    @if(request()->is('/') || request()->routeIs('shop.index'))
        <!-- Premium Resin Art Branded Initial Site Entrance Loader Component (First Visit Only) -->
        <x-site-loader />
    @endif

    <!-- Ambient Background Lighting Glows (Teal Left & Warm Gold Right) - Zero-GPU Pre-Rendered Bitmaps (100% Identical Blur Look, 0% GPU Load) -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden select-none" aria-hidden="true">
        <img src="{{ asset('images/ambient-glow-teal.png') }}"
             class="absolute -top-28 -left-28 w-[720px] h-[720px] max-w-none opacity-90 pointer-events-none"
             alt=""
             loading="eager"
             decoding="async">
        <img src="{{ asset('images/ambient-glow-gold.png') }}"
             class="absolute -top-28 -right-28 w-[720px] h-[720px] max-w-none opacity-90 pointer-events-none"
             alt=""
             loading="eager"
             decoding="async">
        <div class="absolute inset-0 bg-noise opacity-35 pointer-events-none"></div>
    </div>

    <div class="relative z-10 flex-grow flex flex-col">
        <!-- Navigation Header -->
        <x-navbar />

        <!-- Main Body Content -->
        <main class="flex-grow {{ request()->is('/') ? '' : 'pt-24 sm:pt-28' }}">
            {{ $slot }}
        </main>

        <!-- Dark Luxury Footer -->
        <x-footer />
    </div>

    {{-- Auto-open cart drawer when item added --}}
    @if(session('cart_open'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.dispatchEvent(new CustomEvent('open-cart'));
            });
        </script>
    @endif

    {{-- Dynamic CSRF Token Refresh on Window Focus / Visibility Change to prevent 419 Page Expired --}}
    <script>
        (function() {
            let lastFocusTime = Date.now();
            function refreshCsrfToken() {
                // Only refresh if an active form with a CSRF token exists on this page
                if (!document.querySelector('input[name="_token"]')) return;

                // If the user returns to this tab after being away for more than 15 seconds, refresh CSRF
                if (Date.now() - lastFocusTime > 15000) {
                    fetch('{{ route("csrf.token") }}', {
                        headers: { 
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.ok ? response.json() : null)
                    .then(data => {
                        if (data && data.token) {
                            const metaToken = document.querySelector('meta[name="csrf-token"]');
                            if (metaToken) metaToken.setAttribute('content', data.token);
                            document.querySelectorAll('input[name="_token"]').forEach(input => {
                                input.value = data.token;
                            });
                        }
                    })
                    .catch(() => {});
                }
                lastFocusTime = Date.now();
            }

            window.addEventListener('focus', refreshCsrfToken);
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    refreshCsrfToken();
                }
            });
        })();
    </script>

    {{-- Reactive AJAX Wishlist Handler (Zero-Reload, Real-time Header Increment, Seamless State Sync) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('submit', function (e) {
                const form = e.target.closest('.wishlist-toggle-form');
                if (!form) return;

                e.preventDefault();
                e.stopPropagation();

                const btn = form.querySelector('.wishlist-btn') || form.querySelector('button[type="submit"]');
                const productId = form.dataset.productId || form.querySelector('input[name="product_id"]')?.value;
                if (!productId) return;

                if (btn && btn.disabled) return;
                if (btn) btn.disabled = true;

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || form.querySelector('input[name="_token"]')?.value;

                fetch(form.action || '{{ route("wishlist.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        const isAdded = data.added;
                        const newCount = data.count;

                        // 1. Update all matching product cards on the current page for this product_id
                        document.querySelectorAll(`.wishlist-btn[data-product-id="${productId}"]`).forEach(button => {
                            button.setAttribute('data-wishlisted', isAdded ? 'true' : 'false');
                            button.setAttribute('title', isAdded ? 'Remove from Wishlist' : 'Add to Wishlist');

                            const styleType = button.getAttribute('data-style-type');
                            const icon = button.querySelector('.wishlist-icon') || button.querySelector('svg');

                            if (styleType === 'product-card') {
                                if (icon) {
                                    if (isAdded) {
                                        icon.classList.remove('fill-none', 'stroke-[#1C1917]');
                                        icon.classList.add('fill-[#B87333]', 'stroke-[#B87333]');
                                    } else {
                                        icon.classList.remove('fill-[#B87333]', 'stroke-[#B87333]');
                                        icon.classList.add('fill-none', 'stroke-[#1C1917]');
                                    }
                                }
                            } else if (styleType === 'shop-grid' || styleType === 'related-item') {
                                if (isAdded) {
                                    button.classList.remove('glass-pill', 'border', 'border-white/70', 'text-[#1C1917]', 'hover:bg-[#1C1917]', 'hover:text-white');
                                    button.classList.add('bg-[#1C1917]', 'text-white');
                                    if (icon) {
                                        icon.classList.remove('fill-none');
                                        icon.classList.add('fill-current');
                                    }
                                } else {
                                    button.classList.remove('bg-[#1C1917]', 'text-white');
                                    button.classList.add('glass-pill', 'border', 'border-white/70', 'text-[#1C1917]', 'hover:bg-[#1C1917]', 'hover:text-white');
                                    if (icon) {
                                        icon.classList.remove('fill-current');
                                        icon.classList.add('fill-none');
                                    }
                                }
                            } else if (styleType === 'detail-main') {
                                if (isAdded) {
                                    button.classList.remove('bg-white', 'border', 'border-[#DFD9CE]', 'text-[#1C1917]', 'hover:border-[#1C1917]', 'shadow-2xs');
                                    button.classList.add('bg-[#1C1917]', 'text-white', 'shadow-xs');
                                    if (icon) {
                                        icon.classList.remove('fill-none');
                                        icon.classList.add('fill-current');
                                    }
                                } else {
                                    button.classList.remove('bg-[#1C1917]', 'text-white', 'shadow-xs');
                                    button.classList.add('bg-white', 'border', 'border-[#DFD9CE]', 'text-[#1C1917]', 'hover:border-[#1C1917]', 'shadow-2xs');
                                    if (icon) {
                                        icon.classList.remove('fill-current');
                                        icon.classList.add('fill-none');
                                    }
                                }
                            }
                        });

                        // 2. Update Header Wishlist Counter Badge (Desktop & Mobile)
                        const desktopBadge = document.getElementById('nav-wishlist-badge');
                        if (desktopBadge) {
                            desktopBadge.textContent = newCount;
                            if (newCount > 0) {
                                desktopBadge.classList.remove('hidden');
                                desktopBadge.classList.add('flex');
                            } else {
                                desktopBadge.classList.remove('flex');
                                desktopBadge.classList.add('hidden');
                            }
                        }

                        const mobileBadge = document.getElementById('mobile-nav-wishlist-count');
                        if (mobileBadge) {
                            mobileBadge.textContent = newCount;
                            if (newCount > 0) {
                                mobileBadge.classList.remove('hidden');
                                mobileBadge.classList.add('inline-flex');
                            } else {
                                mobileBadge.classList.remove('inline-flex');
                                mobileBadge.classList.add('hidden');
                            }
                        }

                        // 3. Wishlist Index Page Handling (Smooth fadeout if removed on wishlist page)
                        const wishlistCard = document.getElementById(`wishlist-card-${productId}`);
                        if (wishlistCard) {
                            wishlistCard.style.opacity = '0';
                            wishlistCard.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                wishlistCard.remove();
                                const pageCount = document.getElementById('wishlist-page-count');
                                if (pageCount) {
                                    pageCount.textContent = `${newCount} ${newCount === 1 ? 'piece' : 'pieces'}`;
                                }
                                const remainingCards = document.querySelectorAll('.wishlist-page-card');
                                if (remainingCards.length === 0) {
                                    const container = document.getElementById('wishlist-items-container');
                                    if (container) {
                                        container.innerHTML = `
                                            <div id="wishlist-empty-state" class="text-center py-20 bg-white/40 border border-[#E6E1D7] rounded-3xl p-8">
                                                <svg class="w-12 h-12 mx-auto text-[#A8A29E] mb-4 stroke-[1.25]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                                </svg>
                                                <h3 class="font-editorial text-2xl text-[#1C1917] font-light mb-2">No saved pieces</h3>
                                                <p class="text-xs text-[#78716C] mb-6">Save your favorite resin art creations to your wishlist while exploring.</p>
                                                <a href="{{ route('shop.index') }}" class="inline-block bg-[#1C1917] text-white text-xs uppercase tracking-widest px-8 py-3.5 rounded-full font-semibold">
                                                    Browse Catalogue
                                                </a>
                                            </div>
                                        `;
                                    }
                                }
                            }, 250);
                        }
                    }
                })
                .catch(err => {
                    console.error('Wishlist toggle error:', err);
                })
                .finally(() => {
                    if (btn) btn.disabled = false;
                });
            });
        });
    </script>

    {{-- Reactive AJAX Cart Handler (Zero-Reload Drawer Count Increment/Decrement & Add to Bag) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateNavCartCounters(count) {
                const navCount = document.getElementById('nav-cart-count');
                if (navCount) navCount.textContent = count;

                const mobileNavCount = document.getElementById('mobile-nav-cart-count');
                if (mobileNavCount) mobileNavCount.textContent = count;

                const mobileHeaderCount = document.getElementById('mobile-header-cart-count');
                if (mobileHeaderCount) {
                    mobileHeaderCount.textContent = count;
                    if (count > 0) {
                        mobileHeaderCount.classList.remove('hidden');
                        mobileHeaderCount.classList.add('flex');
                    } else {
                        mobileHeaderCount.classList.remove('flex');
                        mobileHeaderCount.classList.add('hidden');
                    }
                }
            }

            function updateCartDrawer(html) {
                const area = document.getElementById('cart-drawer-content-area');
                if (area && typeof html === 'string') {
                    area.innerHTML = html;
                }
            }

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form) return;

                const action = form.getAttribute('action') || '';
                const isUpdate = form.classList.contains('cart-drawer-update-form') || (form.closest('#cart-drawer-content-area') && action.includes('cart/update'));
                const isRemove = form.classList.contains('cart-drawer-remove-form') || (form.closest('#cart-drawer-content-area') && action.includes('cart/remove'));
                const isAdd = action.includes('cart/add') && !action.includes('buy-now');

                if (!isUpdate && !isRemove && !isAdd) return;

                // On standalone cart page, keep default submission for table items
                if (window.location.pathname === '/cart' && !form.closest('#cart-drawer-content-area') && (isUpdate || isRemove)) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                const submitter = e.submitter;
                const formData = new FormData(form);
                if (submitter && submitter.name) {
                    formData.set(submitter.name, submitter.value);
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || form.querySelector('input[name="_token"]')?.value;

                if (submitter) submitter.disabled = true;

                fetch(action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Cart update failed');
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        if (data.drawer_html) {
                            updateCartDrawer(data.drawer_html);
                        }
                        if (typeof data.cart_count !== 'undefined') {
                            updateNavCartCounters(data.cart_count);
                        }
                        if (isAdd) {
                            window.dispatchEvent(new CustomEvent('open-cart'));
                        }
                    }
                })
                .catch(err => {
                    console.error('Cart operation error:', err);
                })
                .finally(() => {
                    if (submitter) submitter.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
