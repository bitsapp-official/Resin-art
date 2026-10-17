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

    protected static ?string $navigationGroup = 'Shop & Catalog';

    protected static ?string $navigationLabel = 'Daily Gold & Bullion Rates';

    protected static ?string $title = 'Daily Gold & Bullion Rates';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.gold-rates-settings';

    protected static ?string $slug = 'gold-rates-settings';

    // Pure Gold Rates & Tax
    public ?string $gold_rate_22kt = null;
    public ?string $gold_rate_18kt = null;
    public ?string $gold_rate_14kt = null;
    public ?string $gold_gst_rate = null;
    public ?string $gold_mrp_markup_percent = null;
    public ?string $gold_rates_updated_at = null;

    public function mount(): void
    {
        $rates = BullionPricingService::getRates();

        $this->form->fill([
            'gold_rate_22kt'           => (string) $rates['rate_22kt'],
            'gold_rate_18kt'           => (string) $rates['rate_18kt'],
            'gold_rate_14kt'           => (string) $rates['rate_14kt'],
            'gold_gst_rate'            => (string) $rates['gst_rate'],
            'gold_mrp_markup_percent'  => (string) ($rates['mrp_markup_percent'] ?? 20),
            'gold_rates_updated_at'    => $rates['updated_at'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Live Gold Rates per Gram (22KT, 18KT & 14KT)')
                    ->description('Set today\'s pure gold rate per gram for each jewellery Karat purity. Making charges are configured per-product on each ring/bracelet.')
                    ->schema([
                        Grid::make(3)->schema([
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
                        ]),
                    ]),

                Section::make('Storefront Showcase MRP & Discount Logic')
                    ->description('Control the automatic strikethrough (underline / cut) MRP displayed across product pages, catalog cards, and size variants.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('gold_mrp_markup_percent')
                                ->label('Global MRP Markup Percentage (%)')
                                ->numeric()
                                ->suffix('%')
                                ->default('20')
                                ->required()
                                ->placeholder('20')
                                ->helperText('Standard e-commerce markup (20% markup gives a Solitairekart-style ~17% discount display). Both Selling Price & Underline Price automatically scale with size & purity.'),
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
            ->title('Gold Rates Saved')
            ->success()
            ->body('Daily gold rates updated. (No products synced yet).')
            ->send();
    }

    public function saveAndSync(): void
    {
        $this->saveQuiet();

        $result = BullionPricingService::syncAllStoreProducts();

        Notification::make()
            ->title('Rates Saved & All Products Synced')
            ->success()
            ->body("Successfully updated {$result['synced_products']} dynamic jewellery products to new gold rates. ({$result['skipped']} manual fixed items untouched).")
            ->send();
    }

    protected function saveQuiet(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('gold_rate_22kt', $data['gold_rate_22kt'] ?? '6966');
        SiteSetting::set('gold_rate_18kt', $data['gold_rate_18kt'] ?? '5700');
        SiteSetting::set('gold_rate_14kt', $data['gold_rate_14kt'] ?? '4433');
        SiteSetting::set('gold_gst_rate', $data['gold_gst_rate'] ?? '3');
        SiteSetting::set('gold_mrp_markup_percent', $data['gold_mrp_markup_percent'] ?? '20');
        SiteSetting::set('gold_rates_updated_at', now()->toDateTimeString());

        $this->gold_rates_updated_at = now()->toDateTimeString();
    }
}
