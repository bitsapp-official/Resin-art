<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Customers & Support';
    protected static ?string $navigationLabel = 'Customer Accounts';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Customer';

    /**
     * Only show non-admin users (customers only).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', false);
    }

    /**
     * No create/edit form — admin only views customer details.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    /**
     * Main customer info panel (Infolist = read-only structured view).
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Customer Profile')
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\TextEntry::make('name')
                                ->label('Full Name')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('email')
                                ->label('Email Address')
                                ->copyable(),
                            Infolists\Components\TextEntry::make('phone')
                                ->label('Phone Number')
                                ->placeholder('Not provided'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Member Since')
                                ->dateTime('d M Y'),
                            Infolists\Components\TextEntry::make('email_verified_at')
                                ->label('Email Verified')
                                ->dateTime('d M Y')
                                ->placeholder('Not verified'),
                            Infolists\Components\TextEntry::make('orders_count')
                                ->label('Total Orders')
                                ->state(fn (User $record) => $record->orders()->count()),
                        ]),
                    ]),

                Infolists\Components\Section::make('Account Status')
                    ->schema([
                        Infolists\Components\Grid::make(2)->schema([
                            Infolists\Components\IconEntry::make('is_blocked')
                                ->label('Blocked')
                                ->boolean()
                                ->trueIcon('heroicon-o-no-symbol')
                                ->falseIcon('heroicon-o-check-circle')
                                ->trueColor('danger')
                                ->falseColor('success'),
                            Infolists\Components\TextEntry::make('blocked_reason')
                                ->label('Block Reason')
                                ->placeholder('—')
                                ->visible(fn (User $record) => $record->is_blocked),
                        ]),
                    ]),

                Infolists\Components\Section::make('Saved Delivery Addresses')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('addresses')
                            ->label('')
                            ->grid(2)
                            ->schema([
                                Infolists\Components\Grid::make(1)->schema([
                                    Infolists\Components\TextEntry::make('full_name')
                                        ->label('Recipient & Contact')
                                        ->weight('bold')
                                        ->icon('heroicon-o-user')
                                        ->formatStateUsing(fn ($record) => ($record->full_name ?: 'Customer') . ($record->phone ? ' · ' . $record->phone : '')),

                                    Infolists\Components\TextEntry::make('is_default')
                                        ->label('Priority')
                                        ->badge()
                                        ->color(fn ($state) => $state ? 'success' : 'gray')
                                        ->formatStateUsing(fn ($state) => $state ? 'Default Address' : 'Additional Address'),

                                    Infolists\Components\TextEntry::make('address_line_1')
                                        ->label('Postal Address')
                                        ->icon('heroicon-o-map-pin')
                                        ->state(function ($record) {
                                            $parts = array_filter([
                                                $record->address_line_1,
                                                $record->address_line_2,
                                                $record->city,
                                                $record->state ? ($record->state . ($record->postal_code ? ' - ' . $record->postal_code : '')) : $record->postal_code,
                                                $record->country,
                                            ]);
                                            return implode(', ', $parts) ?: 'No address details provided';
                                        }),
                                ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * Customers list table with search, sort, and Block/Unblock actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Orders')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_blocked')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-no-symbol')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_blocked')
                    ->label('Account Status')
                    ->trueLabel('Blocked')
                    ->falseLabel('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view'  => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
