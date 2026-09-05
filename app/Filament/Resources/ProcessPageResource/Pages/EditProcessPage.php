<?php

namespace App\Filament\Resources\ProcessPageResource\Pages;

use App\Enums\ProcessPageStatus;
use App\Filament\Resources\ProcessPageResource;
use App\Models\ProcessPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProcessPage extends EditRecord
{
    protected static string $resource = ProcessPageResource::class;

    public Model|string|int|null $record = 1;

    public function mount(Model|string|int|null $record = 1): void
    {
        $this->record = 1;
        parent::mount(1);
    }

    protected function resolveRecord($key): Model
    {
        return ProcessPage::firstOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'OUR PROCESS',
                'title' => 'Six weeks, one object.',
                'description' => 'From timber selection to the final hand-polish, nothing here is hurried.',
                'status' => ProcessPageStatus::PUBLISHED,
            ]
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPublic')
                ->label('View Public Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url('/our-process', shouldOpenInNewTab: true)
                ->color('success'),
        ];
    }
}
