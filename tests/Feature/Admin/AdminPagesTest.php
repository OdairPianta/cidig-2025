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
