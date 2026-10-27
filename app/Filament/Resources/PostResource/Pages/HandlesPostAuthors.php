<?php

namespace App\Filament\Resources\PostResource\Pages;

/** Credited authors live in a pivot, so the form's name list is loaded and saved around the record. */
trait HandlesPostAuthors
{
    /** @var array<int, string> */
    private array $authorNames = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['author_names'] = $this->record->authors()->pluck('name')->all();

        return $data;
    }

    protected function rememberAuthorNames(): void
    {
        $this->authorNames = (array) ($this->form->getRawState()['author_names'] ?? []);
    }

    protected function persistAuthorNames(): void
    {
        $this->record->syncAuthorNames($this->authorNames);
    }
}
