<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $sum = 0;
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $q = (float)($item['qty'] ?? 1);
                $p = (float)($item['unit_price'] ?? 0);
                $sum += ($q * $p);
            }

            $firstItem = $data['items'][0] ?? [];
            $data['item_title'] = $firstItem['name'] ?? 'Bespoke Resin Artwork';
            $data['item_description'] = $firstItem['description'] ?? null;
        }

        $data['total_amount'] = (float) $sum;

        if (($data['payment_status'] ?? 'fully_paid') === 'fully_paid') {
            $data['paid_amount'] = (float) $sum;
        } else {
            $data['paid_amount'] = 0;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Download PDF Action ──
            Actions\Action::make('download_pdf')
                ->label('Download PDF Receipt')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn () => route('admin.invoices.pdf', $this->record))
                ->openUrlInNewTab(true),

            // ── Direct WhatsApp Action ──
            Actions\Action::make('whatsapp_share')
                ->label('WhatsApp Client')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function () {
                    $phone = preg_replace('/[^0-9]/', '', $this->record->client_phone);
                    if (!$phone) return '#';
                    $msg = rawurlencode("Hello {$this->record->client_name}, here is your payment receipt / invoice ({$this->record->invoice_number}) from Maison Résine Atelier.");
                    return "https://wa.me/{$phone}?text={$msg}";
                })
                ->openUrlInNewTab(true)
                ->visible(fn () => !empty($this->record->client_phone)),

            Actions\DeleteAction::make(),
        ];
    }
}
