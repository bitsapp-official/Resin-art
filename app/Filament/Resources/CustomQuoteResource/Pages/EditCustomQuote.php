<?php

namespace App\Filament\Resources\CustomQuoteResource\Pages;

use App\Filament\Resources\CustomQuoteResource;
use App\Enums\CustomQuoteStatus;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCustomQuote extends EditRecord
{
    protected static string $resource = CustomQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── Download PDF ──
            Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('admin.quotes.pdf', $this->record))
                ->openUrlInNewTab(),

            // ── Mark as Sent ──
            Actions\Action::make('mark_sent')
                ->label('Mark as Sent')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn () => $this->record->status === CustomQuoteStatus::DRAFT)
                ->requiresConfirmation()
                ->modalHeading('Mark Quote as Sent?')
                ->modalDescription('This will update the status to "Sent". Make sure you have shared the PDF with the customer.')
                ->action(function () {
                    $this->record->update(['status' => CustomQuoteStatus::SENT]);
                    Notification::make()
                        ->title('Quote marked as Sent')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status']);
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
