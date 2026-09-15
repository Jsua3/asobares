<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use App\Models\Asociado;
use App\Models\Publicidad;
use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * El Directorio carga la capa editorial híbrida sin perder filtros, pestañas ni
 * destinos reales.
 */
class DirectorioEditorialHibridoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_directorio_carga_la_capa_editorial_con_destinos_reales(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);

        $html = $this->get(route('directorio.index'))->assertOk()->getContent();

        $this->assertStringContainsString('directorio-editorial', $html);
        /*
         * La hoja se busca por la URL que Vite resuelve y no por el literal
         * `directorio-editorial.css`, que solo existe con public/hot: tras
         * `npm run build` el archivo lleva hash y la prueba fallaría sin
         * defecto. Rotura: quitar el @vite de la hoja del directorio.
         */
        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/directorio-editorial.css').'"',
            $html,
            'el Directorio no enlaza su hoja editorial'
        );
        $this->assertStringContainsString('Encuentra dónde vive la noche.', $html);
        $this->assertStringContainsString('Explorar establecimientos', $html);
        $this->assertStringContainsString('href="#resultados"', $html);
        $this->assertStringContainsString('id="directorio-filtros-panel"', $html);
        $this->assertStringContainsString('id="directorio-filtros-drawer"', $html);
        $this->assertStringContainsString('id="campo-q-desktop"', $html);
        $this->assertStringContainsString('id="campo-q-mobile"', $html);
        $this->assertStringContainsString('id="campo-municipio-desktop"', $html);
        $this->assertStringContainsString('id="campo-municipio-mobile"', $html);
        $this->assertStringContainsString('id="campo-categoria-desktop"', $html);
        $this->assertStringContainsString('id="campo-categoria-mobile"', $html);
        $this->assertSame(1, substr_count($html, 'id="campo-q-desktop"'));
        $this->assertSame(1, substr_count($html, 'id="campo-q-mobile"'));
        $this->assertStringContainsString('id="resultados"', $html);
        $this->assertStringContainsString('Buscar por nombre', $html);
        $this->assertStringContainsString('Tarjetas', $html);
        $this->assertStringContainsString('Mapa', $html);
        $this->assertStringContainsString('Ver ficha', $html);
        $this->assertStringContainsString('Bruma Gastrobar', $html);
        $this->assertStringNotContainsString('tarjeta-escena', $html);
        $this->assertStringNotContainsString('home-editorial.css', $html);
        // Con build el literal de arriba pasa en vacío: la hoja de la portada
        // se llama home-editorial-<hash>.css. Rotura: añadir su @vite aquí.
        $this->assertStringNotContainsString(
            Vite::asset('resources/css/home-editorial.css'),
            $html,
            'el Directorio no carga la hoja de la portada'
        );
    }

    public function test_el_drawer_movil_bloquea_el_fondo_y_administra_el_foco(): void
    {
        $vista = File::get(resource_path('views/publico/directorio/index.blade.php'));

        $this->assertStringContainsString('class="fixed inset-0 z-[80]"', $vista);
        $this->assertStringContainsString('aria-modal="true"', $vista);
        $this->assertStringContainsString('x-ref="drawerFiltros"', $vista);
        $this->assertStringContainsString('tabindex="-1"', $vista);
        $this->assertStringContainsString('retenerFocoDrawer($event)', $vista);
        $this->assertStringContainsString('Array.from(this.$refs.drawerFiltros.querySelectorAll', $vista);
        $this->assertStringContainsString('disparadorDrawer?.focus()', $vista);
        $this->assertStringContainsString('backdrop-blur-sm', $vista);
    }

    public function test_el_hero_sigue_obedeciendo_el_titulo_editable(): void
    {
        $this->get(route('directorio.index'))
            ->assertOk()
            ->assertSee('Directorio de establecimientos');
    }

    /**
     * H1, entradilla y CTA salen de ajustes. Rotura: volver a cablear los
     * tres textos en la vista; cambiar href="#resultados".
     */
    public function test_el_hero_obedece_frase_entradilla_y_cta_editables(): void
    {
        $this->seed(SettingSeeder::class);

        $this->editarAjuste('directorio_hero_titulo', 'FRASE HERO DIRECTORIO EDITADA');
        $this->editarAjuste('directorio_hero_entradilla', 'ENTRADILLA HERO DIRECTORIO EDITADA');
        $this->editarAjuste('directorio_cta', 'CTA HERO DIRECTORIO EDITADO');

        $html = $this->get(route('directorio.index'))->assertOk()->getContent();
        $hero = $this->fragmentoDelHero($html);

        $this->assertStringContainsString('FRASE HERO DIRECTORIO EDITADA', $hero);
        $this->assertStringContainsString('ENTRADILLA HERO DIRECTORIO EDITADA', $hero);
        $this->assertStringContainsString('CTA HERO DIRECTORIO EDITADO', $hero);
        $this->assertStringContainsString('href="#resultados"', $hero);
        $this->assertStringNotContainsString('Encuentra dónde vive la noche.', $hero);
        $this->assertStringNotContainsString('Explorar establecimientos', $hero);
    }

    /**
     * Sin fila en `settings` la vista pinta el respaldo. Rotura: quitar el
     * segundo argumento de ajuste() y dejar el hero vacío.
     */
    public function test_el_hero_cae_a_los_textos_de_respaldo_si_faltan_ajustes(): void
    {
        $html = $this->get(route('directorio.index'))->assertOk()->getContent();
        $hero = $this->fragmentoDelHero($html);
        $vista = File::get(resource_path('views/publico/directorio/index.blade.php'));

        $this->assertStringContainsString("ajuste('directorio_hero_titulo', 'Encuentra dónde vive la noche.')", $vista);
        $this->assertStringContainsString("ajuste('directorio_hero_entradilla', 'Bares, gastrobares, cafés y experiencias que forman parte del gremio en el Quindío.')", $vista);
        $this->assertStringContainsString("ajuste('directorio_cta', 'Explorar establecimientos')", $vista);
        $this->assertStringContainsString('href="#resultados"', $vista);
        $this->assertStringContainsString('Encuentra dónde vive la noche.', $hero);
        $this->assertStringContainsString('Bares, gastrobares, cafés y experiencias que forman parte del gremio en el Quindío.', $hero);
        $this->assertStringContainsString('Explorar establecimientos', $hero);
        $this->assertStringContainsString('href="#resultados"', $hero);
    }

    public function test_el_css_y_vite_declaran_la_hoja_del_directorio(): void
    {
        $this->assertFileExists(resource_path('css/directorio-editorial.css'));

        $css = File::get(resource_path('css/directorio-editorial.css'));
        $vite = File::get(base_path('vite.config.js'));
        $vista = File::get(resource_path('views/publico/directorio/index.blade.php'));

        $this->assertStringContainsString('.directorio-editorial-hero', $css);
        $this->assertStringContainsString('.directorio-editorial-hero__foto', $css);
        $this->assertStringContainsString('.directorio-editorial-cifras__dato', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce)', $css);
        $this->assertStringContainsString('rgb(11 9 10 / 0.9)', $css);
        $this->assertStringContainsString('rgb(255 248 241 / 0.86)', $css);
        $this->assertStringContainsString('rgb(255 248 241 / 0.9)', $css);
        $this->assertStringContainsString('resources/css/directorio-editorial.css', $vite);
        $this->assertStringContainsString("@vite(['resources/css/directorio-editorial.css'])", $vista);
        $this->assertStringNotContainsString('home-editorial.css', $vista);
    }

    /**
     * El hero deja una ranura a la derecha y no rellena con hueco geométrico
     * ni con la portada del primer asociado. Rotura: volver a pintar
     * `<x-publico.hueco-foto>` o a leer `foto_portada` en el listado.
     */
    public function test_el_hero_reserva_la_fotografia_sin_placeholder_artificial(): void
    {
        $vista = File::get(resource_path('views/publico/directorio/index.blade.php'));
        $hero = $this->fragmentoDelHero($this->get(route('directorio.index'))->assertOk()->getContent());

        $this->assertStringContainsString('directorio-editorial-hero__foto', $vista);
        $this->assertStringContainsString('directorio-editorial-hero__foto', $hero);
        $this->assertStringNotContainsString('hueco-foto', $vista);
        $this->assertStringNotContainsString('foto_portada', $vista);
        $this->assertStringNotContainsString('<x-publico.hueco-foto', $vista);
        $this->assertStringContainsString("ajuste('directorio_hero_titulo'", $vista);
        $this->assertStringContainsString("ajuste('directorio_hero_entradilla'", $vista);
        $this->assertStringContainsString("ajuste('directorio_cta'", $vista);
    }

    /**
     * La pauta del Directorio se nombra como patrocinio y solo envuelve el
     * bloque en un enlace si hay destino. Rotura: quitar el rótulo; enlazar
     * siempre; dejar de leer `url_destino`.
     */
    public function test_la_publicidad_del_directorio_se_identifica_y_solo_enlaza_con_destino(): void
    {
        $componente = File::get(resource_path('views/components/publico/publicidad.blade.php'));

        $this->assertStringContainsString("ajuste('publicidad_rotulo'", $componente);
        $this->assertStringContainsString('$publicidad->url_destino', $componente);
        $this->assertStringContainsString('noopener noreferrer sponsored', $componente);
        $this->assertStringContainsString('directorio-pauta__rotulo', $componente);

        $conDestino = $this->sembrarPautaDeDirectorio([
            'nombre_comercial' => 'Marca Con Destino',
            'url_destino' => 'https://example.com/pauta-directorio',
        ]);

        $htmlConDestino = $this->get(route('directorio.index'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Contenido patrocinado"', $htmlConDestino);
        $this->assertStringContainsString('Marca Con Destino', $htmlConDestino);
        $this->assertStringContainsString('https://example.com/pauta-directorio', $htmlConDestino);
        $this->assertStringContainsString('Conocer más', $htmlConDestino);

        Publicidad::withoutEvents(fn () => $conDestino->update(['url_destino' => null]));

        $htmlSinDestino = $this->get(route('directorio.index'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-label="Contenido patrocinado"', $htmlSinDestino);
        $this->assertStringContainsString('Marca Con Destino', $htmlSinDestino);
        $this->assertStringNotContainsString('https://example.com/pauta-directorio', $htmlSinDestino);
        $this->assertStringNotContainsString('Conocer más', $htmlSinDestino);
        $this->assertStringContainsString('role="group"', $htmlSinDestino);
    }

    public function test_las_pestanas_y_el_mapa_siguen_en_pie(): void
    {
        Asociado::factory()->publicado()->create();

        $this->get(route('directorio.index', ['vista' => 'mapa']))
            ->assertOk()
            ->assertSee('aria-label="Cambiar vista"', false)
            ->assertSee('name="vista" value="mapa"', false)
            ->assertSee('directorio-editorial-mapa', false);
    }

    /**
     * Los ids del panel y de la hoja sobreviven aunque se vacíen, y «Buscar
     * por nombre» sale dos veces, así que buscar literales no protege los
     * filtros. Aquí se cuenta el formulario dentro de cada contenedor y se
     * sigue el cableado del botón móvil hasta la hoja.
     *
     * Roturas: borrar el <x-publico.filtros-directorio> de la hoja móvil;
     * borrar el del panel de escritorio; quitar x-on:click="abrirDrawer()"
     * del botón; renombrar abrirDrawer() en el x-data; que abrirDrawer() no
     * ponga drawerAbierto a true; quitar x-show="drawerAbierto" de la hoja.
     */
    public function test_los_filtros_se_alcanzan_en_escritorio_y_en_movil(): void
    {
        Asociado::factory()->publicado()->create();

        $xpath = $this->xpathDe($this->get(route('directorio.index'))->assertOk()->getContent());
        $formulario = './/form[@method="GET"][.//input[@name="q"]]';

        foreach (['directorio-filtros-panel' => 'escritorio', 'directorio-filtros-drawer' => 'móvil'] as $id => $ancho) {
            $contenedor = $xpath->query('//*[@id="'.$id.'"]');

            $this->assertSame(1, $contenedor->length, "no existe #{$id}");
            $this->assertSame(1, $xpath->query($formulario, $contenedor->item(0))->length, "en {$ancho} no hay formulario de filtros dentro de #{$id}");
        }

        $boton = $xpath->query('//button[@aria-controls="directorio-filtros-drawer"]');
        $this->assertSame(1, $boton->length, 'no hay botón que controle la hoja de filtros');
        $this->assertSame('abrirDrawer()', $boton->item(0)->getAttribute('x-on:click'), 'el botón móvil no abre la hoja de filtros');

        $hoja = $xpath->query('//*[@id="directorio-filtros-drawer"]')->item(0);
        $this->assertSame('drawerAbierto', $hoja->getAttribute('x-show'), 'la hoja no obedece a drawerAbierto');

        $componente = $xpath->query('ancestor::*[@x-data][1]', $boton->item(0))->item(0);
        $this->assertNotNull($componente, 'el botón no vive dentro de un componente de Alpine');
        $this->assertTrue($componente->contains($hoja), 'el botón y la hoja no comparten componente');
        $this->assertStringContainsString('disparadorDrawer = document.activeElement', $componente->getAttribute('x-data'), 'abrirDrawer() no conserva el disparador para devolver el foco');
        $this->assertMatchesRegularExpression(
            '/abrirDrawer\(\) \{.*this\.drawerAbierto = true;/s',
            $componente->getAttribute('x-data'),
            'abrirDrawer() no abre la hoja'
        );
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function sembrarPautaDeDirectorio(array $atributos): Publicidad
    {
        return Publicidad::withoutEvents(fn (): Publicidad => Publicidad::factory()->create(array_merge([
            'ubicacion' => UbicacionPublicidad::Directorio,
            'estado' => EstadoPublicidad::Publicada,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addWeek(),
        ], $atributos)));
    }

    private function editarAjuste(string $clave, string $valor): void
    {
        $ajuste = Setting::query()->where('clave', $clave)->first();

        $this->assertNotNull($ajuste, "El ajuste «{$clave}» no está sembrado.");
        $ajuste->update(['valor' => $valor]);
    }

    private function fragmentoDelHero(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="directorio-editorial-hero"[^>]*>(.*?)<\/section>/s', $html, $coincidencias),
            'El Directorio no pintó el hero editorial.'
        );

        return $coincidencias[1];
    }

    /** El documento servido, listo para consultar por XPath. */
    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }
}
