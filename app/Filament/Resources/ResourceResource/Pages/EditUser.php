<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn (): bool => !auth()->user()->isAdmin()),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->information) {
            $informationData = $this->record->information->toArray();
            $data = array_merge($data, $informationData);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $informationFields = [
            'university_id', 'picture_url', 'linkedin_url', 'lattes_url', 
            'orcid_url', 'minibio', 'description', 'title', 'roles', 'website'
        ];
        
        $informationData = [];
        foreach ($informationFields as $field) {
            if (array_key_exists($field, $data)) {
                $informationData[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        if (!empty($informationData)) {
            if ($this->record->information) {
                $this->record->information->update($informationData);
            } else {
                $this->record->information()->create($informationData);
            }
        }

        return $data;
    }
}