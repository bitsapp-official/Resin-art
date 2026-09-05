<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MonthlyRevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Monthly Orders & Workshop Fulfillment';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $twelveMonthsAgo = Carbon::now()->subMonths(11)->startOfMonth();

        // Single query for monthly order intake and fulfillment
        $monthlyRecords = Order::where('created_at', '>=', $twelveMonthsAgo)
            ->selectRaw("
                strftime('%Y-%m', created_at) as month_key,
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status IN ('SHIPPED', 'DELIVERED', 'completed') THEN 1 END) as fulfilled_orders
            ")
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');

        $months = [];
        $ordersData = [];
        $fulfilledData = [];

        for ($i = 11; $i >= 0; $i--) {
            $carbonMonth = Carbon::now()->subMonths($i);
            $key = $carbonMonth->format('Y-m');
            $label = $carbonMonth->format('M Y');

            $row = $monthlyRecords->get($key);

            $months[] = $label;
            $ordersData[] = (int) ($row ? $row->total_orders : 0);
            $fulfilledData[] = (int) ($row ? $row->fulfilled_orders : 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Orders Received',
                    'data' => $ordersData,
                    'borderColor' => '#AD9575',
                    'backgroundColor' => 'rgba(173, 149, 117, 0.75)',
                    'type' => 'bar',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Fulfilled / Dispatched',
                    'data' => $fulfilledData,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Orders',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
}
