<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="exportReport" class="space-y-4">
            {{ $this->form }}

            <div class="flex items-center justify-end">
                <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" color="primary" size="lg" class="shadow-sm">
                    Generate &amp; Download Report
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
