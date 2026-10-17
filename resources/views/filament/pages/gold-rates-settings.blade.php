<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <x-filament::button
                type="button"
                wire:click="saveAndSync"
                icon="heroicon-o-arrow-path"
                color="warning"
                size="lg"
            >
                Save &amp; Sync All Products With New Rates
            </x-filament::button>

            <x-filament::button
                type="button"
                wire:click="saveOnly"
                icon="heroicon-o-check-circle"
                color="gray"
                size="lg"
            >
                Save Rates Only
            </x-filament::button>

            <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">
                Last updated: {{ $gold_rates_updated_at ?? 'Never' }}
            </span>
        </div>
    </form>
</x-filament-panels::page>
