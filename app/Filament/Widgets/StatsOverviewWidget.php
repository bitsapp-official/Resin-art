<?php

namespace App\Filament\Widgets;

use App\Models\ContactInquiry;
use App\Models\CustomRequest;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();

        // 1. Order operational counts in a single fast aggregate query
        $orderCounts = Order::selectRaw("
            COUNT(*) as total_orders,
            COUNT(CASE WHEN created_at >= '{$todayStart}' THEN 1 END) as today_orders,
            COUNT(CASE WHEN status IN ('PROCESSING', 'CRAFTING', 'QUALITY_CHECK', 'PACKED') THEN 1 END) as processing_orders,
            COUNT(CASE WHEN status = 'SHIPPED' THEN 1 END) as shipped_orders,
            COUNT(CASE WHEN status = 'DELIVERED' THEN 1 END) as delivered_orders
        ")->first();

        $todayOrders = (int) ($orderCounts->today_orders ?? 0);
        $processingOrders = (int) ($orderCounts->processing_orders ?? 0);
        $shippedOrders = (int) ($orderCounts->shipped_orders ?? 0);
        $deliveredOrders = (int) ($orderCounts->delivered_orders ?? 0);

        // 2. Custom Bespoke Requests Stats
        $customStats = CustomRequest::selectRaw("
            COUNT(*) as total_custom,
            COUNT(CASE WHEN status IN ('SUBMITTED', 'UNDER_REVIEW', 'QUOTED') THEN 1 END) as pending_custom
        ")->first();

        $totalCustom = (int) ($customStats->total_custom ?? 0);
        $pendingCustom = (int) ($customStats->pending_custom ?? 0);

        // 3. Client Contact Inquiries Stats
        $contactStats = ContactInquiry::selectRaw("
            COUNT(*) as total_contact,
            COUNT(CASE WHEN status = 'new' THEN 1 END) as new_contact
        ")->first();

        $totalContact = (int) ($contactStats->total_contact ?? 0);
        $newContact = (int) ($contactStats->new_contact ?? 0);

        // 4. 7-day order volume trend
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();
        $recentOrders = Order::where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw("DATE(created_at) as order_date, COUNT(*) as count")
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date');

        $orderTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $row = $recentOrders->get($d);
            $orderTrend[] = (int) ($row ? $row->count : 0);
        }

        return [
            Stat::make("Today's Orders", number_format($todayOrders) . ' New Orders')
                ->description($todayOrders > 0 ? 'Received today · Ready to process' : 'No new orders received today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($orderTrend)
                ->color($todayOrders > 0 ? 'success' : 'gray'),

            Stat::make('Processing Orders', number_format($processingOrders) . ' Orders')
                ->description($processingOrders > 0 ? 'Orders being prepared & packed' : 'All orders processed')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($processingOrders > 0 ? 'warning' : 'gray'),

            Stat::make('Shipped / In Transit', number_format($shippedOrders) . ' Parcels')
                ->description($shippedOrders > 0 ? 'En route with courier to customers' : 'No parcels currently in transit')
                ->descriptionIcon('heroicon-m-truck')
                ->color($shippedOrders > 0 ? 'primary' : 'gray'),

            Stat::make('Delivered Orders', number_format($deliveredOrders) . ' Delivered')
                ->description($deliveredOrders > 0 ? 'Successfully delivered to customers' : 'No delivered orders yet')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($deliveredOrders > 0 ? 'success' : 'gray'),

            Stat::make('Custom Requests', number_format($totalCustom) . ' Inquiries')
                ->description($pendingCustom > 0 ? $pendingCustom . ' awaiting review & quotation' : 'All custom inquiries reviewed')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($pendingCustom > 0 ? 'warning' : 'success'),

            Stat::make('Client Inquiries', number_format($totalContact) . ' Messages')
                ->description($newContact > 0 ? $newContact . ' new awaiting response' : 'All client messages answered')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($newContact > 0 ? 'warning' : 'success'),
        ];
    }
}
