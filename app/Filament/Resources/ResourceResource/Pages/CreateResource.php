<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Filament\Resources\ResourceResource;
use App\Models\UploadedFile;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateResource extends CreateRecord
{
    protected static string $resource = ResourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['published_at'] = $data['published_at'] !== null ? now() : null;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function afterCreate(): void
    {
        $uploadedFilePath = $this->form->getState()['uploaded_file'] ?? null;
        if (!$uploadedFilePath) {
            return;
        }

        UploadedFile::create([
            'path' => $uploadedFilePath,
            'original_name' => basename($uploadedFilePath),
            'mimeType' => \App\Support\Uploads::storage()->mimeType($uploadedFilePath),
            'size' => \App\Support\Uploads::storage()->size($uploadedFilePath),
            'uploaded_by' => auth()->id(),
            'resource_id' => $this->record->id,
        ]);
    }
}
