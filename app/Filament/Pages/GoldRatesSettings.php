<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\BullionPricingService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GoldRatesSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';

    protected static ?string $navigationGroup = 'Gold & Currency Rates';

    protected static ?string $navigationLabel = 'Daily Gold & Bullion Rates';

    protected static ?string $title = 'Daily Gold & Bullion Rates';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.gold-rates-settings';

    protected static ?string $slug = 'gold-rates-settings';

    // Gold Rates, Diamond Rates & Tax
    public ?string $gold_rate_22kt = null;
    public ?string $gold_rate_18kt = null;
    public ?string $gold_rate_14kt = null;
    public ?string $gold_rate_10kt = null;
    public ?string $silver_rate_925 = null;
    public ?string $diamond_rate_vvs1 = null;
    public ?string $diamond_rate_vvs2 = null;
    public ?string $diamond_rate_vs1 = null;
    public ?string $diamond_rate_vs2 = null;
    public ?string $gold_gst_rate = null;
    public ?string $gold_rates_updated_at = null;

    public function mount(): void
    {
        $rates = BullionPricingService::getRates();

        $this->form->fill([
            'gold_rate_22kt'           => (string) ($rates['rate_22kt'] ?? '6966'),
            'gold_rate_18kt'           => (string) ($rates['rate_18kt'] ?? '5700'),
            'gold_rate_14kt'           => (string) ($rates['rate_14kt'] ?? '4433'),
            'gold_rate_10kt'           => (string) ($rates['rate_10kt'] ?? '3167'),
            'silver_rate_925'          => (string) ($rates['rate_silver_925'] ?? '95'),
            'diamond_rate_vvs1'        => (string) ($rates['diamond_rate_vvs1'] ?? '110000'),
            'diamond_rate_vvs2'        => (string) ($rates['diamond_rate_vvs2'] ?? '90000'),
            'diamond_rate_vs1'         => (string) ($rates['diamond_rate_vs1'] ?? '75000'),
            'diamond_rate_vs2'         => (string) ($rates['diamond_rate_vs2'] ?? '60000'),
            'gold_gst_rate'            => (string) ($rates['gst_rate'] ?? '3'),
            'gold_rates_updated_at'    => $rates['updated_at'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Live Gold Rates per Gram (22KT, 18KT, 14KT & 10KT)')
                    ->description('Set today\'s gold rate per gram for each jewellery Karat purity. Making charges are configured per-product on each piece.')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                            TextInput::make('gold_rate_22kt')
                                ->label('22KT Gold Rate (₹ / Gram)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 6966')
                                ->helperText('Standard 22K (916) Hallmark gold rate per gram.'),

                            TextInput::make('gold_rate_18kt')
                                ->label('18KT Gold Rate (₹ / Gram)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 5700')
                                ->helperText('Standard 18K (750) fine diamond jewellery rate per gram.'),

                            TextInput::make('gold_rate_14kt')
                                ->label('14KT Gold Rate (₹ / Gram)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 4433')
                                ->helperText('Standard 14K (585) modern luxury gold rate per gram.'),

                            TextInput::make('gold_rate_10kt')
                                ->label('10KT Gold Rate (₹ / Gram)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 3167')
                                ->helperText('Standard 10K (417) affordable gold rate per gram.'),
                        ]),
                    ]),

                Section::make('Live Silver Bullion Rate per Gram')
                    ->description('Set today\'s rate for 925 Hallmark Sterling Silver per gram.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('silver_rate_925')
                                ->label('925 Silver Rate (₹ / Gram)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 95')
                                ->helperText('Standard 925 Hallmark sterling silver jewellery rate per gram.'),
                        ]),
                    ]),

                Section::make('Live Certified Diamond Rates per Carat (VVS1, VVS2, VS1 & VS2)')
                    ->description('Set today\'s natural certified diamond benchmark rate per carat for each clarity grade. When a jewellery piece has diamond carat weight defined, customer price recalculates dynamically as Carat Weight × Clarity Rate.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('diamond_rate_vvs1')
                                ->label('VVS1 Diamond Rate (₹ / Carat)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 110000')
                                ->helperText('Premium VVS1 clarity natural certified diamond benchmark rate per carat.'),

                            TextInput::make('diamond_rate_vvs2')
                                ->label('VVS2 Diamond Rate (₹ / Carat)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 90000')
                                ->helperText('Superior VVS2 clarity natural certified diamond benchmark rate per carat.'),

                            TextInput::make('diamond_rate_vs1')
                                ->label('VS1 Diamond Rate (₹ / Carat)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 75000')
                                ->helperText('High-grade VS1 clarity natural certified diamond benchmark rate per carat.'),

                            TextInput::make('diamond_rate_vs2')
                                ->label('VS2 Diamond Rate (₹ / Carat)')
                                ->numeric()
                                ->prefix('₹')
                                ->required()
                                ->placeholder('e.g. 60000')
                                ->helperText('Standard VS2 clarity natural certified diamond benchmark rate per carat.'),
                        ]),
                    ]),

                Section::make('Statutory Tax Configuration')
                    ->description('Applicable GST percentage across fine jewellery products.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('gold_gst_rate')
                                ->label('Jewellery GST Rate (%)')
                                ->numeric()
                                ->suffix('%')
                                ->default('3')
                                ->required()
                                ->placeholder('3')
                                ->helperText('Standard jewellery goods & services tax in India (3.0%).'),
                        ]),
                    ]),
            ]);
    }

    public function saveOnly(): void
    {
        $this->saveQuiet();

        Notification::make()
            ->title('Gold, Bullion & Diamond Rates Saved')
            ->success()
            ->body('Daily gold, silver, and diamond clarity benchmark rates updated. (No products synced yet).')
            ->send();
    }

    public function saveAndSync(): void
    {
        $this->saveQuiet();

        $result = BullionPricingService::syncAllStoreProducts();

        Notification::make()
            ->title('Rates Saved & All Products Synced')
            ->success()
            ->body("Successfully updated {$result['synced_products']} dynamic jewellery products to new bullion rates. ({$result['skipped']} manual fixed items untouched).")
            ->send();
    }

    protected function saveQuiet(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('gold_rate_22kt', $data['gold_rate_22kt'] ?? '6966');
        SiteSetting::set('gold_rate_18kt', $data['gold_rate_18kt'] ?? '5700');
        SiteSetting::set('gold_rate_14kt', $data['gold_rate_14kt'] ?? '4433');
        SiteSetting::set('gold_rate_10kt', $data['gold_rate_10kt'] ?? '3167');
        SiteSetting::set('silver_rate_925', $data['silver_rate_925'] ?? '95');
        SiteSetting::set('diamond_rate_vvs1', $data['diamond_rate_vvs1'] ?? '110000');
        SiteSetting::set('diamond_rate_vvs2', $data['diamond_rate_vvs2'] ?? '90000');
        SiteSetting::set('diamond_rate_vs1', $data['diamond_rate_vs1'] ?? '75000');
        SiteSetting::set('diamond_rate_vs2', $data['diamond_rate_vs2'] ?? '60000');
        SiteSetting::set('gold_gst_rate', $data['gold_gst_rate'] ?? '3');
        SiteSetting::set('gold_rates_updated_at', now()->toDateTimeString());

        $this->gold_rates_updated_at = now()->toDateTimeString();
    }
}
