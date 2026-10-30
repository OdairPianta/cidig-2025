<?php

use App\Filament\Pages\Auth\RequestRegistration;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Subject;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\RegistrationRequested;
use App\Notifications\WelcomeNewUser;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    $this->lead = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    $this->line = Subject::create(['name' => 'Ética e IA', 'slug' => 'etica-e-ia', 'description' => 'x']);
    $this->line->users()->attach($this->lead);
});

it('sends a request for a research line to its responsible', function () {
    Notification::fake();

    Livewire::test(RequestRegistration::class)
        ->fillForm(['name' => 'Ana Lima', 'email' => 'ana@example.com', 'subject_id' => $this->line->id])
        ->call('register')
        ->assertHasNoFormErrors();

    $ticket = Ticket::firstOrFail();
    expect($ticket->ticket_type)->toBe(Ticket::TYPE_REGISTRATION)
        ->and($ticket->responsible_id)->toBe($this->lead->id)
        ->and($ticket->subject_id)->toBe($this->line->id);

    $applicant = User::where('email', 'ana@example.com')->firstOrFail();
    expect($applicant->pending_approval)->toBeTrue()
        ->and($applicant->canAccessPanel(Filament::getCurrentPanel()))->toBeFalse();

    Notification::assertSentTo($this->lead, RegistrationRequested::class);
    $this->assertGuest();
});

it('sends a request without a research line to the admin', function () {
    Notification::fake();

    Livewire::test(RequestRegistration::class)
        ->fillForm(['name' => 'Bia', 'email' => 'bia@example.com'])
        ->call('register');

    expect(Ticket::firstOrFail()->responsible_id)->toBe($this->admin->id);
});

it('lets the responsible authorise the request and the person can then get in', function () {
    Notification::fake();
    Livewire::test(RequestRegistration::class)
        ->fillForm(['name' => 'Ana Lima', 'email' => 'ana@example.com', 'subject_id' => $this->line->id])
        ->call('register');
    $ticket = Ticket::firstOrFail();

    $this->actingAs($this->lead);
    Livewire::test(ViewTicket::class, ['record' => $ticket->getKey()])
        ->assertActionVisible('approve')
        ->callAction('approve');

    $applicant = User::where('email', 'ana@example.com')->firstOrFail();
    expect($applicant->pending_approval)->toBeFalse()
        ->and($applicant->is_active)->toBeTrue()
        ->and($applicant->subjects->pluck('id')->all())->toBe([$this->line->id])
        ->and($ticket->refresh()->status)->toBe(Ticket::STATUS_DONE);
    Notification::assertSentTo($applicant, WelcomeNewUser::class);
});

it('does not let an unrelated professor review a request', function () {
    Notification::fake();
    Livewire::test(RequestRegistration::class)->fillForm(['name' => 'Bia', 'email' => 'bia@example.com'])->call('register');
    $ticket = Ticket::firstOrFail();

    $stranger = User::factory()->create(['is_admin' => false, 'is_active' => true]);

    expect(\App\Support\RegistrationRequests::canReview($stranger, $ticket))->toBeFalse()
        ->and(\App\Support\RegistrationRequests::canReview($this->admin, $ticket))->toBeTrue();
});

it('rejects a request without activating the account', function () {
    Notification::fake();
    Livewire::test(RequestRegistration::class)->fillForm(['name' => 'Bia', 'email' => 'bia@example.com'])->call('register');
    $ticket = Ticket::firstOrFail();

    $this->actingAs($this->admin);
    Livewire::test(ViewTicket::class, ['record' => $ticket->getKey()])->callAction('reject');

    expect($ticket->refresh()->status)->toBe(Ticket::STATUS_CLOSED_NOT_DONE)
        ->and(User::where('email', 'bia@example.com')->first()->pending_approval)->toBeTrue();
});
