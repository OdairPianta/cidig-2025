<?php

namespace App\Filament\Resources\SponsorResource\Pages;

use App\Filament\Resources\SponsorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSponsors extends ListRecords
{
    use \App\Filament\Resources\Concerns\OpensViewInModal;

    protected static string $resource = SponsorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
