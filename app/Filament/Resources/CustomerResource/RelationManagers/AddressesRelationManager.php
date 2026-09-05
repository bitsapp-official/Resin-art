<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Saved Delivery Addresses';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('address_line_1')
                    ->label('Street / Building')
                    ->limit(35),

                Tables\Columns\TextColumn::make('city')
                    ->label('City'),

                Tables\Columns\TextColumn::make('state')
                    ->label('State'),

                Tables\Columns\TextColumn::make('postal_code')
                    ->label('PIN Code'),

                Tables\Columns\TextColumn::make('country')
                    ->label('Country'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueColor('success'),
            ])
            ->filters([])
            ->headerActions([])   // Admin cannot add addresses for customers
            ->actions([])         // Admin cannot edit/delete addresses
            ->bulkActions([]);    // No bulk actions
    }
}
