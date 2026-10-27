<?php

use App\Filament\Resources\SerieResource\Pages\CreateSerie;
use App\Filament\Resources\SerieResource\Pages\EditSerie;
use App\Models\Serie;
use App\Models\User;
use App\Models\Video;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('creates a series with ordered video blocks from links or ids', function () {
    Livewire::test(CreateSerie::class)
        ->fillForm([
            'title' => 'Masterclass',
            'slug' => 'masterclass',
            'description' => 'Série',
            'episodes' => [
                ['video' => 'https://www.youtube.com/watch?v=8w9NrNSliuI', 'title' => 'Aula 1', 'description' => 'Com a profa. Ana'],
                ['video' => 'dQw4w9WgXcQ', 'title' => 'Aula 2', 'description' => null],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $videos = Serie::where('slug', 'masterclass')->first()->videos;
    expect($videos->pluck('video_id')->all())->toBe(['8w9NrNSliuI', 'dQw4w9WgXcQ'])
        ->and($videos->first()->pivot->title)->toBe('Aula 1')
        ->and($videos->first()->pivot->description)->toBe('Com a profa. Ana');
});

it('reuses a video that already exists instead of failing on the unique key', function () {
    Video::create(['video_id' => '8w9NrNSliuI', 'title' => 'Existente']);

    Livewire::test(CreateSerie::class)
        ->fillForm([
            'title' => 'S', 'slug' => 's', 'description' => 'x',
            'episodes' => [['video' => '8w9NrNSliuI', 'title' => 'Aula', 'description' => null]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Video::where('video_id', '8w9NrNSliuI')->count())->toBe(1)
        ->and(Serie::where('slug', 's')->first()->videos)->toHaveCount(1);
});

it('requires a title and a valid link for each block', function () {
    Livewire::test(CreateSerie::class)
        ->fillForm([
            'title' => 'S', 'slug' => 's', 'description' => 'x',
            'episodes' => [['video' => 'not a video', 'title' => '', 'description' => null]],
        ])
        ->call('create')
        ->assertHasFormErrors();
});

it('loads, reorders and removes blocks when editing', function () {
    $serie = Serie::create(['slug' => 's', 'title' => 'S', 'description' => 'x']);
    $a = Video::create(['video_id' => 'aaaaaaaaaaa', 'title' => 'A']);
    $b = Video::create(['video_id' => 'bbbbbbbbbbb', 'title' => 'B']);
    $serie->videos()->attach([$a->id => ['title' => 'Primeiro', 'position' => 1], $b->id => ['title' => 'Segundo', 'position' => 2]]);

    $component = Livewire::test(EditSerie::class, ['record' => $serie->getRouteKey()]);
    expect(collect($component->get('data.episodes'))->pluck('title')->values()->all())->toBe(['Primeiro', 'Segundo']);

    // fillForm() merges into the existing items, so replace the whole list to simulate removing the first block.
    $component->set('data.episodes', [
        'only' => ['video' => 'bbbbbbbbbbb', 'title' => 'Segundo', 'description' => null],
    ])->call('save')->assertHasNoFormErrors();

    expect($serie->fresh()->videos->pluck('video_id')->all())->toBe(['bbbbbbbbbbb']);
});

it('shows the block title and description on the public series page', function () {
    $serie = Serie::create(['slug' => 'pub', 'title' => 'Pub', 'description' => 'x', 'is_active' => true]);
    $video = Video::create(['video_id' => 'aaaaaaaaaaa', 'title' => 'Título do vídeo']);
    $serie->videos()->attach($video->id, ['title' => 'Título no bloco', 'description' => 'Descrição no bloco', 'position' => 1]);

    $this->get(route('v2.series.show', 'pub'))
        ->assertOk()
        ->assertSee('Título no bloco')
        ->assertSee('Descrição no bloco');
});
