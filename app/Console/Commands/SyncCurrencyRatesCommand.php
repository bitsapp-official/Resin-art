<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class SyncCurrencyRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync live market currency exchange rates against INR';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching live exchange rates from market APIs...');

        $currencies = CurrencyService::syncLiveRates();

        $rows = [];
        foreach ($currencies as $code => $info) {
            $rows[] = [
                $code,
                $info['name'] ?? $code,
                $info['symbol'] ?? '',
                $info['rate'] ?? 1.0,
                !empty($info['is_base']) ? 'Yes (Base)' : 'No',
                !empty($info['is_active']) ? 'Active' : 'Inactive',
            ];
        }

        $this->table(
            ['Code', 'Name', 'Symbol', 'Rate (1 Unit = X INR)', 'Base', 'Status'],
            $rows
        );

        $this->info('Currency exchange rates successfully synced and saved.');

        return Command::SUCCESS;
    }
}
