<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-between">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Save Currency &amp; Forex Settings
            </x-filament::button>
            <p class="text-xs text-stone-500">
                Prices across the storefront and international checkout recalculate dynamically in real-time.
            </p>
        </div>
    </form>
</x-filament-panels::page>
