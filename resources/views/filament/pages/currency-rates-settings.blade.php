<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check-circle">
                    Save Currency &amp; Forex Settings
                </x-filament::button>

                <x-filament::button type="button" wire:click="syncLiveRates" color="gray" icon="heroicon-o-arrow-path">
                    Sync Live Rates
                </x-filament::button>
            </div>
            <p class="text-xs text-stone-500">
                Prices across the storefront and international checkout recalculate dynamically in real-time.
            </p>
        </div>
    </form>
</x-filament-panels::page>
