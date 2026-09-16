<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La cinta es una barra editorial de navegación — isotipo fijo, rótulos
 * cortos y destinos reales.
 *
 * Roturas: devolver el fondo rojo; meter el logo en el track; href="#";
 * volver a los rótulos largos en mayúsculas.
 */
class CintaEditorialDeLaPortadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_la_cinta_existe_entre_el_hero_y_las_cifras(): void
    {
        $inicio = File::get(resource_path('views/publico/inicio.blade.php'));

        $hero = strpos($inicio, '<x-publico.home.hero');
        $cinta = strpos($inicio, '<x-publico.home.cinta');
        $cifras = strpos($inicio, '<x-publico.home.cifras');

        $this->assertNotFalse($hero);
        $this->assertNotFalse($cinta);
        $this->assertNotFalse($cifras);
        $this->assertLessThan($cinta, $hero);
        $this->assertLessThan($cifras, $cinta);

        $html = $this->get('/')->assertOk()->getContent();

        $posHero = strpos($html, 'hero-portada');
        $posCinta = strpos($html, 'home-editorial-cinta');
        $posCifras = strpos($html, 'home-editorial-cifras');

        $this->assertNotFalse($posHero);
        $this->assertNotFalse($posCinta);
        $this->assertNotFalse($posCifras);
        $this->assertLessThan($posCinta, $posHero);
        $this->assertLessThan($posCifras, $posCinta);
    }

    public function test_el_isotipo_queda_fijo_y_separado_del_track(): void
    {
        $vista = File::get(resource_path('views/components/publico/home/cinta.blade.php'));

        $marca = strpos($vista, 'home-editorial-cinta__marca');
        $isotipo = strpos($vista, 'img/monograma-asobares.png');
        $pista = strpos($vista, 'home-editorial-cinta__pista');
        $recorrido = strpos($vista, 'home-editorial-cinta__recorrido');

        $this->assertNotFalse($marca);
        $this->assertNotFalse($isotipo);
        $this->assertNotFalse($pista);
        $this->assertNotFalse($recorrido);
        $this->assertLessThan($isotipo, $marca);
        $this->assertLessThan($pista, $isotipo);
        $this->assertLessThan($recorrido, $pista);
        $this->assertStringContainsString('home-editorial-cinta__velo--izq', $vista);
        $this->assertStringContainsString('home-editorial-cinta__velo--der', $vista);
        $this->assertStringNotContainsString('home-editorial-cinta__recorrido', explode('home-editorial-cinta__pista', $vista)[0]);
    }

    public function test_la_cinta_usa_el_isotipo_oficial_y_solo_enlaces_reales(): void
    {
        $this->assertFileExists(public_path('img/monograma-asobares.png'));

        $xpath = $this->xpathDe($this->get('/')->assertOk()->getContent());
        $cinta = $this->nodoDeLaCinta($xpath);

        // El marcado se serializa desde el nodo ya localizado: las aserciones de
        // destino y de texto visible siguen leyéndose sobre cadena, pero el
        // recorte ya no depende de qué atributo va primero.
        $marcado = $cinta->ownerDocument->saveHTML($cinta);

        // El enlace a inicio solo contiene el isotipo, así que su nombre
        // accesible es el alt; y un <aside> sin etiqueta no se distingue de
        // otros complementarios.
        $nombreDelSitio = (string) ajuste('sitio_nombre');

        $this->assertNotSame('', trim($nombreDelSitio), 'Sin nombre del sitio la cinta se queda sin nombre accesible.');
        $this->assertSame(
            $nombreDelSitio,
            $cinta->getAttribute('aria-label'),
            'La cinta perdió su aria-label.'
        );

        // El cableado se lee del atributo del nodo, no de una cadena buscada en
        // el fragmento: así consta de qué elemento cuelga, y el valor llega sin
        // escapar, que es como lo recibe Alpine.
        $this->assertSame('cintaEditorial', $cinta->getAttribute('x-data'), 'La cinta perdió su componente de Alpine.');
        $this->assertSame(
            "pausada && 'home-editorial-cinta--pausada'",
            $cinta->getAttribute('x-bind:class'),
            'La cinta ya no refleja el estado de pausa en su clase.'
        );

        $botones = $xpath->query('.//button[contains(concat(" ", normalize-space(@class), " "), " control-movimiento--cinta ")]', $cinta);

        $this->assertSame(1, $botones->length, 'La cinta se quedó sin botón de pausa.');
        $this->assertSame('Pausar cinta editorial', $botones->item(0)->getAttribute('aria-label'));
        $this->assertSame('false', $botones->item(0)->getAttribute('aria-pressed'));
        $this->assertSame('alternar()', $botones->item(0)->getAttribute('x-on:click'), 'El botón de pausa dejó de estar cableado.');

        // El isotipo cuelga del enlace a inicio: se afirma el anidamiento y no
        // que `<img` sea el texto pegado al `>` del enlace, que un <span>
        // envolvente o un comentario rompen sin que falte nada.
        $marcas = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " home-editorial-cinta__marca ")]', $cinta);

        $this->assertSame(1, $marcas->length, 'La cinta perdió el enlace del isotipo.');
        $this->assertSame(route('inicio'), $marcas->item(0)->getAttribute('href'), 'El isotipo de la cinta ya no vuelve al inicio.');

        $isotipos = $xpath->query('.//img[contains(@src, "img/monograma-asobares.png")]', $marcas->item(0));

        $this->assertSame(1, $isotipos->length, 'El enlace del isotipo ya no cuelga del monograma oficial.');
        $this->assertSame(
            $nombreDelSitio,
            $isotipos->item(0)->getAttribute('alt'),
            'El enlace del isotipo se quedó sin nombre accesible: el alt no puede ir vacío.'
        );

        $this->assertStringContainsString('href="'.route('directorio.index').'"', $marcado);
        $this->assertStringContainsString('href="'.route('guia.index').'"', $marcado);
        $this->assertStringContainsString('href="'.route('eventos.index').'"', $marcado);
        $this->assertStringContainsString('href="'.route('empleo.index').'"', $marcado);
        $this->assertStringContainsString('href="'.route('boletin.index').'"', $marcado);
        $this->assertStringContainsString('href="'.route('aliados.index').'"', $marcado);
        $this->assertStringContainsString('href="'.route('afiliate').'"', $marcado);
        $this->assertStringContainsString('href="'.route('quienes-somos').'#iniciativas"', $marcado);

        $this->assertStringContainsString('Directorio', $marcado);
        $this->assertStringContainsString('Abre tu negocio', $marcado);
        $this->assertStringContainsString('Eventos', $marcado);
        $this->assertStringContainsString('Empleo', $marcado);
        $this->assertStringContainsString('Boletín', $marcado);
        $this->assertStringContainsString('Aliados', $marcado);
        $this->assertStringContainsString('Iniciativas', $marcado);
        $this->assertStringContainsString('Afíliate', $marcado);

        $this->assertStringNotContainsString('href="#"', $marcado);
        $this->assertStringNotContainsString('Eventos y capacitaciones', $marcado);
        $this->assertStringNotContainsString('Bolsa de empleo', $marcado);
        $this->assertStringNotContainsString('Boletín del gremio', $marcado);
        $this->assertStringNotContainsString('Aliados del capítulo', $marcado);
        $this->assertStringNotContainsString('Las iniciativas más importantes', $marcado);
    }

    public function test_todos_los_items_visibles_de_la_cinta_son_enlaces(): void
    {
        $xpath = $this->xpathDe($this->get('/')->assertOk()->getContent());
        $cinta = $this->nodoDeLaCinta($xpath);

        // La lista visible se distingue de la copia decorativa por no llevar
        // `aria-hidden`, no por el literal `<ul class="…">`: ahí un `x-ref` o
        // una clase de más dejan el recorte vacío y el aviso llega por lo
        // contrario de lo que mira —«la cinta no tiene ítems»— con los ocho
        // servidos.
        $visibles = $xpath->query(
            './/ul[contains(concat(" ", normalize-space(@class), " "), " home-editorial-cinta__lista ")][not(@aria-hidden)]/li',
            $cinta
        );

        $this->assertGreaterThan(0, $visibles->length, 'La cinta no pintó ningún ítem visible.');

        foreach ($visibles as $item) {
            $enlaces = $xpath->query('.//a[contains(concat(" ", normalize-space(@class), " "), " home-editorial-cinta__enlace ")]', $item);

            $this->assertSame(1, $enlaces->length, 'Un ítem visible de la cinta no es un enlace.');

            // El destino sí se mide con una expresión, pero ya sobre el valor
            // del atributo y no sobre el texto de la etiqueta: reordenar las
            // clases o intercalar un atributo no cambia adónde lleva.
            $this->assertMatchesRegularExpression(
                '/^https?:\/\/\S+$/',
                $enlaces->item(0)->getAttribute('href'),
                'Un ítem visible de la cinta no tiene destino real.'
            );
        }
    }

    public function test_la_copia_del_loop_no_se_lee_ni_se_tabula(): void
    {
        $xpath = $this->xpathDe($this->get('/')->assertOk()->getContent());
        $cinta = $this->nodoDeLaCinta($xpath);

        // Se cuentan nodos ocultos al lector, no apariciones de una cadena que
        // fija el orden de los atributos: ahí un atributo de más baja la cuenta
        // a cero y la guardia denuncia que falta la copia habiendo exactamente
        // una.
        $copias = $xpath->query(
            './/ul[contains(concat(" ", normalize-space(@class), " "), " home-editorial-cinta__lista ")][@aria-hidden="true"]',
            $cinta
        );

        $this->assertSame(1, $copias->length, 'La cinta tiene que llevar exactamente una copia decorativa del recorrido.');

        $enlaces = $xpath->query('.//a', $copias->item(0));

        $this->assertGreaterThan(0, $enlaces->length, 'La copia visual tiene que seguir siendo clicable.');

        foreach ($enlaces as $enlace) {
            $this->assertSame(
                '-1',
                $enlace->getAttribute('tabindex'),
                'La copia decorativa no puede entrar en el orden de tabulación.'
            );
        }
    }

    public function test_el_loop_declara_continuidad_y_el_hover_es_uniforme(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));
        $bloque = $this->bloqueDeLaCinta($css);

        $this->assertStringContainsString('min-width: 100cqi', $bloque);
        $this->assertStringContainsString('flex-shrink: 0', $bloque);
        $this->assertStringContainsString('container-type: inline-size', $bloque);
        $this->assertStringContainsString('translateY(-2px)', $bloque);
        $this->assertStringContainsString('.home-editorial-cinta__item:hover .home-editorial-cinta__enlace', $bloque);
        $this->assertStringContainsString('.home-editorial-cinta__item:hover .home-editorial-cinta__sep', $bloque);
        $this->assertStringContainsString('.home-editorial-cinta:hover .home-editorial-cinta__recorrido', $bloque);
        $this->assertStringContainsString('.home-editorial-cinta:focus-within .home-editorial-cinta__recorrido', $bloque);
        $this->assertStringContainsString('.home-editorial-cinta--pausada .home-editorial-cinta__recorrido', $bloque);
    }

    public function test_el_css_declara_vidrio_mascara_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));

        $this->assertStringContainsString('backdrop-filter: blur(14px)', $css);
        $this->assertStringContainsString('-webkit-backdrop-filter: blur(14px)', $css);
        $this->assertStringContainsString('mask-image: linear-gradient', $css);
        $this->assertStringContainsString('-webkit-mask-image: linear-gradient', $css);
        $this->assertStringContainsString('animation: home-editorial-cinta-desfile 50s linear infinite', $css);
        $this->assertStringContainsString('@keyframes home-editorial-cinta-desfile', $css);
        $this->assertStringContainsString('animation-play-state: paused', $css);
        $this->assertStringContainsString("Alpine.data('cintaEditorial'", File::get(resource_path('js/app.js')));
        $this->assertStringContainsString('this.pausada = reduceMovimiento()', File::get(resource_path('js/app.js')));
        $this->assertStringContainsString('@media (hover: hover) and (pointer: fine)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce)', $css);
        $this->assertStringContainsString('.home-editorial-cinta__lista[aria-hidden=\'true\']', $css);
        $this->assertStringNotContainsString('text-transform: uppercase', $this->bloqueDeLaCinta($css));
        $this->assertStringNotContainsString('--cinta-fondo: #5c1a16', $css);
        $this->assertStringNotContainsString('--cinta-fondo: #7a241c', $css);
    }

    public function test_el_cta_sigue_sin_tocarse(): void
    {
        $cta = File::get(resource_path('views/components/publico/home/cta-afiliacion.blade.php'));
        $css = File::get(resource_path('css/home-editorial.css'));

        $this->assertStringContainsString('config(\'home_banco.cta\')', $cta);
        $this->assertStringContainsString('object-position: 62% 48%', $css);
        $this->assertStringContainsString('object-position: 78% 42%', $css);
        $this->assertStringContainsString('rgb(5 5 5 / 0.55)', $css);
    }

    private function bloqueDeLaCinta(string $css): string
    {
        $inicio = strpos($css, '/* —— Cinta editorial');
        $fin = strpos($css, '/* —— Cifras');

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

    /**
     * La cinta como nodo del árbol servido, y una sola vez.
     *
     * El nombre de etiqueta sí forma parte del contrato —la cinta es un
     * landmark complementario—, pero el recorte por `<aside class="…"` exige
     * además que `class` sea el primer atributo y que su valor sea exacto, así
     * que un `x-data` delante o una variante de clase dejan sin cinta a las
     * tres pruebas que cuelgan de aquí con la cinta servida y entera.
     */
    private function nodoDeLaCinta(\DOMXPath $xpath): \DOMElement
    {
        $cintas = $xpath->query('//aside[contains(concat(" ", normalize-space(@class), " "), " home-editorial-cinta ")]');

        $this->assertSame(1, $cintas->length, 'La portada no pintó la cinta editorial.');

        return $cintas->item(0);
    }
}
