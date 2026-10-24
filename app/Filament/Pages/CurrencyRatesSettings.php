<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\CurrencyService;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class CurrencyRatesSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Gold & Currency Rates';

    protected static ?string $navigationLabel = 'Currency Rates';

    protected static ?string $title = 'Multi-Currency & Exchange Rates Configuration';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.currency-rates-settings';

    protected static ?string $slug = 'currency-rates-settings';

    public ?array $currencies = [];

    public function mount(): void
    {
        $allCurrencies = CurrencyService::getAllCurrencies();
        $list = [];

        foreach ($allCurrencies as $code => $info) {
            if ($code === 'INR') {
                continue; // Base currency is displayed authoritatively in the base info card
            }

            $list[] = [
                'flag'      => $info['flag'] ?? '🌐',
                'code'      => $info['code'] ?? $code,
                'name'      => $info['name'] ?? $code,
                'symbol'    => $info['symbol'] ?? ($code . ' '),
                'rate'      => (string) ($info['rate'] ?? '1.0'),
                'is_active' => !empty($info['is_active']),
            ];
        }

        $this->form->fill([
            'currencies' => $list,
        ]);
    }

    protected function persistCurrentFormState(): void
    {
        $data = $this->form->getState();

        $currencies = [];
        foreach ($data['currencies'] ?? [] as $item) {
            $code = strtoupper(trim($item['code'] ?? ''));
            if (empty($code) || $code === 'INR') {
                continue;
            }

            $symbol = trim($item['symbol'] ?? ($code . ' '));
            $pattern = mb_strlen($symbol) <= 1 ? "{$symbol}%s" : "{$symbol} %s";
            $currencies[$code] = [
                'code'           => $code,
                'name'           => trim($item['name'] ?? $code),
                'symbol'         => $symbol,
                'flag'           => trim($item['flag'] ?? '🌐'),
                'rate'           => (float) ($item['rate'] ?? 1.0),
                'is_base'        => false,
                'is_active'      => !empty($item['is_active']),
                'format_pattern' => $pattern,
            ];
        }

        CurrencyService::saveAllCurrencies($currencies);
    }

    public function syncLiveRates(): void
    {
        try {
            $this->persistCurrentFormState();
        } catch (\Throwable $e) {
            // Continue with existing currencies if validation fails
        }

        $updated = CurrencyService::syncLiveRates();

        $list = [];
        foreach ($updated as $code => $info) {
            if ($code === 'INR') {
                continue;
            }

            $list[] = [
                'flag'      => $info['flag'] ?? '🌐',
                'code'      => $info['code'] ?? $code,
                'name'      => $info['name'] ?? $code,
                'symbol'    => $info['symbol'] ?? ($code . ' '),
                'rate'      => (string) ($info['rate'] ?? '1.0'),
                'is_active' => !empty($info['is_active']),
            ];
        }

        $this->form->fill([
            'currencies' => $list,
        ]);

        Notification::make()
            ->title('Live Currency Rates Synced and Saved!')
            ->body('Market exchange rates have been updated and persisted to store settings.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncLiveRates')
                ->label('Sync Live Rates')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn () => $this->syncLiveRates()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Master Base Currency')
                    ->description('All products, gold karat pricing, BIS hallmark calculations, and merchant bank settlements are in INR (₹).')
                    ->schema([
                        Placeholder::make('master_base_currency')
                            ->hiddenLabel()
                            ->content(new HtmlString('
                                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 14px 18px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                        <span style="font-size: 15px; font-weight: 600; color: #ffffff;">🇮🇳 Indian Rupee (INR - ₹)</span>
                                        <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 11px; padding: 2px 10px; border-radius: 9999px; font-weight: 600; border: 1px solid rgba(16, 185, 129, 0.3);">Storefront Catalog Base</span>
                                    </div>
                                    <p style="font-size: 13px; color: #a1a1aa; margin: 0; line-height: 1.5;">
                                        Enter product prices in <strong>INR (₹)</strong>. For international customers, prices convert directly using the exact rates configured below (e.g., USD = Price in INR ÷ USD Rate).
                                    </p>
                                </div>
                            ')),
                    ]),

                Section::make('Forex Sync Status & API Health')
                    ->description('Live tracking of forex rate updates, API provider publication schedule, and background sync status.')
                    ->schema([
                        Placeholder::make('sync_status_info')
                            ->hiddenLabel()
                            ->content(function () {
                                $lastSynced = SiteSetting::get('currency_rates_last_synced_at');
                                $apiLastUpdate = SiteSetting::get('currency_api_last_update_utc');
                                $apiNextUpdate = SiteSetting::get('currency_api_next_update_utc');

                                $lastSyncedFormatted = $lastSynced ? \Carbon\Carbon::parse($lastSynced)->setTimezone('Asia/Kolkata')->format('d M Y, h:i A T') : 'Not yet synced';
                                $apiLastFormatted = $apiLastUpdate ? \Carbon\Carbon::parse($apiLastUpdate)->setTimezone('Asia/Kolkata')->format('d M Y, h:i A T') : 'Standard 24h Cycle';
                                $apiNextFormatted = $apiNextUpdate ? \Carbon\Carbon::parse($apiNextUpdate)->setTimezone('Asia/Kolkata')->format('d M Y, h:i A T') : 'Daily at 05:30 AM IST';

                                return new HtmlString('
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px 14px;">
                                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #a1a1aa; font-weight: 600;">Store Last Synced</span>
                                            <div style="font-size: 13px; font-weight: 600; color: #10b981; margin-top: 4px;">' . e($lastSyncedFormatted) . '</div>
                                            <span style="font-size: 11px; color: #71717a;">Auto-syncs every 2 hours in background</span>
                                        </div>
                                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px 14px;">
                                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #38bdf8; font-weight: 600;">API Provider Last Update</span>
                                            <div style="font-size: 13px; font-weight: 600; color: #38bdf8; margin-top: 4px;">' . e($apiLastFormatted) . '</div>
                                            <span style="font-size: 11px; color: #71717a;">Market forex baseline published</span>
                                        </div>
                                        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 12px 14px;">
                                            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #fbbf24; font-weight: 600;">API Provider Next Update</span>
                                            <div style="font-size: 13px; font-weight: 600; color: #fbbf24; margin-top: 4px;">' . e($apiNextFormatted) . '</div>
                                            <span style="font-size: 11px; color: #71717a;">Expected provider publication time</span>
                                        </div>
                                    </div>
                                ');
                            }),
                    ])
                    ->collapsible(),

                Section::make('International Currencies List (Add, Edit, Reorder, or Delete Currencies)')
                    ->description('Manage which currencies appear in the website header dropdown. You can add new countries anytime, update conversion rates, reorder them, or click the trash icon to delete any currency.')
                    ->schema([
                        Repeater::make('currencies')
                            ->hiddenLabel()
                            ->itemLabel(fn (array $state): ?string =>
                                trim(($state['flag'] ?? '') . ' ' . ($state['code'] ?? '') . ' — ' . ($state['name'] ?? '') . ' (1 ' . ($state['code'] ?? '') . ' = ₹' . ($state['rate'] ?? '0') . ')') ?: 'New Currency'
                            )
                            ->schema([
                                Grid::make(['default' => 2, 'md' => 6])
                                    ->schema([
                                        TextInput::make('flag')
                                            ->label('Flag Emoji')
                                            ->placeholder('🇺🇸')
                                            ->required()
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        TextInput::make('code')
                                            ->label('Code (3 Letters)')
                                            ->placeholder('USD')
                                            ->required()
                                            ->extraInputAttributes(['style' => 'text-transform: uppercase; font-weight: 700;'])
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        TextInput::make('name')
                                            ->label('Country / Currency Name')
                                            ->placeholder('US Dollar')
                                            ->required()
                                            ->columnSpan(['default' => 2, 'md' => 2]),

                                        TextInput::make('symbol')
                                            ->label('Display Symbol')
                                            ->placeholder('$')
                                            ->required()
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        TextInput::make('rate')
                                            ->label('Rate (1 unit in ₹)')
                                            ->placeholder('86.50')
                                            ->numeric()
                                            ->default('1.0')
                                            ->required()
                                            ->prefix('₹')
                                            ->helperText('Type custom rate OR click "Sync Live Rates" to auto-fetch')
                                            ->columnSpan(['default' => 1, 'md' => 1]),

                                        Toggle::make('is_active')
                                            ->label('Active on Storefront Navbar & Checkout')
                                            ->default(true)
                                            ->inline(false)
                                            ->columnSpan(['default' => 2, 'md' => 6]),
                                    ]),
                            ])
                            ->addActionLabel('+ Add New Currency / Country')
                            ->reorderable()
                            ->deletable(true)
                            ->defaultItems(0),
                    ]),

                Section::make('Live Price Calculation Preview (₹1,00,000 Sample Creation)')
                    ->description('Shows how prices convert across all active currencies using your exact direct rates:')
                    ->schema([
                        Placeholder::make('live_preview_table')
                            ->hiddenLabel()
                            ->content(function () {
                                $currencies = CurrencyService::getActiveCurrencies();
                                $sample = 100000;

                                $html = '<div style="overflow-x: auto; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; background: #18181b;">';
                                $html .= '<table style="width: 100%; text-align: left; border-collapse: collapse; font-size: 13px; color: #d4d4d8;">';
                                $html .= '<thead style="background: rgba(255,255,255,0.06); text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; color: #a1a1aa; border-bottom: 1px solid rgba(255,255,255,0.1);">';
                                $html .= '<tr>';
                                $html .= '<th style="padding: 12px 16px;">Currency</th>';
                                $html .= '<th style="padding: 12px 16px;">Configured Exchange Rate</th>';
                                $html .= '<th style="padding: 12px 16px;">Customer Price</th>';
                                $html .= '<th style="padding: 12px 16px;">Direct Calculation</th>';
                                $html .= '</tr></thead>';
                                $html .= '<tbody>';

                                foreach ($currencies as $code => $info) {
                                    $rate = CurrencyService::getRawRate($code);
                                    $display = CurrencyService::format($sample, $code);
                                    $calcVal = round($sample / max(0.0001, $rate));

                                    $html .= '<tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">';
                                    $html .= '<td style="padding: 12px 16px; font-weight: 500; color: #ffffff;">' . ($info['flag'] ?? '') . ' ' . ($info['name'] ?? $code) . ' <span style="color: #71717a; font-weight: 400;">(' . $code . ')</span></td>';
                                    $html .= '<td style="padding: 12px 16px; font-weight: 600; color: #e4e4e7;">1 ' . $code . ' = ₹' . number_format($rate, 2) . '</td>';
                                    $html .= '<td style="padding: 12px 16px; font-weight: 700; color: #10b981; font-size: 14px;">' . $display . '</td>';
                                    $html .= '<td style="padding: 12px 16px; color: #a1a1aa;">₹1,00,000 ÷ ' . number_format($rate, 2) . ' = ' . ($info['symbol'] ?? '') . number_format($calcVal) . '</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody></table></div>';
                                return new HtmlString($html);
                            }),
                    ]),
            ]);
    }

    public function save(): void
    {
        $this->persistCurrentFormState();

        Notification::make()
            ->title('Currency Rates Saved Successfully')
            ->body('All storefront prices, cart amounts, and international checkouts are now dynamically calculated using your direct exchange rates.')
            ->success()
            ->send();
    }
}
