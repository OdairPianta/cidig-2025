<?php

namespace App\Filament\Support;

use App\Models\Tag;
use Filament\Forms\Components\TagsInput;
use Illuminate\Database\Eloquent\Model;

/**
 * Tag field in the style discussed with the client (Obsidian-like): type, pick a suggestion or press
 * Enter to confirm. Names that match an existing tag (any case, plural or accent) reuse it, new
 * ones are created on save.
 */
class TagNames
{
    public static function make(string $label = 'Tags'): TagsInput
    {
        return TagsInput::make('tag_names')
            ->label($label)
            ->placeholder('Digite uma tag e pressione Enter')
            ->helperText('Digite e pressione Enter para confirmar cada tag. Tags já existentes aparecem como sugestão e são reaproveitadas, sem diferenciar maiúsculas e minúsculas.')
            ->suggestions(fn () => Tag::query()->isActive()->orderBy('name')->pluck('name')->all())
            ->formatStateUsing(fn (?Model $record) => $record?->tags()->orderBy('name')->pluck('name')->all() ?? [])
            ->dehydrated(false)
            ->saveRelationshipsUsing(function (Model $record, ?array $state) {
                $ids = collect($state ?? [])
                    ->map(fn ($name) => Tag::findOrCreate(['name' => $name, 'is_active' => true])?->getKey())
                    ->filter()
                    ->unique()
                    ->all();

                $record->tags()->sync($ids);
            });
    }
}
