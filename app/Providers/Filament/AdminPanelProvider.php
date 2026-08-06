<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AsociadosPorMunicipio;
use App\Filament\Widgets\PendientesDeAprobacion;
use App\Filament\Widgets\RecaudoMensual;
use App\Filament\Widgets\ResumenDelGremio;
use App\Filament\Widgets\UltimasTransacciones;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\ViteException;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(isSimple: false)
            ->brandName('ASOBARES Quindío')
            ->favicon(asset('img/favicon.png'))
            ->brandLogo(asset('img/logo-asobares.svg'))
            ->brandLogoHeight('2rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkModeBrandLogo(asset('img/logo-asobares-blanco.png'))
            // Pub Red, exacto según el manual de marca de Asobares Colombia.
            ->colors([
                'primary' => Color::hex('#EE4137'),
            ])
            // RF-40: segundo factor por app de autenticación o código al correo.
            ->multiFactorAuthentication([
                AppAuthentication::make()
                    ->recoverable()
                    ->recoveryCodeCount(8),
                EmailAuthentication::make(),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            // Sin icono de grupo a propósito: Filament no admite iconos en el
            // grupo y en sus items a la vez, y el icono por recurso orienta más.
            ->navigationGroups([
                NavigationGroup::make('Contenido'),
                NavigationGroup::make('Bolsas'),
                NavigationGroup::make('Bandejas'),
                NavigationGroup::make('Gremio'),
                NavigationGroup::make('Configuración')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                PendientesDeAprobacion::class,
                ResumenDelGremio::class,
                RecaudoMensual::class,
                AsociadosPorMunicipio::class,
                UltimasTransacciones::class,
            ])
            ->assets($this->assetsDelPanel())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * El plugin de graficas solo se registra si Vite ya lo compilo.
     *
     * `panel()` se evalua en CADA arranque de consola, asi que una excepcion
     * aqui deja muerto a artisan entero. Y hay dos formas de llegar a ella:
     * sin manifiesto (clon recien hecho) y con un manifiesto viejo que aun
     * no conoce esta entrada (alguien que compilo en otra rama y vuelve a
     * esta). `ViteManifestNotFoundException` hereda de `ViteException`, asi
     * que un solo catch cubre las dos.
     *
     * Sin esto el procedimiento de compilacion del proyecto se vuelve un
     * punto muerto circular: `view:clear` es el primer paso y necesitaria
     * el manifiesto que solo produce el paso siguiente.
     *
     * @return array<int, Js>
     */
    private function assetsDelPanel(): array
    {
        try {
            return [
                Js::make('panel-graficas', Vite::asset('resources/js/panel-graficas.js'))->module(),
            ];
        } catch (ViteException) {
            return [];
        }
    }
}
