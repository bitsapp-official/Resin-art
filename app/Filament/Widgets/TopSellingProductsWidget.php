<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Resin Masterpieces';

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('status', 'published')
                    ->with('category')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Piece')
                    ->disk('public')
                    ->circular()
                    ->size(36)
                    ->getStateUsing(fn ($record) => is_array($record->images) ? ($record->images[0] ?? null) : $record->images),

                Tables\Columns\TextColumn::make('name')
                    ->label('Masterpiece')
                    ->weight('bold')
                    ->description(fn (Product $record): string => ($record->category->name ?? 'Handcrafted Art') . ($record->price ? ' · ₹' . number_format($record->price, 0) : ''))
                    ->wrap(),

                Tables\Columns\TextColumn::make('inventory_type')
                    ->label('Availability')
                    ->formatStateUsing(fn ($state): string => match (strtoupper((string) $state)) {
                        'MADE_TO_ORDER' => 'Made to Order',
                        'READY_TO_SHIP' => 'Ready to Ship',
                        default => 'Ready to Ship',
                    })
                    ->badge()
                    ->color(fn ($state): string => match (strtoupper((string) $state)) {
                        'MADE_TO_ORDER' => 'warning',
                        'READY_TO_SHIP' => 'success',
                        default => 'success',
                    }),
            ])
            ->paginated(false);
    }
}
