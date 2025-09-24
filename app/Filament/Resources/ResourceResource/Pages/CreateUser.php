<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $informationFields = [
            'university_id', 'picture_url', 'linkedin_url', 'lattes_url', 
            'orcid_url', 'minibio', 'description', 'title', 'roles', 'website'
        ];
        
        $this->informationData = [];
        foreach ($informationFields as $field) {
            if (array_key_exists($field, $data)) {
                $this->informationData[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {

        if (!empty($this->informationData)) {
            $this->record->information()->create($this->informationData);
        }
    }
}