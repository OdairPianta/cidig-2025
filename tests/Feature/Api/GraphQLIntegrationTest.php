<?php

use App\Models\Author;
use App\Models\MapProject;
use App\Models\Resource;
use App\Models\Serie;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tokenEnv = $_ENV['AI_ACCESS_TOKEN'] ?? null;
    $this->tokenServer = $_SERVER['AI_ACCESS_TOKEN'] ?? null;
    $_ENV['AI_ACCESS_TOKEN'] = $_SERVER['AI_ACCESS_TOKEN'] = 'integration-test-token';
});

afterEach(function () {
    foreach (['tokenEnv' => '_ENV', 'tokenServer' => '_SERVER'] as $property => $global) {
        if ($this->{$property} === null) {
            unset($GLOBALS[$global]['AI_ACCESS_TOKEN']);
        } else {
            $GLOBALS[$global]['AI_ACCESS_TOKEN'] = $this->{$property};
        }
    }
});

it('requires the integration token on both GraphQL endpoints', function (string $endpoint) {
    $this->postJson($endpoint, ['query' => '{ __typename }'])->assertUnauthorized();
    $this->withToken('wrong')->postJson($endpoint, ['query' => '{ __typename }'])->assertUnauthorized();
    $this->withToken('integration-test-token')->postJson($endpoint, ['query' => '{ __typename }'])
        ->assertOk()->assertExactJson(['data' => ['__typename' => 'Query']]);
})->with(['/api/graphql', '/graphql']);

it('exposes editorial authors and filters publication before pagination', function () {
    $subject = Subject::create(['name' => 'Digital', 'slug' => 'digital']);
    $creator = User::factory()->create(['name' => 'Editor']);
    $fields = [
        'created_by' => $creator->id, 'subject_id' => $subject->id,
        'title' => 'Published', 'description' => 'Description', 'source' => 'https://example.test/content',
        'author' => 'Ana', 'publisher' => 'Publisher', 'language' => 'pt-BR', 'format_id' => 1,
        'copy_rights' => 'CC BY', 'contributor' => 'Bruno', 'coverage' => 'Brasil',
        'original_date' => '2026-10-01', 'format' => 'PDF', 'identifier' => 'doi:test',
        'published_at' => '2026-11-01 12:00:00',
    ];
    $published = Resource::create($fields);
    $published->authors()->attach(Author::create(['name' => 'Ana'])->id);
    $draft = Resource::create(array_merge($fields, ['title' => 'Draft', 'published_at' => null]));
    $deleted = Resource::create($fields);
    $deleted->delete();

    $this->withToken('integration-test-token')->postJson('/api/graphql', ['query' => '
        { resources(is_published: true, first: 1) {
            data { id published_at is_published author authors { name } user { name }
                publisher language copy_rights contributor coverage original_date format format_id identifier source_url }
            paginatorInfo { total hasMorePages }
        } drafts: resources(is_published: false, first: 10) { data { id is_published } paginatorInfo { total } }
          all: resources(first: 10) { paginatorInfo { total } }
          hidden: resource(id: "'.$draft->id.'", is_published: true) { id }
        }'])
        ->assertOk()->assertJsonMissingPath('errors')
        ->assertJsonPath('data.resources.paginatorInfo.total', 1)
        ->assertJsonPath('data.resources.paginatorInfo.hasMorePages', false)
        ->assertJsonPath('data.resources.data.0.id', (string) $published->id)
        ->assertJsonPath('data.resources.data.0.is_published', true)
        ->assertJsonPath('data.resources.data.0.authors.0.name', 'Ana')
        ->assertJsonPath('data.resources.data.0.user.name', 'Editor')
        ->assertJsonPath('data.resources.data.0.copy_rights', 'CC BY')
        ->assertJsonPath('data.resources.data.0.source_url', $fields['source'])
        ->assertJsonPath('data.resources.data.0.original_date', '2026-10-01')
        ->assertJsonPath('data.drafts.data.0.id', (string) $draft->id)
        ->assertJsonPath('data.drafts.data.0.is_published', false)
        ->assertJsonPath('data.all.paginatorInfo.total', 2)
        ->assertJsonPath('data.hidden', null);
});

it('keeps ordered episode metadata specific to each series and filters inactive videos', function () {
    $serie = Serie::create(['title' => 'Series', 'slug' => 'series', 'description' => 'Description', 'is_active' => true]);
    $other = Serie::create(['title' => 'Other', 'slug' => 'other', 'description' => 'Description', 'is_active' => true]);
    $first = Video::create(['video_id' => 'first', 'title' => 'Global title', 'source_year' => 2026, 'is_active' => true]);
    $second = Video::create(['video_id' => 'second', 'is_active' => true]);
    $inactive = Video::create(['video_id' => 'inactive', 'is_active' => false]);
    $serie->videos()->attach($second, ['position' => 2, 'title' => 'Episode two']);
    $serie->videos()->attach($first, ['position' => 1, 'title' => 'Episode one', 'description' => 'Local description']);
    $serie->videos()->attach($inactive, ['position' => 3]);
    $other->videos()->attach($first, ['position' => 8, 'title' => 'Other title']);

    $this->withToken('integration-test-token')->postJson('/api/graphql', ['query' => '{
        serie(id: "'.$serie->id.'") { videos(is_active: true) { video_id title source_year episode { title description position } } }
        other: serie(id: "'.$other->id.'") { videos { episode { title position } } }
        video(id: "'.$first->id.'") { episode { position } }
    }'])->assertOk()->assertJsonMissingPath('errors')
        ->assertJsonCount(2, 'data.serie.videos')
        ->assertJsonPath('data.serie.videos.0.video_id', 'first')
        ->assertJsonPath('data.serie.videos.0.title', 'Global title')
        ->assertJsonPath('data.serie.videos.0.source_year', 2026)
        ->assertJsonPath('data.serie.videos.0.episode.title', 'Episode one')
        ->assertJsonPath('data.serie.videos.0.episode.position', 1)
        ->assertJsonPath('data.serie.videos.1.episode.position', 2)
        ->assertJsonPath('data.other.videos.0.episode.title', 'Other title')
        ->assertJsonPath('data.other.videos.0.episode.position', 8)
        ->assertJsonPath('data.video.episode', null);
});

it('paginates only approved geolocated projects and uses the sanitized public projection', function () {
    $project = MapProject::create([
        'external_key' => 'public', 'name' => 'Public project', 'latitude' => -23.5, 'longitude' => -46.6,
        'is_approved' => true, 'themes' => ['Education'], 'website' => 'javascript:alert(1)',
        'instagram' => 'https://example.test/profile', 'app_url' => 'https://example.test/app',
        'has_own_app' => false, 'mapping' => 'Private working notes',
    ]);
    MapProject::create(['external_key' => 'draft', 'name' => 'Unapproved', 'latitude' => 1, 'longitude' => 1]);
    MapProject::create(['external_key' => 'no-coordinates', 'name' => 'No coordinates', 'is_approved' => true]);

    $this->withToken('integration-test-token')->postJson('/api/graphql', ['query' => '{
        mapProjects(first: 1) { data { id name lat lng themes tics website instagram app created_at updated_at }
            paginatorInfo { total hasMorePages } }
    }'])->assertOk()->assertJsonMissingPath('errors')
        ->assertJsonPath('data.mapProjects.paginatorInfo.total', 1)
        ->assertJsonPath('data.mapProjects.data.0.id', (string) $project->id)
        ->assertJsonPath('data.mapProjects.data.0.lat', -23.5)
        ->assertJsonPath('data.mapProjects.data.0.themes', ['Education'])
        ->assertJsonPath('data.mapProjects.data.0.tics', [])
        ->assertJsonPath('data.mapProjects.data.0.website', null)
        ->assertJsonPath('data.mapProjects.data.0.instagram', 'https://example.test/profile')
        ->assertJsonPath('data.mapProjects.data.0.app', null);

    $this->withToken('integration-test-token')->postJson('/api/graphql', ['query' => '{ mapProjects { data { mapping external_key } } }'])
        ->assertJsonStructure(['errors' => [['message']]]);
});
