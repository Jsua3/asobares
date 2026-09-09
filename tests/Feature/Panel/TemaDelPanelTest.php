<?php

namespace Tests\Feature\Panel;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El panel dejó de usar el tema de fábrica: comparte los tokens del sitio,
 * así que un cambio de paleta los mueve a los dos a la vez.
 */
class TemaDelPanelTest extends TestCase
{
    public function test_el_panel_registra_su_tema_compilado(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertSame(
            'resources/css/filament/admin/theme.css',
            $panel->getViteTheme(),
            'El panel debe compilar su propio tema, no usar el de fábrica.'
        );
    }

    public function test_el_tema_del_panel_importa_los_tokens_compartidos(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString(
            "@import '../../tokens.css'",
            $tema,
            'Sin los tokens compartidos el panel repetiría los colores a mano.'
        );
    }

    public function test_los_tokens_viven_en_un_solo_archivo(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $sitio = File::get(resource_path('css/app.css'));

        // La definición está en tokens.css...
        $this->assertStringContainsString('--asb-fondo: #f5f3f4', $tokens);
        $this->assertStringContainsString('--asb-acento: #b71f18', $tokens);
        $this->assertStringContainsString('--asb-acento: #f27166', $tokens);

        // ...y el sitio la importa en vez de repetirla.
        $this->assertStringContainsString("@import './tokens.css'", $sitio);
        $this->assertStringNotContainsString('--asb-fondo:', $sitio);
    }

    /**
     * Esta prueba estaba en falso verde: afirmaba sobre una cadena inerte
     * (`file_get_contents` del CSS fuente), pero medida en navegador
     * (`document.fonts` + `measureText`) Poppins nunca se renderizaba — el
     * ancho de "ASOBARES Quindío 2026" a 48px daba 528,07px tanto con
     * `Poppins` como con una fuente inexistente, la firma de un fallback.
     *
     * Hay dos causas reales, y esta prueba afirma sobre las dos:
     *
     * 1. Filament pinta su propio `<style>:root{--font-family: ...}` EN
     *    `base.blade.php`, DESPUÉS del `<link>` del tema compilado, con el
     *    valor de `getFontFamily()`. Sin `->font()` en el panel, ese valor
     *    cae en 'Inter Variable' a secas y pisa el `--font-family: 'Poppins'`
     *    de theme.css sin importar lo que diga el CSS fuente.
     *
     * 2. `vite.config.js` compila los doce `@font-face` de Poppins vía
     *    `bunny('Poppins', ...)` a `public/build/fonts-manifest.json`, pero
     *    nada los enlazaba: sin el `<link>`/`<style>` real, el navegador no
     *    tiene de dónde descargar el glifo y Poppins resuelve por fallback
     *    aunque el nombre de la familia sea correcto.
     */
    public function test_el_panel_declara_poppins_como_familia_de_filament(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $this->assertSame(
            'Poppins',
            $panel->getFontFamily(),
            'Sin font(\'Poppins\', ...), Filament pinta "Inter Variable" en su propio <style>:root, '
            .'ENCIMA del theme.css: el CSS fuente puede decir Poppins y el navegador seguir pintando Inter.'
        );
    }

    /**
     * El mecanismo real que hace llegar los @font-face al navegador es
     * `Vite::fonts()` (lee `public/build/fonts-manifest.json`, que compila
     * `bunny('Poppins', ...)` de vite.config.js), enlazado desde un render
     * hook en HEAD_END. Se comprueba el contenido que produce, no una cadena
     * fija: el nombre del archivo lleva el hash del build, así que solo
     * aparece si el hook de verdad ejecutó Vite::fonts().
     */
    public function test_el_panel_enlaza_la_hoja_de_fuentes_real_en_head_end(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        $renderHooks = (fn (): array => $this->renderHooks)->call($panel);

        $this->assertArrayHasKey(
            PanelsRenderHook::HEAD_END,
            $renderHooks,
            'El panel debe registrar un render hook en HEAD_END: ahí es donde se enlaza la hoja de fuentes real.'
        );

        $hook = $renderHooks[PanelsRenderHook::HEAD_END][''][0] ?? null;

        $this->assertNotNull($hook, 'El hook de HEAD_END debe estar registrado en el scope global.');

        $html = (string) $hook();

        // No se compara contra un HTML fijo ni contra una segunda llamada a
        // Vite::fonts() (la primera ya marcó sus preloads como emitidos, y
        // la segunda los omitiría por deduplicación): se comprueba que el
        // resultado trae la firma de haber leído fonts-manifest.json de
        // verdad. El hash del archivo cambia en cada build, así que esto
        // solo aparece si el hook de verdad ejecuta Vite::fonts().
        $this->assertStringContainsString('@font-face', $html, 'El hook debe traer los @font-face reales, no un <link> vacío.');
        $this->assertStringContainsString('font-family: "Poppins"', $html);
        $this->assertMatchesRegularExpression(
            '#/build/assets/poppins-\d{3}-normal-[\w-]+\.woff2#',
            $html,
            'Debe apuntar a un archivo compilado de verdad (con su hash de build), no a una ruta inventada.'
        );
    }

    public function test_el_fondo_se_aplica_al_workspace_real_en_ambos_temas(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        // Desde el 8 sep el fondo no lo pinta el contenido sino el cuerpo, con el
        // campo de puntos encima: `.fi-main-ctn` quedó transparente para no
        // taparlo. Lo que hay que seguir exigiendo es que el fondo exista en los
        // dos temas, y ahora vive aquí.
        $this->assertStringContainsString('.fi-body {', $tema);
        $this->assertStringContainsString('.dark .fi-body {', $tema);
        $this->assertMatchesRegularExpression('/\.fi-main-ctn \{[^}]*background: transparent;/s', $tema, 'El contenido volvió a pintar fondo propio y taparía el campo de puntos.');
        /*
         * `.fi-body::before` existe desde el 8 sep y pinta el resplandor de la
         * zona del panel, que antes vivía en la barra y por eso nacía debajo
         * del topbar. Lo que sigue prohibido es que esa capa pinte un fondo
         * OPACO: taparía el campo de puntos, que es justo el defecto que este
         * caso vigila.
         */
        $this->assertMatchesRegularExpression('/\.fi-body::before \{[^}]*position: fixed;/s', $tema, 'La capa del resplandor dejó de ser fija: volvería a empezar donde empiece su elemento.');
        $this->assertDoesNotMatchRegularExpression('/\.fi-body::before \{[^}]*background-color:/s', $tema, 'La capa del resplandor pinta fondo opaco y taparía el campo de puntos.');
    }

    public function test_la_topbar_reacciona_al_scroll_sin_un_script_adicional(): void
    {
        $componente = File::get(resource_path('views/filament/components/theme-switcher-topbar.blade.php'));
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString('x-on:scroll.window.passive="syncTopbar()"', $componente);
        $this->assertStringContainsString("closest('.fi-topbar-ctn')", $componente);
        $this->assertStringContainsString('asb-topbar--scrolled', $componente);
        $this->assertStringContainsString('.fi-topbar-ctn.asb-topbar--scrolled .fi-topbar', $tema);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $tema);
    }

    public function test_el_tema_define_la_base_visual_de_tarjetas_y_tablas(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString('.fi-section', $tema);
        $this->assertStringContainsString('.fi-ta-header-cell', $tema);
        $this->assertStringContainsString('.fi-ta-row:hover', $tema);
    }
}
