<?php

namespace Tests\Feature;

use App\Models\Asociado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * DIR-01: el Directorio carga la capa editorial híbrida sin perder
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
        $this->assertStringContainsString('directorio-editorial.css', $html);
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
}
