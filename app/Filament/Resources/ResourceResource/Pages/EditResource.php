<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Filament\Resources\ResourceResource;
use App\Models\UploadedFile;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditResource extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;

    protected static string $resource = ResourceResource::class;

    protected function getBottomDeleteActions(): array
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
        $data['author'] = \App\Models\Resource::authorText($this->data['authors'] ?? []);

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
            \App\Support\Uploads::storage()->delete($oldFile->path);
            $oldFile->delete();
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
