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
                                ->maxSize(25600)
                                ->disk('public')
                                ->directory('products/videos')
                                ->openable()
                                ->downloadable()
                                ->helperText('Upload craft or showcase video (Max 25 MB). Takes priority over external URL.')
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
                    ->description('Synchronized with Live 24KT Bullion Benchmark. Every size variant features metallurgical purity breakdown across 14KT, 18KT, and 22KT with automated Strike-through MRP generation.')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Hidden::make('attributes.pricing_mode')
                            ->default('dynamic'),

                        // 1. Dynamic Formula Parameters (Making Charge & Diamond Value)
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('attributes.making_charge')
                                    ->label('Making / Wastage Charge (₹ / gm)')
                                    ->numeric()
                                    ->step('any')
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->prefix('₹')
                                    ->required()
                                    ->placeholder('e.g. 750')
                                    ->helperText('Per-gram craftsmanship and wastage fee added to gold weight.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllSizePrices($get, $set)),

                                Forms\Components\TextInput::make('attributes.diamond_value')
                                    ->label('Total Diamond / Gemstone Value (₹)')
                                    ->numeric()
                                    ->step('any')
                                    ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                    ->prefix('₹')
                                    ->default(0)
                                    ->placeholder('0 if plain gold')
                                    ->helperText('Total certified natural diamond & gemstone component value.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllSizePrices($get, $set)),
                            ]),

                        // 2. Gold Purities Checkboxes
                        Forms\Components\CheckboxList::make('attributes.gold_purities')
                            ->label('Available Gold Purities')
                            ->options([
                                '14KT Gold' => '14KT Gold',
                                '18KT Gold' => '18KT Gold',
                                '22KT Gold' => '22KT Gold',
                            ])
                            ->columns(3)
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) => self::syncAllSizePrices($get, $set))
                            ->default(['14KT Gold', '18KT Gold', '22KT Gold'])
                            ->columnSpanFull(),

                        // 3. Dynamic Mode: Sizes & Purity Breakdown Table
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('attributes.size_label')
                                            ->label('Size Dimension Classification')
                                            ->placeholder('e.g. Ring Size, Chain Length, Wrist Size')
                                            ->default('Ring Size')
                                            ->helperText('Product variant dimension taxonomy shown on storefront.'),

                                        Forms\Components\TextInput::make('attributes.gold_weight_grams')
                                            ->label('Net Gold Weight (gm) - Single Piece Only')
                                            ->numeric()
                                            ->step('any')
                                            ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                            ->suffix('gm')
                                            ->placeholder('Only if piece has no sizes (e.g. pendant)')
                                            ->helperText('Net pure gold weight in grams for fixed/non-sizeable pieces.')
                                            ->visible(fn (Forms\Get $get) => empty(array_filter((array) ($get('attributes.size_variants') ?? [])))),
                                    ]),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('loadRingSizes')
                                        ->label('+ Quick Preset: Ring Sizes (10 to 18)')
                                        ->color('gray')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $set('attributes.size_label', 'Ring Size');
                                            $set('attributes.size_variants', [
                                                ['size' => 'Size 10', 'weight_grams' => 3.60],
                                                ['size' => 'Size 12', 'weight_grams' => 4.00],
                                                ['size' => 'Size 14', 'weight_grams' => 4.20],
                                                ['size' => 'Size 16', 'weight_grams' => 4.50],
                                                ['size' => 'Size 18', 'weight_grams' => 4.90],
                                            ]);
                                            self::syncAllSizePrices($get, $set);
                                        }),

                                    Forms\Components\Actions\Action::make('loadChainSizes')
                                        ->label('+ Quick Preset: Chain Lengths (16, 18, 20 Inch)')
                                        ->color('gray')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $set('attributes.size_label', 'Chain Length');
                                            $set('attributes.size_variants', [
                                                ['size' => '16 Inch', 'weight_grams' => 5.20],
                                                ['size' => '18 Inch', 'weight_grams' => 5.80],
                                                ['size' => '20 Inch', 'weight_grams' => 6.50],
                                            ]);
                                            self::syncAllSizePrices($get, $set);
                                        }),

                                    Forms\Components\Actions\Action::make('loadBraceletSizes')
                                        ->label('+ Quick Preset: Wrist Sizes (6.5, 7.0, 7.5 Inch)')
                                        ->color('gray')
                                        ->icon('heroicon-m-sparkles')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $set('attributes.size_label', 'Wrist Size');
                                            $set('attributes.size_variants', [
                                                ['size' => '6.5 Inch', 'weight_grams' => 4.80],
                                                ['size' => '7.0 Inch', 'weight_grams' => 5.20],
                                                ['size' => '7.5 Inch', 'weight_grams' => 5.60],
                                            ]);
                                            self::syncAllSizePrices($get, $set);
                                        }),
                                ]),

                                Forms\Components\Repeater::make('attributes.size_variants')
                                    ->label('Dynamic Multi-Purity Valuation Matrix (14KT, 18KT & 22KT)')
                                    ->helperText('Real-time bullion price engine automatically calculates live Selling Prices and Strikethrough Showcase MRPs (25% markup) across 14KT, 18KT, and 22KT purities per size. Values can be fine-tuned manually per variant.')
                                    ->schema([
                                        Forms\Components\Grid::make([
                                            'default' => 2,
                                            'md' => 4,
                                            'lg' => 6,
                                        ])->schema([
                                            // ── ROW 1: Size & Weight (Full width row) ──
                                            Forms\Components\TextInput::make('size')
                                                ->label('Size Name')
                                                ->placeholder('e.g. Size 12')
                                                ->required()
                                                ->columnSpan(['default' => 2, 'md' => 2, 'lg' => 3]),

                                            Forms\Components\TextInput::make('weight_grams')
                                                ->label('Exact Gold Weight (gm)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'decimal'])
                                                ->suffix('gm')
                                                ->placeholder('e.g. 4.00')
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    self::calculateAllPuritiesForVariant($state, $set, $get);
                                                })
                                                ->columnSpan(['default' => 2, 'md' => 2, 'lg' => 3]),

                                            // ── ROW 2: Only Selected Purities (14KT, 18KT, 22KT) ──
                                            Forms\Components\TextInput::make('price_14k')
                                                ->label('14KT Sell (₹)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'numeric'])
                                                ->prefix('₹')
                                                ->dehydrated()
                                                ->visible(function (Forms\Get $get) {
                                                    $purities = $get('../../../attributes.gold_purities')
                                                        ?? $get('../../attributes.gold_purities')
                                                        ?? $get('attributes.gold_purities', isAbsolute: true);
                                                    if ($purities === null) {
                                                        $purities = ['14KT Gold', '18KT Gold', '22KT Gold'];
                                                    }
                                                    return in_array('14KT Gold', (array) $purities, true);
                                                })
                                                ->formatStateUsing(function ($state, Forms\Get $get) {
                                                    if (!empty($state)) return $state;
                                                    $w = (float) ($get('weight_grams') ?? 0);
                                                    if ($w <= 0) return null;
                                                    $m = self::resolveMakingCharge($get);
                                                    $d = self::resolveDiamondValue($get);
                                                    $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '14KT Gold', null, null, $w);
                                                    return $c['final_price'];
                                                })
                                                ->placeholder('Auto 14K Sell')
                                                ->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),

                                            Forms\Components\TextInput::make('mrp_14k')
                                                ->label('14KT Cut (₹)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'numeric'])
                                                ->prefix('₹')
                                                ->dehydrated()
                                                ->visible(function (Forms\Get $get) {
                                                    $purities = $get('../../../attributes.gold_purities')
                                                        ?? $get('../../attributes.gold_purities')
                                                        ?? $get('attributes.gold_purities', isAbsolute: true);
                                                    if ($purities === null) {
                                                        $purities = ['14KT Gold', '18KT Gold', '22KT Gold'];
                                                    }
                                                    return in_array('14KT Gold', (array) $purities, true);
                                                })
                                                ->formatStateUsing(function ($state, Forms\Get $get) {
                                                    if (!empty($state)) return $state;
                                                    $w = (float) ($get('weight_grams') ?? 0);
                                                    if ($w <= 0) return null;
                                                    $m = self::resolveMakingCharge($get);
                                                    $d = self::resolveDiamondValue($get);
                                                    $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '14KT Gold', null, null, $w);
                                                    return $c['mrp_price'];
                                                })
                                                ->placeholder('Auto 14K Cut')
                                                ->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),

                                            Forms\Components\TextInput::make('price_18k')
                                                ->label('18KT Sell (₹)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'numeric'])
                                                ->prefix('₹')
                                                ->dehydrated()
                                                ->visible(function (Forms\Get $get) {
                                                    $purities = $get('../../../attributes.gold_purities')
                                                        ?? $get('../../attributes.gold_purities')
                                                        ?? $get('attributes.gold_purities', isAbsolute: true);
                                                    if ($purities === null) {
                                                        $purities = ['14KT Gold', '18KT Gold', '22KT Gold'];
                                                    }
                                                    return in_array('18KT Gold', (array) $purities, true);
                                                })
                                                ->formatStateUsing(function ($state, Forms\Get $get) {
                                                    if (!empty($state)) return $state;
                                                    $w = (float) ($get('weight_grams') ?? 0);
                                                    if ($w <= 0) return null;
                                                    $m = self::resolveMakingCharge($get);
                                                    $d = self::resolveDiamondValue($get);
                                                    $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '18KT Gold', null, null, $w);
                                                    return $c['final_price'];
                                                })
                                                ->placeholder('Auto 18K Sell')
                                                ->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),

                                            Forms\Components\TextInput::make('mrp_18k')
                                                ->label('18KT Cut (₹)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'numeric'])
                                                ->prefix('₹')
                                                ->dehydrated()
                                                ->visible(function (Forms\Get $get) {
                                                    $purities = $get('../../../attributes.gold_purities')
                                                        ?? $get('../../attributes.gold_purities')
                                                        ?? $get('attributes.gold_purities', isAbsolute: true);
                                                    if ($purities === null) {
                                                        $purities = ['14KT Gold', '18KT Gold', '22KT Gold'];
                                                    }
                                                    return in_array('18KT Gold', (array) $purities, true);
                                                })
                                                ->formatStateUsing(function ($state, Forms\Get $get) {
                                                    if (!empty($state)) return $state;
                                                    $w = (float) ($get('weight_grams') ?? 0);
                                                    if ($w <= 0) return null;
                                                    $m = self::resolveMakingCharge($get);
                                                    $d = self::resolveDiamondValue($get);
                                                    $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '18KT Gold', null, null, $w);
                                                    return $c['mrp_price'];
                                                })
                                                ->placeholder('Auto 18K Cut')
                                                ->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),

                                            Forms\Components\TextInput::make('price_22k')
                                                ->label('22KT Sell (₹)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'numeric'])
                                                ->prefix('₹')
                                                ->dehydrated()
                                                ->visible(function (Forms\Get $get) {
                                                    $purities = $get('../../../attributes.gold_purities')
                                                        ?? $get('../../attributes.gold_purities')
                                                        ?? $get('attributes.gold_purities', isAbsolute: true);
                                                    if ($purities === null) {
                                                        $purities = ['14KT Gold', '18KT Gold', '22KT Gold'];
                                                    }
                                                    return in_array('22KT Gold', (array) $purities, true);
                                                })
                                                ->formatStateUsing(function ($state, Forms\Get $get) {
                                                    if (!empty($state)) return $state;
                                                    $w = (float) ($get('weight_grams') ?? 0);
                                                    if ($w <= 0) return null;
                                                    $m = self::resolveMakingCharge($get);
                                                    $d = self::resolveDiamondValue($get);
                                                    $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '22KT Gold', null, null, $w);
                                                    return $c['final_price'];
                                                })
                                                ->placeholder('Auto 22K Sell')
                                                ->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),

                                            Forms\Components\TextInput::make('mrp_22k')
                                                ->label('22KT Cut (₹)')
                                                ->numeric()
                                                ->step('any')
                                                ->extraInputAttributes(['class' => '[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none', 'inputmode' => 'numeric'])
                                                ->prefix('₹')
                                                ->dehydrated()
                                                ->visible(function (Forms\Get $get) {
                                                    $purities = $get('../../../attributes.gold_purities')
                                                        ?? $get('../../attributes.gold_purities')
                                                        ?? $get('attributes.gold_purities', isAbsolute: true);
                                                    if ($purities === null) {
                                                        $purities = ['14KT Gold', '18KT Gold', '22KT Gold'];
                                                    }
                                                    return in_array('22KT Gold', (array) $purities, true);
                                                })
                                                ->formatStateUsing(function ($state, Forms\Get $get) {
                                                    if (!empty($state)) return $state;
                                                    $w = (float) ($get('weight_grams') ?? 0);
                                                    if ($w <= 0) return null;
                                                    $m = self::resolveMakingCharge($get);
                                                    $d = self::resolveDiamondValue($get);
                                                    $c = \App\Services\BullionPricingService::calculateFinalPrice($w, $m, $d, '22KT Gold', null, null, $w);
                                                    return $c['mrp_price'];
                                                })
                                                ->placeholder('Auto 22K Cut')
                                                ->columnSpan(['default' => 1, 'md' => 1, 'lg' => 1]),
                                        ]),

                                        Forms\Components\Hidden::make('price'),
                                        Forms\Components\Hidden::make('mrp'),
                                    ])
                                    ->itemLabel(fn (array $state): ?string =>
                                        !empty($state['size'])
                                            ? $state['size'] . (!empty($state['weight_grams']) ? ' (' . $state['weight_grams'] . ' gm)' : '')
                                            : null
                                    )
                                    ->cloneable()
                                    ->reorderable(true)
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
                    ->description('Optional: Solitaire carat, color/clarity, cut, certification.')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('attributes.diamond_carat')
                                    ->label('Diamond Carat Weight')
                                    ->placeholder('e.g. 1.50 Carat or 2.83 Carat TW')
                                    ->helperText('Optional: Solitaire / total carat weight.'),

                                Forms\Components\TextInput::make('attributes.diamond_color_clarity')
                                    ->label('Diamond Color & Clarity')
                                    ->placeholder('e.g. E-F Color · VVS1 Clarity')
                                    ->helperText('Optional: Color and clarity grade.'),

                                Forms\Components\TextInput::make('attributes.diamond_cut')
                                    ->label('Diamond Cut / Shape')
                                    ->placeholder('e.g. Round Brilliant, Emerald Cut')
                                    ->helperText('Optional: Shape or cut grade.'),

                                Forms\Components\TextInput::make('attributes.certification')
                                    ->label('Certification')
                                    ->placeholder('e.g. IGI / GIA Certified')
                                    ->helperText('Optional: Leave empty if not certified or not applicable.'),
                            ])
                            ->columns(2)
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

    public static function resolveDiamondValue(Forms\Get $get): float
    {
        $val = $get('attributes.diamond_value')
            ?? $get('../../diamond_value')
            ?? $get('../../../attributes.diamond_value')
            ?? $get('../../attributes.diamond_value')
            ?? $get('data.attributes.diamond_value', isAbsolute: true)
            ?? $get('attributes.diamond_value', isAbsolute: true);

        return (float) ($val ?: 0);
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

    public static function calculateAllPuritiesForVariant($weight, Forms\Set $set, Forms\Get $get): void
    {
        $weight = (float) ($weight ?: 0);
        if ($weight <= 0) {
            $set('price_14k', null);
            $set('mrp_14k', null);
            $set('price_18k', null);
            $set('mrp_18k', null);
            $set('price_22k', null);
            $set('mrp_22k', null);
            $set('price', null);
            $set('mrp', null);
            return;
        }

        $making = self::resolveMakingCharge($get);
        $diamond = self::resolveDiamondValue($get);

        $c14 = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '14KT Gold', null, null, $weight);
        $c18 = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '18KT Gold', null, null, $weight);
        $c22 = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '22KT Gold', null, null, $weight);

        $set('price_14k', $c14['final_price']);
        $set('mrp_14k',   $c14['mrp_price']);

        $set('price_18k', $c18['final_price']);
        $set('mrp_18k',   $c18['mrp_price']);

        $set('price_22k', $c22['final_price']);
        $set('mrp_22k',   $c22['mrp_price']);

        $set('price',     $c18['final_price']);
        $set('mrp',       $c18['mrp_price']);
    }

    public static function syncAllSizePrices(Forms\Get $get, Forms\Set $set): void
    {
        $variants = self::resolveSizeVariants($get);
        if (empty($variants)) {
            return;
        }

        $making = self::resolveMakingCharge($get);
        $diamond = self::resolveDiamondValue($get);

        $updated = false;
        foreach ($variants as $index => $variant) {
            $weight = (float) ($variant['weight_grams'] ?? 0);
            if ($weight > 0) {
                $c14 = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '14KT Gold', null, null, $weight);
                $c18 = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '18KT Gold', null, null, $weight);
                $c22 = \App\Services\BullionPricingService::calculateFinalPrice($weight, $making, $diamond, '22KT Gold', null, null, $weight);

                $variants[$index]['price_14k'] = $c14['final_price'];
                $variants[$index]['mrp_14k']   = $c14['mrp_price'];

                $variants[$index]['price_18k'] = $c18['final_price'];
                $variants[$index]['mrp_18k']   = $c18['mrp_price'];

                $variants[$index]['price_22k'] = $c22['final_price'];
                $variants[$index]['mrp_22k']   = $c22['mrp_price'];

                $variants[$index]['price']     = $c18['final_price'];
                $variants[$index]['mrp']       = $c18['mrp_price'];
                $updated = true;
            }
        }

        if ($updated) {
            $set('attributes.size_variants', $variants);
            $set('data.attributes.size_variants', $variants);
        }
    }
}

