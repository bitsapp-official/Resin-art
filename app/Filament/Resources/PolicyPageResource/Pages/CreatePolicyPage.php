<?php

namespace App\Filament\Resources\PolicyPageResource\Pages;

use App\Filament\Resources\PolicyPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePolicyPage extends CreateRecord
{
    protected static string $resource = PolicyPageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
