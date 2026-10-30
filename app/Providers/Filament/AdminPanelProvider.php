<?php

namespace App\Providers\Filament;

use App\Filament\Resources\TicketResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn () => auth()->user()?->researchLineName() ?? config('app.name'))
            ->homeUrl(fn () => url('/'))
            ->registration(\App\Filament\Pages\Auth\RequestRegistration::class)
            ->passwordReset(\App\Filament\Pages\Auth\RequestPasswordReset::class)
            ->profile(\App\Filament\Pages\EditProfile::class)
            ->colors([
                // Shade 500 is the dark-mode button colour; Rose 500 with white text was 3.7:1, shade 600 is 4.7:1.
                'primary' => array_replace(Color::Rose, [500 => Color::Rose[600]]),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class, // Adicione esta linha
            ])
                    ->renderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): string => view('filament.pages.header-button')->render()
        )
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Perfil')
                    // Opens the profile slide-over instead of leaving the page.
                    ->url("javascript:window.dispatchEvent(new CustomEvent('open-profile'))")
                    ->icon('heroicon-o-user-circle'),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => auth()->check() ? \Illuminate\Support\Facades\Blade::render('@livewire(\App\Livewire\ProfileSlideOver::class)') : ''
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
            
    }
}