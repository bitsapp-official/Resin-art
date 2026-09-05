<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Report Configuration Form --}}
        <form wire:submit.prevent="exportReport" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end space-x-3 pt-2">
                <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" color="primary" size="lg">
                    Generate & Download Report (Excel / CSV)
                </x-filament::button>
            </div>
        </form>

        {{-- Informational Cards for Reports --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4">
            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center font-bold text-sm">₹</div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Monthly Sales & Revenue</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Complete gross revenue, customer details, and items sold breakdown for monthly accounting.</p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 flex items-center justify-center font-bold text-sm">%</div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Orders & GST Breakdown</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Tax compliance report with subtotal, calculated GST rates, shipping fees, and net invoice totals.</p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center font-bold text-sm">✦</div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Custom Orders & Inquiries</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Client requirement specifications, dimensions, reference photos, and custom quote statuses.</p>
            </div>

            <div class="p-5 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 flex items-center justify-center font-bold text-sm">📦</div>
                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Inventory & Stock Valuation</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Current stock counts, low stock alerts, unit pricing, and overall atelier catalog valuation.</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
