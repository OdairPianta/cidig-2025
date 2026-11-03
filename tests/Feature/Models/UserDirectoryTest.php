<?php

use App\Models\University;
use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only active people with the requested role in name order', function (string $role, string $method) {
    $university = University::create(['name' => 'USP', 'is_active' => true]);
    $bia = User::factory()->create(['name' => 'Bia', 'is_active' => true]);
    $ana = User::factory()->create(['name' => 'Ana', 'is_active' => true]);
    $inactive = User::factory()->create(['name' => 'Inativo', 'is_active' => false]);
    $other = User::factory()->create(['name' => 'Outro', 'is_active' => true]);
    foreach ([$bia, $ana, $inactive] as $user) {
        $user->information()->create(['roles' => [$role], 'university_id' => $university->id]);
    }
    $other->information()->create(['roles' => ['unrelated-role']]);
    User::factory()->create(['name' => 'Sem perfil', 'is_active' => true]);

    $people = User::{$method}();
    expect($people->modelKeys())->toBe([$ana->id, $bia->id])
        ->and($people->first()->information->university->is($university))->toBeTrue();
})->with([
    'coordinators' => ['coordinator', 'getCoordinators'],
    'researchers' => ['researcher', 'getResearchers'],
    'designers' => ['designer', 'getDesigners'],
    'developers' => ['developer', 'getDevelopers'],
    'invited researchers' => ['invited', 'getInvitedResearchers'],
]);

it('builds a public profile consistently from an account or its name', function () {
    $university = University::create(['name' => 'USP']);
    $user = User::factory()->create(['name' => 'Ana Souza']);
    $user->information()->create([
        'university_id' => $university->id,
        'picture_url' => 'https://images.example.test/ana.png',
        'minibio' => 'Pesquisadora',
        'description' => 'Currículo completo',
        'roles' => ['researcher'],
        'title' => 'Professora',
        'website' => 'https://example.test/ana',
    ]);
    $expected = [
        'id' => $user->id, 'name' => 'Ana Souza', 'slug' => 'ana-souza', 'university' => 'USP',
        'picture_url' => 'https://images.example.test/ana.png', 'lattes_url' => null,
        'orcid_url' => null, 'linkedin_url' => null, 'minibio' => 'Pesquisadora',
        'description' => 'Currículo completo', 'title' => 'Professora', 'website' => 'https://example.test/ana',
    ];

    expect(User::getPersonMergedData($user->fresh()))->toBe($expected)
        ->and(User::getPersonMergedData('Ana Souza'))->toBe($expected)
        ->and($user->fresh()->information->user->is($user))->toBeTrue();
});

it('keeps profile defaults for an array an unknown name or an account without information', function () {
    $expected = [
        'id' => null, 'name' => 'Convidado', 'slug' => '', 'university' => '',
        'picture_url' => '/images/team/default.jpg', 'lattes_url' => null,
        'orcid_url' => null, 'linkedin_url' => null, 'minibio' => '',
        'description' => '', 'title' => null, 'website' => null,
    ];
    expect(User::getPersonMergedData(['name' => 'Convidado']))->toBe($expected)
        ->and(User::getPersonMergedData('Convidado'))->toBe($expected)
        ->and(User::getPersonMergedData(null))->toBe([]);

    $user = User::factory()->create(['name' => 'Sem Perfil']);
    $profile = User::getPersonMergedData($user);
    expect($profile['id'])->toBe($user->id)
        ->and($profile['slug'])->toBe('sem-perfil')
        ->and($profile['university'])->toBe('')
        ->and($profile['picture_url'])->toBe('/images/team/default.jpg');
});

it('generates initials and a public slug from the account name', function () {
    $user = new User(['name' => 'Maria Clara Souza']);
    expect($user->initials())->toBe('MC')
        ->and($user->slug)->toBe('maria-clara-souza')
        ->and(User::getSlugFrom('João da Silva'))->toBe('joao-da-silva');
});

it('returns no picture for an empty profile and preserves an external picture url', function () {
    $information = new UserInformation;
    expect($information->picture_link)->toBe('');
    $information->picture_url = 'https://images.example.test/ana.png';
    expect($information->picture_link)->toBe('https://images.example.test/ana.png');

    $user = new User;
    $user->setRelation('information', $information);
    expect($user->getPictureLink())->toBe('https://images.example.test/ana.png');
});
