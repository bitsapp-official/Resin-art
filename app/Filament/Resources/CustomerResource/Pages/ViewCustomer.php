<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('block')
                ->label('Block Customer')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (User $record) => !$record->is_blocked)
                ->form([
                    Forms\Components\Textarea::make('blocked_reason')
                        ->label('Reason for blocking this account')
                        ->placeholder('e.g. Repeated fraudulent order cancellations, suspicious chargeback activity...')
                        ->required()
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->modalHeading('Block Customer Account')
                ->modalDescription('Blocking will immediately suspend this customer from placing orders and navigating the portal.')
                ->modalSubmitActionLabel('Confirm & Block')
                ->action(function (User $record, array $data) {
                    $record->update([
                        'is_blocked' => true,
                        'blocked_reason' => $data['blocked_reason'],
                    ]);
                    
                    Notification::make()
                        ->title('Customer account has been blocked.')
                        ->danger()
                        ->send();
                }),

            Actions\Action::make('unblock')
                ->label('Unblock Customer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (User $record) => (bool) $record->is_blocked)
                ->requiresConfirmation()
                ->modalHeading('Restore Customer Account')
                ->modalDescription('Are you sure you want to unblock and restore full portal access for this customer?')
                ->modalSubmitActionLabel('Confirm & Restore Access')
                ->action(function (User $record) {
                    $record->update([
                        'is_blocked' => false,
                        'blocked_reason' => null,
                    ]);

                    Notification::make()
                        ->title('Customer account has been restored.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
