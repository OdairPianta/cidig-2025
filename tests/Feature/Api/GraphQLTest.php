<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Nuwave\Lighthouse\Events\EndRequest;
use Nuwave\Lighthouse\Events\StartRequest;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->originalTokenEnv = $_ENV['AI_ACCESS_TOKEN'] ?? null;
    $this->originalTokenServer = $_SERVER['AI_ACCESS_TOKEN'] ?? null;
    $_ENV['AI_ACCESS_TOKEN'] = $_SERVER['AI_ACCESS_TOKEN'] = 'graphql-test-token';
});

afterEach(function () {
    foreach (['originalTokenEnv' => '_ENV', 'originalTokenServer' => '_SERVER'] as $property => $global) {
        if ($this->{$property} === null) {
            unset($GLOBALS[$global]['AI_ACCESS_TOKEN']);
        } else {
            $GLOBALS[$global]['AI_ACCESS_TOKEN'] = $this->{$property};
        }
    }
});

it('rejects an unauthorised GraphQL request before executing it', function (?string $authorization) {
    $headers = $authorization === null ? [] : ['Authorization' => $authorization];

    $this->postJson('/api/graphql', ['query' => '{ __typename }'], $headers)
        ->assertUnauthorized()
        ->assertExactJson(['errors' => [[
            'message' => 'Invalid or missing AI access token',
            'extensions' => ['category' => 'authentication'],
        ]]]);
})->with([
    'missing header' => [null],
    'incorrect token' => ['Bearer wrong-token'],
    'empty bearer' => ['Bearer '],
    'incorrect authentication scheme' => ['Basic graphql-test-token'],
]);

it('refuses access when the server has no configured token', function () {
    $_ENV['AI_ACCESS_TOKEN'] = $_SERVER['AI_ACCESS_TOKEN'] = '';

    $this->withToken('graphql-test-token')->postJson('/api/graphql', ['query' => '{ __typename }'])
        ->assertUnauthorized();
});

it('executes an authenticated query and dispatches its lifecycle events', function () {
    Event::fake([StartRequest::class, EndRequest::class]);
    $user = User::factory()->create(['name' => 'Ana Silva', 'email' => 'ana@example.test']);

    $this->withToken('graphql-test-token')->postJson('/api/graphql', [
        'query' => 'query ($id: ID!) { user(id: $id) { id name email } }',
        'variables' => ['id' => (string) $user->id],
    ])->assertOk()->assertExactJson(['data' => ['user' => [
        'id' => (string) $user->id, 'name' => 'Ana Silva', 'email' => 'ana@example.test',
    ]]]);

    Event::assertDispatched(StartRequest::class);
    Event::assertDispatched(EndRequest::class);
});

it('filters and paginates users without returning unrelated accounts', function () {
    User::factory()->create(['name' => 'Ana Lima']);
    User::factory()->create(['name' => 'Ana Souza']);
    User::factory()->create(['name' => 'Bruno Lima']);

    $this->withToken('graphql-test-token')->postJson('/api/graphql', [
        'query' => '{ users(name: "Ana%", first: 1) { data { name } paginatorInfo { total count hasMorePages } } }',
    ])->assertOk()->assertJsonMissingPath('errors')
        ->assertJsonCount(1, 'data.users.data')
        ->assertJsonPath('data.users.data.0.name', fn ($name) => str_starts_with($name, 'Ana'))
        ->assertJsonPath('data.users.paginatorInfo.total', 2)
        ->assertJsonPath('data.users.paginatorInfo.count', 1)
        ->assertJsonPath('data.users.paginatorInfo.hasMorePages', true);
});

it('returns a subject by slug and null for a missing subject', function () {
    Subject::create(['name' => 'Ética digital', 'slug' => 'etica-digital']);

    $this->withToken('graphql-test-token')->postJson('/api/graphql', [
        'query' => '{ found: subject(slug: "etica-digital") { name slug } missing: subject(slug: "missing") { id } }',
    ])->assertOk()->assertExactJson(['data' => [
        'found' => ['name' => 'Ética digital', 'slug' => 'etica-digital'], 'missing' => null,
    ]]);
});

it('reports invalid GraphQL input without exposing account data', function (string $query) {
    $response = $this->withToken('graphql-test-token')->postJson('/api/graphql', ['query' => $query]);

    $response->assertOk()->assertJsonStructure(['errors' => [['message']]]);
    expect($response->json('data.user'))->toBeNull();
})->with([
    'invalid email argument' => ['{ user(email: "invalid-email") { id email } }'],
    'mutually exclusive arguments' => ['{ user(id: "1", email: "ana@example.test") { id } }'],
    'unknown field' => ['{ fieldThatDoesNotExist }'],
    'syntax error' => ['{ user('],
]);
