<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\CustomRequest;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Orders & Sales';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Invoices & Receipts';
    protected static ?string $modelLabel = 'Invoice / Receipt';
    protected static ?string $pluralModelLabel = 'Invoices & Receipts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Details & Link')
                    ->schema([
                        Forms\Components\Select::make('custom_request_id')
                            ->label('Link to Website Custom Request (Optional)')
                            ->options(
                                CustomRequest::latest()->get()->mapWithKeys(function ($cr) {
                                    return [$cr->id => "{$cr->public_reference} — {$cr->name} (" . ($cr->idea_description ? \Illuminate\Support\Str::limit($cr->idea_description, 30) : 'Custom Item') . ")"];
                                })
                            )
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state && $cr = CustomRequest::with(['user.addresses'])->find($state)) {
                                    $set('client_name', $cr->name);
                                    $set('client_email', $cr->email);
                                    $set('client_phone', $cr->whatsapp ?: '');
                                    
                                    // Smart Address Resolution (Never put phone number into address)
                                    $resolvedAddress = '';
                                    if (!empty($cr->phone) && preg_match('/[a-zA-Z]/', $cr->phone)) {
                                        $resolvedAddress = $cr->phone;
                                    } elseif ($cr->user && $userAddr = ($cr->user->addresses->where('is_default', true)->first() ?? $cr->user->addresses->first())) {
                                        $resolvedAddress = trim("{$userAddr->address_line_1}, {$userAddr->city}, {$userAddr->state} {$userAddr->postal_code}, {$userAddr->country}", ', ');
                                    } elseif (!empty($cr->phone) && $cr->phone !== $cr->whatsapp) {
                                        $resolvedAddress = $cr->phone;
                                    }
                                    
                                    $set('client_address', $resolvedAddress);
                                    if ($cr->idea_description) {
                                        $set('item_description', $cr->idea_description);
                                    }
                                }
                            })
                            ->placeholder('Select a custom request to auto-fill client data...'),

                        Forms\Components\TextInput::make('client_name')
                            ->label('Client / Collector Name')
                            ->required()
                            ->placeholder('e.g. Rajesh Kumar'),

                        Forms\Components\TextInput::make('client_email')
                            ->label('Client Email')
                            ->email()
                            ->placeholder('e.g. rajesh@example.com'),

                        Forms\Components\TextInput::make('client_phone')
                            ->label('Client Phone / WhatsApp')
                            ->placeholder('e.g. +91 98765 43210'),

                        Forms\Components\TextInput::make('client_address')
                            ->label('Destination / Shipping Address')
                            ->placeholder('e.g. Mumbai, Maharashtra, India'),
                    ])->columns(2),

                Forms\Components\Section::make('Item & Pricing Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Invoice / Receipt Number')
                            ->default(fn () => Invoice::generateNumber())
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Repeater::make('items')
                            ->label('Artwork / Product Items')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Artwork / Product Name')
                                    ->required()
                                    ->placeholder('e.g. Bespoke Resin River Table')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('description')
                                    ->label('Specifications & Details')
                                    ->placeholder('e.g. 8-seater Teakwood with Emerald Green Resin Pour'),
                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::recalculateTotals($get, $set)),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->required()
                                    ->placeholder('e.g. 150000')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::recalculateTotals($get, $set)),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->collapsible()
                            ->cloneable()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) => static::recalculateTotals($get, $set)),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('currency_symbol')
                                    ->label('Currency')
                                    ->options([
                                        '₹' => '₹ (INR)',
                                        '$' => '$ (USD)',
                                        '€' => '€ (EUR)',
                                        '£' => '£ (GBP)',
                                    ])
                                    ->default('₹')
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Payment Status & Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'fully_paid' => 'Paid (100%)',
                                'unpaid'     => 'Unpaid / Pending',
                            ])
                            ->default('fully_paid')
                            ->required(),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method / Mode')
                            ->options([
                                'Direct Consultation / Bank Transfer' => 'Bank Transfer / NEFT',
                                'UPI Payment'                         => 'UPI Payment',
                                'PayPal / Wise'                       => 'PayPal / Wise',
                                'Credit / Debit Card'                 => 'Credit / Debit Card',
                                'Cash Payment'                        => 'Cash / Direct',
                            ])
                            ->default('Direct Consultation / Bank Transfer')
                            ->required(),

                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Transaction ID / Reference (Optional)')
                            ->placeholder('e.g. UTR987654321 or UPI/12345678')
                            ->hint('If provided, it will be displayed under Payment Verification on the PDF.'),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Invoice / Receipt Date')
                            ->default(now())
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function recalculateTotals(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('items') ?? [];
        $sum = 0;
        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $sum += ($qty * $price);
        }
        if ($sum > 0) {
            $set('total_amount', number_format($sum, 2, '.', ''));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Receipt No')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->description(fn ($record) => $record->client_phone ?: $record->client_email),

                Tables\Columns\TextColumn::make('item_title')
                    ->label('Artwork Item')
                    ->limit(35)
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Price')
                    ->formatStateUsing(fn ($record) => $record->currency_symbol . ' ' . number_format($record->total_amount, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->formatStateUsing(fn ($record) => $record->currency_symbol . ' ' . number_format($record->paid_amount, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fully_paid'     => 'success',
                        'advance_paid'   => 'warning',
                        'partially_paid' => 'info',
                        'unpaid'         => 'danger',
                        default          => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // ── Download PDF Action ──
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => route('admin.invoices.pdf', $record))
                    ->openUrlInNewTab(true),

                // ── Direct WhatsApp Action ──
                Tables\Actions\Action::make('whatsapp_share')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        $phone = preg_replace('/[^0-9]/', '', $record->client_phone);
                        if (!$phone) return '#';
                        $msg = rawurlencode("Hello {$record->client_name}, here is your payment receipt / invoice ({$record->invoice_number}) from Maison Résine Atelier.");
                        return "https://wa.me/{$phone}?text={$msg}";
                    })
                    ->openUrlInNewTab(true)
                    ->visible(fn ($record) => !empty($record->client_phone)),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
