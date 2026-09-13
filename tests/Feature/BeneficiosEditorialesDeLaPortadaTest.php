<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-FINAL-03: los cinco beneficios oficiales se leen como lista editorial,
 * no como tarjetas sueltas, y el CTA sigue yendo a afiliación.
 *
 * Roturas: inventar un sexto beneficio; href="#"; quitar el sello; volver a
 * poner el CTA delante de la lista en el DOM.
 */
class BeneficiosEditorialesDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_portada_sigue_pintando_los_cinco_beneficios_oficiales_y_el_cta(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeBeneficios($html);

        $this->assertStringContainsString('Beneficios de pertenecer al gremio', $seccion);
        $this->assertStringContainsString('Más beneficios. Más oportunidades.', $seccion);
        $this->assertStringContainsString('Representación gremial', $seccion);
        $this->assertStringContainsString('Descuentos en SAYCO y OSA', $seccion);
        $this->assertStringContainsString('Beneficios con aliados estratégicos', $seccion);
        $this->assertStringContainsString('Formación empresarial', $seccion);
        $this->assertStringContainsString('Orientación jurídica gratuita', $seccion);
        $this->assertStringContainsString('Conoce la afiliación', $seccion);
        $this->assertStringContainsString('href="'.route('afiliate').'"', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);

        $this->assertSame(5, preg_match_all('/<li class="home-editorial-beneficio"/', $seccion));
        $this->assertSame(5, preg_match_all('/home-editorial-beneficio__icono" aria-hidden="true"/', $seccion));
    }

    public function test_el_cta_queda_despues_de_la_lista_en_el_dom(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/respalda.blade.php'));

        $lista = strpos($vista, 'home-editorial-beneficios');
        $cta = strpos($vista, 'home-editorial-respalda__cta');

        $this->assertNotFalse($lista);
        $this->assertNotFalse($cta);
        $this->assertLessThan($cta, $lista);
        $this->assertStringContainsString('route(\'afiliate\')', $vista);
        $this->assertStringContainsString("config('home_banco.beneficios')", $vista);
    }

    public function test_el_css_declara_composicion_hover_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDeRespalda($css);

        $this->assertStringContainsString('grid-template-areas', $bloque);
        $this->assertStringContainsString('.home-editorial-respalda__cta', $bloque);
        $this->assertStringContainsString('html:not(.dark) .home-editorial-respalda', $bloque);
        $this->assertStringContainsString('.dark .home-editorial-respalda', $bloque);
        $this->assertStringContainsString('translateX(2px)', $bloque);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce)', $bloque);
        $this->assertStringNotContainsString('home-editorial-respalda:hover .home-editorial-respalda__img', $bloque);
    }

    private function seccionDeBeneficios(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="home-editorial-respalda[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion),
            'La portada no pintó la franja de beneficios.'
        );

        return $seccion[1];
    }

    private function bloqueDeRespalda(string $css): string
    {
        $inicio = strpos($css, '/* —— Respalda');
        $fin = strpos($css, '/* —— Actualidad');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
    }
}
