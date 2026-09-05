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
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                    $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                ),

                            Forms\Components\TextInput::make('slug')
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
                                ->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Section::make('Pricing & Inventory')
                        ->schema([
                            Forms\Components\TextInput::make('price')
                                ->numeric()
                                ->prefix('₹')
                                ->required(),

                            Forms\Components\TextInput::make('sale_price')
                                ->numeric()
                                ->prefix('₹')
                                ->nullable(),

                            Forms\Components\Select::make('inventory_type')
                                ->options([
                                    'READY_TO_SHIP' => 'Ready to Ship (Stock Tracked)',
                                    'MADE_TO_ORDER' => 'Made to Order (Crafted on Demand)',
                                ])
                                ->default('READY_TO_SHIP')
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

                    Forms\Components\Section::make('Available Size Options & Prices (Optional)')
                        ->description('Configure size variants with custom pricing if this product is offered in multiple sizes.')
                        ->schema([
                            Forms\Components\Repeater::make('attributes.size_variants')
                                ->label('Size Variants')
                                ->schema([
                                    Forms\Components\TextInput::make('size')
                                        ->label('Size / Variant Name (e.g. S - 40 cm)')
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Price (₹)')
                                        ->numeric()
                                        ->prefix('₹')
                                        ->required(),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->collapsed(),

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

                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured Piece')
                                ->default(false),

                            Forms\Components\Toggle::make('is_new')
                                ->label('New Arrival')
                                ->default(false),

                            Forms\Components\Toggle::make('is_bestseller')
                                ->label('Bestseller')
                                ->default(false),
                        ]),

                    Forms\Components\Section::make('Product Gallery')
                        ->schema([
                            Forms\Components\FileUpload::make('images')
                                ->multiple()
                                ->image()
                                ->disk('public')
                                ->directory('products')
                                ->reorderable()
                                ->openable()
                                ->downloadable()
                                ->label('Product Images'),
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
                    ->disk('public')
                    ->circular()
                    ->getStateUsing(fn ($record) => is_array($record->images) ? ($record->images[0] ?? null) : $record->images),

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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
