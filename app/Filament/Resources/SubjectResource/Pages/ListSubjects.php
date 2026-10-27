<?php

namespace App\Filament\Resources\SubjectResource\Pages;

use App\Filament\Resources\SubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubjects extends ListRecords
{
    use \App\Filament\Resources\Concerns\OpensViewInModal;

    protected static string $resource = SubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
