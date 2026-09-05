<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full flex flex-col bg-[#FAF8F5] text-[#1C1917] antialiased relative overflow-x-hidden">
    @if(request()->is('/') || request()->routeIs('shop.index'))
        <!-- Premium Resin Art Branded Initial Site Entrance Loader Component (First Visit Only) -->
        <x-site-loader />
    @endif

    <!-- Ambient Background Lighting Glows (Teal Left & Warm Gold Right) -->
    <div class="fixed -top-24 -left-24 w-[650px] h-[650px] bg-[#D1E3DA]/40 blur-[130px] rounded-full pointer-events-none z-0"></div>
    <div class="fixed -top-24 -right-24 w-[650px] h-[650px] bg-[#F5E5CE]/40 blur-[130px] rounded-full pointer-events-none z-0"></div>

    <!-- Background Noise Overlay -->
    <div class="fixed inset-0 z-0 bg-noise opacity-40 pointer-events-none"></div>

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

</body>
</html>
