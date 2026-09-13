<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-FINAL-05: la franja «ASOBARES en movimiento» es una transición
 * editorial, no una promesa de clips ni un segundo protagonista.
 *
 * Roturas: devolver «Próxima pieza»; href="#"; quitar un acceso real;
 * volver a pintar la intro de banda audiovisual.
 */
class MovimientoEditorialDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_portada_pinta_movimiento_con_accesos_reales_y_sin_promesas_vacias(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $seccion = $this->seccionDeMovimiento($html);

        $this->assertStringContainsString('ASOBARES en movimiento', $seccion);
        $this->assertStringContainsString('Historias cortas para sentir el gremio.', $seccion);
        $this->assertStringContainsString('Abre tu negocio', $seccion);
        $this->assertStringContainsString('Ver agenda', $seccion);
        $this->assertStringContainsString('Boletín ASOBARES', $seccion);
        $this->assertStringContainsString('href="'.route('guia.index').'"', $seccion);
        $this->assertStringContainsString('href="'.route('eventos.index').'"', $seccion);
        $this->assertStringContainsString('href="'.route('boletin.index').'"', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);
        $this->assertStringNotContainsString('Próxima pieza', $seccion);
        $this->assertStringNotContainsString('Clips de afiliados', $seccion);
        $this->assertStringNotContainsString('banda audiovisual', $seccion);
        $this->assertStringNotContainsString('home-editorial-evento', $seccion);
    }

    public function test_la_vista_ya_no_declara_proxima_pieza_ni_intro_de_clips(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/actualidad.blade.php'));

        $this->assertStringContainsString('home-editorial-movimiento', $vista);
        $this->assertStringContainsString('route(\'guia.index\')', $vista);
        $this->assertStringContainsString('route(\'eventos.index\')', $vista);
        $this->assertStringContainsString('route(\'boletin.index\')', $vista);
        $this->assertStringNotContainsString('portada_videos_proxima_rotulo', $vista);
        $this->assertStringNotContainsString('portada_videos_proxima_texto', $vista);
        $this->assertStringNotContainsString('portada_videos_intro', $vista);
        $this->assertStringNotContainsString('href="#"', $vista);
    }

    public function test_el_css_declara_atmosfera_temas_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDeMovimiento($css);

        $this->assertStringContainsString('.home-editorial-movimiento', $bloque);
        $this->assertStringContainsString('html:not(.dark) .home-editorial-movimiento', $bloque);
        $this->assertStringContainsString('.dark .home-editorial-movimiento', $bloque);
        $this->assertStringContainsString('home-editorial-movimiento__atmosfera', $bloque);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringNotContainsString('scroll-snap', $bloque);
    }

    private function seccionDeMovimiento(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="[^"]*home-editorial-movimiento[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion),
            'La portada no pintó la franja «ASOBARES en movimiento».'
        );

        return $seccion[1];
    }

    private function bloqueDeMovimiento(string $css): string
    {
        $inicio = strpos($css, '/* —— Actualidad');
        $fin = strpos($css, '/* —— Publicidad');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
    }
}
