<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('rcadmin')
            // Sin esto, cada clic dentro del panel (ir a un listado, abrir un
            // registro, "Atrás") hacía una recarga completa del navegador --
            // se volvía a descargar y ejecutar todo el JS/CSS de Filament +
            // Livewire + Alpine en cada página, aunque el panel casi no
            // cambie entre una vista y otra. Con spa() Livewire navega por
            // AJAX (wire:navigate) y solo reemplaza el contenido; no hay
            // componentes propios con <script> que dependan de un reload
            // completo (ver ESTADO_SEGURIDAD_MIGRACION.md, diagnóstico de
            // lentitud del panel).
            ->spa()
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('CESSA Admin')
            ->brandLogo(asset('img/cessa_logo.jpg'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::hex('#004c98'),
                'danger' => Color::Rose,
                'warning' => Color::hex('#ffe71e'),
            ])
            ->navigationGroups([
                'Menú Principal',
                'Reportes',
                'Configuración',
            ])
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
