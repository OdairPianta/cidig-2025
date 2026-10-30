<?php

use App\Filament\Resources\MapProjectResource;
use App\Filament\Resources\MapProjectResource\Pages\ListMapProjects;
use App\Models\MapProject;
use App\Models\User;
use App\Support\MapProjectImport;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function mapCsv(array $rows): string
{
    $header = ['Carimbo de data/hora', 'Responder:', 'Nombre del proyecto mapeado:', '¿Cual es su país de origen?', 'Latitud', 'Longitud',
        '¿Qué es el enlace de Instagram?', 'Número de teléfono del investigador responsable de la  recolección de datos',
        '1. Nome do projeto mapeado', '  Qual o país de origem?  ', 'Qual a cidade de origem (ou mais próxima)?', 'Latitude', 'Longitude',
        'Quais dos actantes abaixo fazem parte do projeto pesquisado', 'Qual o link do Instagran?', 'Desenvolve tecnologia própria (App\'s)?',
        'Nome do pesquisador responsável pela coleta de dados', 'Telefone do pesquisador responsável pela  coleta de dados', 'E-mail do pesquisador responsável pela  coleta de dados'];

    $path = tempnam(sys_get_temp_dir(), 'map').'.csv';
    $out = fopen($path, 'w');
    fputcsv($out, $header, ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($out, array_replace(array_fill(0, count($header), ''), $row), ',', '"', '');
    }
    fclose($out);

    return $path;
}

it('cleans the coordinates people typed in the form', function () {
    $import = new MapProjectImport;

    expect($import->coordinates('-334.513', '-706.653'))->toBe([-33.4513, -70.6653, 'corrected'])
        ->and($import->coordinates('-23,5338', '-46.6359'))->toBe([-23.5338, -46.6359, 'sheet'])
        ->and($import->coordinates('23°32′50″ S', '46°38′09″ O')[2])->toBe('corrected')
        ->and($import->coordinates('abc', '')[0])->toBeNull();

    [$lat, $lng] = $import->coordinates('23°32′50″ S', '46°38′09″ O');
    expect($lat)->toEqualWithDelta(-23.547222, 0.0001)->and($lng)->toEqualWithDelta(-46.635833, 0.0001);
});

it('imports Portuguese and Spanish answers without the researcher phone or e-mail', function () {
    $path = mapCsv([
        ['24/06/2025 22:31:14', 'Português', '', '', '', '', '', '', 'Ciudadanía Inteligente', 'Chile', 'Santiago', '-334.513', '-706.653',
            'Coletivos humanos (comunidades, associações), Algoritmos', 'https://www.instagram.com/ciudadaniai/', 'Não', 'Murilo Cruz', '19982185603', 'mc.cruz@usp.br'],
        ['25/06/2025 10:00:00', 'Español', 'Proyecto Mapa', 'México', '19.4326', '-99.1332', 'https://instagram.com/x', '5511999999999'],
        ['25/06/2025 11:00:00', 'Português', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $result = (new MapProjectImport)->import($path);
    expect($result)->toBe(['created' => 2, 'existing' => 0, 'skipped' => 1, 'without_coordinates' => 0]);

    $chile = MapProject::where('name', 'Ciudadanía Inteligente')->first();
    expect($chile->country)->toBe('Chile')
        ->and($chile->latitude)->toBe(-33.4513)
        ->and($chile->coordinates_source)->toBe('corrected')
        ->and($chile->actants)->toBe(['Coletivos humanos (comunidades, associações)', 'Algoritmos'])
        ->and($chile->researcher_name)->toBe('Murilo Cruz')
        ->and($chile->is_approved)->toBeFalse()
        ->and($chile->source_language)->toBe('pt');
    expect(MapProject::where('name', 'Proyecto Mapa')->first()->source_language)->toBe('es');

    // Phone and e-mail never reach the database.
    expect(json_encode(MapProject::all()->toArray()))->not->toContain('19982185603')->not->toContain('mc.cruz@usp.br')->not->toContain('5511999999999');

    // A second import of the same file adds nothing.
    expect((new MapProjectImport)->import($path)['created'])->toBe(0);
});

it('only puts approved projects with coordinates on the public map', function () {
    MapProject::create(['external_key' => 'a', 'name' => 'Aprovado', 'latitude' => -23.5, 'longitude' => -46.6, 'is_approved' => true, 'researcher_name' => 'Ana']);
    MapProject::create(['external_key' => 'b', 'name' => 'Pendente', 'latitude' => -23.5, 'longitude' => -46.6, 'is_approved' => false]);
    MapProject::create(['external_key' => 'c', 'name' => 'Sem coordenadas', 'is_approved' => true]);

    $this->get('/mapa')->assertOk()->assertSee('Aprovado')->assertSee('Ana')->assertDontSee('Pendente')->assertDontSee('Sem coordenadas');
});

it('never lets a spreadsheet link become a javascript: URL on the map', function () {
    $project = MapProject::create(['external_key' => 'x', 'name' => 'X', 'website' => 'javascript:alert(1)', 'instagram' => 'https://instagram.com/ok']);

    expect($project->toMapArray()['website'])->toBeNull()
        ->and($project->toMapArray()['instagram'])->toBe('https://instagram.com/ok')
        ->and($project->toMapArray())->not->toHaveKey('email')->not->toHaveKey('phone');
});

it('lets only admins manage the map projects and approve in bulk', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false, 'is_active' => true]));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    expect(MapProjectResource::canAccess())->toBeFalse();

    $this->actingAs(User::factory()->create(['is_admin' => true, 'is_active' => true]));
    $one = MapProject::create(['external_key' => '1', 'name' => 'Um']);
    $two = MapProject::create(['external_key' => '2', 'name' => 'Dois']);

    Livewire::test(ListMapProjects::class)->assertSuccessful()->assertSee('Um')
        ->callTableBulkAction('approve', [$one, $two]);

    expect(MapProject::where('is_approved', true)->count())->toBe(2);
});
