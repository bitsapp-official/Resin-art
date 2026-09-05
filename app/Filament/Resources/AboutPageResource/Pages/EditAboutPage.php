<?php

namespace App\Filament\Resources\AboutPageResource\Pages;

use App\Filament\Resources\AboutPageResource;
use App\Models\AboutPage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAboutPage extends EditRecord
{
    protected static string $resource = AboutPageResource::class;

    public Model|string|int|null $record = 1;

    public function mount(Model|string|int|null $record = 1): void
    {
        $this->record = 1;
        parent::mount(1);
    }

    protected function resolveRecord($key): Model
    {
        return AboutPage::firstOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'THE HOUSE · EST. 2013',
                'hero_title' => 'A quiet atelier.',
                'hero_description' => 'Maison Résine story intro text.',
                'is_published' => true,
            ]
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPublic')
                ->label('View Public Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url('/about', shouldOpenInNewTab: true)
                ->color('success'),
        ];
    }
}
