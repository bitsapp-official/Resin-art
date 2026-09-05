<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusUpdatedMail;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders & Sales';

    protected static ?string $navigationLabel = 'Online Orders';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Overview')
                    ->schema([
                        Forms\Components\TextInput::make('order_reference')
                            ->label('Order Reference')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->label('Customer Email')
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at_display')
                            ->label('Order Placed At (Time)')
                            ->formatStateUsing(fn ($record) => $record?->created_at ? $record->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') . ' (' . $record->created_at->timezone('Asia/Kolkata')->diffForHumans() . ')' : '—')
                            ->disabled()
                            ->helperText('Exact timestamp when patron placed this order'),
                        Forms\Components\Select::make('status')
                            ->label('Order Status')
                            ->options(Order::STATUS_LABELS)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if (in_array($state, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]) && !$get('shipped_at')) {
                                    $set('shipped_at', now());
                                }
                            }),
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'unpaid'   => 'Unpaid',
                                'paid'     => 'Paid',
                                'refunded' => 'Refunded',
                                'failed'   => 'Failed',
                            ])
                            ->disabled()
                            ->helperText('Automated by Stripe Payment Gateway. Read-only for audit security.')
                            ->required(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Transaction Ref')
                            ->disabled(),
                        Forms\Components\TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->prefix('₹')
                            ->disabled(),
                        Forms\Components\DatePicker::make('estimated_delivery_at')
                            ->label('Estimated Delivery Date')
                            ->placeholder('e.g. Expected Delivery Date'),
                    ])->columns(3),

                Forms\Components\Section::make('Shipping & Dispatch Logistics')
                    ->description('Logistics and dispatch tracking details (Visible when status is Dispatched or Delivered)')
                    ->visible(fn (Forms\Get $get): bool => in_array($get('status'), [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]))
                    ->schema([
                        Forms\Components\TextInput::make('courier')
                            ->label('Courier / Logistics Partner')
                            ->placeholder('e.g. Blue Dart / FedEx / Delhivery / DHL'),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('AWB / Tracking Number')
                            ->placeholder('Tracking Reference ID'),
                        Forms\Components\TextInput::make('tracking_url')
                            ->label('Public Tracking URL')
                            ->placeholder('https://courier.com/track/...'),
                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->label('Dispatched Timestamp *')
                            ->default(now())
                            ->required(fn (Forms\Get $get): bool => in_array($get('status'), [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]))
                            ->helperText('Exact date & time when the parcel was handed over to the courier partner.'),
                    ])->columns(2),

                Forms\Components\Section::make('Ordered Products & Items')
                    ->description('Products, quantities, and pricing breakdown for this order.')
                    ->schema([
                        Forms\Components\Placeholder::make('items_preview')
                            ->hiddenLabel()
                            ->content(function ($record) {
                                if (!$record || $record->items->isEmpty()) {
                                    return 'No artwork items found in this order.';
                                }
                                return view('filament.orders.items-preview', ['order' => $record]);
                            }),
                    ])->columnSpanFull(),

                Forms\Components\Section::make('Customer Address Snapshots')
                    ->schema([
                        Forms\Components\KeyValue::make('shipping_address_snapshot')
                            ->label('Shipping Destination Crate')
                            ->disabled(),
                        Forms\Components\KeyValue::make('billing_address_snapshot')
                            ->label('Billing Address')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_reference')
                    ->label('Reference')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Collector')
                    ->searchable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Pieces')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Order::STATUS_LABELS[strtoupper($state)] ?? (in_array(strtoupper($state), ['CRAFTING', 'QUALITY_CHECK', 'PACKED']) ? 'Processing' : $state))
                    ->color(fn (string $state): string => match (strtoupper($state)) {
                        Order::STATUS_CONFIRMED                         => 'info',
                        Order::STATUS_PROCESSING, 'CRAFTING', 'QUALITY_CHECK', 'PACKED' => 'warning',
                        Order::STATUS_SHIPPED                           => 'primary',
                        Order::STATUS_DELIVERED                         => 'success',
                        Order::STATUS_CANCELLED                         => 'danger',
                        default                                         => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'     => 'success',
                        'unpaid'   => 'warning',
                        'refunded' => 'info',
                        'failed'   => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('courier')
                    ->label('Courier')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking')
                    ->fontFamily('mono')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Placed At')
                    ->dateTime('d M Y, h:i A')
                    ->description(fn (Order $record): string => $record->created_at ? $record->created_at->timezone('Asia/Kolkata')->diffForHumans() : '')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Order::STATUS_LABELS),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'paid'     => 'Paid',
                        'unpaid'   => 'Unpaid',
                        'refunded' => 'Refunded',
                        'failed'   => 'Failed',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // ── Direct Tax Invoice PDF Download ───────────────────────────
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Order $record): string => route('admin.orders.pdf', $record))
                    ->openUrlInNewTab(),

                // ── Edit Order Details ─────────────────────────────────────────
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-m-pencil-square'),

                // ── Sequential E-Commerce Status Actions ─────────────────────────
                Tables\Actions\Action::make('mark_processing')
                    ->label('Processing')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->visible(fn (Order $record): bool => in_array($record->status, [Order::STATUS_CONFIRMED, 'pending', 'confirmed']))
                    ->requiresConfirmation()
                    ->modalHeading('Move to Processing?')
                    ->modalDescription('This marks the order as Processing and notifies the customer that packaging & preparation has started.')
                    ->action(function (Order $record): void {
                        $record->update(['status' => Order::STATUS_PROCESSING]);
                        try { Mail::to($record->email)->send(new OrderStatusUpdatedMail($record)); } catch (\Exception $e) {}
                        Notification::make()->title('Status updated: Processing')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_shipped')
                    ->label('Ship Order')
                    ->icon('heroicon-m-truck')
                    ->color('primary')
                    ->visible(fn (Order $record): bool => in_array($record->status, [Order::STATUS_PROCESSING, Order::STATUS_CONFIRMED, 'CRAFTING', 'QUALITY_CHECK', 'PACKED']))
                    ->form([
                        Forms\Components\TextInput::make('courier')
                            ->label('Courier Name')
                            ->placeholder('e.g. Blue Dart, FedEx, Delhivery')
                            ->required(),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking / AWB Number')
                            ->required(),
                        Forms\Components\TextInput::make('tracking_url')
                            ->label('Public Tracking URL')
                            ->placeholder('https://...'),
                        Forms\Components\DatePicker::make('estimated_delivery_at')
                            ->label('Estimated Delivery Date')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'status'                => Order::STATUS_SHIPPED,
                            'courier'               => $data['courier'],
                            'tracking_number'       => $data['tracking_number'],
                            'tracking_url'          => $data['tracking_url'] ?? null,
                            'shipped_at'            => now(),
                            'estimated_delivery_at' => $data['estimated_delivery_at'],
                        ]);
                        try { Mail::to($record->email)->send(new OrderStatusUpdatedMail($record)); } catch (\Exception $e) {}
                        Notification::make()->title('Status updated: Shipped')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_delivered')
                    ->label('Delivered')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === Order::STATUS_SHIPPED)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Delivery?')
                    ->modalDescription('Mark this order as successfully delivered to the customer.')
                    ->action(function (Order $record): void {
                        $record->update(['status' => Order::STATUS_DELIVERED]);
                        try { Mail::to($record->email)->send(new OrderStatusUpdatedMail($record)); } catch (\Exception $e) {}
                        Notification::make()->title('Status updated: Delivered')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
