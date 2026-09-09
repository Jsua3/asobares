<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AsociadosPorMunicipio;
use App\Filament\Widgets\EntradasAlSitio;
use App\Filament\Widgets\PaginasMasVisitadas;
use App\Filament\Widgets\PendientesDeAprobacion;
use App\Filament\Widgets\PorDondeEntranAlSitio;
use App\Filament\Widgets\RecaudoMensual;
use App\Filament\Widgets\ResumenDelGremio;
use App\Filament\Widgets\UltimasTransacciones;
use App\Filament\Widgets\VisitasDelSitio;
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
            // El `.svg` que había aquí no era un vector: eran 49 KB de un
            // `<svg><image xlink:href="data:img/png;base64,…">` con el MIME
            // mal escrito. El `.png` son los mismos píxeles, 36 KB, y es el
            // que ya usa el sitio público.
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
            // nada lo enlazaba: por eso Poppins nunca llegaba a resolver, con
            // o sin este `font()`. `Vite::fonts()` (Illuminate\Foundation\Vite)
            // lee ese manifiesto y devuelve los preload + el <style> real.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => Vite::fonts(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): HtmlString => new HtmlString(view('filament.components.theme-switcher-topbar')->render()),
            )
            // La cuenta, ARRIBA y junto al control de tema (D-L21, corregida
            // por segunda vez el 7 sep). Estuvo al pie de la barra y luego como
            // primera fila; Sua la quiere en el cromo superior, con el nombre y
            // el rango que el círculo de iniciales de Filament no mostraba.
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): HtmlString => new HtmlString(view('filament.components.cuenta-en-la-barra', ['donde' => 'cromo'])->render()),
            )
            // Y la MISMA cuenta al pie de la barra, que es donde la quiso Sua en
            // el teléfono (D-L30): anclada abajo a la izquierda y con los iconos
            // pasando por debajo. Las dos copias existen a la vez en el marcado y
            // el CSS apaga con `display: none` la que no toca, que es lo único
            // que la saca del orden de tabulación.
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn (): HtmlString => new HtmlString(view('filament.components.cuenta-en-la-barra', ['donde' => 'pie'])->render()),
            )
            // El campo de puntos del fondo de TODA la interfaz (D-L24, ampliado
            // en D-L26). Va lo primero del cuerpo, fijo y por debajo de todo.
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): HtmlString => new HtmlString(view('filament.components.puntos-de-la-barra')->render()),
            )
            // Con la cuenta abajo, el menú de usuario de Filament sobra: dos
            // disparadores para la misma sesión se contradicen en cuanto uno
            // cambie. Perfil, sitio y salida viven ahora en la hoja del módulo.
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
            // La campana se retira (D-L22, 7 sep): lo que anunciaba lo cuenta
            // mejor la banda «Te está esperando» del tablero, que además dice
            // qué hay que aprobar y desde cuándo espera. Si el gremio pide
            // avisos de verdad, es un frente propio con su decisión.
            //
            // ->databaseNotifications()
            // ->databaseNotificationsPolling('30s')
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
            ->widgets([
                PendientesDeAprobacion::class,
                ResumenDelGremio::class,
                RecaudoMensual::class,
                AsociadosPorMunicipio::class,
                UltimasTransacciones::class,
                // El flujo del sitio, de arriba abajo: primero los tres números
                // (Acta 08, A-03), luego la curva de 30 días, luego por dónde
                // entran y qué se mira una vez dentro.
                EntradasAlSitio::class,
                VisitasDelSitio::class,
                PorDondeEntranAlSitio::class,
                PaginasMasVisitadas::class,
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
                Js::make('panel-barra-lateral', Vite::asset('resources/js/panel-barra-lateral.js'))->module(),
                Js::make('panel-barra-puntos', Vite::asset('resources/js/panel-barra-puntos.js'))->module(),
                Js::make('panel-barra-resorte', Vite::asset('resources/js/panel-barra-resorte.js'))->module(),
            ];
        } catch (ViteException) {
            return [];
        }
    }
}
