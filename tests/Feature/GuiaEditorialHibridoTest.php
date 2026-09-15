<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Abre tu negocio carga su capa editorial sin reactivar el salto del
 * `view-transition-name: filtro-activo` en el selector de municipios.
 */
class GuiaEditorialHibridoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_guia_carga_la_capa_editorial_sin_transicion_de_filtro(): void
    {
        $html = $this->get(route('guia.index'))->assertOk()->getContent();
        $vista = File::get(resource_path('views/publico/guia/index.blade.php'));

        $this->assertStringContainsString('guia-editorial', $html);
        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/guia-editorial.css').'"',
            $html,
            'Abre tu negocio no enlaza su hoja editorial'
        );
        $this->assertStringContainsString("@vite(['resources/css/guia-editorial.css'])", $vista);
        $this->assertStringNotContainsString('view-transition-name', $vista);
        $this->assertStringNotContainsString('filtro-activo', $vista);
        $this->assertStringContainsString('guia-editorial-municipio', $html);
        $this->assertStringContainsString('Escoge tu municipio', $html);
    }

    public function test_el_css_declara_crema_reduced_motion_y_la_identidad_viva(): void
    {
        $this->assertFileExists(resource_path('css/guia-editorial.css'));
        $this->assertFileExists(public_path('media/abre-tu-negocio/animacion-logo.mp4'));

        $css = File::get(resource_path('css/guia-editorial.css'));
        $vite = File::get(base_path('vite.config.js'));

        $this->assertStringContainsString('resources/css/guia-editorial.css', $vite);
        $this->assertStringContainsString('#f7f1e8', $css);
        $this->assertStringContainsString('.guia-editorial-logo-vivo', $css);
        $this->assertStringContainsString('mix-blend-mode: normal', $css);
        $this->assertStringContainsString('#0f1112', $css);
        $this->assertStringContainsString('4.375rem + 1.75rem', $css);
        $this->assertStringContainsString('object-position: 32% 50%', $css);
        $this->assertStringContainsString('--guia-hero-tope', $css);
        $this->assertStringContainsString('media/abre-tu-negocio/hero-abre-tu-negocio.png', File::get(resource_path('views/publico/guia/index.blade.php')));
        $this->assertFileExists(public_path('media/abre-tu-negocio/hero-abre-tu-negocio.png'));
        $this->assertStringNotContainsString('mix-blend-mode: screen', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('view-transition-name: none', $css);
        $this->assertStringContainsString(".guia-editorial-municipio[aria-current='true']", $css);
        $this->assertStringNotContainsString('guia-isotipo-deriva', $css);
    }

    /**
     * El hero cicla el master en bucle nativo, sin controles ni pausa JS.
     * Rotura: `controls`; reintroducir `setTimeout` de 900 ms tras `ended`.
     */
    public function test_el_hero_reproduce_la_animacion_de_identidad_sin_controles(): void
    {
        $vista = File::get(resource_path('views/publico/guia/index.blade.php'));
        $html = $this->get(route('guia.index'))->assertOk()->getContent();
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('media/abre-tu-negocio/animacion-logo.mp4', $html);
        $this->assertStringContainsString('guia-editorial-logo-vivo', $html);
        $this->assertStringContainsString('guia-editorial-escena', $html);
        $this->assertStringContainsString('x-data="guiaIdentidad"', $vista);
        $this->assertStringContainsString('Alpine.data(\'guiaIdentidad\'', $js);
        $this->assertStringContainsString('reduceMovimiento()', $js);
        $this->assertStringContainsString('video.loop = true', $js);
        $this->assertStringNotContainsString('pausaReinicio', $js);
        $this->assertDoesNotMatchRegularExpression(
            '/<video[^>]*\scontrols[^>]*>/i',
            $html,
            'La identidad del hero no puede llevar controles.'
        );
        $this->assertStringContainsString("ajuste('guia_titulo')", $vista);
        $this->assertStringContainsString("ajuste('guia_intro')", $vista);
        $this->assertStringContainsString("ajuste('guia_foto', null)", $vista);
    }
}
