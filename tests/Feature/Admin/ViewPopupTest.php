<?php

use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\SerieResource\Pages\ListSeries;
use App\Filament\Resources\SponsorResource\Pages\ListSponsors;
use App\Filament\Resources\SubjectResource\Pages\ListSubjects;
use App\Filament\Resources\UniversityResource\Pages\ListUniversities;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Serie;
use App\Models\Sponsor;
use App\Models\Subject;
use App\Models\University;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

it('opens the metadata in a pop-up instead of navigating', function (string $page, callable $record) {
    $record = $record();

    $component = Livewire::test($page);

    $action = $component->instance()->getTable()->getAction('view')->record($record);
    expect($action->getUrl())->toBeNull();
    // Row clicks must fall back to the view action rather than a URL.
    expect($component->instance()->getTable()->getRecordUrl($record))->toBeNull()
        ->and($component->instance()->getTable()->getRecordAction($record))->toBe('view');

    $component->callTableAction('view', $record)->assertSuccessful();
})->with([
    'sponsors' => [ListSponsors::class, fn () => Sponsor::create(['name' => 'Parceiro X'])],
    'universities' => [ListUniversities::class, fn () => University::create(['name' => 'USP', 'is_active' => true])],
    'series' => [ListSeries::class, fn () => Serie::create(['slug' => 's', 'title' => 'S', 'description' => 'x'])],
    'subjects' => [ListSubjects::class, fn () => Subject::create(['name' => 'Tema', 'slug' => 'tema', 'description' => 'x', 'is_active' => true])],
    'users' => [ListUsers::class, fn () => User::factory()->create()],
]);

it('keeps a separate action to open the subject page', function () {
    $subject = Subject::create(['name' => 'Tema', 'slug' => 'tema', 'description' => 'x', 'is_active' => true]);

    Livewire::test(ListSubjects::class)->assertTableActionHasUrl('openPage', route('v2.searchBySubject', $subject->id), $subject);
});
