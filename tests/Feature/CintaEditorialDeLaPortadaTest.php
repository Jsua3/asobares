<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-07.1: la cinta es una franja de vidrio con isotipo y solo destinos
 * reales; el CTA deja ver más la fotografía sin tocar el archivo.
 *
 * Roturas: devolver el fondo rojo sólido; pintar un ítem sin href; recortar
 * el pantallazo como logo; poner opacity en el <img> del CTA.
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

    public function test_la_cinta_usa_el_isotipo_oficial_y_solo_enlaces_reales(): void
    {
        $this->assertFileExists(public_path('img/monograma-asobares.png'));

        $html = $this->get('/')->assertOk()->getContent();
        $cinta = $this->fragmentoDeLaCinta($html);

        $this->assertStringContainsString('img/monograma-asobares.png', $cinta);
        $this->assertStringContainsString('href="'.route('inicio').'"', $cinta);
        $this->assertStringNotContainsString('ASOBARES QUINDÍO', mb_strtoupper($cinta));

        $this->assertStringContainsString('href="'.route('directorio.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('guia.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('eventos.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('empleo.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('boletin.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('aliados.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('afiliate').'"', $cinta);
        $this->assertStringContainsString('href="'.route('quienes-somos').'#iniciativas"', $cinta);

        $this->assertStringContainsString('Directorio', $cinta);
        $this->assertStringContainsString((string) ajuste('portada_guia_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('eventos_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('portada_empleo_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('boletin_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('portada_aliados_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('hero_cta_afiliate'), $cinta);

        $this->assertStringNotContainsString('href="#"', $cinta);
        $this->assertStringNotContainsString('Vibrarte — En ejecución', $cinta);
        $this->assertStringNotContainsString('Representación gremial', $cinta);
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
        $this->assertStringNotContainsString('<a ', $copia[1]);
    }

    public function test_el_css_declara_vidrio_desfile_y_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));

        $this->assertStringContainsString('rgb(12 12 12 / 0.72)', $css);
        $this->assertStringContainsString('backdrop-filter: blur(10px)', $css);
        $this->assertStringContainsString('-webkit-backdrop-filter: blur(10px)', $css);
        $this->assertStringContainsString('@keyframes home-editorial-cinta-desfile', $css);
        $this->assertStringContainsString('@keyframes home-editorial-cinta-brillo', $css);
        $this->assertStringContainsString('animation-play-state: paused', $css);
        $this->assertStringContainsString('@media (hover: hover) and (pointer: fine)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce)', $css);
        $this->assertStringContainsString('.home-editorial-cinta__lista[aria-hidden=\'true\']', $css);
        $this->assertStringContainsString('overflow-x: auto', $css);
        $this->assertStringContainsString('height: 2.125rem', $css);
        $this->assertStringContainsString('height: 2.5rem', $css);
        $this->assertStringNotContainsString('--cinta-fondo: #5c1a16', $css);
        $this->assertStringNotContainsString('--cinta-fondo: #7a241c', $css);
    }

    public function test_el_cta_conserva_la_foto_y_aligera_el_velo(): void
    {
        $cta = File::get(resource_path('views/components/publico/home/cta-afiliacion.blade.php'));
        $css = File::get(resource_path('css/home-editorial.css'));

        $this->assertStringContainsString('config(\'home_banco.cta\')', $cta);
        $this->assertDoesNotMatchRegularExpression('/<img[^>]*style="[^"]*opacity/', $cta);
        $this->assertStringNotContainsString('opacity-50', $cta);
        $this->assertStringNotContainsString('opacity-70', $cta);

        $this->assertStringContainsString('object-position: 62% 48%', $css);
        $this->assertStringContainsString('object-position: 78% 42%', $css);
        $this->assertStringContainsString('rgb(5 5 5 / 0.55)', $css);
        $this->assertStringNotContainsString('rgb(5 5 5 / 0.72) 0%', $css);
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
