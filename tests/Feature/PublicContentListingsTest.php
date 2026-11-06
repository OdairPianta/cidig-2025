<?php

use App\Models\Post;
use App\Models\Serie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only active series on both public listing routes', function (string $path) {
    $active = Serie::create(['title' => 'Série pública', 'slug' => 'publica', 'description' => 'Debates', 'is_active' => true]);
    Serie::create(['title' => 'Série desativada', 'slug' => 'inativa', 'description' => 'Debates', 'is_active' => false]);

    $this->get($path)->assertOk()->assertViewIs('v2.series.index')
        ->assertViewHas('series', fn ($series) => $series->modelKeys() === [$active->id]);
})->with(['/series', '/webseries']);

it('lists only published posts in descending publication order', function () {
    $user = User::factory()->create(['name' => 'Ana Souza']);
    $base = ['user_id' => $user->id, 'content' => 'Texto', 'is_published' => true];
    $older = Post::create(['title' => 'Antigo', 'published_at' => now()->subDays(2)] + $base);
    $recent = Post::create(['title' => 'Recente', 'published_at' => now()->subDay()] + $base);
    Post::create(['title' => 'Rascunho', 'is_published' => false] + $base);
    Post::create(['title' => 'Agendado', 'published_at' => now()->addDay()] + $base);
    Post::create(['title' => 'Arquivado', 'archived_at' => now()] + $base);

    $this->get('/posts')->assertOk()->assertViewIs('posts.index')
        ->assertViewHas('posts', fn ($posts) => $posts->getCollection()->modelKeys() === [$recent->id, $older->id]);
});

it('restricts an authors post listing to that authors published posts', function () {
    $ana = User::factory()->create(['name' => 'Ana Souza']);
    $bia = User::factory()->create(['name' => 'Bia Lima']);
    $mine = Post::create(['title' => 'Da Ana', 'content' => 'Texto', 'user_id' => $ana->id, 'is_published' => true]);
    Post::create(['title' => 'Da Bia', 'content' => 'Texto', 'user_id' => $bia->id, 'is_published' => true]);
    Post::create(['title' => 'Rascunho', 'content' => 'Texto', 'user_id' => $ana->id, 'is_published' => false]);

    $this->get('/ana-souza/posts')->assertOk()->assertViewIs('posts.user-posts')
        ->assertViewHas('user', fn ($user) => $user->is($ana))
        ->assertViewHas('posts', fn ($posts) => $posts->getCollection()->modelKeys() === [$mine->id]);
    $this->get('/unknown-author/posts')->assertNotFound();
});
