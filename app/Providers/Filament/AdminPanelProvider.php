<?php

namespace App\Providers\Filament;

use App\Http\Responses\LogoutDelPanelResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\ViteException;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        parent::register();

        $this->app->bind(LogoutResponseContract::class, LogoutDelPanelResponse::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa()
            ->login()
            ->profile(isSimple: false)
            ->brandName('ASOBARES Quindío')
            ->favicon(asset('img/favicon.png'))
            // El logotipo es de mapa de bits: se sirve el mismo `.png` que usa
            // el sitio público, no un `.svg` que solo envuelva esa imagen.
            ->brandLogo(asset('img/logo-asobares.png'))
            ->brandLogoHeight('2rem')
            ->darkMode()
            ->defaultThemeMode(ThemeMode::Light)
            ->themeSwitcher()
            ->viteTheme('resources/css/filament/admin/theme.css')
            // Sin esto Filament ignora el `--font-family: 'Poppins'` de
            // theme.css: su propio layout base siempre pinta un `<style>`
            // con `--font-family` DESPUÉS del tema, y sin `font()` esa
            // variable vale 'Inter Variable' a secas. `LocalFontProvider`
            // sin URL no agrega ningún <link>: el @font-face de verdad lo
            // sirve `Vite::fonts()` desde el render hook de abajo, así que
            // aquí solo hace falta corregir el nombre de la familia.
            ->font('Poppins', provider: LocalFontProvider::class)
            // `laravel-vite-plugin` con `fonts: [bunny('Poppins', ...)]`
            // compila un `fonts-manifest.json` con los doce @font-face, pero
            // nada lo enlaza por sí solo: sin este gancho Poppins no resuelve,
            // con o sin `font()`. `Vite::fonts()` (Illuminate\Foundation\Vite)
            // lee ese manifiesto y devuelve los preload y el <style> real.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => Vite::fonts(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): HtmlString => new HtmlString(view('filament.components.theme-switcher-topbar')->render()),
            )
            // La cuenta, en el cromo superior y junto al control de tema: un
            // chip con el nombre y el rango, que el círculo de iniciales de
            // Filament no muestra.
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): HtmlString => new HtmlString(view('filament.components.cuenta-en-la-barra', ['donde' => 'cromo'])->render()),
            )
            // Y la MISMA cuenta al pie de la barra, que es donde vive en el
            // teléfono: anclada abajo a la izquierda y con los iconos pasando
            // por debajo. Las dos copias existen a la vez en el marcado y
            // el CSS apaga con `display: none` la que no toca, que es lo único
            // que la saca del orden de tabulación.
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): HtmlString => new HtmlString(view('filament.components.cuenta-en-la-barra', ['donde' => 'pie'])->render()),
            )
            // El campo de puntos del fondo de TODA la interfaz. Va lo primero
            // del cuerpo, fijo y por debajo de todo.
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): HtmlString => new HtmlString(view('filament.components.puntos-de-la-barra')->render()),
            )
            // La cuenta tiene su propio módulo, así que el menú de usuario de
            // Filament sobra: dos disparadores para la misma sesión se
            // contradicen en cuanto uno cambie. Perfil, sitio y salida viven en
            // la hoja del módulo.
            ->userMenu(false)
            // Pub Red, exacto según el manual de marca de Asobares Colombia.
            ->colors([
                'primary' => Color::hex('#EE4137'),
            ])
            // RF-40: segundo factor por app de autenticación o código al correo.
            //
            // `isRequired` es lo que lo vuelve una defensa y no una opción.
            // Registrar los dos proveedores sólo los ofrecía: quien nunca
            // entraba a su perfil a activarlos seguía entrando con la
            // contraseña sola, y este panel gobierna los pagos, la cartera y
            // los datos personales de los afiliados.
            //
            // No deja a nadie fuera: quien todavía no tiene factor no se topa
            // con un portazo sino con `SetUpRequiredMultiFactorAuthentication`,
            // la pantalla de alta obligatoria de Filament, y entra en cuanto
            // lo configura.
            ->multiFactorAuthentication(
                [
                    AppAuthentication::make()
                        ->recoverable()
                        ->recoveryCodeCount(8),
                    EmailAuthentication::make(),
                ],
                isRequired: true,
            )
            // Sin campana de notificaciones: lo pendiente lo cuentan la banda
            // «Te está esperando» del tablero y los contadores del menú.
            // `Panel\AvisosQueSeVenTest` impide escribir avisos que nadie lee.
            //
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
            // `discoverWidgets()` recorre `Filament/Widgets` RECURSIVAMENTE:
            // cualquier subdirectorio (como `Observatorio/`) entra también,
            // con su propio `$sort` compitiendo por posición con el tablero.
            // Las gráficas de `Observatorio/` optan por quedar fuera con
            // `GraficaDelObservatorio::$isDiscovered = false` en vez de vivir
            // en otro directorio: siguen registradas a mano en
            // `Observatorio::getFooterWidgets()`. El siguiente subdirectorio
            // de widgets que se agregue aquí necesita el mismo mecanismo si
            // no debe aparecer en el tablero.
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
     * Los módulos JS del panel solo se registran si Vite ya los compiló.
     *
     * `panel()` se evalúa en CADA arranque de consola, así que una excepción
     * aquí deja muerto a artisan entero. Y hay dos formas de llegar a ella:
     * sin manifiesto (clon recién hecho) y con un manifiesto viejo que aún
     * no conoce alguna de estas entradas (alguien que compiló en otra rama y
     * vuelve a esta). `ViteManifestNotFoundException` hereda de
     * `ViteException`, así que un solo catch cubre las dos.
     *
     * Sin esto el procedimiento de compilación del proyecto se vuelve un
     * punto muerto circular: `view:clear` es el primer paso y necesitaría
     * el manifiesto que solo produce el paso siguiente.
     *
     * @return array<int, Js>
     */
    private function assetsDelPanel(): array
    {
        try {
            return [
                Js::make('panel-graficas', Vite::asset('resources/js/panel-graficas.js'))->module(),
                Js::make('panel-barra-lateral', Vite::asset('resources/js/panel-barra-lateral.js'))->module(),
                Js::make('panel-barra-puntos', Vite::asset('resources/js/panel-barra-puntos.js'))->module(),
                Js::make('panel-barra-resorte', Vite::asset('resources/js/panel-barra-resorte.js'))->module(),
            ];
        } catch (ViteException) {
            return [];
        }
    }
}
