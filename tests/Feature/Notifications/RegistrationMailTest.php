<?php

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\RegistrationRequested;
use App\Notifications\WelcomeNewUser;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds the approval email with the correct recipient and ticket link', function () {
    $reviewer = User::factory()->create(['name' => 'Ana']);
    $ticket = Ticket::create(['title' => 'Cadastro', 'status' => Ticket::STATUS_OPEN, 'description' => 'Solicitação de Bia', 'ticket_type' => Ticket::TYPE_REGISTRATION, 'created_by' => $reviewer->id, 'responsible_id' => $reviewer->id]);
    $notification = new RegistrationRequested($ticket);
    $mail = $notification->toMail($reviewer);

    expect($notification->via($reviewer))->toBe(['mail'])
        ->and($mail->subject)->toBe('Nova solicitação de cadastro na plataforma Cidadania Digital')
        ->and($mail->greeting)->toBe('Olá, Ana!')
        ->and($mail->introLines)->toContain('Solicitação de Bia')
        ->and($mail->actionText)->toBe('Analisar solicitação')
        ->and($mail->actionUrl)->toBe(route('filament.admin.resources.tickets.show', $ticket));
});

it('builds the welcome email with a password reset link for the approved account', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create(['name' => 'Bia', 'email' => 'bia@example.test']);
    $notification = new WelcomeNewUser('test-reset-token');
    $mail = $notification->toMail($user);
    parse_str(parse_url($mail->actionUrl, PHP_URL_QUERY), $query);

    expect($notification->via($user))->toBe(['mail'])
        ->and($mail->subject)->toBe('Bem-vindo(a) à plataforma Cidadania Digital')
        ->and($mail->greeting)->toBe('Olá, Bia!')
        ->and($mail->actionText)->toBe('Definir senha e acessar')
        ->and($query['token'])->toBe('test-reset-token')
        ->and($query['email'])->toBe('bia@example.test');
});
