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
