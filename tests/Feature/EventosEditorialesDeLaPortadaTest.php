<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Evento;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La franja de Eventos de la portada solo pinta agenda real, con enlaces de
 * detalle y secuencia, sin mezclar iniciativas ni empleo.
 *
 * Roturas: href="#"; devolver la tarjeta de vacantes; pintar un único
 * protagonista cuando hay tres próximos; controles sin nombre accesible.
 */
class EventosEditorialesDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_portada_pinta_eventos_reales_y_ya_no_mezcla_iniciativa_ni_empleo_en_ese_bloque(): void
    {
        $proximos = Evento::publicado()->proximo()->take(3)->get();

        $this->assertGreaterThanOrEqual(2, $proximos->count());

        $html = $this->get('/')->assertOk()->getContent();
        $xpath = $this->xpathDe($html);
        $franja = $this->franjaDeEventos($xpath);
        $seccion = $this->interiorDe($franja);

        $this->assertStringContainsString('Próximos eventos del gremio', $seccion);
        $this->assertStringContainsString('Eventos que mueven la noche del Quindío.', $seccion);
        $this->assertStringContainsString('href="'.route('eventos.index').'"', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);

        foreach ($proximos as $evento) {
            $this->assertStringContainsString($evento->titulo, $seccion);
            $this->assertStringContainsString('href="'.route('eventos.show', $evento).'"', $seccion);
        }

        $this->assertStringNotContainsString('href="'.route('empleo.index').'"', $seccion);
        $this->assertStringNotContainsString('href="'.route('quienes-somos').'#iniciativas"', $seccion);
        $this->assertStringNotContainsString('Ver vacantes', $seccion);
        // Literal y no ajuste(): portada_empleo_titulo está jubilado, y una
        // aguja vacía hace fallar assertStringNotContainsString siempre.
        $this->assertStringNotContainsString('Bolsa de empleo', $seccion);
        $this->assertStringNotContainsString(ajuste('iniciativas_titulo'), $seccion);

        // Se cuentan elementos que llevan de verdad la clase, no apariciones
        // del texto `class="…home-editorial-evento` en el marcado: una ligadura
        // de Alpine que nombre la clase no puede doblar la cuenta.
        $this->assertSame(
            $proximos->count(),
            $xpath->query('.//*['.$this->conClase('home-editorial-evento').']', $franja)->length,
            'La franja tiene que pintar una tarjeta por cada evento próximo.'
        );

        $this->assertStringContainsString('aria-label="Ver el evento anterior"', $seccion);
        $this->assertStringContainsString('aria-label="Ver el evento siguiente"', $seccion);
        $this->assertStringContainsString('aria-label="Pausar carrusel de eventos"', $seccion);
        $this->assertStringContainsString('aria-pressed="false"', $seccion);
        $this->assertStringContainsString('x-on:click="alternarPausaManual()"', $seccion);

        // Los puntos solo pintan un número con aria-hidden: sin aria-label son
        // botones sin nombre. `@aria-label` lee el atributo servido, así que
        // una ligadura `:aria-label` de Alpine no cuela como nombre accesible.
        $this->assertSame(
            $proximos->count(),
            $xpath->query('.//button['.$this->conClase('home-editorial-eventos__punto').'][starts-with(@aria-label, "Ir al evento ")]', $franja)->length,
            'Cada evento tiene que tener un punto con nombre accesible.'
        );

        // El árbol devuelve el valor del atributo ya decodificado: el rótulo se
        // compara crudo, y un título con «&» o con comillas no necesita escape
        // ni rompe la consulta.
        $rotulosServidos = array_map(
            static fn (\DOMElement $punto): string => $punto->getAttribute('aria-label'),
            iterator_to_array($xpath->query('.//button['.$this->conClase('home-editorial-eventos__punto').']', $franja))
        );

        foreach ($proximos->values() as $indice => $evento) {
            $this->assertContains(
                'Ir al evento '.($indice + 1).' de '.$proximos->count().': '.$evento->titulo,
                $rotulosServidos,
                'El punto '.($indice + 1).' no lleva el rótulo accesible de «'.$evento->titulo.'».'
            );
        }
    }

    public function test_un_solo_evento_proximo_se_pinta_sin_controles_ni_href_vacio(): void
    {
        Evento::query()->update(['estado' => EstadoPublicacion::Borrador]);

        $unico = Evento::factory()->publicado()->create([
            'titulo' => 'Foro único de la portada',
            'slug' => 'foro-unico-de-la-portada',
            'lugar' => 'Armenia, Quindío',
            'precio' => 0,
            'imagen' => null,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $xpath = $this->xpathDe($html);
        $franja = $this->franjaDeEventos($xpath);
        $seccion = $this->interiorDe($franja);

        $this->assertStringContainsString('Foro único de la portada', $seccion);
        $this->assertStringContainsString('href="'.route('eventos.show', $unico).'"', $seccion);
        $this->assertStringContainsString('home-editorial-evento__fallback', $seccion);
        $this->assertStringNotContainsString('videos/asobares-institucional.jpg', $seccion);
        $this->assertSame(
            1,
            $xpath->query('.//*['.$this->conClase('home-editorial-evento').']', $franja)->length,
            'Con un solo evento próximo la franja pinta una sola tarjeta.'
        );
        $this->assertStringNotContainsString('aria-label="Ver el evento anterior"', $seccion);
        $this->assertStringNotContainsString('href="#"', $seccion);
    }

    public function test_el_css_declara_secuencia_temas_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDeEventos($css);

        $this->assertStringContainsString('.home-editorial-eventos', $bloque);
        $this->assertStringContainsString('html:not(.dark) .home-editorial-eventos', $bloque);
        $this->assertStringContainsString('.dark .home-editorial-eventos', $bloque);
        $this->assertStringContainsString('scroll-snap', $bloque);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('.home-editorial-evento__img', $css);
        $this->assertStringContainsString('top: 1rem;', $bloque);
        $this->assertStringContainsString('right: 3.75rem;', $bloque);
        $this->assertStringContainsString('right: 1rem;', $bloque);
        $this->assertStringContainsString('right: 6.5rem;', $bloque);
        $this->assertStringContainsString('rgb(120 21 17 / 0.96)', $bloque);
    }

    public function test_asobares_en_movimiento_sigue_fuera_de_la_franja_de_eventos(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/actualidad.blade.php'));

        $eventos = strpos($vista, 'home-editorial-eventos');
        $movimiento = strpos($vista, 'home-editorial-movimiento');

        $this->assertNotFalse($eventos);
        $this->assertNotFalse($movimiento);
        $this->assertLessThan($movimiento, $eventos);
        $this->assertStringContainsString('urlDeFotoDeLaHome($evento->imagen)', $vista);
        $this->assertStringNotContainsString("config('home_banco.evento')", $vista);
        $this->assertStringContainsString('route(\'eventos.show\'', $vista);
        $this->assertStringNotContainsString('route(\'empleo.index\')', $vista);
    }

    public function test_el_carrusel_no_deja_una_tarjeta_activa_estatica_cuando_hay_secuencia(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/actualidad.blade.php'));

        $this->assertStringContainsString("! \$haySecuencia && \$indice === 0 ? 'is-activo' : ''", $vista);
        $this->assertStringContainsString(":class=\"indice === {{ \$indice }} && 'is-activo'\"", $vista);
        $this->assertStringContainsString(':aria-current="indice === {{ $indice }} ? \'true\' : \'false\'"', $vista);
        $this->assertStringNotContainsString("{{ \$indice === 0 ? 'is-activo' : '' }}", $vista);

        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('pausadoTemporalmente: false', $js);
        $this->assertStringContainsString('this.pausadoPorUsuario = reduceMovimiento()', $js);
        $this->assertStringContainsString('&& ! this.pausadoPorUsuario', $js);
        $this->assertStringContainsString('&& ! this.pausadoTemporalmente', $js);
        $this->assertStringContainsString('pausarTemporal()', $js);
        $this->assertStringContainsString('reanudarTemporal()', $js);
        $this->assertStringContainsString('alternarPausaManual()', $js);
    }

    /**
     * El <section> de la franja editorial de eventos dentro del documento
     * servido. Se localiza por token de clase sobre el árbol: el orden de los
     * atributos de la etiqueta no es la regresión que se vigila, y un atributo
     * nuevo delante de `class` no puede fingir que la franja falta.
     */
    private function franjaDeEventos(\DOMXPath $xpath): \DOMElement
    {
        $franja = $xpath->query('//section['.$this->conClase('home-editorial-eventos').']');

        $this->assertSame(1, $franja->length, 'La portada no pintó la franja editorial de eventos.');

        return $franja->item(0);
    }

    /**
     * El marcado interior de un nodo, tal como se sirvió. Acota a la franja las
     * aserciones de texto y de valor de atributo —el hero sirve el póster
     * institucional en esta misma respuesta— y el recorte lo hace el árbol: una
     * <section> anidada no puede dejar el interior en el encabezado y convertir
     * un «no contiene» en verde vacío.
     */
    private function interiorDe(\DOMElement $nodo): string
    {
        $interior = '';

        foreach ($nodo->childNodes as $hijo) {
            $interior .= $nodo->ownerDocument->saveHTML($hijo);
        }

        return $interior;
    }

    /**
     * Predicado XPath de clase exacta: exige el token entero, para que
     * `home-editorial-evento__fallback` no cuente como tarjeta.
     */
    private function conClase(string $clase): string
    {
        return 'contains(concat(" ", normalize-space(@class), " "), " '.$clase.' ")';
    }

    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    private function bloqueDeEventos(string $css): string
    {
        $inicio = strpos($css, '/* —— Eventos');
        $fin = strpos($css, '/* —— Actualidad');

        $this->assertNotFalse($inicio);
        $this->assertNotFalse($fin);

        return substr($css, $inicio, $fin - $inicio);
    }
}
