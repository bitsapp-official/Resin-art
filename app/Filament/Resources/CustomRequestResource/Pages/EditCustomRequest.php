<?php

namespace App\Filament\Resources\CustomRequestResource\Pages;

use App\Filament\Resources\CustomRequestResource;
use App\Filament\Resources\InvoiceResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomRequest extends EditRecord
{
    protected static string $resource = CustomRequestResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Auto-dismiss/mark-as-read the admin bell notification for this custom request when opened
        $this->dismissRelatedNotification();
    }

    /**
     * Delete any admin bell notification that links to this custom request.
     */
    protected function dismissRelatedNotification(): void
    {
        try {
            $admins = User::where('is_admin', true)
                ->orWhere('id', 1)
                ->get();

            $urlSuffix = '/admin/custom-requests/' . $this->record->id . '/edit';

            foreach ($admins as $admin) {
                $admin->notifications()
                    ->where('data->format', 'filament')
                    ->get()
                    ->filter(function ($n) use ($urlSuffix) {
                        $actions = data_get($n->data, 'actions', []);
                        foreach ($actions as $action) {
                            $url = data_get($action, 'url', '');
                            if (str_ends_with($url, $urlSuffix)) {
                                return true;
                            }
                        }
                        return false;
                    })
                    ->each(fn ($n) => $n->delete());
            }
        } catch (\Throwable) {
            // Silent fail — never break the UI
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Direct WhatsApp Action ──
            Actions\Action::make('whatsapp_contact')
                ->label('WhatsApp Client')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(function () {
                    $phone = preg_replace('/[^0-9]/', '', $this->record->whatsapp ?: $this->record->phone);
                    if (!$phone) return '#';
                    $msg = rawurlencode("Hello {$this->record->name}, this is Maison Résine Atelier regarding your custom artwork request ({$this->record->public_reference}).");
                    return "https://wa.me/{$phone}?text={$msg}";
                })
                ->openUrlInNewTab(true)
                ->visible(fn () => !empty($this->record->whatsapp || $this->record->phone)),

            // ── Create Receipt / Invoice Action ──
            Actions\Action::make('create_receipt')
                ->label('Create Receipt / Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('primary')
                ->url(fn () => InvoiceResource::getUrl('create') . '?request=' . $this->record->id)
                ->openUrlInNewTab(false),

            Actions\DeleteAction::make(),
        ];
    }
}
