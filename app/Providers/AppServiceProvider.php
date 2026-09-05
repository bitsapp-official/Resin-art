<?php

namespace App\Providers;

use App\Models\ContactInquiry;
use App\Policies\ContactInquiryPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Custom Filament Logout Controller to prevent wiping frontend customer session
        $this->app->bind(
            \Filament\Http\Controllers\Auth\LogoutController::class,
            \App\Http\Controllers\Filament\CustomLogoutController::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ContactInquiry::class, ContactInquiryPolicy::class);

        // We use app()->booted() to ensure this runs AFTER all service providers
        // (including Filament's NotificationsServiceProvider) have finished booting.
        // This guarantees our custom DatabaseNotifications overrides Filament's default.
        $this->app->booted(function () {
            \Livewire\Livewire::component('database-notifications', \App\Filament\Livewire\DatabaseNotifications::class);
        });
    }
}
