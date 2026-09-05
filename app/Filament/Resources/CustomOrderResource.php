<?php

namespace App\Filament\Resources;

use App\Enums\CustomOrderStatus;
use App\Filament\Resources\CustomOrderResource\Pages;
use App\Models\CustomOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomOrderResource extends Resource
{
    protected static ?string $model = CustomOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Orders & Sales';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Orders';
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Clean, simple order status options for admin UI
     */
    public static function getStatusOptions(): array
    {
        return [
            CustomOrderStatus::CONFIRMED->value     => 'Confirmed',
            CustomOrderStatus::IN_PRODUCTION->value => 'In Production',
            CustomOrderStatus::SHIPPED->value       => 'Shipped',
            CustomOrderStatus::DELIVERED->value     => 'Delivered',
            CustomOrderStatus::CANCELLED->value     => 'Cancelled',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM
    // ─────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Select::make('custom_request_id')
                            ->relationship('request', 'public_reference')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('custom_quote_id')
                            ->relationship('quote', 'quote_reference')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('order_reference')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Financials')
                    ->schema([
                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('₹')
                            ->disabled(),
                        Forms\Components\TextInput::make('remaining_amount')
                            ->numeric()
                            ->prefix('₹')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Production Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(static::getStatusOptions())
                            ->required(),
                    ])->columns(1),

                Forms\Components\Section::make('Shipping Details')
                    ->schema([
                        Forms\Components\TextInput::make('courier_name')
                            ->label('Courier Company')
                            ->placeholder('e.g. BlueDart, Delhivery, DTDC'),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking Number'),
                        Forms\Components\TextInput::make('tracking_url')
                            ->label('Tracking URL')
                            ->url()
                            ->placeholder('https://...'),
                        Forms\Components\DatePicker::make('shipping_date')
                            ->label('Shipped On'),
                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Delivered At'),
                    ])->columns(2),
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_reference')
                    ->label('Order Ref')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('request.public_reference')
                    ->label('Request')
                    ->searchable()
                    ->description(fn ($record) => $record->request?->name),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->money('inr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money('inr'),

                Tables\Columns\TextColumn::make('shipping_date')
                    ->label('Shipped')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(static::getStatusOptions()),
            ])
            ->actions([
                // ── Quick Status Update ──
                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(static::getStatusOptions())
                            ->required(),
                        Forms\Components\TextInput::make('courier_name')
                            ->label('Courier Company')
                            ->placeholder('BlueDart, Delhivery, etc.'),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking Number'),
                    ])
                    ->fillForm(fn ($record) => [
                        'status'          => $record->status->value ?? $record->status,
                        'courier_name'    => $record->courier_name,
                        'tracking_number' => $record->tracking_number,
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(array_filter([
                            'status'          => $data['status'],
                            'courier_name'    => $data['courier_name'] ?? null,
                            'tracking_number' => $data['tracking_number'] ?? null,
                            'shipping_date'   => in_array($data['status'], ['shipped', 'delivered']) && !$record->shipping_date
                                ? now()->toDateString()
                                : $record->shipping_date,
                            'delivered_at'    => $data['status'] === 'delivered' && !$record->delivered_at
                                ? now()
                                : $record->delivered_at,
                        ]));
                        Notification::make()
                            ->title('Order status updated')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('Details'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomOrders::route('/'),
            'edit'  => Pages\EditCustomOrder::route('/{record}/edit'),
        ];
    }
}
