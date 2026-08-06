<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AsociadosPorMunicipio;
use App\Filament\Widgets\InscripcionesDelMes;
use App\Filament\Widgets\ResumenDelGremio;
use App\Filament\Widgets\UltimasTransacciones;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                ResumenDelGremio::class,
                InscripcionesDelMes::class,
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
     * El plugin de graficas solo se registra si Vite ya produjo algo.
     *
     * `Vite::asset()` lanza cuando no encuentra manifiesto ni servidor de
     * desarrollo, y `panel()` se evalua en CADA arranque de consola. Sin esta
     * guarda, un clon recien hecho no puede ejecutar ni `key:generate`, y el
     * procedimiento de compilacion del proyecto —`view:clear` antes de
     * `npm run build`— se vuelve un punto muerto circular.
     *
     * Degradar a lista vacia es seguro: sin nada compilado no hay panel que
     * pintar, asi que no se pierde nada que existiera.
     *
     * @return array<int, Js>
     */
    private function assetsDelPanel(): array
    {
        $servidorDeDesarrollo = file_exists(public_path('hot'));
        $manifiesto = file_exists(public_path('build/manifest.json'));

        if (! $servidorDeDesarrollo && ! $manifiesto) {
            return [];
        }

        return [
            Js::make('panel-graficas', Vite::asset('resources/js/panel-graficas.js'))->module(),
        ];
    }
}
