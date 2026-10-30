<?php

use App\Filament\Resources\MenuResource\Pages\CreateMenu;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageResource\Pages\CreatePage;
use App\Models\Menu;
use App\Models\Page;
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

it('lets the admin build a publications page with items', function () {
    Livewire::test(CreatePage::class)
        // The items section only appears once the template is chosen.
        ->set('data.template', Page::TEMPLATE_PUBLICATIONS)
        ->fillForm([
            'title' => 'Livros e Artigos',
            'slug' => 'livros-e-artigos',
            'template' => Page::TEMPLATE_PUBLICATIONS,
            'is_published' => true,
            'items' => [
                ['title' => 'Cidadania digital', 'authors' => 'A. Silva', 'year' => 2024, 'category' => 'Livro', 'url' => 'https://exemplo.org/livro'],
                ['title' => 'Dados e direitos', 'authors' => 'B. Souza', 'year' => 2025, 'category' => 'Artigo'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::where('slug', 'livros-e-artigos')->firstOrFail();
    expect($page->items)->toHaveCount(2)->and($page->items->first()->title)->toBe('Cidadania digital');

    auth()->logout();
    $this->get('/p/livros-e-artigos')
        ->assertOk()
        ->assertSee('Livros e Artigos')
        ->assertSee('Livro')->assertSee('Artigo')
        ->assertSee('Cidadania digital')->assertSee('A. Silva');
});

it('renders text and card templates', function () {
    Page::create(['title' => 'Sobre o Observatório', 'slug' => 'obs', 'template' => Page::TEMPLATE_TEXT, 'body' => "## Missão\n\nTexto **forte**", 'is_published' => true]);
    $cases = Page::create(['title' => 'Casos', 'slug' => 'casos', 'template' => Page::TEMPLATE_CASES, 'is_published' => true]);
    $cases->items()->create(['title' => 'Caso do Chile', 'category' => 'Chile', 'url' => 'https://exemplo.org/chile']);

    $this->get('/p/obs')->assertOk()->assertSee('Missão')->assertSee('<strong>forte</strong>', false);
    $this->get('/p/casos')->assertOk()->assertSee('Caso do Chile')->assertSee('Ver estudo de caso');
});

it('does not serve unpublished pages and hides their menu item', function () {
    $page = Page::create(['title' => 'Rascunho', 'slug' => 'rascunho', 'template' => Page::TEMPLATE_TEXT, 'body' => 'x', 'is_published' => false]);
    Menu::create(['slug' => 'Rascunho', 'page_id' => $page->id, 'is_active' => true, 'order' => 1]);

    $this->get('/p/rascunho')->assertNotFound();
    expect(Menu::active()->count())->toBe(0);

    $page->update(['is_published' => true]);
    expect(Menu::active()->count())->toBe(1)
        ->and(Menu::active()->first()->link)->toBe(route('v2.page', 'rascunho'));
});

it('links a menu item to a page and shows it in the public menu', function () {
    $page = Page::create(['title' => 'Casos', 'slug' => 'casos', 'template' => Page::TEMPLATE_CASES, 'is_published' => true]);

    Livewire::test(CreateMenu::class)
        ->fillForm(['slug' => 'Estudos de caso', 'page_id' => $page->id, 'order' => 1, 'is_active' => true])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->get('/')->assertSee('Estudos de caso')->assertSee('/p/casos', false);

    Livewire::test(CreateMenu::class)
        ->fillForm(['slug' => 'Sem destino', 'order' => 2])
        ->call('create')
        ->assertHasFormErrors(['link']);
});

it('keeps page management to the admin', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false, 'is_active' => true]));

    expect(PageResource::canAccess())->toBeFalse();
});

it('ships the Publicações, Observatório and Estudos de caso pages as unpublished drafts', function () {
    $pages = Page::whereIn('slug', ['publicacoes', 'observatorio', 'estudos-de-caso'])->orderBy('slug')->get();

    expect($pages->pluck('template', 'slug')->all())->toBe([
        'estudos-de-caso' => Page::TEMPLATE_CASES,
        'observatorio' => Page::TEMPLATE_OBSERVATORY,
        'publicacoes' => Page::TEMPLATE_PUBLICATIONS,
    ])->and($pages->every(fn (Page $page) => ! $page->is_published))->toBeTrue();

    auth()->logout();
    $this->get('/p/publicacoes')->assertNotFound();
});
