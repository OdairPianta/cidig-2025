<?php

use App\Filament\Pages\TagAnalytics;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\ViewPost;
use App\Filament\Resources\SerieResource\Pages\ViewSerie;
use App\Filament\Resources\SponsorResource\Pages\ViewSponsor;
use App\Filament\Resources\SubjectResource\Pages\ViewSubject;
use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Filament\Resources\UniversityResource\Pages\CreateUniversity;
use App\Filament\Resources\UniversityResource\Pages\ViewUniversity;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\Post;
use App\Models\Serie;
use App\Models\Sponsor;
use App\Models\Subject;
use App\Models\Tag;
use App\Models\University;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
    $this->actingAs($this->admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('renders the view pages that used to be empty', function () {
    $user = User::factory()->create();
    $serie = Serie::create(['slug' => 'minha-serie', 'title' => 'Minha série', 'description' => 'x']);
    $subject = Subject::create(['name' => 'Verbete', 'slug' => 'verbete']);
    $university = University::create(['name' => 'USP', 'is_active' => true]);
    $sponsor = Sponsor::create(['name' => 'Patrocinador']);
    $post = Post::create(['user_id' => $user->id, 'title' => 'Olá mundo', 'content' => '<p>corpo</p>', 'is_published' => true]);

    Livewire::test(ViewUser::class, ['record' => $user->getKey()])->assertSuccessful();
    Livewire::test(ViewSerie::class, ['record' => $serie->getKey()])->assertSuccessful();
    Livewire::test(ViewSubject::class, ['record' => $subject->getKey()])->assertSuccessful();
    Livewire::test(ViewUniversity::class, ['record' => $university->getKey()])->assertSuccessful();
    Livewire::test(ViewSponsor::class, ['record' => $sponsor->getKey()])->assertSuccessful();
    Livewire::test(ViewPost::class, ['record' => $post->getKey()])->assertSuccessful()->assertSee('corpo');

    expect(PostResource::getUrl('view', ['record' => $post]))->toContain('/posts/');
});

it('soft deletes a user instead of failing on foreign keys', function () {
    $user = User::factory()->create();
    $user->delete();

    expect(User::find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull();
});

it('archived posts disappear from the public blog', function () {
    $author = User::factory()->create();
    $post = Post::create(['user_id' => $author->id, 'title' => 'Teste', 'content' => 'x', 'is_published' => true]);

    expect(Post::published()->count())->toBe(1);

    $post->update(['archived_at' => now()]);

    expect(Post::published()->count())->toBe(0);
});

it('rejects an institution that already exists with a different spelling', function () {
    University::create(['name' => 'Universidade de São Paulo', 'is_active' => true]);

    Livewire::test(CreateUniversity::class)
        ->fillForm(['name' => 'universidade de sao paulo'])
        ->call('create')
        ->assertHasFormErrors(['name']);
});

it('lists tags and renders the analytics page', function () {
    Tag::create(['name' => 'Algoritmo']);
    Tag::create(['name' => 'Algoritmos']);

    Livewire::test(ListTags::class)->assertSuccessful();
    Livewire::test(TagAnalytics::class)->assertSuccessful()->assertSee('Algoritmo');
});

it('lists users with the sort filter', function () {
    Livewire::test(ListUsers::class)->assertSuccessful();
});

it('sanitises the post body but keeps links and images', function () {
    $author = User::factory()->create();
    $post = Post::create([
        'user_id' => $author->id,
        'title' => 'Seguro',
        'content' => '<p onclick="x()">oi <a href="https://example.com">link</a></p><img src="/storage/a.png"><script>alert(1)</script>',
        'is_published' => true,
    ]);

    expect($post->safe_content)
        ->toContain('href="https://example.com"')
        ->toContain('target="_blank"')
        ->toContain('<img src="/storage/a.png"')
        ->not->toContain('<script')
        ->not->toContain('onclick');
});

it('lets an admin pick any verbete when creating content', function () {
    Subject::create(['name' => 'Verbete A', 'slug' => 'a']);
    Subject::create(['name' => 'Verbete B', 'slug' => 'b']);

    Livewire::test(\App\Filament\Resources\ResourceResource\Pages\CreateResource::class)
        ->assertFormFieldExists('subject_id', fn ($field) => $field->getOptions() === Subject::orderBy('name')->pluck('name', 'id')->all());
});

it('filters and sorts verbetes in the simplified list', function () {
    Subject::create(['name' => 'Zeta', 'slug' => 'zeta']);
    Subject::create(['name' => 'Alfa', 'slug' => 'alfa']);

    Livewire::test(\App\Filament\Resources\SubjectResource\Pages\ListSubjects::class)
        ->assertSuccessful()
        ->filterTable('order', ['order_by' => 'content_desc'])
        ->assertSuccessful()
        ->filterTable('order', ['order_by' => 'posts_desc'])
        ->assertSuccessful();
});

it('builds a clean excerpt without html entities', function () {
    $author = User::factory()->create();
    $post = Post::create(['user_id' => $author->id, 'title' => 'T', 'content' => '<p>Testando&nbsp;a&nbsp;criação &amp; mais&nbsp;</p>', 'is_published' => true]);

    expect($post->excerpt)->toBe('Testando a criação & mais');
});

it('keeps exactly one sponsor logo in use and mirrors it into image', function () {
    $sponsor = Sponsor::create(['name' => 'Patrocinador']);
    $a = $sponsor->logos()->create(['path' => 'sponsors/a.png', 'label' => 'Claro', 'is_active' => false]);
    $b = $sponsor->logos()->create(['path' => 'sponsors/b.png', 'label' => 'Escuro', 'is_active' => true]);
    $c = $sponsor->logos()->create(['path' => 'sponsors/c.png', 'is_active' => true]);

    $sponsor->syncActiveLogo();

    expect($sponsor->fresh()->image)->toBe('sponsors/b.png')
        ->and($a->fresh()->is_active)->toBeFalse()
        ->and($b->fresh()->is_active)->toBeTrue()
        ->and($c->fresh()->is_active)->toBeFalse();

    // No logo marked: the first one is used.
    $b->update(['is_active' => false]);
    $sponsor->syncActiveLogo();

    expect($sponsor->fresh()->image)->toBe('sponsors/a.png');

    // No logos at all: image is cleared.
    $sponsor->logos()->delete();
    $sponsor->syncActiveLogo();

    expect($sponsor->fresh()->image)->toBeNull();
});

it('deactivates the other logos when one is switched on in the form', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    \Illuminate\Support\Facades\Storage::disk('public')->put('sponsors/a.png', 'x');
    \Illuminate\Support\Facades\Storage::disk('public')->put('sponsors/b.png', 'x');

    $sponsor = Sponsor::create(['name' => 'Patrocinador']);
    $sponsor->logos()->create(['path' => 'sponsors/a.png', 'is_active' => true]);
    $second = $sponsor->logos()->create(['path' => 'sponsors/b.png', 'is_active' => false]);

    $component = Livewire::test(\App\Filament\Resources\SponsorResource\Pages\EditSponsor::class, ['record' => $sponsor->getKey()]);
    $secondKey = collect($component->get('data.logos'))
        ->search(fn ($item) => in_array('sponsors/b.png', array_values((array) ($item['path'] ?? []))));

    expect($secondKey)->not->toBeFalse();

    $component->set("data.logos.{$secondKey}.is_active", true);

    $states = collect($component->get('data.logos'))->map(fn ($item) => $item['is_active']);

    expect($states->filter()->count())->toBe(1)
        ->and($states[$secondKey])->toBeTrue();
});

it('renders the sponsor view page with logo variants', function () {
    $sponsor = Sponsor::create(['name' => 'Patrocinador', 'image' => 'sponsors/a.png']);
    $sponsor->logos()->create(['path' => 'sponsors/a.png', 'label' => 'Claro', 'is_active' => true]);

    Livewire::test(ViewSponsor::class, ['record' => $sponsor->getKey()])->assertSuccessful()->assertSee('Claro');
});

it('shows every content metadata field on the view page and offers easy copy', function () {
    $subject = Subject::create(['name' => 'Verbete X', 'slug' => 'x']);
    $tag = Tag::create(['name' => 'Algoritmo']);
    $content = \App\Models\Resource::create([
        'title' => 'Conteúdo de teste',
        'description' => 'Descrição completa',
        'source' => 'https://example.com/artigo',
        'format_id' => \App\Models\Resource::FORMAT_TEXT,
        'subject_id' => $subject->id,
        'created_by' => $this->admin->id,
        'author' => 'Maria',
        'publisher' => 'Editora Y',
        'copy_rights' => 'CC-BY',
        'language' => 'pt-BR',
    ]);
    $content->tags()->attach($tag);

    Livewire::test(\App\Filament\Resources\ResourceResource\Pages\ViewResource::class, ['record' => $content->getKey()])
        ->assertSuccessful()
        ->assertSee(['Conteúdo de teste', 'Verbete X', 'Algoritmo', 'Maria', 'Editora Y', 'CC-BY', 'https://example.com/artigo', 'Copiar todos os dados']);

    expect($content->fresh()->toCopyText())
        ->toContain('Título: Conteúdo de teste')
        ->toContain('Tags: Algoritmo')
        ->toContain('Direitos: CC-BY');
});

it('saves the copyright field of a content', function () {
    $subject = Subject::create(['name' => 'Verbete X', 'slug' => 'x']);
    $tag = Tag::create(['name' => 'T']);
    $author = \App\Models\Author::findOrCreateByName('Ana Souza');

    Livewire::test(\App\Filament\Resources\ResourceResource\Pages\CreateResource::class)
        ->fillForm([
            'subject_id' => $subject->id, 'format_id' => 1, 'tags' => [$tag->id], 'title' => 'T', 'description' => 'D',
            'source' => 'https://example.com', 'authors' => [$author->id], 'publisher' => 'P', 'language' => 'pt', 'copy_rights' => 'CC-BY',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $resource = \App\Models\Resource::first();

    expect($resource->copy_rights)->toBe('CC-BY')
        ->and($resource->author)->toBe('Ana Souza')
        ->and($resource->authors->pluck('id')->all())->toBe([$author->id]);
});

it('only shows professors their own tickets', function () {
    $professor = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    $other = User::factory()->create(['is_admin' => false, 'is_active' => true]);
    $mine = \App\Models\Ticket::create(['ticket_type' => 1, 'title' => 'Meu', 'description' => 'x', 'status' => 0, 'created_by' => $professor->id, 'responsible_id' => $other->id]);
    $theirs = \App\Models\Ticket::create(['ticket_type' => 1, 'title' => 'Alheio', 'description' => 'x', 'status' => 0, 'created_by' => $other->id, 'responsible_id' => $other->id]);

    $this->actingAs($professor);

    $ids = \App\Filament\Resources\TicketResource::getEloquentQuery()->pluck('id');

    expect($ids)->toContain($mine->id)->not->toContain($theirs->id);
});

it('keeps the menu admin only', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false, 'is_active' => true]));

    expect(\App\Filament\Resources\MenuResource::canAccess())->toBeFalse();
});

it('stores the source metadata and uploader of a video', function () {
    Livewire::test(\App\Filament\Resources\VideoResource\Pages\CreateVideo::class)
        ->fillForm([
            'video_id' => 'https://youtu.be/8w9NrNSliuI',
            'source_author' => 'Canal Exemplo',
            'source_title' => 'Título original',
            'source_channel' => 'Exemplo TV',
            'source_year' => 2021,
            'title' => 'Título na plataforma',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $video = \App\Models\Video::firstOrFail();

    expect($video->video_id)->toBe('8w9NrNSliuI')
        ->and($video->source_author)->toBe('Canal Exemplo')
        ->and($video->source_year)->toBe(2021)
        ->and($video->created_by)->toBe($this->admin->id);
});

it('reuses authors regardless of case, accents and spacing', function () {
    $first = \App\Models\Author::findOrCreateByName('José da  Silva');
    $second = \App\Models\Author::findOrCreateByName('jose da silva.');

    expect($second->id)->toBe($first->id)
        ->and(\App\Models\Author::count())->toBe(1);
});

it('keeps the plain-text author column in sync with the linked authors', function () {
    $ana = \App\Models\Author::findOrCreateByName('Ana Souza');
    $bruno = \App\Models\Author::findOrCreateByName('Bruno Lima');

    expect(\App\Models\Resource::authorText([$bruno->id, $ana->id]))->toBe('Ana Souza; Bruno Lima');
});
