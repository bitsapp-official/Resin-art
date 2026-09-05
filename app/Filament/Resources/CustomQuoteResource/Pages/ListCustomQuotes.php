<?php

namespace App\Filament\Resources\CustomQuoteResource\Pages;

use App\Filament\Resources\CustomQuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomQuotes extends ListRecords
{
    protected static string $resource = CustomQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
