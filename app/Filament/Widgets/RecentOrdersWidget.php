<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Online Orders Feed';

    protected static ?int $sort = 5;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_reference')
                    ->label('Order #')
                    ->weight('bold')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('shipping_address_snapshot.full_name')
                    ->label('Patron Name')
                    ->description(fn (Order $record): string => $record->email ?? ''),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Masterpieces')
                    ->getStateUsing(fn (Order $record): string => ($record->items()->count() ?: 1) . ' Piece(s)')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match (strtoupper((string) $state)) {
                        'CONFIRMED', 'PAID' => 'Confirmed',
                        'PROCESSING', 'CRAFTING', 'QUALITY_CHECK', 'PACKED' => 'Processing',
                        'SHIPPED' => 'Shipped',
                        'DELIVERED' => 'Delivered',
                        'CANCELLED' => 'Cancelled',
                        default => ucfirst(strtolower((string) $state)),
                    })
                    ->color(fn ($state): string => match (strtoupper((string) $state)) {
                        'CONFIRMED', 'PAID' => 'info',
                        'PROCESSING', 'CRAFTING', 'QUALITY_CHECK', 'PACKED' => 'warning',
                        'SHIPPED' => 'primary',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed At')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('View / Process')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->size('xs')
                    ->color('primary')
                    ->url(fn (Order $record): string => '/admin/orders/' . $record->id . '/edit'),
            ])
            ->paginated(false);
    }
}
