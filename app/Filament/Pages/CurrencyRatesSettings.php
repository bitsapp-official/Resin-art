<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\CurrencyService;
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

    protected static ?string $navigationLabel = 'Currency & Forex Rates';

    protected static ?string $title = 'Multi-Currency & Forex Configuration';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.currency-rates-settings';

    protected static ?string $slug = 'currency-rates-settings';

    public ?string $currency_hedging_buffer_percent = null;
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
            'currency_hedging_buffer_percent' => (string) SiteSetting::get('currency_hedging_buffer_percent', '3.5'),
            'currencies'                      => $list,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Master Base Currency & Payment Gateway Fee Protection')
                    ->description('All products, gold karat pricing, BIS hallmark calculations, and merchant bank settlements are in INR (₹).')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Placeholder::make('master_base_currency')
                                    ->label('Authoritative Base Currency')
                                    ->content(new HtmlString('
                                        <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 16px;">
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                                <span style="font-size: 15px; font-weight: 600; color: #ffffff;">🇮🇳 Indian Rupee (INR - ₹)</span>
                                                <span style="background: rgba(16, 185, 129, 0.15); color: #34d399; font-size: 11px; padding: 2px 8px; border-radius: 9999px; font-weight: 600; border: 1px solid rgba(16, 185, 129, 0.3);">Catalog Base</span>
                                            </div>
                                            <p style="font-size: 12px; color: #a1a1aa; margin: 0; line-height: 1.5;">
                                                Enter all product catalogue prices in <strong>INR (₹)</strong> as usual. The atelier automatically converts into any customer currency in real-time.
                                            </p>
                                        </div>
                                    ')),

                                TextInput::make('currency_hedging_buffer_percent')
                                    ->label('Forex Hedging Buffer (%)')
                                    ->helperText('Recommended: 3.5%. Safely absorbs Stripe/Bank cross-border foreign exchange transaction fees so your Indian merchant bank settles 100% of your product value.')
                                    ->numeric()
                                    ->required()
                                    ->prefix('%'),
                            ]),
                    ]),

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
                                            ->required()
                                            ->prefix('₹')
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
                    ->description('Shows how prices look across all active currencies with safe fee protection and whole-number rounding:')
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
                                $html .= '<th style="padding: 12px 16px;">Standard Rate</th>';
                                $html .= '<th style="padding: 12px 16px;">Safe Protected Rate</th>';
                                $html .= '<th style="padding: 12px 16px;">Customer Sees Price</th>';
                                $html .= '<th style="padding: 12px 16px;">Your Bank Settlement</th>';
                                $html .= '</tr></thead>';
                                $html .= '<tbody>';

                                foreach ($currencies as $code => $info) {
                                    $raw = CurrencyService::getRawRate($code);
                                    $eff = CurrencyService::getEffectiveRate($code);
                                    $display = CurrencyService::format($sample, $code);

                                    $html .= '<tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">';
                                    $html .= '<td style="padding: 12px 16px; font-weight: 500; color: #ffffff;">' . ($info['flag'] ?? '') . ' ' . ($info['name'] ?? $code) . ' <span style="color: #71717a; font-weight: 400;">(' . $code . ')</span></td>';
                                    $html .= '<td style="padding: 12px 16px;">₹' . number_format($raw, 2) . '</td>';
                                    $html .= '<td style="padding: 12px 16px; color: #f59e0b; font-weight: 600;">₹' . number_format($eff, 2) . '</td>';
                                    $html .= '<td style="padding: 12px 16px; font-weight: 700; color: #10b981;">' . $display . '</td>';
                                    $html .= '<td style="padding: 12px 16px;"><span style="display: inline-block; padding: 2px 10px; border-radius: 9999px; font-size: 11px; background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">Exact ₹1,00,000 INR</span></td>';
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
        $data = $this->form->getState();

        // 1. Save hedging buffer %
        SiteSetting::set('currency_hedging_buffer_percent', $data['currency_hedging_buffer_percent']);

        // 2. Prepare currencies list
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

        // 3. Save into CurrencyService
        CurrencyService::saveAllCurrencies($currencies);

        Notification::make()
            ->title('Currencies & Forex Rates Saved Successfully')
            ->body('All storefront prices, cart amounts, and Stripe international checkouts are now dynamically calculated using your updated rates.')
            ->success()
            ->send();
    }
}
