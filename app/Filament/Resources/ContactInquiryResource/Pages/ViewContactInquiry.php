<?php

namespace App\Filament\Resources\ContactInquiryResource\Pages;

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiryResource;
use App\Mail\ContactInquiryReply;
use App\Models\ContactInquiry;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewContactInquiry extends ViewRecord
{
    protected static string $resource = ContactInquiryResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Auto-mark as READ when admin first views
        if ($this->record->status === ContactInquiryStatus::NEW) {
            $this->record->update(['status' => ContactInquiryStatus::READ]);
        }

        // Auto-dismiss the bell notification for this inquiry
        // Works whether admin clicked from bell OR from list page
        $this->dismissRelatedNotification();
    }

    /**
     * Delete any admin bell notification that links to this inquiry.
     */
    protected function dismissRelatedNotification(): void
    {
        try {
            $admins = User::where('is_admin', true)
                ->orWhere('id', 1)
                ->get();

            $urlSuffix = '/admin/contact-inquiries/' . $this->record->id;

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
            // ─── Reply via Email ──────────────────────────────────────────
            Actions\Action::make('sendReply')
                ->label('Reply to Customer')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    Forms\Components\Textarea::make('reply_message')
                        ->label('Your Reply')
                        ->placeholder('Type your reply to the customer here...')
                        ->rows(7)
                        ->required(),
                ])
                ->action(function (array $data) {
                    /** @var ContactInquiry $record */
                    $record = $this->record;
                    try {
                        Mail::to($record->email)->queue(new ContactInquiryReply($record, $data['reply_message']));

                        $record->update([
                            'status'     => ContactInquiryStatus::REPLIED,
                            'replied_at' => now(),
                        ]);

                        // Refresh the record on screen
                        $this->refreshFormData(['status', 'replied_at']);

                        Notification::make()
                            ->title('Reply sent to ' . $record->name)
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Failed to send: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // ─── Close Inquiry ────────────────────────────────────────────
            Actions\Action::make('closeInquiry')
                ->label('Close Inquiry')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->visible(fn () => $this->record->status !== ContactInquiryStatus::CLOSED)
                ->requiresConfirmation()
                ->modalHeading('Close this inquiry?')
                ->modalDescription('The inquiry will be marked as closed. You can still view it later.')
                ->action(function () {
                    $this->record->update([
                        'status'    => ContactInquiryStatus::CLOSED,
                        'closed_at' => now(),
                    ]);

                    $this->refreshFormData(['status', 'closed_at']);

                    Notification::make()
                        ->title('Inquiry closed')
                        ->success()
                        ->send();
                }),
        ];
    }
}

