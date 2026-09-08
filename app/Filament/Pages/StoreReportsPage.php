<?php

namespace App\Filament\Pages;

use App\Models\CustomRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\SimpleXlsxExporter;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
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
    public ?string $export_format = 'xlsx';

    public function mount(): void
    {
        $this->form->fill([
            'report_type'   => 'monthly_sales',
            'period'        => 'this_month',
            'export_format' => 'xlsx',
            'start_date'    => Carbon::now()->startOfMonth()->toDateString(),
            'end_date'      => Carbon::now()->endOfMonth()->toDateString(),
            'status_filter' => 'ALL',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Store Reports & Data Export Engine')
                    ->icon('heroicon-o-document-chart-bar')
                    ->description('Select your report specification, format (Excel/CSV), reporting period, and status filters to generate verified download sheets.')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                Select::make('report_type')
                                    ->label('Report Type')
                                    ->prefixIcon('heroicon-o-document-text')
                                    ->options([
                                        'monthly_sales'    => '1. Monthly Sales & Revenue Performance Report',
                                        'orders_gst'       => '2. Detailed Orders & GST Tax Summary Report',
                                        'custom_requests' => '3. Custom Orders & Atelier Inquiries Report',
                                        'inventory_stock'  => '4. Products & Stock Inventory Valuation Report',
                                    ])
                                    ->default('monthly_sales')
                                    ->required()
                                    ->reactive()
                                    ->helperText(fn ($get) => match ($get('report_type')) {
                                        'monthly_sales'    => 'Includes: Gross Revenue, Net Realized, Customer Contacts, and Itemized Sold Breakdown.',
                                        'orders_gst'       => 'Includes: 18% CGST/SGST Tax Breakdown, Taxable Subtotals, Shipping Fees, and Invoices.',
                                        'custom_requests' => 'Includes: Bespoke Commissions, Client Design Specs, Dimensions, and Quotation Status.',
                                        'inventory_stock'  => 'Includes: Live Catalog Stock Units, Low Stock Flags, Unit Pricing, and Asset Valuation.',
                                        default            => null,
                                    })
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),

                                Select::make('period')
                                    ->label('Reporting Period')
                                    ->prefixIcon('heroicon-o-calendar-days')
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
                                    })
                                    ->helperText('Configures date range based on standard accounting cycles.')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 6,
                                    ]),
                            ]),

                        Grid::make(12)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->default(Carbon::now()->startOfMonth()->toDateString())
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->default(Carbon::now()->endOfMonth()->toDateString())
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                Select::make('export_format')
                                    ->label('Download Format')
                                    ->prefixIcon('heroicon-o-table-cells')
                                    ->options([
                                        'xlsx' => 'Microsoft Excel (.xlsx)',
                                        'csv'  => 'Universal CSV (.csv)',
                                    ])
                                    ->default('xlsx')
                                    ->required()
                                    ->reactive()
                                    ->helperText(fn ($get) => $get('export_format') === 'xlsx'
                                        ? 'True formatted Excel spreadsheet (.xlsx)'
                                        : 'Standard comma-separated file with UTF-8 BOM')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),

                                Select::make('status_filter')
                                    ->label('Order Status Filter')
                                    ->prefixIcon('heroicon-o-funnel')
                                    ->options([
                                        'ALL'        => 'All Orders / Records',
                                        'CONFIRMED'  => 'Confirmed / Paid Only',
                                        'PROCESSING' => 'Processing in Atelier Only',
                                        'SHIPPED'    => 'In Transit (Shipped) Only',
                                        'DELIVERED'  => 'Delivered to Patron Only',
                                        'CANCELLED'  => 'Cancelled Only',
                                    ])
                                    ->default('ALL')
                                    ->visible(fn ($get) => in_array($get('report_type'), ['monthly_sales', 'orders_gst']))
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * Friendly display name for each report.
     */
    public function getReportName(string $type): string
    {
        return match ($type) {
            'monthly_sales'    => 'Monthly Sales & Revenue',
            'orders_gst'       => 'Orders & GST Breakdown',
            'custom_requests' => 'Custom Orders & Inquiries',
            'inventory_stock'  => 'Inventory & Stock Valuation',
            default            => 'Selected Report',
        };
    }

    /**
     * Real-time counts for store metrics.
     */
    public function getReportCounts(): array
    {
        return [
            'orders'    => Order::count(),
            'custom'    => CustomRequest::count(),
            'products'  => Product::count(),
            'low_stock' => Product::where('stock', '<', 5)->count(),
        ];
    }

    /**
     * Generate and stream either real Excel (.xlsx) or universal CSV file.
     */
    public function exportReport(): StreamedResponse
    {
        $data = $this->form->getState();
        $reportType = $data['report_type'] ?? 'monthly_sales';
        $exportFormat = $data['export_format'] ?? 'xlsx';
        $startDate = Carbon::parse($data['start_date'] ?? now()->startOfMonth())->startOfDay();
        $endDate = Carbon::parse($data['end_date'] ?? now()->endOfMonth())->endOfDay();
        $statusFilter = $data['status_filter'] ?? 'ALL';

        $headers = [];
        $rows = [];

        switch ($reportType) {
            case 'monthly_sales':
            case 'orders_gst':
                $headers = [
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
                ];

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

                    $rows[] = [
                        $order->order_reference,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $snap['full_name'] ?? ($order->user->name ?? 'Guest Patron'),
                        $order->email,
                        $snap['phone'] ?? ($order->user->phone ?? 'N/A'),
                        $snap['city'] ?? 'N/A',
                        $snap['state'] ?? 'N/A',
                        $snap['postal_code'] ?? 'N/A',
                        $order->items->sum('quantity'),
                        (float)$order->subtotal,
                        (float)$order->tax,
                        (float)$order->shipping_fee,
                        (float)$order->grand_total,
                        strtoupper($order->payment_method ?? 'ONLINE'),
                        strtoupper($order->payment_status ?? 'PAID'),
                        strtoupper($order->status),
                        $itemsSummary,
                    ];
                }
                break;

            case 'custom_requests':
                $headers = [
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
                ];

                $customRequests = CustomRequest::whereBetween('created_at', [$startDate, $endDate])
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($customRequests as $req) {
                    $rows[] = [
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
                        (int)($req->quantity ?? 1),
                        $req->preferred_style ?? 'Custom Flow',
                        $req->preferred_colors ?? 'N/A',
                        $req->timeline_type ?? 'Standard',
                        $req->required_date ? Carbon::parse($req->required_date)->format('Y-m-d') : 'Flexible',
                        $req->status instanceof \BackedEnum ? $req->status->value : (string)($req->status?->value ?? $req->status ?? 'NEW'),
                        $req->idea_description,
                    ];
                }
                break;

            case 'inventory_stock':
                $headers = [
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
                ];

                $products = Product::with('category')->orderBy('name')->get();

                foreach ($products as $p) {
                    $stockValuation = $p->stock * ($p->sale_price ?: $p->price);
                    $rows[] = [
                        $p->id,
                        $p->name,
                        $p->sku ?? 'N/A',
                        $p->category->name ?? 'Uncategorized',
                        $p->inventory_type,
                        (int)$p->stock,
                        (int)($p->low_stock_threshold ?? 2),
                        (float)$p->price,
                        $p->sale_price ? (float)$p->sale_price : 'N/A',
                        (float)$stockValuation,
                        strtoupper($p->status),
                        $p->is_featured ? 'YES' : 'NO',
                        $p->is_bestseller ? 'YES' : 'NO',
                    ];
                }
                break;
        }

        $dateSuffix = $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd');
        $baseFilename = 'maison_resine_' . $reportType . '_' . $dateSuffix;

        if ($exportFormat === 'xlsx') {
            $filename = $baseFilename . '.xlsx';
            $sheetTitle = $this->getReportName($reportType);
            $xlsxContent = SimpleXlsxExporter::create($headers, $rows, $sheetTitle);

            return new StreamedResponse(function () use ($xlsxContent) {
                echo $xlsxContent;
            }, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ]);
        }

        // CSV export
        $filename = $baseFilename . '.csv';
        return new StreamedResponse(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel CSV compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                $formattedRow = array_map(function ($cell) {
                    if (is_float($cell)) {
                        return number_format($cell, 2, '.', '');
                    }
                    return $cell;
                }, $row);
                fputcsv($handle, $formattedRow);
            }
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
