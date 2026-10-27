<?php

namespace App\Filament\Resources\SponsorResource\Pages;

use App\Filament\Resources\SponsorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSponsor extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;

    protected static string $resource = SponsorResource::class;

    protected function getBottomDeleteActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->syncActiveLogo();
    }
}
