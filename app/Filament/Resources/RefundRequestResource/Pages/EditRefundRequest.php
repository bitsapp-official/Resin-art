<?php

namespace App\Filament\Resources\RefundRequestResource\Pages;

use App\Filament\Resources\RefundRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditRefundRequest extends EditRecord
{
    protected static string $resource = RefundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Approve Refund Request')
                ->modalDescription('Approve this customer refund request. Once approved, the "Process Stripe Refund" action will become available.')
                ->visible(fn (): bool => $this->record->status === 'REQUESTED')
                ->action(function () {
                    $this->record->update([
                        'status'      => 'APPROVED',
                        'admin_notes' => trim(($this->record->admin_notes ? $this->record->admin_notes . "\n" : '') . "Approved by Admin on " . now()->format('d M Y H:i')),
                    ]);

                    if ($this->record->user_id) {
                        try {
                            \App\Models\CustomerNotification::create([
                                'user_id'    => $this->record->user_id,
                                'title'      => 'Refund Request Approved',
                                'message'    => "Your refund request of ₹" . number_format($this->record->amount, 2) . " for order " . ($this->record->order?->order_reference ?? "#{$this->record->order_id}") . " has been approved by Atelier Admin and is queued for payout.",
                                'type'       => 'refund_update',
                                'action_url' => route('account.refunds.index'),
                            ]);
                        } catch (\Throwable $e) {}
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Refund Request Approved')
                        ->body('Status updated to APPROVED. You may now click "Process Stripe Refund".')
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            \Filament\Actions\Action::make('process_stripe_refund')
                ->label('Process Stripe Refund')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Execute Stripe Online Refund')
                ->modalDescription(fn (): string => "This will issue a real refund of ₹" . number_format($this->record->amount, 2) . " via Stripe API directly to the patron's payment card and mark the order cancelled.")
                ->visible(fn (): bool => $this->record->status === 'APPROVED' && empty($this->record->stripe_refund_id))
                ->action(function () {
                    try {
                        $result = \App\Services\StripeRefundService::processRefund($this->record);
                        \Filament\Notifications\Notification::make()
                            ->title('Stripe Refund Successful')
                            ->body("Refund ID: {$result['stripe_refund_id']} for ₹" . number_format($result['amount'], 2))
                            ->success()
                            ->send();

                        $this->fillForm();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Stripe Refund Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            \Filament\Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->required()
                        ->placeholder('e.g. Beyond cancellation window or artwork already custom dispatched.'),
                ])
                ->visible(fn (): bool => in_array($this->record->status, ['REQUESTED', 'APPROVED']) && empty($this->record->stripe_refund_id))
                ->action(function (array $data) {
                    $this->record->update([
                        'status'      => 'REJECTED',
                        'admin_notes' => trim(($this->record->admin_notes ? $this->record->admin_notes . "\n" : '') . "Rejected by Admin: " . $data['rejection_reason']),
                    ]);

                    if ($this->record->user_id) {
                        try {
                            \App\Models\CustomerNotification::create([
                                'user_id'    => $this->record->user_id,
                                'title'      => 'Refund Request Update',
                                'message'    => "Your refund request for order " . ($this->record->order?->order_reference ?? "#{$this->record->order_id}") . " was not approved. Atelier Note: " . $data['rejection_reason'],
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

                    $this->fillForm();
                }),

            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $refund = $this->record;

        // If refund is marked completed, update order payment_status to 'refunded'
        if ($refund->status === 'COMPLETED' && $refund->order) {
            $refund->order->update(['payment_status' => 'refunded']);
        }

        // Notify customer in-app
        if ($refund->user_id) {
            \App\Models\CustomerNotification::create([
                'user_id' => $refund->user_id,
                'title' => "Refund Status Update [#" . ($refund->order?->order_reference ?? $refund->id) . "]",
                'message' => "Your refund request for ₹" . number_format($refund->amount, 2) . " is now: " . str_replace('_', ' ', $refund->status),
                'type' => 'refund_update',
                'action_url' => route('account.refunds.index'),
            ]);
        }
    }
}
