<?php

namespace App\Filament\Resources\AboutPageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('number')
                    ->label('Value Number / Index (e.g. 01)')
                    ->default('01')
                    ->required()
                    ->maxLength(10),

                Forms\Components\TextInput::make('title')
                    ->label('Value Title (e.g. Slow)')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Value Description')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

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
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('title')
                    ->weight('semibold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50),

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
