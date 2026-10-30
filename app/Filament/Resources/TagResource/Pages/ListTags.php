<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cloud')
                ->label('Nuvem de tags')
                ->icon('heroicon-o-cloud')
                ->color('gray')
                ->url(\App\Filament\Pages\TagAnalytics::getUrl())
                ->visible(fn () => \App\Filament\Pages\TagAnalytics::canAccess()),
            Actions\CreateAction::make(),
        ];
    }
}
