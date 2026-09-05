<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundRequestResource\Pages;
use App\Models\RefundRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RefundRequestResource extends Resource
{
    protected static ?string $model = RefundRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Customers & Support';

    protected static ?string $navigationLabel = 'Refund Requests';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Refund Request Details')
                    ->schema([
                        Forms\Components\TextInput::make('order_reference_display')
                            ->label('Order Reference')
                            ->formatStateUsing(fn ($record) => $record?->order?->order_reference ?? '—')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('collector_name_display')
                            ->label('Collector Name')
                            ->formatStateUsing(fn ($record) => $record?->user?->name ?? $record?->order?->email ?? '—')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('amount_display')
                            ->label('Refund Amount')
                            ->prefix('₹')
                            ->formatStateUsing(fn ($record) => number_format((float)($record?->amount ?? 0), 2))
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('reason')
                            ->label('Reason for Refund / Cancellation')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Refund Status')
                            ->options([
                                'REQUESTED'  => 'Requested (Pending Review)',
                                'APPROVED'   => 'Approved (Processing Transfer)',
                                'COMPLETED'  => 'Completed (Refund Credited)',
                                'REJECTED'   => 'Rejected',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin / Accounts Processing Notes')
                            ->placeholder('e.g. Refund initiated via Razorpay / Bank transfer UTR: XXXXXXXXX')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_reference')->label('Order')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Collector')->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('Refund Amount')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('reason')->label('Reason')->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVED', 'COMPLETED' => 'success',
                        'REJECTED', 'FAILED' => 'danger',
                        'UNDER_REVIEW', 'PROCESSING', 'REQUESTED' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Requested Date')->dateTime('M d, Y H:i'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRefundRequests::route('/'),
            'edit' => Pages\EditRefundRequest::route('/{record}/edit'),
        ];
    }
}
