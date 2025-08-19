<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Filament\Resources\ResourceResource;
use App\Models\UploadedFile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditResource extends EditRecord
{
    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        if ($data['published_at'] == 1) {
            $data['published_at'] = now();
        } else {
            $data['published_at'] = null;
        }

        return $data;
    }

    public function afterSave(): void
    {
        $uploadedFilePath = $this->form->getState()['uploaded_file'] ?? null;
        $resource = $this->record;

        if (!$uploadedFilePath) {
            return;
        }

        // Delete old file if exists
        $oldFile = $resource->uploadedFile;
        if ($oldFile) {
            Storage::disk('public')->delete($oldFile->path);
            $oldFile->delete();
        }

        UploadedFile::create([
            'path' => $uploadedFilePath,
            'original_name' => basename($uploadedFilePath),
            'mimeType' => Storage::disk('public')->mimeType($uploadedFilePath),
            'size' => Storage::disk('public')->size($uploadedFilePath),
            'uploaded_by' => auth()->id(),
            'resource_id' => $this->record->id,
        ]);
    }
}
