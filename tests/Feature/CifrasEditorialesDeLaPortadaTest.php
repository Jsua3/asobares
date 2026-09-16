<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Las cuatro cifras del Observatorio conservan su representación colombiana y
 * se animan una vez al entrar en viewport.
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

        // La franja se localiza sobre el árbol y no sobre el texto del marcado.
        // Un recorte anclado a `<section class="home-editorial-cifras` exige que
        // `class` sea el primer atributo y que el nombre sea el primer token de
        // la lista, así que un `x-data` delante o una clase más lo dejan sin
        // franja con la franja servida y entera; aflojarlo a `<section [^>]*class=`
        // tampoco salva el reordenamiento. Y el prefijo casa además con
        // `home-editorial-cifras-gremio`, que es otra franja y no lleva ninguna
        // cifra animada: el aviso llegaría hablando del recuento y no de la falta.
        $xpath = $this->xpathDe($html);

        $franja = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " home-editorial-cifras ")]');

        $this->assertSame(1, $franja->length, 'La portada no pintó la franja del Observatorio.');

        $animadas = $xpath->query('.//*[@data-cifra-final]', $franja->item(0));

        // El mensaje sirve en las dos direcciones, de menos y de más: cuando el
        // recuento cae a cero el defecto es que no se anima ninguna.
        $this->assertSame(
            count(self::FINALES),
            $animadas->length,
            'El Observatorio tiene que servir exactamente sus cuatro cifras con data-cifra-final.'
        );

        $servidas = [];

        foreach ($animadas as $cifra) {
            $servidas[] = $cifra->getAttribute('data-cifra-final');

            $this->assertSame(
                $cifra->getAttribute('data-cifra-final'),
                trim($cifra->textContent),
                'La cifra visible no coincide con el valor final que restituye el observador.'
            );
        }

        $this->assertSame(
            self::FINALES,
            $servidas,
            'Las cuatro cifras del Observatorio cambiaron de valor o de orden.'
        );

        $this->assertSame(
            count(self::FINALES),
            $xpath->query('//*[@data-cifra-final]')->length,
            'Solo las cuatro cifras del Observatorio se animan: la portada no puede servir un quinto data-cifra-final.'
        );
    }

    public function test_la_animacion_usa_observer_nativo_y_respeta_movimiento_reducido(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('data-cifra-final', $js);
        $this->assertStringContainsString('IntersectionObserver', $js);
        $this->assertDoesNotMatchRegularExpression('/countup|odometer|anime\.js|gsap/i', $js);

        // Acotado a `prepararCifras`, y el motivo es el mismo que vale para
        // toda guardia con `[\s\S]*` sobre un archivo entero: aquí
        // `reduceMovimiento()` lo llaman otras nueve veces --la banda, el
        // hero, la cinta--, así que «hay un reduceMovimiento() en algún punto
        // posterior a la primera mención de data-cifra-final» se queda verde
        // aunque las cifras hubieran perdido la suya. Lo que se vigila es que
        // la animación de las cifras mire `prefers-reduced-motion`, no que
        // alguien en el archivo la mire.
        $this->assertStringContainsString(
            'reduceMovimiento()',
            $this->bloqueDeLasCifrasEnJs($js),
            'Las cifras dejaron de mirar `prefers-reduced-motion`: se animarían igual para quien pidió menos movimiento.'
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

    /**
     * El cuerpo de `prepararCifras`, desde su declaración hasta el `};` que la
     * cierra. Afirma que existe antes de devolverlo, para que ninguna guardia
     * suya pueda aprobar por ausencia si alguien renombra la función.
     */
    private function bloqueDeLasCifrasEnJs(string $js): string
    {
        $inicio = strpos($js, 'const prepararCifras = () => {');

        $this->assertNotFalse($inicio, 'No encontré `prepararCifras` en app.js.');

        $fin = strpos($js, "\n};", $inicio);

        $this->assertNotFalse($fin, 'No encontré el cierre de `prepararCifras` en app.js.');

        return substr($js, $inicio, $fin - $inicio);
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
