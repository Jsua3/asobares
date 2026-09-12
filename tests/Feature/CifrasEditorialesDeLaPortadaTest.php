<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-FINAL-01: las cuatro cifras del Observatorio conservan su
 * representación colombiana y se animan una vez al entrar en viewport.
 *
 * Roturas: cambiar 12,65 % / $2.104.124 / 72,82 % / 35,28 %; animar
 * siempre aunque pidan menos movimiento; añadir una librería de números.
 */
class CifrasEditorialesDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<string>
     */
    private const array FINALES = [
        '12,65 %',
        '$2.104.124',
        '72,82 %',
        '35,28 %',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_las_cuatro_cifras_conservan_su_representacion_final(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match('/<section class="home-editorial-cifras[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion);

        $this->assertNotSame([], $seccion, 'La portada no pintó la franja del Observatorio.');

        foreach (self::FINALES as $final) {
            $this->assertStringContainsString($final, $seccion[1]);
            $this->assertStringContainsString('data-cifra-final="'.$final.'"', $seccion[1]);
        }

        $this->assertSame(
            4,
            preg_match_all('/data-cifra-final="/', $seccion[1]),
            'Solo las cuatro cifras del Observatorio se animan.'
        );
    }

    public function test_la_animacion_usa_observer_nativo_y_respeta_movimiento_reducido(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('data-cifra-final', $js);
        $this->assertStringContainsString('IntersectionObserver', $js);
        $this->assertDoesNotMatchRegularExpression('/countup|odometer|anime\.js|gsap/i', $js);

        $this->assertMatchesRegularExpression(
            '/data-cifra-final[\s\S]*reduceMovimiento\(\)/',
            $js
        );
    }

    public function test_la_superficie_de_cifras_distingue_claro_y_oscuro(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDeCifras($css);

        $this->assertStringContainsString('.home-editorial-cifras', $bloque);
        $this->assertStringContainsString('html:not(.dark) .home-editorial-cifras', $bloque);
        $this->assertStringContainsString('.dark .home-editorial-cifras', $bloque);
        $this->assertStringContainsString('.home-editorial-cifra:hover', $bloque);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

    private function bloqueDeCifras(string $css): string
    {
        $inicio = strpos($css, '/* —— Cifras');
        $fin = strpos($css, '/* —— Descubre');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);
        $this->assertGreaterThan($inicio, $fin);

        return substr($css, $inicio, $fin - $inicio);
    }
}
