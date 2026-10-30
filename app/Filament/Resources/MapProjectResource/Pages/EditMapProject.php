<?php

namespace App\Filament\Resources\MapProjectResource\Pages;

use App\Filament\Resources\MapProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMapProject extends EditRecord
{
    protected static string $resource = MapProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('Excluir')];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // A pin typed by hand is no longer "corrected" from the sheet.
        if (((float) ($data['latitude'] ?? 0)) !== ((float) $this->record->latitude) || ((float) ($data['longitude'] ?? 0)) !== ((float) $this->record->longitude)) {
            $data['coordinates_source'] = 'manual';
        }

        return $data;
    }
}
