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
            ->brandName('Maison Résine Admin')
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
            ->databaseNotifications()
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
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
