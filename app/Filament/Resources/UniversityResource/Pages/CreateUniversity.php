<?php

namespace App\Filament\Resources\UniversityResource\Pages;

use App\Filament\Resources\UniversityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUniversity extends CreateRecord
{
    protected static string $resource = UniversityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
