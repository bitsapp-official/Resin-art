<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    /**
     * Top filter tabs for rapid order workflow management
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Orders')
                ->badge(Order::count()),

            'today' => Tab::make("Today's Orders")
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(Order::whereDate('created_at', today())->count())
                ->badgeColor('success'),

            'confirmed' => Tab::make('Confirmed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [Order::STATUS_CONFIRMED, 'pending', 'confirmed']))
                ->badge(Order::whereIn('status', [Order::STATUS_CONFIRMED, 'pending', 'confirmed'])->count())
                ->badgeColor('info'),

            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [Order::STATUS_PROCESSING, 'CRAFTING', 'QUALITY_CHECK', 'PACKED']))
                ->badge(Order::whereIn('status', [Order::STATUS_PROCESSING, 'CRAFTING', 'QUALITY_CHECK', 'PACKED'])->count())
                ->badgeColor('warning'),

            'shipped' => Tab::make('Shipped')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_SHIPPED))
                ->badge(Order::where('status', Order::STATUS_SHIPPED)->count())
                ->badgeColor('primary'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_DELIVERED))
                ->badge(Order::where('status', Order::STATUS_DELIVERED)->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Order::STATUS_CANCELLED))
                ->badge(Order::where('status', Order::STATUS_CANCELLED)->count())
                ->badgeColor('danger'),
        ];
    }
}
