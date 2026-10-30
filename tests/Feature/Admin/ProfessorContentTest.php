<?php

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\SerieResource;
use App\Filament\Resources\SerieResource\Pages\CreateSerie;
use App\Models\Post;
use App\Models\Serie;
use App\Models\Subject;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->professor = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    $this->other = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    $this->actingAs($this->professor);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('lets professors open the series and post forms without a 403', function () {
    expect(SerieResource::canAccess())->toBeTrue()
        ->and(PostResource::canAccess())->toBeTrue();

    Livewire::test(CreateSerie::class)->assertSuccessful();
    Livewire::test(CreatePost::class)->assertSuccessful();
});

it('only lists the series and posts a professor owns', function () {
    $mine = Serie::create(['slug' => 'minha', 'title' => 'Minha', 'description' => 'x']);
    $mine->users()->attach($this->professor);
    $theirs = Serie::create(['slug' => 'dela', 'title' => 'Dela', 'description' => 'x']);
    $theirs->users()->attach($this->other);

    Post::create(['user_id' => $this->professor->id, 'title' => 'Meu', 'content' => 'x', 'is_published' => true]);
    Post::create(['user_id' => $this->other->id, 'title' => 'Dele', 'content' => 'x', 'is_published' => true]);

    expect(SerieResource::getEloquentQuery()->pluck('title')->all())->toBe(['Minha'])
        ->and(PostResource::getEloquentQuery()->pluck('title')->all())->toBe(['Meu']);
});

it('names the panel and the contents menu after the professor research line', function () {
    $line = Subject::create(['name' => 'Ética e IA', 'slug' => 'etica-e-ia', 'description' => 'x']);
    $this->professor->subjects()->attach($line);

    expect($this->professor->researchLineName())->toBe('Ética e IA')
        ->and(Filament::getBrandName())->toBe('Ética e IA')
        ->and(\App\Filament\Resources\ResourceResource::getPluralModelLabel())->toBe('Conteúdos de Ética e IA');

    $this->actingAs(User::factory()->create(['is_admin' => true]));
    expect(Filament::getBrandName())->toBe(config('app.name'));
});

it('shows the logged-in person name in the public menu instead of "Admin"', function () {
    $this->professor->update(['name' => 'Maria Souza']);

    $this->get('/')->assertOk()->assertSee('Maria Souza');
});

it('logs in from the public menu panel and goes back to the same page', function () {
    $user = User::factory()->create(['email' => 'prof@example.com', 'password' => bcrypt('segredo123'), 'is_admin' => false]);
    auth()->logout();

    $this->get('/')->assertSee('site-login-panel');

    $this->from('/')->post(route('site.login'), ['email' => 'prof@example.com', 'password' => 'segredo123'])
        ->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

it('shows an error in the panel for wrong credentials and refuses pending sign-ups', function () {
    $pending = User::factory()->create(['email' => 'p@example.com', 'password' => bcrypt('segredo123')]);
    $pending->forceFill(['pending_approval' => true])->save();
    auth()->logout();

    $this->from('/')->post(route('site.login'), ['email' => 'p@example.com', 'password' => 'errada'])
        ->assertRedirect('/')->assertSessionHasErrors('email', null, 'siteLogin');

    $this->from('/')->post(route('site.login'), ['email' => 'p@example.com', 'password' => 'segredo123'])
        ->assertSessionHasErrors('email', null, 'siteLogin');
    $this->assertGuest();
});

it('shows the full résumé in "Sobre o pesquisador" and keeps the mini bio for the card', function () {
    $researcher = User::factory()->create(['name' => 'Maria Souza']);
    $researcher->information()->create([
        'minibio' => 'Resumo curto para o cartão da equipe, com mais de noventa caracteres para passar na validação.',
        'description' => "Currículo completo.\nSegunda linha.",
    ]);

    $this->get('/researcher/maria-souza')
        ->assertOk()
        ->assertSee('Currículo completo.', false)
        ->assertSee('Segunda linha.', false)
        ->assertDontSee('Resumo curto para o cartão');

    $researcher->information->update(['description' => null]);

    $this->get('/researcher/maria-souza')->assertSee('Resumo curto para o cartão');
});

it('edits the profile in a slide-over without leaving the page', function () {
    $university = \App\Models\University::create(['name' => 'USP', 'is_active' => true]);

    \Livewire\Livewire::test(\App\Livewire\ProfileSlideOver::class)
        ->call('open')
        ->assertActionMounted('edit')
        ->callAction('edit', [
            'name' => 'Nova Silva',
            'university_id' => $university->id,
            'roles' => ['professor'],
            'minibio' => 'Resumo',
            'description' => 'Currículo completo',
        ])
        ->assertHasNoActionErrors();

    $this->professor->refresh();
    expect($this->professor->name)->toBe('Nova Silva')
        ->and($this->professor->information->description)->toBe('Currículo completo')
        ->and($this->professor->information->university_id)->toBe($university->id);
});

it('renders the slide-over and a profile menu item that opens it', function () {
    $this->get('/admin')->assertOk()->assertSee('open-profile', false);
});

it('keeps the login panel above the page background image', function () {
    auth()->logout();

    $this->get('/')->assertSee('.site-login-panel { position: relative; z-index: 1030;', false);
});

it('previews the public link under the address field of series and posts', function () {
    $this->professor->update(['name' => 'Maria Souza']);

    \Livewire\Livewire::test(\App\Filament\Resources\SerieResource\Pages\CreateSerie::class)
        ->assertSee('Como o link vai ficar')
        ->assertSee('/webseries/', false)
        ->fillForm(['slug' => 'minha-serie'])
        ->assertSee('minha-serie');

    \Livewire\Livewire::test(\App\Filament\Resources\PostResource\Pages\CreatePost::class)
        ->fillForm(['user_id' => $this->professor->id, 'title' => 'Meu Primeiro Post', 'slug' => null])
        ->assertSee('/maria-souza/', false)
        ->assertSee('meu-primeiro-post')
        ->fillForm(['user_id' => $this->professor->id, 'title' => 'Meu Primeiro Post', 'slug' => 'outro-endereco'])
        ->assertSee('outro-endereco');
});
