<?php

namespace App\Filament\Resources\ProcessPageResource\Pages;

use App\Filament\Resources\ProcessPageResource;
use Filament\Resources\Pages\ListRecords;

class ListProcessPages extends ListRecords
{
    protected static string $resource = ProcessPageResource::class;

    public function mount(): void
    {
        $this->redirect(static::getResource()::getUrl('edit'));
    }
}
