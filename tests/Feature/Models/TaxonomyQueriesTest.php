<?php

use App\Models\Resource;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters institutions by active state exact name and selected identifiers', function () {
    $usp = University::create(['name' => 'USP', 'is_active' => true]);
    $inactive = University::create(['name' => 'USP Leste', 'is_active' => false]);
    $other = University::create(['name' => 'Unisinos', 'is_active' => true]);
    $user = User::factory()->create();
    $information = $user->information()->create(['university_id' => $usp->id]);

    expect(University::active()->pluck('id')->all())->toBe([$usp->id, $other->id])
        ->and(University::byName('USP')->pluck('id')->all())->toBe([$usp->id])
        ->and(University::byIds([$inactive->id])->pluck('id')->all())->toBe([$inactive->id])
        ->and(University::byIds([])->count())->toBe(0)
        ->and($usp->userInformations->modelKeys())->toBe([$information->id]);
});

it('filters tags by active state exact name and selected identifiers', function () {
    $active = Tag::create(['name' => 'Dados', 'is_active' => true]);
    $inactive = Tag::create(['name' => 'Dados abertos', 'is_active' => false]);
    expect(Tag::active()->pluck('id')->all())->toBe([$active->id])
        ->and(Tag::byName('Dados')->pluck('id')->all())->toBe([$active->id])
        ->and(Tag::byIds([$inactive->id])->pluck('id')->all())->toBe([$inactive->id])
        ->and(Tag::byIds([])->count())->toBe(0);
});

it('returns only duplicate tag groups and leaves unique tags out', function () {
    $first = Tag::create(['name' => 'Algoritmo']);
    $duplicate = Tag::create(['name' => 'Algoritmos']);
    Tag::create(['name' => 'Privacidade']);

    $groups = Tag::duplicateGroups();
    expect($groups->keys()->all())->toBe(['algoritmo'])
        ->and($groups->get('algoritmo')->modelKeys())->toBe([$first->id, $duplicate->id]);
});

it('prevents deleting a tag used by a subject or resource', function () {
    $tag = Tag::create(['name' => 'Dados']);
    $subject = Subject::create(['name' => 'Ética', 'slug' => 'etica']);
    expect($tag->canBeExcluded())->toBeTrue();
    $tag->subjects()->attach($subject);
    expect($tag->canBeExcluded())->toBeFalse();
    $tag->subjects()->detach($subject);

    $user = User::factory()->create();
    $resource = Resource::create([
        'title' => 'Artigo', 'author' => 'Ana', 'key_words' => 'dados', 'description' => 'Texto',
        'publisher' => 'USP', 'language' => 'pt', 'source' => 'https://example.test/artigo',
        'format_id' => Resource::FORMAT_TEXT, 'subject_id' => $subject->id, 'created_by' => $user->id,
    ]);
    $tag->resources()->attach($resource);
    expect($tag->canBeExcluded())->toBeFalse();
    $tag->resources()->detach($resource);
    expect($tag->canBeExcluded())->toBeTrue();
});
