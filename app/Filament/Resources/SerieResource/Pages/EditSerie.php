<?php

namespace App\Filament\Resources\SerieResource\Pages;

use App\Filament\Resources\SerieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSerie extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;

    protected static string $resource = SerieResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['episodes'] = SerieResource::episodesFor($this->record);

        return $data;
    }

    protected function getBottomDeleteActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
