<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Filament\Resources\ResourceResource;
use App\Models\UploadedFile;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

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

    public function afterSave(): void
    {
        if (isset($data['source_file'])) {
            $uploadedFile = request()->file('source_file');
            $fileMetadata = new UploadedFile([
                'path'          => $data['source_file'],
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mimeType'      => $uploadedFile->getMimeType(),
                'size'          => $uploadedFile->getSize(),
                'uploaded_by'   => auth()->id(),
            ]);

            $this->record->uploadedFile()->save($fileMetadata);
        }
    }
}
