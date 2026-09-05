<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrderStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Order Status Distribution';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '275px';

    protected function getData(): array
    {
        // Group status counts from database
        $rawCounts = Order::selectRaw('UPPER(status) as status_code, COUNT(*) as count')
            ->groupBy('status_code')
            ->pluck('count', 'status_code')
            ->toArray();

        // Standard E-Commerce status mapping with colors
        $consolidated = [
            'CONFIRMED'  => ['label' => 'Confirmed', 'color' => 'rgba(16, 185, 129, 0.9)'],
            'PROCESSING' => ['label' => 'Processing', 'color' => 'rgba(245, 158, 11, 0.9)'],
            'SHIPPED'    => ['label' => 'Shipped', 'color' => 'rgba(14, 165, 233, 0.9)'],
            'DELIVERED'  => ['label' => 'Delivered', 'color' => 'rgba(173, 149, 117, 0.9)'],
            'CANCELLED'  => ['label' => 'Cancelled', 'color' => 'rgba(239, 68, 68, 0.9)'],
        ];

        // Combine legacy synonyms into PROCESSING
        foreach (['CRAFTING', 'QUALITY_CHECK', 'PACKED'] as $legacyStatus) {
            if (isset($rawCounts[$legacyStatus])) {
                $rawCounts['PROCESSING'] = ($rawCounts['PROCESSING'] ?? 0) + $rawCounts[$legacyStatus];
                unset($rawCounts[$legacyStatus]);
            }
        }
        if (isset($rawCounts['PENDING'])) {
            $rawCounts['CONFIRMED'] = ($rawCounts['CONFIRMED'] ?? 0) + $rawCounts['PENDING'];
            unset($rawCounts['PENDING']);
        }

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($consolidated as $code => $meta) {
            $count = (int) ($rawCounts[$code] ?? 0);
            if ($count > 0) {
                $labels[] = $meta['label'] . ' (' . $count . ')';
                $data[] = $count;
                $colors[] = $meta['color'];
            }
        }

        // Fallback if no orders exist yet
        if (empty($data)) {
            $labels = ['No Orders Yet'];
            $data = [1];
            $colors = ['rgba(107, 114, 128, 0.3)'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders Count',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#18181b',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'display' => false,
                    'grid' => ['display' => false],
                    'ticks' => ['display' => false],
                ],
                'y' => [
                    'display' => false,
                    'grid' => ['display' => false],
                    'ticks' => ['display' => false],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 12,
                        'padding' => 12,
                        'color' => '#9ca3af',
                    ],
                ],
            ],
            'cutout' => '68%',
            'maintainAspectRatio' => false,
        ];
    }
}
