<?php

namespace App\Providers;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Uploads: public disk, 10 MB max per image (server allows 50 MB) and clear messages.
        FileUpload::configureUsing(fn (FileUpload $component) => $component
            ->disk(\App\Support\Uploads::disk())
            ->maxSize(10240)
            ->validationMessages([
                'max' => 'O arquivo é muito grande. O tamanho máximo é 10 MB.',
                'mimetypes' => 'Formato não suportado. Use JPG, PNG, GIF ou WebP.',
                'image' => 'O arquivo precisa ser uma imagem (JPG, PNG, GIF ou WebP).',
            ]));

        // Centre the toast notifications (e.g. "Usuário cadastrado").
        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(\Filament\Support\Enums\VerticalAlignment::Center);

        if (!app()->isLocal()) {
            \URL::forceScheme('https');
        }
    }
}
