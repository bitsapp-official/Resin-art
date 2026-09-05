<?php

namespace App\Filament\Resources\AboutPageResource\Pages;

use App\Filament\Resources\AboutPageResource;
use Filament\Resources\Pages\ListRecords;

class ListAboutPages extends ListRecords
{
    protected static string $resource = AboutPageResource::class;

    public function mount(): void
    {
        $this->redirect(static::getResource()::getUrl('edit'));
    }
}
