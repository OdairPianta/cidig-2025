<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;
    
    public static function getNavigationLabel(): string
    {
        return 'Visualizar';
    }
    
    public function getTitle(): string
    {
        return 'Visualizar Ticket';
    }
}