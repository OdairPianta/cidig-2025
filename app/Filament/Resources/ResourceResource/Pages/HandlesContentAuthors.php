<?php

namespace App\Filament\Resources\ResourceResource\Pages;

use App\Models\Author;

/** Authors live in a pivot, so the form's name list is loaded and saved around the record. */
trait HandlesContentAuthors
{
    /** @var array<int, string> */
    private array $authorNames = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['author_names'] = $this->record->authors()->orderBy('name')->pluck('name')->all();

        return $data;
    }

    /** Remembers the typed names and gives the plain-text column a value until they are synced. */
    protected function rememberAuthorNames(array $data): array
    {
        $this->authorNames = (array) ($this->form->getRawState()['author_names'] ?? []);
        $data['author'] = collect($this->authorNames)->flatMap(fn ($name) => Author::parseNames((string) $name))->unique()->implode('; ');

        return $data;
    }

    protected function persistAuthorNames(): void
    {
        $this->record->syncAuthorNames($this->authorNames);
    }
}
