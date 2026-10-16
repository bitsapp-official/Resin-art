<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Hex('#AD9575'),
                'amber' => Color::Amber,
                'emerald' => Color::Emerald,
                'purple' => Color::Purple,
                'sky' => Color::Sky,
            ])
            ->brandName('Solitaire Fine Jewels Admin')
            ->navigationGroups([
                'Orders & Sales',
                'Shop & Catalog',
                'Customers & Support',
                'Atelier & Content Pages',
                'Store Settings',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->spa()
            ->databaseNotifications()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    /* General Product Photos: 2-Column Grid (instead of 3) */
                    .filepond-grid-2 .filepond--item,
                    .filepond-grid-2 .filepond--list .filepond--item,
                    [class*="filepond-grid-2"] .filepond--item {
                        width: calc(50% - 0.5em) !important;
                    }

                    /* Filament Tabs Positioning & Width Constraint for FilePond */
                    .fi-fo-tabs {
                        position: relative !important;
                    }
                    .fi-fo-tabs-tab {
                        width: 100% !important;
                        max-width: 100% !important;
                        box-sizing: border-box !important;
                    }
                    .fi-fo-tabs-tab.invisible,
                    .fi-fo-tabs-tab[class*="invisible"] {
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                        right: 0 !important;
                        width: 100% !important;
                        max-width: 100% !important;
                        height: 0 !important;
                        overflow: hidden !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        border: 0 !important;
                        box-sizing: border-box !important;
                        pointer-events: none !important;
                        visibility: hidden !important;
                    }

                    /* Floating Toast Notifications: Solid 100% opaque, elevated shadow, high z-index */
                    .fi-notifications,
                    [class*="fi-notifications"] {
                        z-index: 99999 !important;
                    }
                    .fi-no-notification {
                        background-color: #18181b !important;
                        border: 1px solid rgba(255, 255, 255, 0.15) !important;
                        border-radius: 1rem !important;
                        box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.8), 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
                        opacity: 1 !important;
                        position: relative !important;
                        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    }
                    /* On hover, keep 100% solid opaque background - NEVER become transparent! */
                    .fi-no-notification:hover {
                        background-color: #222226 !important;
                        box-shadow: 0 25px 35px -5px rgba(0, 0, 0, 0.9) !important;
                        transform: translateY(-1px) !important;
                    }
                    /* Slide-over / Flyout notification modal list items */
                    .fi-modal .fi-no-notification,
                    .fi-slide-over .fi-no-notification {
                        background-color: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                    }
                    .fi-modal .fi-no-notification:hover,
                    .fi-slide-over .fi-no-notification:hover {
                        background-color: rgba(173, 149, 117, 0.12) !important;
                        transform: translateX(3px) !important;
                    }

                    /* Dashboard Row 2 Equal Height & Spacing Alignment */
                    .fi-wi-grid {
                        align-items: stretch !important;
                    }
                    .fi-wi-grid > * {
                        display: flex !important;
                        flex-direction: column !important;
                        height: 100% !important;
                    }
                    .fi-wi-grid > * > * {
                        flex: 1 1 auto !important;
                        display: flex !important;
                        flex-direction: column !important;
                        height: 100% !important;
                    }
                    .fi-wi-widget {
                        height: 100% !important;
                        display: flex !important;
                        flex-direction: column !important;
                    }
                    .fi-wi-widget .fi-section {
                        height: 100% !important;
                        display: flex !important;
                        flex-direction: column !important;
                        flex: 1 1 auto !important;
                    }
                    .fi-wi-widget .fi-section-content-ctn,
                    .fi-wi-widget .fi-section-content {
                        flex: 1 1 auto !important;
                        display: flex !important;
                        flex-direction: column !important;
                        justify-content: space-between !important;
                    }
                    /* Table Widgets Scroll & Responsiveness */
                    .fi-wi-table .fi-ta-content {
                        overflow-x: auto !important;
                        overflow-y: visible !important;
                        -webkit-overflow-scrolling: touch;
                    }
                    .fi-wi-table .fi-ta-content::-webkit-scrollbar {
                        height: 6px;
                    }
                    .fi-wi-table .fi-ta-content::-webkit-scrollbar-track {
                        background: rgba(255, 255, 255, 0.04);
                        border-radius: 4px;
                    }
                    .fi-wi-table .fi-ta-content::-webkit-scrollbar-thumb {
                        background: rgba(173, 149, 117, 0.4);
                        border-radius: 4px;
                    }
                    .fi-wi-table .fi-ta-content::-webkit-scrollbar-thumb:hover {
                        background: rgba(173, 149, 117, 0.7);
                    }
                    .fi-wi-table table {
                        width: 100% !important;
                    }
                </style>'
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                    (function () {
                        function refreshPond(root) {
                            if (!root) return;
                            try {
                                var pond = (window.FilePond && window.FilePond.find(root)) ||
                                           (window.Alpine && window.Alpine.$data && window.Alpine.$data(root) && window.Alpine.$data(root).pond);
                                if (pond) {
                                    var cur = pond.itemInsertInterval || 75;
                                    pond.setOptions({
                                        itemInsertInterval: cur === 75 ? 76 : 75
                                    });
                                    if (typeof pond.sort === "function") {
                                        pond.sort(function () { return 0; });
                                    }
                                }
                            } catch (e) {}
                        }

                        function recomputeAllVisiblePonds() {
                            if (window.__framePainter) {
                                try {
                                    window.__framePainter.readers.forEach(function (r) { r(); });
                                    window.__framePainter.writers.forEach(function (w) { w(performance.now()); });
                                } catch (e) {}
                            }

                            document.querySelectorAll(".fi-fo-tabs-tab.fi-active .filepond--root, .filepond--root").forEach(function (root) {
                                if (root.offsetParent !== null) {
                                    refreshPond(root);
                                }
                            });
                        }

                        document.addEventListener("DOMContentLoaded", function () {
                            // 1. Observe tabs gaining fi-active class
                            var observer = new MutationObserver(function (mutations) {
                                var hasTabChange = false;
                                mutations.forEach(function (mutation) {
                                    if (mutation.type === "attributes" && mutation.attributeName === "class") {
                                        var target = mutation.target;
                                        if (target.classList && target.classList.contains("fi-fo-tabs-tab") && target.classList.contains("fi-active")) {
                                            hasTabChange = true;
                                            target.querySelectorAll(".filepond--root").forEach(function (root) {
                                                refreshPond(root);
                                            });
                                        }
                                    }
                                });
                                if (hasTabChange) {
                                    [10, 50, 150, 300].forEach(function (t) {
                                        setTimeout(recomputeAllVisiblePonds, t);
                                    });
                                }
                            });

                            observer.observe(document.body, {
                                attributes: true,
                                subtree: true,
                                attributeFilter: ["class"]
                            });

                            // 2. Also listen to clicks on tab buttons
                            document.addEventListener("click", function (e) {
                                var btn = e.target.closest("button, [role=\'tab\'], .fi-tabs-item");
                                if (btn) {
                                    [10, 50, 120, 250, 450].forEach(function (t) {
                                        setTimeout(recomputeAllVisiblePonds, t);
                                    });
                                }
                            }, true);
                        });
                    })();
                </script>'
            )
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authGuard('admin')
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
