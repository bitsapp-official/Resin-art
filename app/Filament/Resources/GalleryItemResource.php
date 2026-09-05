<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryItemResource\Pages;
use App\Models\GalleryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';

    protected static ?string $navigationGroup = 'Atelier & Content Pages';

    protected static ?string $navigationLabel = 'Gallery Items';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Item Details')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('gallery_category_id')
                                    ->label('Category')
                                    ->relationship('galleryCategory', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('location')
                                    ->label('Location (Optional)')
                                    ->placeholder('e.g. Bordeaux, France')
                                    ->maxLength(255),
                            ])->columns(3),

                        Forms\Components\Section::make('Media Asset & Alt Text')
                            ->schema([
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Gallery Photography Image')
                                    ->disk('public')
                                    ->directory('gallery')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                    ->maxSize(5120)
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('image_alt')
                                    ->label('Image Accessibility Alt Text')
                                    ->required()
                                    ->default(fn ($get) => $get('title') ?? 'Resin art piece photography')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Publishing & Visibility')
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Item')
                                    ->default(false),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active / Published')
                                    ->default(true),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('galleryCategory.name')
                    ->label('Category')
                    ->badge()
                    ->color('amber')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('gallery_category_id')
                    ->label('Category')
                    ->relationship('galleryCategory', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),

                    Tables\Actions\BulkAction::make('mark_featured')
                        ->label('Mark as Featured')
                        ->icon('heroicon-o-star')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true])),

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
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
