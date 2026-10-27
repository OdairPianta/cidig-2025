<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    use \App\Filament\Resources\Concerns\DeleteAtBottom;

    protected static string $resource = VideoResource::class;

    protected function getBottomDeleteActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
