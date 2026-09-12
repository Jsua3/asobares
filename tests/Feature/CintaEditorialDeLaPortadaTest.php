<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-07.2: la cinta es una barra editorial de navegación — isotipo fijo,
 * rótulos cortos y destinos reales. El CTA no se vuelve a tocar.
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

        $html = $this->get('/')->assertOk()->getContent();
        $cinta = $this->fragmentoDeLaCinta($html);

        $this->assertStringContainsString('img/monograma-asobares.png', $cinta);
        $this->assertStringContainsString('href="'.route('inicio').'"', $cinta);

        $this->assertStringContainsString('href="'.route('directorio.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('guia.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('eventos.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('empleo.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('boletin.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('aliados.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('afiliate').'"', $cinta);
        $this->assertStringContainsString('href="'.route('quienes-somos').'#iniciativas"', $cinta);

        $this->assertStringContainsString('Directorio', $cinta);
        $this->assertStringContainsString('Abre tu negocio', $cinta);
        $this->assertStringContainsString('Eventos', $cinta);
        $this->assertStringContainsString('Empleo', $cinta);
        $this->assertStringContainsString('Boletín', $cinta);
        $this->assertStringContainsString('Aliados', $cinta);
        $this->assertStringContainsString('Iniciativas', $cinta);
        $this->assertStringContainsString('Afíliate', $cinta);

        $this->assertStringNotContainsString('href="#"', $cinta);
        $this->assertStringNotContainsString('Eventos y capacitaciones', $cinta);
        $this->assertStringNotContainsString('Bolsa de empleo', $cinta);
        $this->assertStringNotContainsString('Boletín del gremio', $cinta);
        $this->assertStringNotContainsString('Aliados del capítulo', $cinta);
        $this->assertStringNotContainsString('Las iniciativas más importantes', $cinta);
    }

    public function test_todos_los_items_visibles_de_la_cinta_son_enlaces(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $cinta = $this->fragmentoDeLaCinta($html);

        $this->assertTrue(
            (bool) preg_match(
                '/<ul class="home-editorial-cinta__lista">(.*?)<\/ul>/s',
                $cinta,
                $visible
            )
        );

        preg_match_all('/<li class="home-editorial-cinta__item">(.*?)<\/li>/s', $visible[1], $items);

        $this->assertNotSame([], $items[1]);

        foreach ($items[1] as $item) {
            $this->assertMatchesRegularExpression(
                '/<a href="https?:\/\/[^"]+" class="home-editorial-cinta__enlace/',
                $item,
                'Un ítem visible de la cinta no tiene destino real.'
            );
        }
    }

    public function test_la_copia_del_loop_no_se_lee_ni_se_tabula(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $cinta = $this->fragmentoDeLaCinta($html);

        $this->assertSame(
            1,
            preg_match_all('/<ul class="home-editorial-cinta__lista" aria-hidden="true">/', $cinta)
        );

        $this->assertTrue(
            (bool) preg_match(
                '/<ul class="home-editorial-cinta__lista" aria-hidden="true">(.*?)<\/ul>/s',
                $cinta,
                $copia
            )
        );

        preg_match_all('/<a\b[^>]*>/', $copia[1], $enlaces);

        $this->assertNotSame([], $enlaces[0], 'La copia visual tiene que seguir siendo clicable.');

        foreach ($enlaces[0] as $enlace) {
            $this->assertStringContainsString('tabindex="-1"', $enlace);
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

    private function fragmentoDeLaCinta(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<aside class="home-editorial-cinta"[^>]*>.*?<\/aside>/s', $html, $coincidencias),
            'La portada no pintó la cinta editorial.'
        );

        return $coincidencias[0];
    }
}
