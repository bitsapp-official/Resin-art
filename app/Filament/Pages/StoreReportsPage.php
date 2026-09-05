<?php

namespace App\Filament\Pages;

use App\Models\CustomRequest;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoreReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Orders & Sales';

    protected static ?string $navigationLabel = 'Store Reports & Exports';

    protected static ?string $title = 'Store Reports & Excel/CSV Export Engine';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.store-reports-page';

    protected static ?string $slug = 'store-reports';

    public ?string $report_type = 'monthly_sales';
    public ?string $period = 'this_month';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?string $status_filter = 'ALL';

    public function mount(): void
    {
        $this->form->fill([
            'report_type'   => 'monthly_sales',
            'period'        => 'this_month',
            'start_date'    => Carbon::now()->startOfMonth()->toDateString(),
            'end_date'      => Carbon::now()->endOfMonth()->toDateString(),
            'status_filter' => 'ALL',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Configuration & Date Range')
                    ->description('Select the type of report, date range, and status filter to generate a detailed Excel-compatible CSV report.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('report_type')
                                    ->label('Report Type')
                                    ->options([
                                        'monthly_sales'      => '1. Monthly Sales & Revenue Performance Report',
                                        'orders_gst'         => '2. Detailed Orders & GST Tax Summary Report',
                                        'custom_requests'   => '3. Custom Orders & Inquiries Report',
                                        'inventory_stock'    => '4. Products & Stock Inventory Valuation Report',
                                    ])
                                    ->default('monthly_sales')
                                    ->required()
                                    ->reactive(),

                                Select::make('period')
                                    ->label('Reporting Period')
                                    ->options([
                                        'this_month' => 'This Month (' . Carbon::now()->format('F Y') . ')',
                                        'last_month' => 'Last Month (' . Carbon::now()->subMonth()->format('F Y') . ')',
                                        'this_year'  => 'Year to Date (' . Carbon::now()->format('Y') . ')',
                                        'all_time'   => 'All Time Records',
                                        'custom'     => 'Custom Date Range (Specify Below)',
                                    ])
                                    ->default('this_month')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state === 'this_month') {
                                            $set('start_date', Carbon::now()->startOfMonth()->toDateString());
                                            $set('end_date', Carbon::now()->endOfMonth()->toDateString());
                                        } elseif ($state === 'last_month') {
                                            $set('start_date', Carbon::now()->subMonth()->startOfMonth()->toDateString());
                                            $set('end_date', Carbon::now()->subMonth()->endOfMonth()->toDateString());
                                        } elseif ($state === 'this_year') {
                                            $set('start_date', Carbon::now()->startOfYear()->toDateString());
                                            $set('end_date', Carbon::now()->endOfYear()->toDateString());
                                        } elseif ($state === 'all_time') {
                                            $set('start_date', '2020-01-01');
                                            $set('end_date', Carbon::now()->toDateString());
                                        }
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->default(Carbon::now()->startOfMonth()->toDateString())
                                    ->required(),

                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->default(Carbon::now()->endOfMonth()->toDateString())
                                    ->required(),

                                Select::make('status_filter')
                                    ->label('Order Status Filter')
                                    ->options([
                                        'ALL'        => 'All Orders / Records',
                                        'CONFIRMED'  => 'Confirmed / Paid Only',
                                        'PROCESSING' => 'Processing in Atelier Only',
                                        'SHIPPED'    => 'In Transit (Shipped) Only',
                                        'DELIVERED'  => 'Delivered to Patron Only',
                                        'CANCELLED'  => 'Cancelled Only',
                                    ])
                                    ->default('ALL')
                                    ->visible(fn ($get) => in_array($get('report_type'), ['monthly_sales', 'orders_gst'])),
                            ]),
                    ]),
            ]);
    }

    /**
     * Generate and stream the Excel/CSV report file.
     */
    public function exportReport(): StreamedResponse
    {
        $data = $this->form->getState();
        $reportType = $data['report_type'] ?? 'monthly_sales';
        $startDate = Carbon::parse($data['start_date'] ?? now()->startOfMonth())->startOfDay();
        $endDate = Carbon::parse($data['end_date'] ?? now()->endOfMonth())->endOfDay();
        $statusFilter = $data['status_filter'] ?? 'ALL';

        $filename = 'maison_resine_' . $reportType . '_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($reportType, $startDate, $endDate, $statusFilter) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            switch ($reportType) {
                case 'monthly_sales':
                case 'orders_gst':
                    // CSV Columns
                    fputcsv($handle, [
                        'Order Reference',
                        'Order Date & Time',
                        'Patron Name',
                        'Email Address',
                        'Phone Number',
                        'Shipping City',
                        'Shipping State',
                        'Postal Code',
                        'Total Items Ordered',
                        'Subtotal (INR)',
                        'GST Tax (INR)',
                        'Shipping Fee (INR)',
                        'Grand Total (INR)',
                        'Payment Method',
                        'Payment Status',
                        'Fulfillment Status',
                        'Items Breakdown (SKU x Qty)',
                    ]);

                    $query = Order::with(['items.product'])
                        ->whereBetween('created_at', [$startDate, $endDate]);

                    if ($statusFilter !== 'ALL') {
                        $query->where('status', $statusFilter);
                    }

                    $orders = $query->orderBy('created_at', 'desc')->get();

                    foreach ($orders as $order) {
                        $snap = $order->shipping_address_snapshot ?? [];
                        $itemsSummary = $order->items->map(function ($item) {
                            return ($item->product_name ?? 'Artwork') . ' (' . ($item->product_sku ?? 'N/A') . ') x' . $item->quantity . ' @ ₹' . number_format($item->unit_price, 2);
                        })->implode(' | ');

                        fputcsv($handle, [
                            $order->order_reference,
                            $order->created_at->format('Y-m-d H:i:s'),
                            $snap['full_name'] ?? ($order->user->name ?? 'Guest Patron'),
                            $order->email,
                            $snap['phone'] ?? ($order->user->phone ?? 'N/A'),
                            $snap['city'] ?? 'N/A',
                            $snap['state'] ?? 'N/A',
                            $snap['postal_code'] ?? 'N/A',
                            $order->items->sum('quantity'),
                            number_format($order->subtotal, 2, '.', ''),
                            number_format($order->tax, 2, '.', ''),
                            number_format($order->shipping_fee, 2, '.', ''),
                            number_format($order->grand_total, 2, '.', ''),
                            strtoupper($order->payment_method ?? 'ONLINE'),
                            strtoupper($order->payment_status ?? 'PAID'),
                            strtoupper($order->status),
                            $itemsSummary,
                        ]);
                    }
                    break;

                case 'custom_requests':
                    fputcsv($handle, [
                        'Reference #',
                        'Submission Date',
                        'Client Name',
                        'Email Address',
                        'Phone / WhatsApp',
                        'Project Type',
                        'Width',
                        'Height',
                        'Depth',
                        'Unit',
                        'Quantity',
                        'Preferred Resin Style',
                        'Preferred Color Palette',
                        'Timeline Type',
                        'Target Delivery Date',
                        'Inquiry Status',
                        'Client Concept Description',
                    ]);

                    $customRequests = CustomRequest::whereBetween('created_at', [$startDate, $endDate])
                        ->orderBy('created_at', 'desc')
                        ->get();

                    foreach ($customRequests as $req) {
                        fputcsv($handle, [
                            $req->public_reference,
                            $req->created_at->format('Y-m-d H:i:s'),
                            $req->name,
                            $req->email,
                            $req->phone ?? ($req->whatsapp ?? 'N/A'),
                            $req->project_type,
                            $req->width ?? 'N/A',
                            $req->height ?? 'N/A',
                            $req->depth ?? 'N/A',
                            $req->unit ?? 'cm',
                            $req->quantity ?? 1,
                            $req->preferred_style ?? 'Custom Flow',
                            $req->preferred_colors ?? 'N/A',
                            $req->timeline_type ?? 'Standard',
                            $req->required_date ? Carbon::parse($req->required_date)->format('Y-m-d') : 'Flexible',
                            $req->status,
                            $req->idea_description,
                        ]);
                    }
                    break;

                case 'inventory_stock':
                    fputcsv($handle, [
                        'Product ID',
                        'Artwork Name',
                        'SKU',
                        'Category',
                        'Inventory Type',
                        'Stock Available',
                        'Low Stock Threshold',
                        'Base Price (INR)',
                        'Sale Price (INR)',
                        'Stock Valuation (INR)',
                        'Status',
                        'Featured',
                        'Bestseller',
                    ]);

                    $products = Product::with('category')->orderBy('name')->get();

                    foreach ($products as $p) {
                        $stockValuation = $p->stock * ($p->sale_price ?: $p->price);
                        fputcsv($handle, [
                            $p->id,
                            $p->name,
                            $p->sku ?? 'N/A',
                            $p->category->name ?? 'Uncategorized',
                            $p->inventory_type,
                            $p->stock,
                            $p->low_stock_threshold ?? 2,
                            number_format($p->price, 2, '.', ''),
                            $p->sale_price ? number_format($p->sale_price, 2, '.', '') : 'N/A',
                            number_format($stockValuation, 2, '.', ''),
                            strtoupper($p->status),
                            $p->is_featured ? 'YES' : 'NO',
                            $p->is_bestseller ? 'YES' : 'NO',
                        ]);
                    }
                    break;
            }

            fclose($handle);
        }, 200, $headers);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Download Excel / CSV Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action('exportReport'),
        ];
    }
}
