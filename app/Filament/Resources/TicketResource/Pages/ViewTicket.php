<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Support\RegistrationRequests;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;
    
    public static function getNavigationLabel(): string
    {
        return 'Visualizar';
    }
    
    protected function getHeaderActions(): array
    {
        $review = fn (): bool => RegistrationRequests::canReview(auth()->user(), $this->record);

        return [
            Actions\Action::make('approve')
                ->label('Autorizar cadastro')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('A pessoa receberá um e-mail para definir a senha e acessar a plataforma.')
                ->visible($review)
                ->action(function () {
                    RegistrationRequests::approve($this->record, auth()->user());
                    Notification::make()->success()->title('Cadastro autorizado')->send();
                    $this->record->refresh();
                }),
            Actions\Action::make('reject')
                ->label('Recusar')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->visible($review)
                ->action(function () {
                    RegistrationRequests::reject($this->record, auth()->user());
                    Notification::make()->title('Solicitação recusada')->send();
                    $this->record->refresh();
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Visualizar Ticket';
    }
}