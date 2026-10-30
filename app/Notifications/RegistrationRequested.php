<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationRequested extends Notification
{
    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nova solicitação de cadastro na plataforma Cidadania Digital')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Uma pessoa pediu acesso à plataforma e a solicitação aguarda a sua autorização.')
            ->line($this->ticket->description)
            ->action('Analisar solicitação', route('filament.admin.resources.tickets.show', $this->ticket));
    }
}
