<?php

namespace App\Filament\Resources\SerieResource\Pages;

use App\Filament\Resources\SerieResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSerie extends ViewRecord
{
    protected static string $resource = SerieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Editar'),
        ];
    }
}
