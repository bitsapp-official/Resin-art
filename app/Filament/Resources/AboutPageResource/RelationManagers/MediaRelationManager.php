<?php

namespace App\Filament\Resources\AboutPageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Media Placement Type')
                    ->options([
                        \App\Models\AboutMedia::TYPE_ATELIER => 'Atelier Main Image',
                        \App\Models\AboutMedia::TYPE_CLIENT_HOME => 'Client Residence Placement',
                        \App\Models\AboutMedia::TYPE_ARTIST_WORKSHOP => 'Artist / Workshop Feature',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Media Image')
                    ->disk('public')
                    ->directory('about')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('alt_text')
                    ->label('Alt Text (Accessibility / SEO)')
                    ->maxLength(255),

                Forms\Components\TextInput::make('caption')
                    ->label('Caption Text')
                    ->maxLength(255),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active / Visible')
                    ->default(true),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->disk('public')
                    ->square()
                    ->label('Preview'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        \App\Models\AboutMedia::TYPE_ATELIER => 'primary',
                        \App\Models\AboutMedia::TYPE_CLIENT_HOME => 'success',
                        \App\Models\AboutMedia::TYPE_ARTIST_WORKSHOP => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('caption')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
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
}
