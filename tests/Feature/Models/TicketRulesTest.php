<?php

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only lets the creator edit an open ticket and the responsible work on it', function (int $status) {
    $creator = User::factory()->create();
    $responsible = User::factory()->create();
    $stranger = User::factory()->create();
    $ticket = Ticket::create(['title' => 'Erro', 'description' => 'Detalhes', 'ticket_type' => Ticket::TYPE_HELP, 'created_by' => $creator->id, 'responsible_id' => $responsible->id, 'status' => $status]);

    expect($ticket->canEdit($creator))->toBe($status === Ticket::STATUS_OPEN)
        ->and($ticket->canEdit($stranger))->toBeFalse()
        ->and($ticket->canEdit($responsible))->toBeFalse()
        ->and($ticket->canWork($responsible))->toBeTrue()
        ->and($ticket->canWork($creator))->toBeFalse()
        ->and($ticket->canWork($stranger))->toBeFalse();
})->with([Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_DONE, Ticket::STATUS_CLOSED_NOT_DONE]);

it('shows the correct status label and colour', function (int $status, string $label, string $css) {
    $ticket = new Ticket(['status' => $status]);
    expect($ticket->getStatusText())->toBe($label)->and($ticket->getTicketStatusCssClass())->toBe($css);
})->with([
    'open' => [0, 'Aberto', 'bg-danger'],
    'in progress' => [1, 'Em progresso', 'bg-secondary'],
    'done' => [2, 'Realizado', 'bg-success'],
    'closed without a fix' => [3, 'Fechado - Não realizado', 'bg-success'],
]);

it('filters tickets by creator responsible and status without leaking unrelated records', function () {
    $ana = User::factory()->create();
    $bia = User::factory()->create();
    $base = ['title' => 'Ticket', 'description' => 'Detalhes', 'ticket_type' => Ticket::TYPE_HELP];
    $mine = Ticket::create($base + ['created_by' => $ana->id, 'responsible_id' => $bia->id, 'status' => Ticket::STATUS_OPEN]);
    $received = Ticket::create($base + ['created_by' => $bia->id, 'responsible_id' => $ana->id, 'status' => Ticket::STATUS_IN_PROGRESS]);
    $done = Ticket::create($base + ['created_by' => $bia->id, 'responsible_id' => $bia->id, 'status' => Ticket::STATUS_DONE]);

    expect(Ticket::byCreatedUser($ana)->pluck('id')->all())->toBe([$mine->id])
        ->and(Ticket::byResponsibleUser($ana)->pluck('id')->all())->toBe([$received->id])
        ->and(Ticket::byStatus([Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])->pluck('id')->all())->toBe([$mine->id, $received->id])
        ->and(Ticket::byStatus([])->count())->toBe(0)
        ->and($ana->createdTickets->modelKeys())->toBe([$mine->id])
        ->and($ana->receivedTickets->modelKeys())->toBe([$received->id]);
});

it('resolves the updater and comment author independently of the ticket creator', function () {
    $creator = User::factory()->create();
    $reviewer = User::factory()->create();
    $ticket = Ticket::create(['title' => 'Erro', 'description' => 'Detalhes', 'status' => Ticket::STATUS_OPEN, 'ticket_type' => Ticket::TYPE_HELP, 'created_by' => $creator->id, 'responsible_id' => $reviewer->id, 'updated_by' => $reviewer->id]);
    $comment = TicketComment::create(['ticket_id' => $ticket->id, 'created_by' => $reviewer->id, 'description' => 'Em análise', 'comment_type' => TicketComment::TYPE_USER_COMMENT]);

    expect($ticket->updatedBy->is($reviewer))->toBeTrue()
        ->and($comment->createdBy->is($reviewer))->toBeTrue()
        ->and($comment->ticket->is($ticket))->toBeTrue();
});
