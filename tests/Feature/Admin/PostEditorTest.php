<?php

use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true, 'name' => 'Ana Souza']);
    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders markdown with links that open in a new tab', function () {
    $html = Post::renderContent("# Título\n\nVeja [o site](https://exemplo.org) e ![foto](https://exemplo.org/a.png).");

    expect($html)->toContain('<h1>Título</h1>')
        ->and($html)->toContain('href="https://exemplo.org"')
        ->and($html)->toContain('target="_blank"')
        ->and($html)->toContain('<img src="https://exemplo.org/a.png"');
});

it('still renders posts saved as html and strips scripts', function () {
    $html = Post::renderContent('<p>Olá <strong>mundo</strong></p><script>alert(1)</script><a href="javascript:alert(1)">x</a>');

    expect($html)->toContain('<p>Olá <strong>mundo</strong></p>')
        ->and($html)->not->toContain('<script')
        ->and($html)->not->toContain('javascript:');
});

it('keeps youtube and vimeo embeds but drops other iframes', function () {
    $yt = Post::renderContent('<iframe width="560" height="315" src="https://www.youtube.com/embed/8w9NrNSliuI" allowfullscreen></iframe>');
    $bad = Post::renderContent('<iframe src="https://evil.example.com/x"></iframe>');

    expect($yt)->toContain('<iframe')->and($yt)->toContain('youtube.com/embed/8w9NrNSliuI')
        ->and($bad)->not->toContain('evil.example.com');
});

it('creates a post from markdown with several credited authors', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Meu post',
            'content' => "Texto **forte**",
            'is_published' => true,
            'author_names' => ['Ana Souza', 'Bruno Lima; Carla Dias'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('title', 'Meu post')->first();
    expect($post->authors->pluck('name')->all())->toBe(['Ana Souza', 'Bruno Lima', 'Carla Dias'])
        ->and($post->byline)->toBe('Ana Souza; Bruno Lima; Carla Dias')
        ->and($post->slug)->toBe('meu-post');
});

it('loads and updates the credited authors when editing', function () {
    $post = Post::create(['user_id' => $this->admin->id, 'title' => 'P', 'content' => 'x', 'is_published' => true]);
    $post->syncAuthorNames(['Ana Souza']);

    $component = Livewire::test(EditPost::class, ['record' => $post->getRouteKey()]);
    expect($component->get('data.author_names'))->toBe(['Ana Souza']);

    $component->set('data.author_names', ['Bruno Lima'])->call('save')->assertHasNoFormErrors();
    expect($post->fresh()->authors->pluck('name')->all())->toBe(['Bruno Lima']);
});

it('generates a unique slug when two titles collide', function () {
    $a = Post::create(['user_id' => $this->admin->id, 'title' => 'Mesmo título', 'content' => 'x']);
    $b = Post::create(['user_id' => $this->admin->id, 'title' => 'Mesmo título', 'content' => 'x']);

    expect([$a->slug, $b->slug])->toBe(['mesmo-titulo', 'mesmo-titulo-2']);
});

it('only shows scheduled posts once their date has arrived', function () {
    Post::create(['user_id' => $this->admin->id, 'title' => 'Agora', 'content' => 'x', 'is_published' => true, 'published_at' => now()->subMinute()]);
    Post::create(['user_id' => $this->admin->id, 'title' => 'Depois', 'content' => 'x', 'is_published' => true, 'published_at' => now()->addDay()]);

    expect(Post::published()->pluck('title')->all())->toBe(['Agora']);
});

it('serves the public post page with rendered content and byline', function () {
    $post = Post::create(['user_id' => $this->admin->id, 'title' => 'Publicado', 'content' => "Olá [link](https://exemplo.org)", 'is_published' => true, 'published_at' => now()->subMinute()]);
    $post->syncAuthorNames(['Ana Souza', 'Bruno Lima']);

    $this->get(route('post.show', ['ana-souza', $post->slug]))
        ->assertOk()
        ->assertSee('Autoria: Ana Souza; Bruno Lima')
        ->assertSee('href="https://exemplo.org"', false);
});
