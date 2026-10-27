<?php

namespace App\Filament\Resources\UserInformationResource\Pages;

use App\Filament\Resources\UserInformationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserInformation extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;

    protected static string $resource = UserInformationResource::class;

    protected function getBottomDeleteActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
