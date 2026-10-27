<?php

namespace App\Filament\Pages;

use App\Support\UploadsDiagnostics;
use Filament\Pages\Page;

class UploadsCheck extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $navigationLabel = 'Diagnóstico de uploads';

    protected static ?string $title = 'Diagnóstico de uploads';

    protected static string $view = 'filament.pages.uploads-check';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /** @return array{ok: bool, rows: array<int, array{label: string, value: string, status: string}>} */
    public function getResult(): array
    {
        return UploadsDiagnostics::run();
    }
}
