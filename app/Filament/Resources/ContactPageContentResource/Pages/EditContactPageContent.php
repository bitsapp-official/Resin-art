<?php

namespace App\Filament\Resources\ContactPageContentResource\Pages;

use App\Filament\Resources\ContactPageContentResource;
use App\Models\ContactPageContent;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditContactPageContent extends EditRecord
{
    protected static string $resource = ContactPageContentResource::class;

    public Model|string|int|null $record = 1;

    public function mount(Model|string|int|null $record = 1): void
    {
        $this->record = 1;
        parent::mount(1);
    }

    protected function resolveRecord($key): Model
    {
        return ContactPageContent::firstOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'GET IN TOUCH',
                'title' => 'Write to the Atelier.',
            ]
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPublic')
                ->label('View Public Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url('/contact', shouldOpenInNewTab: true)
                ->color('success'),
        ];
    }
}
