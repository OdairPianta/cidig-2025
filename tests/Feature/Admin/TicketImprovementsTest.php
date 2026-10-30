<?php

use App\Filament\Resources\TicketResource\Pages\CreateTicket;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

function makeTicket(string $title, int $status, string $createdAt = 'now'): Ticket
{
    $ticket = Ticket::create(['ticket_type' => 1, 'title' => $title, 'description' => 'x', 'status' => $status, 'created_by' => test()->admin->id, 'responsible_id' => test()->admin->id]);
    $ticket->forceFill(['created_at' => $createdAt])->save();

    return $ticket;
}

it('lists tickets by status priority: open, in progress, closed without a fix, then resolved', function () {
    $resolved = makeTicket('Resolvido', Ticket::STATUS_DONE);
    $closed = makeTicket('Fechado', Ticket::STATUS_CLOSED_NOT_DONE);
    $progress = makeTicket('Andamento', Ticket::STATUS_IN_PROGRESS);
    $open = makeTicket('Aberto', Ticket::STATUS_OPEN);

    Livewire::test(ListTickets::class)
        ->assertCanSeeTableRecords([$open, $progress, $closed, $resolved], inOrder: true);
});

it('can order the list by creation date', function () {
    $old = makeTicket('Antigo', Ticket::STATUS_OPEN, '2026-01-01');
    $new = makeTicket('Novo', Ticket::STATUS_DONE, '2026-06-01');

    Livewire::test(ListTickets::class)
        ->filterTable('order', ['order_by' => 'created_desc'])
        ->assertCanSeeTableRecords([$new, $old], inOrder: true);
});

it('opens a ticket in a pop-up from the list', function () {
    $ticket = makeTicket('Com contexto', Ticket::STATUS_OPEN);
    $ticket->update(['page_url' => 'https://cidig.test/linha/ia', 'description' => '**Corrigir** o título']);

    $component = Livewire::test(ListTickets::class);
    $table = $component->instance()->getTable();

    expect($table->getAction('view')->record($ticket)->getUrl())->toBeNull()
        ->and($table->getRecordUrl($ticket))->toBeNull()
        ->and($table->getRecordAction($ticket))->toBe('view');

    $component->callTableAction('view', $ticket)->assertSuccessful();
});

it('saves where the problem is with the ticket', function () {
    Livewire::test(CreateTicket::class)
        ->fillForm([
            'ticket_type' => 1, 'status' => 0, 'responsible_id' => $this->admin->id,
            'title' => 'Botão quebrado', 'description' => 'Veja a **imagem**',
            'page_url' => 'https://cidig.test/blog/post',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Ticket::firstOrFail()->page_url)->toBe('https://cidig.test/blog/post');
});

it('records the page a ticket was reported from on the public site', function () {
    $this->post(route('bugReport'), ['title' => 'Erro', 'description' => 'x', 'page_url' => 'https://cidig.test/sobre'])->assertRedirect();
    $this->post(route('helpRequest'), ['title' => 'Dúvida', 'description' => 'x', 'page_url' => 'javascript:alert(1)'])->assertRedirect();

    expect(Ticket::where('title', 'Erro')->first()->page_url)->toBe('https://cidig.test/sobre')
        ->and(Ticket::where('title', 'Dúvida')->first()->page_url)->toBeNull();
});

it('actually reorders the user and research line lists with "Ordenar por"', function () {
    $old = User::factory()->create(['name' => 'Ana', 'created_at' => '2026-01-01']);
    $new = User::factory()->create(['name' => 'Zeca', 'created_at' => '2026-06-01']);

    Livewire::test(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
        ->filterTable('order', ['order_by' => 'created_desc'])
        ->assertCanSeeTableRecords([$new, $old], inOrder: true);

    $a = \App\Models\Subject::create(['name' => 'Alfa', 'slug' => 'alfa']);
    $a->forceFill(['created_at' => '2026-01-01'])->save();
    $z = \App\Models\Subject::create(['name' => 'Zeta', 'slug' => 'zeta']);
    $z->forceFill(['created_at' => '2026-06-01'])->save();

    Livewire::test(\App\Filament\Resources\SubjectResource\Pages\ListSubjects::class)
        ->filterTable('order', ['order_by' => 'created_desc'])
        ->assertCanSeeTableRecords([$z, $a], inOrder: true);
});
