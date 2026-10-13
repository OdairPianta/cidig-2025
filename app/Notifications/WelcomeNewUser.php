<?php

namespace App\Notifications;

use Filament\Facades\Filament;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNewUser extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = Filament::getResetPasswordUrl($this->token, $notifiable);

        return (new MailMessage)
            ->subject('Bem-vindo(a) à plataforma Cidadania Digital')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Seu cadastro foi criado na plataforma Cidadania Digital.')
            ->line('Clique no botão abaixo para definir sua senha, acessar seu perfil e completar suas informações.')
            ->action('Definir senha e acessar', $url)
            ->line('Se você não esperava este e-mail, pode ignorá-lo.');
    }
}
