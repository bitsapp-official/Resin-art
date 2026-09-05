<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryCategoryResource\Pages;
use App\Models\GalleryCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GalleryCategoryResource extends Resource
{
    protected static ?string $model = GalleryCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Gallery Categories';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Details')
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
                            ->unique(GalleryCategory::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active / Visible')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->fontFamily('mono')
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gallery_items_count')
                    ->counts('galleryItems')
                    ->label('Items Count'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryCategories::route('/'),
            'create' => Pages\CreateGalleryCategory::route('/create'),
            'edit' => Pages\EditGalleryCategory::route('/{record}/edit'),
        ];
    }
}
