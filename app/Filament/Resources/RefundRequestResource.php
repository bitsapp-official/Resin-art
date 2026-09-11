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

                        Forms\Components\TextInput::make('stripe_refund_id')
                            ->label('Stripe Refund ID')
                            ->disabled()
                            ->placeholder('Not processed via Stripe'),

                        Forms\Components\TextInput::make('stripe_refund_status')
                            ->label('Stripe Refund Status')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('refunded_at')
                            ->label('Refunded At')
                            ->disabled(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin / Accounts Processing Notes')
                            ->placeholder('e.g. Refund initiated via Stripe or Bank transfer')
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
                Tables\Columns\TextColumn::make('stripe_refund_id')->label('Stripe Refund ID')->searchable()->toggleable(),
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
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Refund Request')
                    ->modalDescription('Approve this customer refund request. Once approved, you can click "Process Stripe Refund" to execute the online refund to their card.')
                    ->visible(fn (RefundRequest $record): bool => $record->status === 'REQUESTED')
                    ->action(function (RefundRequest $record) {
                        $record->update([
                            'status'      => 'APPROVED',
                            'admin_notes' => trim(($record->admin_notes ? $record->admin_notes . "\n" : '') . "Approved by Admin on " . now()->format('d M Y H:i')),
                        ]);

                        if ($record->user_id) {
                            try {
                                \App\Models\CustomerNotification::create([
                                    'user_id'    => $record->user_id,
                                    'title'      => 'Refund Request Approved',
                                    'message'    => "Your refund request of ₹" . number_format($record->amount, 2) . " for order " . ($record->order?->order_reference ?? "#{$record->order_id}") . " has been approved by Atelier Admin and is queued for payout.",
                                    'type'       => 'refund_update',
                                    'action_url' => route('account.refunds.index'),
                                ]);
                            } catch (\Throwable $e) {}
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Refund Request Approved')
                            ->body('Status updated to APPROVED. You may now click "Process Stripe Refund" to execute the online transfer.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('process_stripe_refund')
                    ->label('Process Stripe Refund')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Execute Stripe Online Refund')
                    ->modalDescription(fn (RefundRequest $record): string => "This will issue a real refund of ₹" . number_format($record->amount, 2) . " via Stripe API directly to the patron's payment card and mark the order cancelled.")
                    ->visible(fn (RefundRequest $record): bool => $record->status === 'APPROVED' && empty($record->stripe_refund_id))
                    ->action(function (RefundRequest $record) {
                        try {
                            $result = \App\Services\StripeRefundService::processRefund($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Stripe Refund Successful')
                                ->body("Refund ID: {$result['stripe_refund_id']} for ₹" . number_format($result['amount'], 2))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Stripe Refund Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->placeholder('e.g. Beyond cancellation window or artwork already custom dispatched.'),
                    ])
                    ->visible(fn (RefundRequest $record): bool => in_array($record->status, ['REQUESTED', 'APPROVED']) && empty($record->stripe_refund_id))
                    ->action(function (RefundRequest $record, array $data) {
                        $record->update([
                            'status'      => 'REJECTED',
                            'admin_notes' => trim(($record->admin_notes ? $record->admin_notes . "\n" : '') . "Rejected by Admin: " . $data['rejection_reason']),
                        ]);

                        if ($record->user_id) {
                            try {
                                \App\Models\CustomerNotification::create([
                                    'user_id'    => $record->user_id,
                                    'title'      => 'Refund Request Update',
                                    'message'    => "Your refund request for order " . ($record->order?->order_reference ?? "#{$record->order_id}") . " was not approved. Atelier Note: " . $data['rejection_reason'],
                                    'type'       => 'refund_update',
                                    'action_url' => route('account.refunds.index'),
                                ]);
                            } catch (\Throwable $e) {}
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Refund Request Rejected')
                            ->body('Customer request has been marked REJECTED.')
                            ->warning()
                            ->send();
                    }),

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
