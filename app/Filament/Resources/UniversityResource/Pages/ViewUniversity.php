<?php

namespace App\Filament\Resources\UniversityResource\Pages;

use App\Filament\Resources\UniversityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUniversity extends ViewRecord
{
    protected static string $resource = UniversityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Editar'),
        ];
    }
}
