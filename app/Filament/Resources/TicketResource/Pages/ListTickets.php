<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos'),
            'created' => Tab::make('Criados por mim')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_by', auth()->id())),
            'assigned' => Tab::make('Atribuídos a mim')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('responsible_id', auth()->id())),
        ];
    }
}
