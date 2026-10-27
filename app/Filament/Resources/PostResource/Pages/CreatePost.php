<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    use HandlesPostAuthors;

    protected static string $resource = PostResource::class;

    protected function getRedirectUrl(): string
    {
        // Open the new post so the author can check how it looks.
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->rememberAuthorNames();

        $data['user_id'] = $data['user_id'] ?? auth()->id();
        
        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->persistAuthorNames();
    }
}
