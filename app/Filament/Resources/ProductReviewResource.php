<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductReviewResource\Pages;
use App\Filament\Resources\ProductReviewResource\RelationManagers;
use App\Models\ProductReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Shop & Catalog';

    protected static ?string $navigationLabel = 'Customer Reviews';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\TextInput::make('reviewer_name')
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\TextInput::make('title'),
                Forms\Components\Textarea::make('comment')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_featured_on_home')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reviewer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\ToggleColumn::make('is_featured_on_home'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
