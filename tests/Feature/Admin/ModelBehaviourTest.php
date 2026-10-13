<?php

use App\Models\Tag;
use App\Models\University;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('extracts the youtube id from urls and bare ids', function (string $input) {
    expect(Video::extractYoutubeId($input))->toBe('8w9NrNSliuI');
})->with([
    'bare id' => '8w9NrNSliuI',
    'watch url' => 'https://www.youtube.com/watch?v=8w9NrNSliuI',
    'watch url with params' => 'https://www.youtube.com/watch?feature=share&v=8w9NrNSliuI&t=10s',
    'short url' => 'https://youtu.be/8w9NrNSliuI?si=abc',
    'embed url' => 'https://www.youtube.com/embed/8w9NrNSliuI',
]);

it('rejects invalid youtube input', function () {
    expect(Video::extractYoutubeId('not a video'))->toBeNull();
});

it('stores only the video id when a url is saved', function () {
    $video = Video::create(['video_id' => 'https://youtu.be/8w9NrNSliuI']);

    expect($video->fresh()->video_id)->toBe('8w9NrNSliuI');
});

it('detects duplicated tags ignoring case, accents and plurals', function () {
    $tag = Tag::create(['name' => 'Algoritmos']);

    expect(Tag::findDuplicateOf('algoritmo')?->id)->toBe($tag->id)
        ->and(Tag::findDuplicateOf('ALGORÍTMOS')?->id)->toBe($tag->id)
        ->and(Tag::findDuplicateOf('Blockchain'))->toBeNull();
});

it('reuses an existing tag in findOrCreate and is safe against quotes', function () {
    $tag = Tag::create(['name' => 'Dados abertos']);

    expect(Tag::findOrCreate(['name' => 'dado aberto'])->id)->toBe($tag->id);
    expect(Tag::findOrCreate(['name' => "D'Angelo' OR '1'='1"]))->not->toBeNull();
    expect(Tag::count())->toBe(2);
});

it('merges duplicate tags and keeps pivot relations', function () {
    $keep = Tag::create(['name' => 'Algoritmo']);
    $dup = Tag::create(['name' => 'Algoritmos']);

    $subjectId = \DB::table('subjects')->insertGetId(['name' => 'S', 'slug' => 's', 'created_at' => now(), 'updated_at' => now()]);
    \DB::table('subject_tag')->insert([['subject_id' => $subjectId, 'tag_id' => $dup->id]]);

    expect($keep->mergeFrom([$dup]))->toBe(1);
    expect(Tag::find($dup->id))->toBeNull();
    expect(\DB::table('subject_tag')->where('tag_id', $keep->id)->count())->toBe(1);
});

it('finds institutions by name or alias regardless of language spelling', function () {
    $university = University::create([
        'name' => 'Universidade Politécnica de Turim',
        'aliases' => ['Politecnico di Torino'],
        'is_active' => true,
    ]);

    expect(University::findByName('universidade politecnica de turim')?->id)->toBe($university->id)
        ->and(University::findByName('Politecnico di Torino')?->id)->toBe($university->id)
        ->and(University::findOrCreateByName('POLITECNICO DI TORINO')->id)->toBe($university->id)
        ->and(University::count())->toBe(1);
});

it('suggests similar institutions', function () {
    University::create(['name' => 'Universidade de São Paulo', 'is_active' => true]);

    expect(University::similarTo('Universidade de Sao Paulo (USP)')->pluck('name')->all())
        ->toContain('Universidade de São Paulo');
});
