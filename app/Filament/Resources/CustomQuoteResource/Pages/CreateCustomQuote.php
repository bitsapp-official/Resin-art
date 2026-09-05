<?php

namespace App\Filament\Resources\CustomQuoteResource\Pages;

use App\Filament\Resources\CustomQuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomQuote extends CreateRecord
{
    protected static string $resource = CustomQuoteResource::class;
}
