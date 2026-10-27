<?php

namespace App\Filament\Resources\SerieResource\Pages;

use App\Filament\Resources\SerieResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeries extends ListRecords
{
    use \App\Filament\Resources\Concerns\OpensViewInModal;

    protected static string $resource = SerieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
