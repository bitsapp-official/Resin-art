<?php

namespace App\Filament\Resources;

use App\Enums\CustomQuoteStatus;
use App\Enums\DepositType;
use App\Filament\Resources\CustomQuoteResource\Pages;
use App\Models\CustomQuote;
use App\Models\CustomRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomQuoteResource extends Resource
{
    protected static ?string $model = CustomQuote::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-rupee';
    protected static ?string $navigationGroup = 'Orders & Sales';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Custom Quotes';

    /**
     * Clean, simple quote status options for admin UI
     */
    public static function getStatusOptions(): array
    {
        return [
            CustomQuoteStatus::DRAFT->value   => 'Draft',
            CustomQuoteStatus::SENT->value    => 'Sent',
            CustomQuoteStatus::EXPIRED->value => 'Expired',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM
    // ─────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        // ── Quote Info ──
                        Forms\Components\Section::make('Quote Details')
                            ->schema([
                                Forms\Components\Select::make('custom_request_id')
                                    ->label('Linked Request')
                                    ->relationship('request', 'public_reference')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(fn () => request('request') ? (int) request('request') : null)
                                    ->helperText(function ($get) {
                                        $id = $get('custom_request_id');
                                        if (!$id) return null;
                                        $req = CustomRequest::find($id);
                                        return $req
                                            ? "Customer: {$req->name} | Project: {$req->project_type}"
                                            : null;
                                    })
                                    ->reactive(),

                                Forms\Components\TextInput::make('quote_reference')
                                    ->label('Quote Reference')
                                    ->required()
                                    ->default(fn () => CustomQuote::generateReference())
                                    ->unique(ignoreRecord: true)
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Select::make('status')
                                    ->options(static::getStatusOptions())
                                    ->default(CustomQuoteStatus::DRAFT->value)
                                    ->required(),

                                Forms\Components\DateTimePicker::make('valid_until')
                                    ->label('Valid Until')
                                    ->default(now()->addDays(7)),

                                Forms\Components\TextInput::make('estimated_completion')
                                    ->label('Estimated Delivery')
                                    ->placeholder('e.g. 15–20 working days'),
                            ])->columns(2),

                        // ── Line Items ──
                        Forms\Components\Section::make('Line Items (Scope of Work)')
                            ->description('Add each item — artwork, materials, packaging, etc.')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship('items')
                                    ->schema([
                                        Forms\Components\TextInput::make('description')
                                            ->required()
                                            ->placeholder('e.g. Ocean Resin Wall Art 60×40cm')
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->minValue(1),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->required()
                                            ->minValue(0),
                                    ])
                                    ->columns(4)
                                    ->defaultItems(1)
                                    ->reorderableWithDragAndDrop(true)
                                    ->orderColumn('sort_order')
                                    ->addActionLabel('+ Add Item'),
                            ]),

                        // ── Charges & Deposit ──
                        Forms\Components\Section::make('Additional Charges & Deposit')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_amount')
                                    ->label('Shipping (₹)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('₹'),
                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Tax / GST (₹)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('₹'),
                                Forms\Components\TextInput::make('discount_amount')
                                    ->label('Discount (₹)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('₹'),

                                Forms\Components\Select::make('deposit_type')
                                    ->options(DepositType::class)
                                    ->default(DepositType::PERCENTAGE)
                                    ->required(),
                                Forms\Components\TextInput::make('deposit_amount')
                                    ->numeric()
                                    ->default(50)
                                    ->label('Deposit Amount / %')
                                    ->required()
                                    ->helperText('Enter percentage (e.g. 50 for 50%) or fixed ₹ amount'),
                            ])->columns(3),

                        // ── Notes ──
                        Forms\Components\Section::make('Terms & Notes for Customer')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('')
                                    ->rows(3)
                                    ->placeholder("50% advance required to begin production.\nBalance due before dispatch.\nCustom pieces are non-refundable once production has started.\nEstimated completion: 15–20 working days after approval."),
                            ]),
                    ])->columnSpan(['lg' => 3]),
            ])->columns(3);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE
    // ─────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_reference')
                    ->label('Quote Ref')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('request.public_reference')
                    ->label('Request')
                    ->searchable()
                    ->description(fn ($record) => $record->request?->name),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('inr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(static::getStatusOptions()),
            ])
            ->actions([
                // ── Download PDF ──
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => route('admin.quotes.pdf', $record))
                    ->openUrlInNewTab(),

                // ── Mark as Sent ──
                Tables\Actions\Action::make('mark_sent')
                    ->label('Mark Sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn ($record) => ($record->status->value ?? $record->status) === CustomQuoteStatus::DRAFT->value)
                    ->requiresConfirmation()
                    ->modalHeading('Mark Quote as Sent?')
                    ->modalDescription('Confirm you have shared the PDF quote with the customer.')
                    ->action(function ($record) {
                        $record->update(['status' => CustomQuoteStatus::SENT->value]);
                        Notification::make()
                            ->title('Quote marked as Sent ✓')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomQuotes::route('/'),
            'create' => Pages\CreateCustomQuote::route('/create'),
            'edit'   => Pages\EditCustomQuote::route('/{record}/edit'),
        ];
    }
}
