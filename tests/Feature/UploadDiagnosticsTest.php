<?php

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Support\Uploads;
use Illuminate\Support\Facades\Storage;

it('parses php ini sizes', function () {
    expect(Uploads::iniBytes('2M'))->toBe(2 * 1024 ** 2)
        ->and(Uploads::iniBytes('50M'))->toBe(50 * 1024 ** 2)
        ->and(Uploads::iniBytes('512K'))->toBe(512 * 1024)
        ->and(Uploads::iniBytes('1G'))->toBe(1024 ** 3);
    expect(Uploads::humanBytes(2 * 1024 ** 2))->toBe('2 MB');
});

it('explains a PHP-level upload failure instead of the generic English text', function () {
    $component = \Filament\Forms\Components\FileUpload::make('photo');
    $messages = $component->getValidationMessages();

    expect($messages['uploaded'])->toContain('limite do servidor');
});

it('reports a working uploads disk', function () {
    Storage::fake('public');
    config(['filesystems.uploads_disk' => 'public']);

    $this->artisan('uploads:check')
        ->expectsOutputToContain('Gravar arquivo')
        ->assertExitCode(Uploads::serverLimitBytes() < 12 * 1024 ** 2 ? 1 : 0);
});

it('fails when the uploads disk cannot be written', function () {
    config(['filesystems.disks.broken' => ['driver' => 'local', 'root' => '/dev/null/nope', 'throw' => true], 'filesystems.uploads_disk' => 'broken']);

    $this->artisan('uploads:check')->assertExitCode(1);
});

it('shows the diagnostics page to admins only', function () {
    \Illuminate\Support\Facades\Storage::fake('public');
    config(['filesystems.uploads_disk' => 'public']);
    \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

    $this->actingAs(\App\Models\User::factory()->create(['is_admin' => false, 'is_active' => true]));
    expect(\App\Filament\Pages\UploadsCheck::canAccess())->toBeFalse();

    $this->actingAs(\App\Models\User::factory()->create(['is_admin' => true, 'is_active' => true]));
    \Livewire\Livewire::test(\App\Filament\Pages\UploadsCheck::class)->assertSee('upload_max_filesize')->assertSee('UPLOADS_DISK');
});
