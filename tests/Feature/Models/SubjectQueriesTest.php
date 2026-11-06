<?php

use App\Models\Resource;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents exclusion of a subject with assigned users or resources', function () {
    $subject = Subject::create(['name' => 'Ética', 'slug' => 'etica']);
    $user = User::factory()->create();
    expect($subject->canBeExcluded())->toBeTrue();

    $subject->users()->attach($user);
    expect($subject->canBeExcluded())->toBeFalse();
    $subject->users()->detach($user);

    $resource = Resource::create(['title' => 'Artigo', 'subject_id' => $subject->id, 'created_by' => $user->id, 'author' => 'Ana', 'key_words' => 'ética', 'description' => 'Artigo', 'publisher' => 'USP', 'language' => 'pt', 'source' => 'https://example.test/artigo', 'format_id' => Resource::FORMAT_TEXT]);
    expect($subject->canBeExcluded())->toBeFalse();
    $resource->delete();
    expect($subject->canBeExcluded())->toBeTrue();
});

it('resolves subject tags and videos without including another subjects videos', function () {
    $subject = Subject::create(['name' => 'Ética', 'slug' => 'etica']);
    $other = Subject::create(['name' => 'Dados', 'slug' => 'dados']);
    $tag = Tag::create(['name' => 'Cidadania']);
    $unrelated = Tag::create(['name' => 'Saúde']);
    $subject->tags()->attach($tag);
    $video = Video::create(['video_id' => 'aaaaaaaaaaa', 'subject_id' => $subject->id]);
    Video::create(['video_id' => 'bbbbbbbbbbb', 'subject_id' => $other->id]);

    expect($subject->isTagAssociated($tag->id))->toBeTrue()
        ->and($subject->isTagAssociated($unrelated->id))->toBeFalse()
        ->and($subject->videos->modelKeys())->toBe([$video->id]);
});
