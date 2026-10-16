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

                    Forms\Components\Section::make('Gold Purity & Pricing')
                        ->description('Select available gold purities and configure exact Selling Price and optional MRP / Strikethrough Price for each Karat.')
                        ->schema([
                            Forms\Components\CheckboxList::make('attributes.gold_purities')
                                ->label('Available Gold Purities')
                                ->options([
                                    '14KT Gold' => '14KT Gold',
                                    '18KT Gold' => '18KT Gold',
                                    '22KT Gold' => '22KT Gold',
                                ])
                                ->columns(3)
                                ->live()
                                ->helperText('Select the gold purities offered. Enter the exact selling price for each selected purity below.')
                                ->columnSpanFull(),

                            Forms\Components\Grid::make(1)
                                ->schema([
                                    // 14KT Row
                                    Forms\Components\Grid::make(2)
                                        ->visible(fn (Forms\Get $get) => in_array('14KT Gold', (array) ($get('attributes.gold_purities') ?? []), true))
                                        ->schema([
                                            Forms\Components\TextInput::make('attributes.purity_prices.14KT Gold')
                                                ->label('14KT Selling Price (₹)')
                                                ->numeric()
                                                ->prefix('₹')
                                                ->placeholder('e.g. 288270')
                                                ->required(fn (Forms\Get $get) => in_array('14KT Gold', (array) ($get('attributes.gold_purities') ?? []), true)),

                                            Forms\Components\TextInput::make('attributes.purity_mrp.14KT Gold')
                                                ->label('14KT MRP (₹ Optional)')
                                                ->numeric()
                                                ->prefix('₹')
                                                ->placeholder('e.g. 387612'),
                                        ]),

                                    // 18KT Row
                                    Forms\Components\Grid::make(2)
                                        ->visible(fn (Forms\Get $get) => in_array('18KT Gold', (array) ($get('attributes.gold_purities') ?? []), true))
                                        ->schema([
                                            Forms\Components\TextInput::make('attributes.purity_prices.18KT Gold')
                                                ->label('18KT Selling Price (₹)')
                                                ->numeric()
                                                ->prefix('₹')
                                                ->placeholder('e.g. 304721')
                                                ->required(fn (Forms\Get $get) => in_array('18KT Gold', (array) ($get('attributes.gold_purities') ?? []), true)),

                                            Forms\Components\TextInput::make('attributes.purity_mrp.18KT Gold')
                                                ->label('18KT MRP (₹ Optional)')
                                                ->numeric()
                                                ->prefix('₹')
                                                ->placeholder('e.g. 387612'),
                                        ]),

                                    // 22KT Row
                                    Forms\Components\Grid::make(2)
                                        ->visible(fn (Forms\Get $get) => in_array('22KT Gold', (array) ($get('attributes.gold_purities') ?? []), true))
                                        ->schema([
                                            Forms\Components\TextInput::make('attributes.purity_prices.22KT Gold')
                                                ->label('22KT Selling Price (₹)')
                                                ->numeric()
                                                ->prefix('₹')
                                                ->placeholder('e.g. 325364')
                                                ->required(fn (Forms\Get $get) => in_array('22KT Gold', (array) ($get('attributes.gold_purities') ?? []), true)),

                                            Forms\Components\TextInput::make('attributes.purity_mrp.22KT Gold')
                                                ->label('22KT MRP (₹ Optional)')
                                                ->numeric()
                                                ->prefix('₹')
                                                ->placeholder('e.g. 410000'),
                                        ]),
                                ])
                                ->columnSpanFull(),

                            // Fallback pricing for pieces where no gold purities are selected (e.g. silver / fashion / single price)
                            Forms\Components\Grid::make(2)
                                ->visible(fn (Forms\Get $get) => empty(array_filter((array) ($get('attributes.gold_purities') ?? []))))
                                ->schema([
                                    Forms\Components\TextInput::make('price')
                                        ->label('MRP / Regular Price (₹)')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->required(fn (Forms\Get $get) => empty(array_filter((array) ($get('attributes.gold_purities') ?? []))))
                                        ->placeholder('e.g. 50000')
                                        ->helperText('Regular catalog MRP (shown when piece does not use Karat purities).'),

                                    Forms\Components\TextInput::make('sale_price')
                                        ->label('Special Offer Price (₹ Optional)')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->nullable()
                                        ->placeholder('Leave blank if no discount')
                                        ->helperText('Optional discounted selling price.'),
                                ])
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Section::make('Jewellery Customization & Variant Options')
                        ->description('Configure Metal Colors & Photos, Size variants (with 1-click presets), and Diamond specifications.')
                        ->collapsible(false)
                        ->schema([
                            // 1. Metal Colors & Photos
                            Forms\Components\CheckboxList::make('attributes.metal_colors')
                                ->label('Available Metal Colors / Tones')
                                ->options([
                                    'Yellow Gold' => 'Yellow Gold',
                                    'White Gold'  => 'White Gold',
                                    'Rose Gold'   => 'Rose Gold',
                                ])
                                ->columns(3)
                                ->live()
                                ->helperText('Select the gold colors offered. Only selected colors will show photo upload tabs below.')
                                ->columnSpanFull(),

                            Forms\Components\Tabs::make('Metal Tone Photos & Angle Views (Dynamic Swatch Switcher)')
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
                                                ->imagePreviewHeight('180')
                                                ->image()
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                                ->maxSize(5120)
                                                ->disk('public')
                                                ->directory('products/colors')
                                                ->openable()
                                                ->downloadable()
                                                ->formatStateUsing(fn ($state) => is_string($state) ? [$state] : (array) ($state ?? []))
                                                ->helperText('Upload 1 or multiple angle photos (front, side, hand-worn). Shown first by default. Click ↗ to view in full size. Drag to reorder.')
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
                                                ->imagePreviewHeight('180')
                                                ->image()
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                                ->maxSize(5120)
                                                ->disk('public')
                                                ->directory('products/colors')
                                                ->openable()
                                                ->downloadable()
                                                ->formatStateUsing(fn ($state) => is_string($state) ? [$state] : (array) ($state ?? []))
                                                ->helperText('Upload 1 or multiple angle photos. Switches dynamically when White Gold is clicked. Click ↗ to view in full size. Drag to reorder.')
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
                                                ->imagePreviewHeight('180')
                                                ->image()
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                                ->maxSize(5120)
                                                ->disk('public')
                                                ->directory('products/colors')
                                                ->openable()
                                                ->downloadable()
                                                ->formatStateUsing(fn ($state) => is_string($state) ? [$state] : (array) ($state ?? []))
                                                ->helperText('Upload 1 or multiple angle photos. Switches dynamically when Rose Gold is clicked. Click ↗ to view in full size. Drag to reorder.')
                                                ->columnSpanFull(),
                                        ]),
                                ])
                                ->columnSpanFull(),

                            // 2. Sizes & Preset Loaders
                            Forms\Components\TextInput::make('attributes.size_label')
                                ->label('Size Attribute Label')
                                ->placeholder('e.g. Ring Size, Chain Length, Wrist Size')
                                ->default('Ring Size')
                                ->helperText('Label displayed above the size options on the product page.')
                                ->columnSpanFull(),

                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('loadRingSizes')
                                    ->label('+ Quick Preset: Ring Sizes (10 to 18)')
                                    ->color('gray')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function (Forms\Set $set) {
                                        $set('attributes.size_label', 'Ring Size');
                                        $set('attributes.size_variants', [
                                            ['size' => 'Size 10', 'price' => null],
                                            ['size' => 'Size 12', 'price' => null],
                                            ['size' => 'Size 14', 'price' => null],
                                            ['size' => 'Size 16', 'price' => null],
                                            ['size' => 'Size 18', 'price' => null],
                                        ]);
                                    }),

                                Forms\Components\Actions\Action::make('loadChainSizes')
                                    ->label('+ Quick Preset: Chain Lengths (16, 18, 20 Inch)')
                                    ->color('gray')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function (Forms\Set $set) {
                                        $set('attributes.size_label', 'Chain Length');
                                        $set('attributes.size_variants', [
                                            ['size' => '16 Inch', 'price' => null],
                                            ['size' => '18 Inch', 'price' => null],
                                            ['size' => '20 Inch', 'price' => null],
                                        ]);
                                    }),

                                Forms\Components\Actions\Action::make('loadBraceletSizes')
                                    ->label('+ Quick Preset: Wrist Sizes (6.5, 7.0, 7.5 Inch)')
                                    ->color('gray')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function (Forms\Set $set) {
                                        $set('attributes.size_label', 'Wrist Size');
                                        $set('attributes.size_variants', [
                                            ['size' => '6.5 Inch', 'price' => null],
                                            ['size' => '7.0 Inch', 'price' => null],
                                            ['size' => '7.5 Inch', 'price' => null],
                                        ]);
                                    }),
                            ])
                            ->columnSpanFull(),

                            Forms\Components\Repeater::make('attributes.size_variants')
                                ->label('Available Sizes & Price')
                                ->schema([
                                    Forms\Components\TextInput::make('size')
                                        ->label('Size')
                                        ->placeholder('e.g. Size 12, 18 Inch, 6.5 Inch')
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Price (₹)')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->required()
                                        ->placeholder('e.g. 24999'),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->columnSpanFull(),

                            // 4. Diamond & Gemstone Specifications
                            Forms\Components\Fieldset::make('Diamond & Gemstone Specifications (Optional)')
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

                                    Forms\Components\TextInput::make('attributes.gold_weight')
                                        ->label('Approx Gold Weight')
                                        ->placeholder('e.g. 3.85 gms approx')
                                        ->helperText('Optional: Net gold weight.'),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ]),

                ])->columnSpan(2),

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
                ])->columnSpan(1),
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
                        $hasSale = $record->sale_price && $record->sale_price < $record->price;
                        if ($hasSale) {
                            $selling = '₹' . number_format($record->sale_price, 2);
                            $original = '₹' . number_format($record->price, 2);
                            return '<div class="flex flex-col leading-tight">' .
                                   '<span class="font-semibold">' . $selling . '</span>' .
                                   '<s style="text-decoration: line-through !important; text-decoration-thickness: 1.5px; color: #9ca3af; font-size: 11px; margin-top: 2px;">MRP: ' . $original . '</s>' .
                                   '</div>';
                        }

                        $variants = $record->attributes['size_variants'] ?? [];
                        $base = '₹' . number_format($record->price, 2);
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
        return parent::getEloquentQuery()->latest('id');
    }
}
