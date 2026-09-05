<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Shop & Catalog';

    protected static ?string $navigationLabel = 'Collections';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Collection Details')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Collection::class, 'slug', ignoreRecord: true),

                                Forms\Components\TextInput::make('subtitle')
                                    ->label('Subtitle / Tagline (e.g. WALNUT & FLOWING RESIN)')
                                    ->placeholder('e.g. WALNUT & FLOWING RESIN')
                                    ->maxLength(255),

                                Forms\Components\FileUpload::make('cover_image')
                                    ->label('Cover Image File Upload')
                                    ->disk('public')
                                    ->directory('collections')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                    ->maxSize(5120)
                                    ->columnSpanFull()
                                    ->helperText('Secure Cover Image Upload (Max size: 5MB | JPG, PNG, WebP, AVIF)'),

                                Forms\Components\Textarea::make('description')
                                    ->label('Collection Description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('Assigned Products (Many-to-Many)')
                            ->schema([
                                Forms\Components\Select::make('products')
                                    ->relationship('products', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->label('Select Products for this Collection'),
                            ]),

                        Forms\Components\Section::make('Search Engine Optimization (SEO)')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('SEO Title')
                                    ->placeholder('Default: Collection Name | Maison Résine')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('meta_description')
                                    ->label('SEO Meta Description')
                                    ->rows(3),
                            ])->collapsible(),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Publishing & Status')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'ACTIVE'   => 'Active (Publicly Visible)',
                                        'INACTIVE' => 'Inactive (Hidden)',
                                    ])
                                    ->default('ACTIVE')
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active Flag')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_featured_on_home')
                                    ->label('Featured on Homepage (Max 3)')
                                    ->helperText('Display this collection in the Homepage Featured Collections grid (Exactly 3 slots available)')
                                    ->rules([
                                        fn (\Filament\Forms\Get $get, ?\App\Models\Collection $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                            if ($value) {
                                                $query = \App\Models\Collection::where('is_featured_on_home', true);
                                                if ($record) {
                                                    $query->where('id', '!=', $record->id);
                                                }
                                                if ($query->count() >= 3) {
                                                    $fail('Maximum 3 collections can be featured on the homepage. Please uncheck another collection first.');
                                                }
                                            }
                                        },
                                    ])
                                    ->default(false),

                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->effective_cover_image),

                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->subtitle ?: null),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Items')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\ToggleColumn::make('is_featured_on_home')
                    ->label('Featured on Home (Max 3)')
                    ->updateStateUsing(function ($record, $state) {
                        if ($state) {
                            $featuredCount = \App\Models\Collection::where('is_featured_on_home', true)
                                ->where('id', '!=', $record->id)
                                ->count();

                            if ($featuredCount >= 3) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Limit Reached: Max 3 Allowed')
                                    ->body('Homepage has slots for exactly 3 featured collections (1 Large Hero + 2 Cards). Please turn OFF another collection before activating this one.')
                                    ->warning()
                                    ->send();

                                return $record->is_featured_on_home;
                            }
                        }

                        $record->update(['is_featured_on_home' => $state]);
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive',
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
