<script>
    // Immediate pre-render check: if already visited in this session, skip preloader completely
    if (sessionStorage.getItem('mr_atelier_visited')) {
        document.documentElement.classList.add('skip-site-loader');
    }
</script>

<style>
    .skip-site-loader #atelier-site-loader,
    .preloader-done #atelier-site-loader {
        display: none !important;
    }
</style>

<noscript>
    <style>
        #atelier-site-loader { display: none !important; }
    </style>
</noscript>

{{-- Premium Branded Entrance Preloader Component (First Time Website Open Only) --}}
<div id="atelier-site-loader" class="fixed inset-0 z-[999999] bg-[#FAF8F5] flex flex-col items-center justify-center overflow-hidden transition-all duration-700 ease-out">
    {{-- Translucent Fluid Organic Resin Ambient Blobs --}}
    <div class="absolute -top-32 -left-32 w-[550px] h-[550px] bg-[#0E5E6F]/12 rounded-full blur-[100px] pointer-events-none animate-resin-blob-1"></div>
    <div class="absolute -bottom-32 -right-32 w-[550px] h-[550px] bg-[#C5A070]/14 rounded-full blur-[100px] pointer-events-none animate-resin-blob-2"></div>
    <div class="absolute inset-0 bg-noise opacity-30 pointer-events-none"></div>

    {{-- Center Branded Reveal Content --}}
    <div class="relative z-10 flex flex-col items-center px-6 text-center select-none">
        
        {{-- Brand Serif Logo --}}
        <div class="font-editorial text-4xl sm:text-5xl lg:text-6xl text-[#1C1917] font-light tracking-tight leading-none animate-brand-reveal">
            Maison <em class="italic font-normal">R&eacute;sine</em>
        </div>

        {{-- Subtitle --}}
        <div class="text-[9.5px] uppercase tracking-[0.38em] font-medium text-[#8E877D] mt-3 animate-brand-reveal-sub">
            Atelier &middot; Bordeaux
        </div>

        {{-- Thin Organic Progress Bar --}}
        <div class="w-36 h-[1.5px] bg-[#EBE5DA] rounded-full overflow-hidden mt-8 relative">
            <div id="site-loader-progress" class="h-full bg-gradient-to-r from-[#0E5E6F] via-[#AD9575] to-[#1C1917] w-0 transition-all duration-700 ease-out"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const loader = document.getElementById('atelier-site-loader');
    const progressBar = document.getElementById('site-loader-progress');
    if (!loader) return;

    // If user has already visited in this session, remove loader immediately with zero delay
    if (sessionStorage.getItem('mr_atelier_visited')) {
        if (loader.parentNode) {
            loader.parentNode.removeChild(loader);
        }
        document.body.classList.add('preloader-done');
        return;
    }

    const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (isReducedMotion) {
        loader.style.display = 'none';
        document.body.classList.add('preloader-done');
        sessionStorage.setItem('mr_atelier_visited', 'true');
        return;
    }

    const minDuration = 900;
    const startTime = Date.now();

    // Smooth progress line fill
    requestAnimationFrame(function () {
        if (progressBar) {
            progressBar.style.width = '100%';
        }
    });

    function dismissLoader() {
        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, minDuration - elapsed);

        setTimeout(function () {
            loader.classList.add('opacity-0', 'pointer-events-none', 'scale-[1.01]');
            document.body.classList.add('preloader-done');
            window.dispatchEvent(new CustomEvent('atelier-loader-done'));
            sessionStorage.setItem('mr_atelier_visited', 'true');

            setTimeout(function () {
                if (loader && loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            }, 600);
        }, remaining);
    }

    if (document.readyState === 'complete') {
        dismissLoader();
    } else {
        window.addEventListener('load', dismissLoader);
        setTimeout(dismissLoader, 1600); // Fail-safe fallback
    }
})();
</script>
