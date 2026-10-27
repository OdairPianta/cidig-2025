<?php

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use App\Models\UserInformation;
use App\Support\PasswordRecovery;
use App\Support\ProfileLinks;
use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('cleans pasted Lattes values down to the canonical url', function () {
    expect(ProfileLinks::lattes('http://lattes.cnpq.br/1234567890123456'))->toBe('http://lattes.cnpq.br/1234567890123456')
        ->and(ProfileLinks::lattes(' 1234567890123456/ '))->toBe('http://lattes.cnpq.br/1234567890123456')
        ->and(ProfileLinks::lattes('https://lattes.cnpq.br/1234567890123456?utm=x'))->toBe('http://lattes.cnpq.br/1234567890123456')
        ->and(ProfileLinks::lattes('12345'))->toBeNull()
        ->and(ProfileLinks::lattes('maria'))->toBeNull();
});

it('cleans pasted ORCID values down to the canonical url', function () {
    expect(ProfileLinks::orcid('https://orcid.org/0000-0002-1825-009X/'))->toBe('https://orcid.org/0000-0002-1825-009X')
        ->and(ProfileLinks::orcid('0000000218250097'))->toBe('https://orcid.org/0000-0002-1825-0097')
        ->and(ProfileLinks::orcid('0000-0002-1825-009x'))->toBe('https://orcid.org/0000-0002-1825-009X')
        ->and(ProfileLinks::orcid('1234'))->toBeNull();
});

it('accepts LinkedIn urls and bare handles with letters only', function () {
    expect(ProfileLinks::linkedin('https://br.linkedin.com/in/maria-silva-123/?trk=x'))->toBe('https://www.linkedin.com/in/maria-silva-123')
        ->and(ProfileLinks::linkedin('maria-silva'))->toBe('https://www.linkedin.com/in/maria-silva')
        ->and(ProfileLinks::linkedin('1234567890123456'))->toBeNull()
        ->and(ProfileLinks::linkedin('ab'))->toBeNull();
});

it('saves normalised identifiers and the recovery email from the admin user form', function () {
    $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'lattes_url' => '1234567890123456',
            'orcid_url' => '0000000218250097',
            'linkedin_url' => 'maria-silva',
            'recovery_email' => 'maria@pessoal.com',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $info = $user->fresh()->information;
    expect($info->lattes_url)->toBe('http://lattes.cnpq.br/1234567890123456')
        ->and($info->orcid_url)->toBe('https://orcid.org/0000-0002-1825-0097')
        ->and($info->linkedin_url)->toBe('https://www.linkedin.com/in/maria-silva')
        ->and($info->recovery_email)->toBe('maria@pessoal.com');
});

it('shows a specific error for each invalid identifier', function () {
    $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['lattes_url' => '123', 'orcid_url' => '999', 'linkedin_url' => '12345678'])
        ->call('save')
        ->assertHasFormErrors(['lattes_url', 'orcid_url', 'linkedin_url']);
});

it('finds the account by login or recovery email', function () {
    $user = User::factory()->create(['email' => 'prof@usp.br']);
    UserInformation::create(['user_id' => $user->id, 'recovery_email' => 'Prof@Gmail.com']);

    expect(PasswordRecovery::accountEmailFor('PROF@usp.br'))->toBe('prof@usp.br')
        ->and(PasswordRecovery::accountEmailFor('prof@gmail.com'))->toBe('prof@usp.br')
        ->and(PasswordRecovery::accountEmailFor('ninguem@x.com'))->toBeNull();
});

it('sends the reset link to both addresses', function () {
    $user = User::factory()->create(['email' => 'prof@usp.br']);
    UserInformation::create(['user_id' => $user->id, 'recovery_email' => 'prof@gmail.com']);

    expect($user->fresh()->routeNotificationForMail(new ResetPassword('t')))->toBe(['prof@usp.br', 'prof@gmail.com'])
        ->and($user->fresh()->routeNotificationForMail())->toBe('prof@usp.br');
});

it('tells the visitor when the password reset email does not exist', function () {
    Notification::fake();
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(\App\Filament\Pages\Auth\RequestPasswordReset::class)
        ->fillForm(['email' => 'ninguem@example.com'])
        ->call('request');

    Notification::assertNothingSent();
    expect(collect(session('filament.notifications', []))->pluck('title')->all())->toContain(PasswordRecovery::NOT_FOUND);
});

it('sends a reset link when the recovery email is typed', function () {
    Notification::fake();
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create(['email' => 'prof@usp.br']);
    UserInformation::create(['user_id' => $user->id, 'recovery_email' => 'prof@gmail.com']);

    Livewire::test(\App\Filament\Pages\Auth\RequestPasswordReset::class)
        ->fillForm(['email' => 'prof@gmail.com'])
        ->call('request');

    Notification::assertSentTo($user, \Filament\Notifications\Auth\ResetPassword::class);
});

it('stores the profile data when an admin creates a user', function () {
    Notification::fake();
    $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Maria Silva',
            'email' => 'maria@usp.br',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'roles' => ['professor'],
            'lattes_url' => '1234567890123456',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $info = User::where('email', 'maria@usp.br')->first()->information;
    expect($info->roles)->toBe(['professor'])
        ->and($info->lattes_url)->toBe('http://lattes.cnpq.br/1234567890123456');
});
