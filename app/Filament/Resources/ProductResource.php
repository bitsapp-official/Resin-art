<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Shop & Catalog';

    protected static ?string $navigationLabel = 'Products';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── ROW 1 (LEFT 2/3): Product Identification & Inventory ──
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Product Details')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Product Name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                    $set('slug', Str::slug($state))
                                ),

                            Forms\Components\TextInput::make('slug')
                                ->label('Slug (Auto-generated)')
                                ->required()
                                ->maxLength(255)
                                ->unique(Product::class, 'slug', ignoreRecord: true),

                            Forms\Components\TextInput::make('sku')
                                ->label('SKU')
                                ->maxLength(100)
                                ->unique(Product::class, 'sku', ignoreRecord: true),

                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Forms\Components\Select::make('collections')
                                ->relationship('collections', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->label('Assigned Collections'),

                            Forms\Components\RichEditor::make('description')
                                ->label('Product Details & Description')
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strike',
                                    'link',
                                    'h2',
                                    'h3',
                                    'blockquote',
                                    'codeBlock',
                                    'bulletList',
                                    'orderedList',
                                    'undo',
                                    'redo',
                                ])
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make('Inventory & Fulfillment')
                        ->schema([
                            Forms\Components\Select::make('inventory_type')
                                ->label('Fulfillment / Inventory Type')
                                ->options([
                                    'MADE_TO_ORDER' => 'Made to Order (Crafted on Demand)',
                                    'READY_TO_SHIP' => 'Ready to Ship (Stock Tracked)',
                                ])
                                ->default('MADE_TO_ORDER')
                                ->live()
                                ->required(),

                            Forms\Components\TextInput::make('stock')
                                ->label('Available Stock Units')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(fn (Forms\Get $get) => $get('inventory_type') === 'READY_TO_SHIP')
                                ->visible(fn (Forms\Get $get) => $get('inventory_type') === 'READY_TO_SHIP'),

                            Forms\Components\TextInput::make('low_stock_threshold')
                                ->label('Low Stock Alert Threshold')
                                ->numeric()
                                ->default(2)
                                ->visible(fn (Forms\Get $get) => $get('inventory_type') === 'READY_TO_SHIP'),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

                // ── ROW 1 (RIGHT 1/3): Status & Primary Visuals ──
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status & Visibility')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Product Status')
                                ->options([
                                    'published' => 'Active (Visible in Store)',
                                    'draft'     => 'Draft (Hidden from Customers)',
                                    'archived'  => 'Archived (Discontinued)',
                                ])
                                ->default('published')
                                ->required(),

                            Forms\Components\Hidden::make('is_featured')
                                ->default(true),

                            Forms\Components\Toggle::make('is_new')
                                ->label('New Arrival')
                                ->default(false),

                            Forms\Components\Toggle::make('is_bestseller')
                                ->label('Bestseller')
                                ->default(false),
                        ]),

                    Forms\Components\Section::make('Product Gallery & Media')
                        ->description('Product photos and craft video.')
                        ->schema([
                            Forms\Components\FileUpload::make('images')
                                ->label('Product Photos')
                                ->formatStateUsing(function ($record, $state) {
                                    $colorImgs = [];
                                    if ($record) {
                                        $attrs = $record->jewellery_attributes ?? [];
                                        if (!empty($attrs['color_images']) && is_array($attrs['color_images'])) {
                                            foreach ($attrs['color_images'] as $list) {
                                                foreach ((array) $list as $ci) {
                                                    if (!empty($ci)) $colorImgs[] = ltrim($ci, '/');
                                                }
                                            }
                                        }
                                        $raw = $record->getAttributes()['images'] ?? null;
                                        $imgs = is_string($raw) ? (json_decode($raw, true) ?: []) : (array) ($raw ?? []);
                                    } else {
                                        $imgs = is_string($state) ? [$state] : (array) ($state ?? []);
                                    }

                                    return array_values(array_filter($imgs, function ($img) use ($colorImgs) {
                                        if (empty($img) || !is_string($img)) return false;
                                        $clean = ltrim($img, '/');
                                        if (str_starts_with($clean, 'products/colors/')) return false;
                                        if (in_array($clean, $colorImgs, true)) return false;
                                        return true;
                                    }));
                                })
                                ->mutateDehydratedStateUsing(function ($state, Forms\Get $get) {
                                    $colorData = $get('attributes.color_images') ?? [];
                                    $colorImgs = [];
                                    if (is_array($colorData)) {
                                        foreach ($colorData as $list) {
                                            foreach ((array) $list as $ci) {
                                                if (!empty($ci)) $colorImgs[] = ltrim($ci, '/');
                                            }
                                        }
                                    }
                                    $imgs = (array) ($state ?? []);
                                    return array_values(array_filter($imgs, function ($img) use ($colorImgs) {
                                        if (empty($img) || !is_string($img)) return false;
                                        $clean = ltrim($img, '/');
                                        if (str_starts_with($clean, 'products/colors/')) return false;
                                        if (in_array($clean, $colorImgs, true)) return false;
                                        return true;
                                    }));
                                })
                                ->multiple()
                                ->appendFiles()
                                ->panelLayout('grid')
                                ->extraAttributes(['class' => 'filepond-grid-2'])
                                ->imagePreviewHeight('180')
                                ->image()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                ->maxSize(5120)
                                ->maxFiles(10)
                                ->disk('public')
                                ->directory('products')
                                ->reorderable()
                                ->openable()
                                ->downloadable(),

                            Forms\Components\FileUpload::make('video_path')
                                ->label('Product Showcase Video (Upload MP4 / WebM)')
                                ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                                ->maxSize(20480)
                                ->disk('public')
                                ->directory('products/videos')
                                ->openable()
                                ->downloadable()
                                ->helperText('Upload craft or showcase video (Max 20 MB). Recommended: compressed 1080p MP4 for fast mobile playback. Takes priority over external URL.')
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('video_url')
                                ->label('YouTube Video URL (Optional)')
                                ->placeholder('https://www.youtube.com/watch?v=... or https://youtu.be/... or Shorts')
                                ->url()
                                ->nullable()
                                ->helperText('Paste a YouTube link if not uploading an MP4 file. Video player and thumbnail poster are handled automatically.')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpan(['lg' => 1]),

                // ── ROW 2 (FULL WIDTH): Gold Valuation Engine & Dynamic Size Matrix ──
                Forms\Components\Section::make('Gold Valuation Engine & Dynamic Size Matrix')
                    ->description('Synchronized with Live Daily Gold Rates. Every size variant features metallurgical purity breakdown across 14KT, 18KT, and 22KT with automated Strike-through MRP generation.')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Hidden::make('attributes.pricing_mode')
                            ->default('dynamic'),

                        // 1. Dynamic Formula Parameters (Gold Weight FIRST, then Making Charge, Diamond Carat, Profit Margin %, Cut Price)
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 5,
                        ])
                            ->schema([
                                Forms\Components\TextInput::make('attributes.gold_weight_grams')
                                    ->label('Approx. Gold Weight (gm)')
                                    ->numeric()
                                    ->step('any')
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->suffix('gm')
                                    ->required()
                                    ->placeholder('e.g. 2.50')
                                    ->helperText('Benchmark gold weight in grams. Fixed across all sizes.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                                    ->columnSpan(['default' => 1, 'sm' => 1]),

                                Forms\Components\TextInput::make('attributes.making_charge')
                                    ->label('Making / Wastage (₹ / gm)')
                                    ->numeric()
                                    ->step('any')
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->prefix('₹')
                                    ->required()
                                    ->placeholder('e.g. 750')
                                    ->helperText('Per-gram craftsmanship fee added to gold weight.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                                    ->columnSpan(['default' => 1, 'sm' => 1]),

                                Forms\Components\TextInput::make('attributes.diamond_carat')
                                    ->label('Total Diamond Weight (ct)')
                                    ->numeric()
                                    ->step('any')
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->suffix('ct')
                                    ->placeholder('0.00')
                                    ->helperText(function (Forms\Get $get) {
                                        $carat = self::resolveDiamondCarat($get);
                                        if ($carat > 0) {
                                            $val = self::resolveDiamondValue($get);
                                            $clarities = self::resolveDiamondClarities($get);
                                            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                            return "✓ Auto-calculated diamond cost: ₹" . number_format($val) . " ({$firstClarity} base)";
                                        }
                                        return 'Enter total diamond carat weight. Leave 0 for plain jewellery.';
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                                    ->columnSpan(['default' => 1, 'sm' => 1]),

                                Forms\Components\TextInput::make('attributes.profit_margin_percent')
                                    ->label('Profit Margin (%)')
                                    ->numeric()
                                    ->step('any')
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->suffix('%')
                                    ->placeholder('e.g. 5')
                                    ->helperText(function (Forms\Get $get) {
                                        $p = self::resolveProfitMargin($get);
                                        if ($p > 0) {
                                            return "✓ {$p}% profit margin added on total cost.";
                                        }
                                        return 'Profit margin percentage added on total cost.';
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                                    ->columnSpan(['default' => 1, 'sm' => 1]),

                                Forms\Components\TextInput::make('attributes.cut_price_percent')
                                    ->label('Cut Price (% Strike MRP)')
                                    ->numeric()
                                    ->step('any')
                                    ->minValue(0)
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->suffix('%')
                                    ->placeholder('e.g. 20')
                                    ->helperText(function (Forms\Get $get, ?\App\Models\Product $record) {
                                        $pct = self::resolveCutPricePercent($get);
                                        if ($pct > 0) {
                                            return "✓ +{$pct}% strike MRP applied dynamically across all diamond sizes & purities.";
                                        }
                                        $legacyCut = (float) ($record?->jewellery_attributes['common_cut_price'] ?? 0);
                                        if ($legacyCut > 0) {
                                            return 'Legacy fixed cut price ₹' . number_format($legacyCut) . ' is active. Enter a % above (e.g. 20) to auto-scale across all carats & purities.';
                                        }
                                        return 'Percentage added on selling price to display strike-through MRP across all diamond sizes & purities.';
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                                    ->columnSpan(['default' => 1, 'sm' => 1]),
                            ]),

                        // 1.5 Diamond Size / Carat Options (Dynamic Customer Selection e.g. 1.00 ct to 5.00 ct)
                        Forms\Components\TagsInput::make('attributes.diamond_carat_options')
                            ->label('Available Diamond Sizes / Carat Weights for Customer')
                            ->placeholder('e.g. 1.00 ct, 1.20 ct, 1.50 ct, 2.00 ct, 2.50 ct, 3.00 ct, 4.00 ct, 5.00 ct')
                            ->suggestions([
                                '1.00 ct',
                                '1.20 ct',
                                '1.50 ct',
                                '2.00 ct',
                                '2.50 ct',
                                '3.00 ct',
                                '4.00 ct',
                                '5.00 ct',
                            ])
                            ->helperText('Configure diamond carat sizes customers can select. You can add, change, or remove any custom carat weight (e.g. 1.00 ct to 5.00 ct). Price recalculates dynamically based on selected carat weight × daily clarity rate.')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if (!empty($state) && is_array($state)) {
                                    $first = reset($state);
                                    if (preg_match('/([\d\.]+)/', (string) $first, $m)) {
                                        $set('attributes.diamond_carat', (float) $m[1]);
                                    }
                                }
                                self::syncAllPricesAndPreviews($get, $set);
                            })
                            ->columnSpanFull(),

                        // 2. Diamond Clarity Selection (VVS1, VVS2, VS1, VS2, Moissanite)
                        Forms\Components\CheckboxList::make('attributes.diamond_clarities')
                            ->label('Available Diamond & Stone Options for Customer')
                            ->options([
                                'VVS1'       => 'VVS1 (Premium Diamond)',
                                'VVS2'       => 'VVS2 (Superior Diamond)',
                                'VS1'        => 'VS1 (Diamond)',
                                'VS2'        => 'VS2 (Diamond)',
                                'Moissanite' => 'Moissanite (VVS1 D-Colorless)',
                            ])
                            ->columns(['default' => 2, 'sm' => 3, 'lg' => 5])
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                            ->default(['VVS1', 'VVS2', 'VS1', 'VS2'])
                            ->helperText('Select which diamond & stone options customers can choose. Customer price recalculates dynamically as Carat Weight × Daily Stone Rate.')
                            ->columnSpanFull(),

                        // 2. Gold & Metal Purities Checkboxes
                        Forms\Components\CheckboxList::make('attributes.gold_purities')
                            ->label('Available Metal & Gold Purities')
                            ->options([
                                '10KT Gold'   => '10KT Gold',
                                '14KT Gold'   => '14KT Gold',
                                '18KT Gold'   => '18KT Gold',
                                '22KT Gold'   => '22KT Gold',
                                '925 Silver'  => '925 Silver',
                            ])
                            ->columns(['default' => 2, 'sm' => 3, 'lg' => 5])
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllPricesAndPreviews($get, $set))
                            ->default(['14KT Gold', '18KT Gold', '22KT Gold'])
                            ->columnSpanFull(),

                        // 3. Live Calculated Sell Price Showcase (Directly Under Available Purities)
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm'      => 2,
                            'md'      => 3,
                            'lg'      => 5,
                        ])
                            ->schema([
                                Forms\Components\TextInput::make('attributes.preview_10k')
                                    ->label('10KT Sell Price (₹)')
                                    ->prefix('₹')
                                    ->readOnly()
                                    ->extraAttributes(['class' => '[&_.fi-input-wrp-addon]:!bg-transparent'])
                                    ->dehydrated(false)
                                    ->placeholder('0')
                                    ->visible(function (Forms\Get $get) {
                                        $purities = (array) ($get('attributes.gold_purities') ?? ['14KT Gold', '18KT Gold', '22KT Gold']);
                                        return in_array('10KT Gold', $purities, true);
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w > 0) {
                                            $m = self::resolveMakingCharge($get);
                                            $d = self::resolveDiamondValue($get);
                                            $p = self::resolveProfitMargin($get);
                                            $carat = self::resolveDiamondCarat($get);
                                            $clarities = self::resolveDiamondClarities($get);
                                            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                            $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '10KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                            $component->state(number_format($c['final_price']));
                                        }
                                    })
                                    ->formatStateUsing(function ($state, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w <= 0) return '';
                                        $m = self::resolveMakingCharge($get);
                                        $d = self::resolveDiamondValue($get);
                                        $p = self::resolveProfitMargin($get);
                                        $carat = self::resolveDiamondCarat($get);
                                        $clarities = self::resolveDiamondClarities($get);
                                        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                        $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '10KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                        return number_format($c['final_price']);
                                    })
                                    ->helperText(fn (Forms\Get $get) => self::buildPurityPreviewHelperText($get, '10KT Gold'))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('attributes.preview_14k')
                                    ->label('14KT Sell Price (₹)')
                                    ->prefix('₹')
                                    ->readOnly()
                                    ->extraAttributes(['class' => '[&_.fi-input-wrp-addon]:!bg-transparent'])
                                    ->dehydrated(false)
                                    ->placeholder('0')
                                    ->visible(function (Forms\Get $get) {
                                        $purities = (array) ($get('attributes.gold_purities') ?? ['14KT Gold', '18KT Gold', '22KT Gold']);
                                        return in_array('14KT Gold', $purities, true);
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w > 0) {
                                            $m = self::resolveMakingCharge($get);
                                            $d = self::resolveDiamondValue($get);
                                            $p = self::resolveProfitMargin($get);
                                            $carat = self::resolveDiamondCarat($get);
                                            $clarities = self::resolveDiamondClarities($get);
                                            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                            $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '14KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                            $component->state(number_format($c['final_price']));
                                        }
                                    })
                                    ->formatStateUsing(function ($state, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w <= 0) return '';
                                        $m = self::resolveMakingCharge($get);
                                        $d = self::resolveDiamondValue($get);
                                        $p = self::resolveProfitMargin($get);
                                        $carat = self::resolveDiamondCarat($get);
                                        $clarities = self::resolveDiamondClarities($get);
                                        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                        $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '14KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                        return number_format($c['final_price']);
                                    })
                                    ->helperText(fn (Forms\Get $get) => self::buildPurityPreviewHelperText($get, '14KT Gold'))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('attributes.preview_18k')
                                    ->label('18KT Sell Price (₹)')
                                    ->prefix('₹')
                                    ->readOnly()
                                    ->extraAttributes(['class' => '[&_.fi-input-wrp-addon]:!bg-transparent'])
                                    ->dehydrated(false)
                                    ->placeholder('0')
                                    ->visible(function (Forms\Get $get) {
                                        $purities = (array) ($get('attributes.gold_purities') ?? ['14KT Gold', '18KT Gold', '22KT Gold']);
                                        return in_array('18KT Gold', $purities, true);
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w > 0) {
                                            $m = self::resolveMakingCharge($get);
                                            $d = self::resolveDiamondValue($get);
                                            $p = self::resolveProfitMargin($get);
                                            $carat = self::resolveDiamondCarat($get);
                                            $clarities = self::resolveDiamondClarities($get);
                                            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                            $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '18KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                            $component->state(number_format($c['final_price']));
                                        }
                                    })
                                    ->formatStateUsing(function ($state, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w <= 0) return '';
                                        $m = self::resolveMakingCharge($get);
                                        $d = self::resolveDiamondValue($get);
                                        $p = self::resolveProfitMargin($get);
                                        $carat = self::resolveDiamondCarat($get);
                                        $clarities = self::resolveDiamondClarities($get);
                                        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                        $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '18KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                        return number_format($c['final_price']);
                                    })
                                    ->helperText(fn (Forms\Get $get) => self::buildPurityPreviewHelperText($get, '18KT Gold'))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('attributes.preview_22k')
                                    ->label('22KT Sell Price (₹)')
                                    ->prefix('₹')
                                    ->readOnly()
                                    ->extraAttributes(['class' => '[&_.fi-input-wrp-addon]:!bg-transparent'])
                                    ->dehydrated(false)
                                    ->placeholder('0')
                                    ->visible(function (Forms\Get $get) {
                                        $purities = (array) ($get('attributes.gold_purities') ?? ['14KT Gold', '18KT Gold', '22KT Gold']);
                                        return in_array('22KT Gold', $purities, true);
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w > 0) {
                                            $m = self::resolveMakingCharge($get);
                                            $d = self::resolveDiamondValue($get);
                                            $p = self::resolveProfitMargin($get);
                                            $carat = self::resolveDiamondCarat($get);
                                            $clarities = self::resolveDiamondClarities($get);
                                            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                            $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '22KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                            $component->state(number_format($c['final_price']));
                                        }
                                    })
                                    ->formatStateUsing(function ($state, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w <= 0) return '';
                                        $m = self::resolveMakingCharge($get);
                                        $d = self::resolveDiamondValue($get);
                                        $p = self::resolveProfitMargin($get);
                                        $carat = self::resolveDiamondCarat($get);
                                        $clarities = self::resolveDiamondClarities($get);
                                        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                        $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '22KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                        return number_format($c['final_price']);
                                    })
                                    ->helperText(fn (Forms\Get $get) => self::buildPurityPreviewHelperText($get, '22KT Gold'))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('attributes.preview_silver')
                                    ->label('925 Silver Sell Price (₹)')
                                    ->prefix('₹')
                                    ->readOnly()
                                    ->extraAttributes(['class' => '[&_.fi-input-wrp-addon]:!bg-transparent'])
                                    ->dehydrated(false)
                                    ->placeholder('0')
                                    ->visible(function (Forms\Get $get) {
                                        $purities = (array) ($get('attributes.gold_purities') ?? ['14KT Gold', '18KT Gold', '22KT Gold']);
                                        return in_array('925 Silver', $purities, true);
                                    })
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w > 0) {
                                            $m = self::resolveMakingCharge($get);
                                            $d = self::resolveDiamondValue($get);
                                            $p = self::resolveProfitMargin($get);
                                            $carat = self::resolveDiamondCarat($get);
                                            $clarities = self::resolveDiamondClarities($get);
                                            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                            $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '925 Silver', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                            $component->state(number_format($c['final_price']));
                                        }
                                    })
                                    ->formatStateUsing(function ($state, Forms\Get $get) {
                                        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
                                        if ($w <= 0) return '';
                                        $m = self::resolveMakingCharge($get);
                                        $d = self::resolveDiamondValue($get);
                                        $p = self::resolveProfitMargin($get);
                                        $carat = self::resolveDiamondCarat($get);
                                        $clarities = self::resolveDiamondClarities($get);
                                        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
                                        $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '925 Silver', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
                                        return number_format($c['final_price']);
                                    })
                                    ->helperText(fn (Forms\Get $get) => self::buildPurityPreviewHelperText($get, '925 Silver'))
                                    ->columnSpan(1),
                            ]),

                        // 4. Piece Type: Multiple Sizes vs Single Piece
                        Forms\Components\Radio::make('attributes.has_sizes')
                            ->label('Sizing Type')
                            ->options([
                                'yes' => 'Multiple Sizes (Rings / Chains)',
                                'no'  => 'Single Piece (Studs / Earrings / Pendant)',
                            ])
                            ->default(fn (Forms\Get $get, ?\App\Models\Product $record) => ($record && empty($record->attributes['size_variants'])) ? 'no' : 'yes')
                            ->inline()
                            ->inlineLabel(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state === 'no') {
                                    $set('attributes.size_variants', []);
                                } else {
                                    $current = (array) ($get('attributes.size_variants') ?? []);
                                    if (empty($current)) {
                                        $set('attributes.size_variants', [
                                            ['size' => null]
                                        ]);
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        // 4B. Sizing Configuration: Simple Size Labels Only (When has_sizes is 'yes')
                        Forms\Components\Group::make()
                            ->visible(fn (Forms\Get $get) => $get('attributes.has_sizes') !== 'no')
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('loadRingSizes')
                                        ->label('+ Quick Preset: Ring Sizes (6 to 20)')
                                        ->color('gray')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Forms\Set $set) {
                                            $set('attributes.size_label', 'Ring Size');
                                            $sizes = [];
                                            for ($s = 6; $s <= 20; $s++) {
                                                $sizes[] = ['size' => 'Size ' . $s];
                                            }
                                            $set('attributes.size_variants', $sizes);
                                        }),

                                    Forms\Components\Actions\Action::make('loadChainSizes')
                                        ->label('+ Quick Preset: Chain Lengths (16, 18, 20 Inch)')
                                        ->color('gray')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Forms\Set $set) {
                                            $set('attributes.size_label', 'Chain Length');
                                            $set('attributes.size_variants', [
                                                ['size' => '16 Inch'],
                                                ['size' => '18 Inch'],
                                                ['size' => '20 Inch'],
                                            ]);
                                        }),

                                    Forms\Components\Actions\Action::make('loadBraceletSizes')
                                        ->label('+ Quick Preset: Wrist Sizes (6.5, 7.0, 7.5 Inch)')
                                        ->color('gray')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Forms\Set $set) {
                                            $set('attributes.size_label', 'Wrist Size');
                                            $set('attributes.size_variants', [
                                                ['size' => '6.5 Inch'],
                                                ['size' => '7.0 Inch'],
                                                ['size' => '7.5 Inch'],
                                            ]);
                                        }),
                                ]),

                                Forms\Components\Repeater::make('attributes.size_variants')
                                    ->label('Available Sizes (Customer Options)')
                                    ->helperText('Benchmark gold weight and price remain fixed across all sizes.')
                                    ->schema([
                                        Forms\Components\TextInput::make('size')
                                            ->hiddenLabel()
                                            ->placeholder('e.g. Size 6')
                                            ->required(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => !empty($state['size']) ? $state['size'] : 'New Size')
                                    ->grid([
                                        'default' => 2,
                                        'sm' => 2,
                                        'md' => 4,
                                        'lg' => 4,
                                        'xl' => 4,
                                        '2xl' => 4,
                                    ])
                                    ->reorderable(true)
                                    ->cloneable(false)
                                    ->defaultItems(0)
                                    ->addActionLabel('+ Add Size')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        // Fallback pricing for pieces without gold purities (e.g. silver / fashion / single price)
                        Forms\Components\Grid::make(2)
                            ->visible(fn (Forms\Get $get) => empty(array_filter((array) ($get('attributes.gold_purities') ?? []))))
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('MRP / Regular Price (₹)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->required(fn (Forms\Get $get) => empty(array_filter((array) ($get('attributes.gold_purities') ?? []))))
                                    ->placeholder('e.g. 50000'),

                                Forms\Components\TextInput::make('sale_price')
                                    ->label('Special Offer Price (₹ Optional)')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->nullable()
                                    ->placeholder('Leave blank if no discount'),
                            ])
                            ->columnSpanFull(),
                    ]),

                // ── ROW 3 (FULL WIDTH): Metal Colors & Gallery Photos ──
                Forms\Components\Section::make('Metal Colors & Gallery Photos')
                    ->description('Configure Metal Colors & Angle Views (Dynamic Swatch Switcher).')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\CheckboxList::make('attributes.metal_colors')
                            ->label('Available Metal Colors / Tones')
                            ->options([
                                'Yellow Gold' => 'Yellow Gold',
                                'White Gold'  => 'White Gold',
                                'Rose Gold'   => 'Rose Gold',
                            ])
                            ->columns(3)
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Tabs::make('Metal Tone Photos & Angle Views')
                            ->visible(fn (Forms\Get $get) => !empty(array_filter((array) ($get('attributes.metal_colors') ?? []))))
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Yellow Gold')
                                    ->visible(fn (Forms\Get $get) => in_array('Yellow Gold', (array) ($get('attributes.metal_colors') ?? []), true))
                                    ->badge(fn (Forms\Get $get) => count(array_filter((array) ($get('attributes.color_images.Yellow Gold') ?? []))))
                                    ->schema([
                                        Forms\Components\FileUpload::make('attributes.color_images.Yellow Gold')
                                            ->label('Yellow Gold Photos')
                                            ->multiple()
                                            ->appendFiles()
                                            ->reorderable()
                                            ->panelLayout('grid')
                                            ->extraAttributes(['class' => 'filepond-grid-3'])
                                            ->imagePreviewHeight('160')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                            ->maxSize(5120)
                                            ->maxFiles(20)
                                            ->disk('public')
                                            ->directory('products/colors')
                                            ->openable()
                                            ->downloadable()
                                            ->formatStateUsing(fn ($state) => is_string($state) ? [$state] : (array) ($state ?? []))
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('White Gold')
                                    ->visible(fn (Forms\Get $get) => in_array('White Gold', (array) ($get('attributes.metal_colors') ?? []), true))
                                    ->badge(fn (Forms\Get $get) => count(array_filter((array) ($get('attributes.color_images.White Gold') ?? []))))
                                    ->schema([
                                        Forms\Components\FileUpload::make('attributes.color_images.White Gold')
                                            ->label('White Gold Photos')
                                            ->multiple()
                                            ->appendFiles()
                                            ->reorderable()
                                            ->panelLayout('grid')
                                            ->extraAttributes(['class' => 'filepond-grid-3'])
                                            ->imagePreviewHeight('160')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                            ->maxSize(5120)
                                            ->maxFiles(20)
                                            ->disk('public')
                                            ->directory('products/colors')
                                            ->openable()
                                            ->downloadable()
                                            ->formatStateUsing(fn ($state) => is_string($state) ? [$state] : (array) ($state ?? []))
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Rose Gold')
                                    ->visible(fn (Forms\Get $get) => in_array('Rose Gold', (array) ($get('attributes.metal_colors') ?? []), true))
                                    ->badge(fn (Forms\Get $get) => count(array_filter((array) ($get('attributes.color_images.Rose Gold') ?? []))))
                                    ->schema([
                                        Forms\Components\FileUpload::make('attributes.color_images.Rose Gold')
                                            ->label('Rose Gold Photos')
                                            ->multiple()
                                            ->appendFiles()
                                            ->reorderable()
                                            ->panelLayout('grid')
                                            ->extraAttributes(['class' => 'filepond-grid-3'])
                                            ->imagePreviewHeight('160')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                            ->maxSize(5120)
                                            ->maxFiles(20)
                                            ->disk('public')
                                            ->directory('products/colors')
                                            ->openable()
                                            ->downloadable()
                                            ->formatStateUsing(fn ($state) => is_string($state) ? [$state] : (array) ($state ?? []))
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                // ── ROW 4 (FULL WIDTH): Diamond & Gemstone Specifications ──
                Forms\Components\Section::make('Diamond & Gemstone Specifications')
                    ->description('Optional: Diamond color, cut, and certification. (Diamond weight & clarities are managed in the dynamic pricing section above).')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm'      => 3,
                        ])
                            ->schema([
                                Forms\Components\TextInput::make('attributes.diamond_color')
                                    ->label('Diamond Color')
                                    ->placeholder('e.g. E-F Color or D-E, G-H Color')
                                    ->helperText('Optional: Diamond color grade (DEF, GH). Clarity is selected dynamically by customer above.'),

                                Forms\Components\TextInput::make('attributes.diamond_cut')
                                    ->label('Diamond Cut / Shape')
                                    ->placeholder('e.g. Round Brilliant, Emerald Cut')
                                    ->helperText('Optional: Shape or cut grade.'),

                                Forms\Components\TextInput::make('attributes.certification')
                                    ->label('Certification')
                                    ->placeholder('e.g. IGI / GIA Certified')
                                    ->helperText('Optional: Leave empty if not certified or not applicable.'),
                            ])
                            ->columnSpanFull(),
                    ]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Image')
                    ->circular()
                    ->getStateUsing(function ($record) {
                        return $record->primary_image_url;
                    }),

                Tables\Columns\IconColumn::make('video_path')
                    ->label('Video')
                    ->getStateUsing(fn ($record) => !empty($record->video_path) || !empty($record->video_url))
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon(null)
                    ->color('warning')
                    ->tooltip(fn ($record) => (!empty($record->video_path) || !empty($record->video_url)) ? 'Product has showcase video' : 'No video')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->sku ? 'SKU: ' . $record->sku : null),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('attributes.diamond_carat')
                    ->label('Diamond')
                    ->getStateUsing(function ($record) {
                        $opts = $record->diamond_carat_options;
                        if (!empty($opts) && count($opts) > 1) {
                            return count($opts) . ' Sizes (' . $opts[0] . ' - ' . end($opts) . ')';
                        }
                        if (!empty($opts)) {
                            return $opts[0];
                        }
                        if ($record->diamond_carat_weight > 0) {
                            return $record->diamond_carat_weight . ' ct';
                        }
                        return null;
                    })
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->html()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $effectivePrice = (float) $record->effective_price;
                        $effectiveMrp = (float) ($record->effective_mrp ?: $record->price);
                        $hasSale = $effectiveMrp > $effectivePrice;

                        if ($hasSale) {
                            $selling = '₹' . number_format($effectivePrice, 2);
                            $original = '₹' . number_format($effectiveMrp, 2);
                            return '<div class="flex flex-col leading-tight">' .
                                   '<span class="font-semibold">' . $selling . '</span>' .
                                   '<s style="text-decoration: line-through !important; text-decoration-thickness: 1.5px; color: #9ca3af; font-size: 11px; margin-top: 2px;">MRP: ' . $original . '</s>' .
                                   '</div>';
                        }

                        $variants = $record->attributes['size_variants'] ?? [];
                        $base = '₹' . number_format($effectivePrice, 2);
                        if (!empty($variants) && count($variants) > 1) {
                            return '<div class="flex flex-col leading-tight">' .
                                   '<span class="font-medium">' . $base . '</span>' .
                                   '<span class="text-[10.5px] text-gray-400">Multiple sizes</span>' .
                                   '</div>';
                        }

                        return '<span class="font-medium">' . $base . '</span>';
                    }),

                Tables\Columns\TextColumn::make('inventory_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'READY_TO_SHIP' => 'success',
                        'MADE_TO_ORDER' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record): string => match ($record->inventory_type) {
                        'READY_TO_SHIP' => "In Stock ({$record->stock})",
                        'MADE_TO_ORDER' => 'Made to Order',
                        default => $record->inventory_type,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('inventory_type')
                    ->options([
                        'READY_TO_SHIP' => 'Ready to Ship',
                        'MADE_TO_ORDER' => 'Made to Order',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view_on_site')
                    ->label('View on Site')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (\App\Models\Product $record): string => route('shop.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (\App\Models\Product $record): bool => !empty($record->slug)),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ])
            ->latest('id');
    }

    public static function resolveMakingCharge(Forms\Get $get): float
    {
        $val = $get('attributes.making_charge')
            ?? $get('../../making_charge')
            ?? $get('../../../attributes.making_charge')
            ?? $get('../../attributes.making_charge')
            ?? $get('data.attributes.making_charge', isAbsolute: true)
            ?? $get('attributes.making_charge', isAbsolute: true);

        $making = (float) ($val ?: 0);
        if ($making <= 0) {
            $rates = \App\Services\BullionPricingService::getRates();
            $making = (float) ($rates['making_per_gram'] ?: 750);
        }

        return $making;
    }

    public static function resolveDiamondCaratOptions(Forms\Get $get): array
    {
        $val = $get('attributes.diamond_carat_options')
            ?? $get('../../diamond_carat_options')
            ?? $get('../../../attributes.diamond_carat_options')
            ?? $get('../../attributes.diamond_carat_options')
            ?? $get('data.attributes.diamond_carat_options', isAbsolute: true)
            ?? $get('attributes.diamond_carat_options', isAbsolute: true);

        return is_array($val) ? array_values(array_filter($val)) : [];
    }

    public static function resolveDiamondCarat(Forms\Get $get): float
    {
        $val = $get('attributes.diamond_carat')
            ?? $get('../../diamond_carat')
            ?? $get('../../../attributes.diamond_carat')
            ?? $get('../../attributes.diamond_carat')
            ?? $get('data.attributes.diamond_carat', isAbsolute: true)
            ?? $get('attributes.diamond_carat', isAbsolute: true);

        if (is_numeric($val) && (float) $val > 0) return (float) $val;
        if (!empty($val) && preg_match('/([\d\.]+)/', (string) $val, $m)) {
            return (float) $m[1];
        }

        $options = self::resolveDiamondCaratOptions($get);
        if (!empty($options)) {
            $first = reset($options);
            if (preg_match('/([\d\.]+)/', (string) $first, $m)) {
                return (float) $m[1];
            }
        }

        return 0.0;
    }

    public static function resolveDiamondClarities(Forms\Get $get): array
    {
        $val = $get('attributes.diamond_clarities')
            ?? $get('../../diamond_clarities')
            ?? $get('../../../attributes.diamond_clarities')
            ?? $get('../../attributes.diamond_clarities')
            ?? $get('data.attributes.diamond_clarities', isAbsolute: true)
            ?? $get('attributes.diamond_clarities', isAbsolute: true);

        return is_array($val) ? array_values(array_filter($val)) : ['VVS1', 'VVS2', 'VS1', 'VS2'];
    }

    public static function resolveDiamondValue(Forms\Get $get): float
    {
        $carat = self::resolveDiamondCarat($get);
        if ($carat > 0) {
            $clarities = self::resolveDiamondClarities($get);
            $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';
            $rate = \App\Services\BullionPricingService::getDiamondRateForClarity($firstClarity);
            return round($carat * $rate, 2);
        }

        $val = $get('attributes.diamond_value')
            ?? $get('../../diamond_value')
            ?? $get('../../../attributes.diamond_value')
            ?? $get('../../attributes.diamond_value')
            ?? $get('data.attributes.diamond_value', isAbsolute: true)
            ?? $get('attributes.diamond_value', isAbsolute: true);

        return (float) ($val ?: 0);
    }

    public static function resolveProfitMargin(Forms\Get $get): float
    {
        $val = $get('attributes.profit_margin_percent')
            ?? $get('../../profit_margin_percent')
            ?? $get('../../../attributes.profit_margin_percent')
            ?? $get('../../attributes.profit_margin_percent')
            ?? $get('data.attributes.profit_margin_percent', isAbsolute: true)
            ?? $get('attributes.profit_margin_percent', isAbsolute: true);

        return (float) ($val ?: 0);
    }

    public static function resolveCutPricePercent(Forms\Get $get): float
    {
        $val = $get('attributes.cut_price_percent')
            ?? $get('../../cut_price_percent')
            ?? $get('../../../attributes.cut_price_percent')
            ?? $get('../../attributes.cut_price_percent')
            ?? $get('data.attributes.cut_price_percent', isAbsolute: true)
            ?? $get('attributes.cut_price_percent', isAbsolute: true);

        return max(0.0, (float) ($val ?: 0));
    }

    public static function buildPurityPreviewHelperText(Forms\Get $get, string $purity): ?string
    {
        $w = (float) ($get('attributes.gold_weight_grams') ?? 0);
        if ($w <= 0) {
            return null;
        }
        $m = self::resolveMakingCharge($get);
        $d = self::resolveDiamondValue($get);
        $p = self::resolveProfitMargin($get);
        $cutPct = self::resolveCutPricePercent($get);
        $carat = self::resolveDiamondCarat($get);
        $clarities = self::resolveDiamondClarities($get);
        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';

        $c = \App\Services\BullionPricingService::calculateFinalPrice(
            $w, $m, $d, $purity, null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity
        );
        $claritySuffix = $d > 0 ? " ({$firstClarity})" : '';
        $text = 'Cost: ₹' . number_format($c['cost_with_gst']) . ' + Profit: ₹' . number_format($c['profit_amount']) . ($p > 0 ? " ({$p}%)" : '') . $claritySuffix;
        if ($cutPct > 0) {
            $cutMrp = round($c['final_price'] * (1 + ($cutPct / 100)));
            $text .= ' · Cut MRP: ₹' . number_format($cutMrp) . " (+{$cutPct}%)";
        }
        return $text;
    }

    public static function resolveSizeVariants(Forms\Get $get): array
    {
        return (array) (
            $get('data.attributes.size_variants', isAbsolute: true)
            ?? $get('attributes.size_variants')
            ?? $get('attributes.size_variants', isAbsolute: true)
            ?? []
        );
    }

    public static function applyCommonCutPriceToAllVariants(float $cutPrice, Forms\Set $set, Forms\Get $get): void
    {
        if ($cutPrice <= 0) return;
        $variants = self::resolveSizeVariants($get);
        if (empty($variants)) return;

        foreach ($variants as $index => $v) {
            $variants[$index]['mrp_14k'] = $cutPrice;
            $variants[$index]['mrp_18k'] = $cutPrice;
            $variants[$index]['mrp_22k'] = $cutPrice;
            $variants[$index]['mrp']     = $cutPrice;
        }

        $set('attributes.size_variants', $variants);
        $set('data.attributes.size_variants', $variants);
    }

    public static function calculateAllPuritiesForVariant($weight, Forms\Set $set, Forms\Get $get): void
    {
        $weight = (float) ($weight ?: 0);
        if ($weight <= 0) {
            $set('price_10k', null);
            $set('mrp_10k', null);
            $set('price_14k', null);
            $set('mrp_14k', null);
            $set('price_18k', null);
            $set('mrp_18k', null);
            $set('price_22k', null);
            $set('mrp_22k', null);
            $set('price_silver', null);
            $set('mrp_silver', null);
            $set('price', null);
            $set('mrp', null);
            return;
        }

        $making = self::resolveMakingCharge($get);
        $diamond = self::resolveDiamondValue($get);
        $profit = self::resolveProfitMargin($get);
        $cutPct = self::resolveCutPricePercent($get);

        $commonCut = (float) (
            $get('../../attributes.common_cut_price')
            ?? $get('../../../attributes.common_cut_price')
            ?? $get('attributes.common_cut_price', isAbsolute: true)
            ?? $get('data.attributes.common_cut_price', isAbsolute: true)
            ?? 0
        );
        $mrpAttrs = ['cut_price_percent' => $cutPct, 'common_cut_price' => $cutPct > 0 ? null : $commonCut];

        $c10  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '10KT Gold', null, null, $weight, $profit);
        $c14  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '14KT Gold', null, null, $weight, $profit);
        $c18  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '18KT Gold', null, null, $weight, $profit);
        $c22  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '22KT Gold', null, null, $weight, $profit);
        $cSil = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '925 Silver', null, null, $weight, $profit);

        $set('price_10k',    $c10['final_price']);
        $set('mrp_10k',      \App\Services\BullionPricingService::calculateStrikeMrp($c10['final_price'], $mrpAttrs, $c10['mrp_price']));

        $set('price_14k',    $c14['final_price']);
        $set('mrp_14k',      \App\Services\BullionPricingService::calculateStrikeMrp($c14['final_price'], $mrpAttrs, $c14['mrp_price']));

        $set('price_18k',    $c18['final_price']);
        $set('mrp_18k',      \App\Services\BullionPricingService::calculateStrikeMrp($c18['final_price'], $mrpAttrs, $c18['mrp_price']));

        $set('price_22k',    $c22['final_price']);
        $set('mrp_22k',      \App\Services\BullionPricingService::calculateStrikeMrp($c22['final_price'], $mrpAttrs, $c22['mrp_price']));

        $set('price_silver', $cSil['final_price']);
        $set('mrp_silver',   \App\Services\BullionPricingService::calculateStrikeMrp($cSil['final_price'], $mrpAttrs, $cSil['mrp_price']));

        $set('price',        $c18['final_price']);
        $set('mrp',          \App\Services\BullionPricingService::calculateStrikeMrp($c18['final_price'], $mrpAttrs, $c18['mrp_price']));
    }

    public static function syncPricePreviews(Forms\Get $get, Forms\Set $set): void
    {
        $w = (float) (
            $get('attributes.gold_weight_grams')
            ?? $get('attributes.gold_weight_grams', isAbsolute: true)
            ?? $get('data.attributes.gold_weight_grams', isAbsolute: true)
            ?? 0
        );
        $m = self::resolveMakingCharge($get);
        $d = self::resolveDiamondValue($get);
        $p = self::resolveProfitMargin($get);
        $carat = self::resolveDiamondCarat($get);
        $clarities = self::resolveDiamondClarities($get);
        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';

        if ($w > 0) {
            $c10  = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '10KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
            $c14  = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '14KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
            $c18  = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '18KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
            $c22  = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '22KT Gold', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);
            $cSil = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '925 Silver', null, null, $w, $p, $carat > 0 ? $carat : null, $firstClarity);

            $set('attributes.preview_10k', number_format($c10['final_price']));
            $set('attributes.preview_14k', number_format($c14['final_price']));
            $set('attributes.preview_18k', number_format($c18['final_price']));
            $set('attributes.preview_22k', number_format($c22['final_price']));
            $set('attributes.preview_silver', number_format($cSil['final_price']));
        } else {
            $set('attributes.preview_10k', '');
            $set('attributes.preview_14k', '');
            $set('attributes.preview_18k', '');
            $set('attributes.preview_22k', '');
            $set('attributes.preview_silver', '');
        }
    }

    public static function syncAllPricesAndPreviews(Forms\Get $get, Forms\Set $set): void
    {
        self::syncPricePreviews($get, $set);
        self::syncAllSizePrices($get, $set);
    }

    public static function syncAllSizePrices(Forms\Get $get, Forms\Set $set): void
    {
        $variants = self::resolveSizeVariants($get);
        if (empty($variants)) {
            return;
        }

        $making = self::resolveMakingCharge($get);
        $diamond = self::resolveDiamondValue($get);
        $profit = self::resolveProfitMargin($get);
        $cutPct = self::resolveCutPricePercent($get);
        $carat = self::resolveDiamondCarat($get);
        $clarities = self::resolveDiamondClarities($get);
        $firstClarity = !empty($clarities) ? $clarities[0] : 'VVS1';

        $commonCut = (float) (
            $get('attributes.common_cut_price')
            ?? $get('attributes.common_cut_price', isAbsolute: true)
            ?? $get('data.attributes.common_cut_price', isAbsolute: true)
            ?? 0
        );
        $mrpAttrs = ['cut_price_percent' => $cutPct, 'common_cut_price' => $cutPct > 0 ? null : $commonCut];

        $updated = false;
        foreach ($variants as $index => $variant) {
            $weight = (float) ($variant['weight_grams'] ?? 0);
            if ($weight > 0) {
                $c10  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '10KT Gold', null, null, $weight, $profit, $carat > 0 ? $carat : null, $firstClarity);
                $c14  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '14KT Gold', null, null, $weight, $profit, $carat > 0 ? $carat : null, $firstClarity);
                $c18  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '18KT Gold', null, null, $weight, $profit, $carat > 0 ? $carat : null, $firstClarity);
                $c22  = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '22KT Gold', null, null, $weight, $profit, $carat > 0 ? $carat : null, $firstClarity);
                $cSil = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '925 Silver', null, null, $weight, $profit, $carat > 0 ? $carat : null, $firstClarity);

                $variants[$index]['price_10k']    = $c10['final_price'];
                $variants[$index]['price_14k']    = $c14['final_price'];
                $variants[$index]['price_18k']    = $c18['final_price'];
                $variants[$index]['price_22k']    = $c22['final_price'];
                $variants[$index]['price_silver'] = $cSil['final_price'];
                $variants[$index]['price']        = $c18['final_price'];

                $variants[$index]['mrp_10k']    = \App\Services\BullionPricingService::calculateStrikeMrp($c10['final_price'], $mrpAttrs, $variants[$index]['mrp_10k'] ?? $c10['mrp_price']);
                $variants[$index]['mrp_14k']    = \App\Services\BullionPricingService::calculateStrikeMrp($c14['final_price'], $mrpAttrs, $variants[$index]['mrp_14k'] ?? $c14['mrp_price']);
                $variants[$index]['mrp_18k']    = \App\Services\BullionPricingService::calculateStrikeMrp($c18['final_price'], $mrpAttrs, $variants[$index]['mrp_18k'] ?? $c18['mrp_price']);
                $variants[$index]['mrp_22k']    = \App\Services\BullionPricingService::calculateStrikeMrp($c22['final_price'], $mrpAttrs, $variants[$index]['mrp_22k'] ?? $c22['mrp_price']);
                $variants[$index]['mrp_silver'] = \App\Services\BullionPricingService::calculateStrikeMrp($cSil['final_price'], $mrpAttrs, $variants[$index]['mrp_silver'] ?? $cSil['mrp_price']);
                $variants[$index]['mrp']        = \App\Services\BullionPricingService::calculateStrikeMrp($c18['final_price'], $mrpAttrs, $variants[$index]['mrp'] ?? $c18['mrp_price']);

                $updated = true;
            }
        }

        if ($updated) {
            $set('attributes.size_variants', $variants);
            $set('data.attributes.size_variants', $variants);
        }
    }

}

