<?php

namespace Tests\Feature;

use App\Models\Asociado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * El Directorio carga la capa editorial híbrida sin perder
 * filtros, pestañas ni destinos reales.
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

    public function test_el_hero_sigue_obedeciendo_el_titulo_editable(): void
    {
        $this->get(route('directorio.index'))
            ->assertOk()
            ->assertSee('Directorio de establecimientos');
    }

    public function test_el_css_y_vite_declaran_la_hoja_del_directorio(): void
    {
        $this->assertFileExists(resource_path('css/directorio-editorial.css'));

        $css = File::get(resource_path('css/directorio-editorial.css'));
        $vite = File::get(base_path('vite.config.js'));
        $vista = File::get(resource_path('views/publico/directorio/index.blade.php'));

        $this->assertStringContainsString('.directorio-editorial-hero', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce)', $css);
        $this->assertStringContainsString('resources/css/directorio-editorial.css', $vite);
        $this->assertStringContainsString("@vite(['resources/css/directorio-editorial.css'])", $vista);
        $this->assertStringNotContainsString('home-editorial.css', $vista);
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
     * filtros. Aquí se cuenta el formulario dentro de cada
     * contenedor y se sigue el cableado del botón móvil hasta la hoja.
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
        $this->assertMatchesRegularExpression(
            '/abrirDrawer\(\) \{\s*this\.drawerAbierto = true;/',
            $componente->getAttribute('x-data'),
            'abrirDrawer() no abre la hoja'
        );
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
