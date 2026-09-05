<?php

namespace App\Filament\Resources\ContactPageContentResource\Pages;

use App\Filament\Resources\ContactPageContentResource;
use Filament\Resources\Pages\ListRecords;

class ListContactPageContents extends ListRecords
{
    protected static string $resource = ContactPageContentResource::class;

    public function mount(): void
    {
        $this->redirect(static::getResource()::getUrl('edit'));
    }
}
