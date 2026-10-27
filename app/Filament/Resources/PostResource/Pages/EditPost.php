<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;
    use HandlesPostAuthors;

    protected static string $resource = PostResource::class;

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
        $this->rememberAuthorNames();

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        } elseif (!$data['is_published']) {
            $data['published_at'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->persistAuthorNames();
    }
}
