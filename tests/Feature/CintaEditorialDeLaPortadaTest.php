<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Beneficio;
use App\Models\Iniciativa;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-07: la cinta editorial va entre el Hero y Cifras, con datos ya
 * existentes y enlaces solo a rutas reales. No prueba la duración del
 * desplazamiento: eso es dirección de arte, no un contrato.
 *
 * Roturas: quitar el componente de `inicio.blade.php`; cablear un número de
 * afiliados; inventar una ruta; dejar la copia del loop tabulable.
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

    public function test_la_cinta_pinta_iniciativas_beneficios_y_afiliados_reales(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $cinta = $this->fragmentoDeLaCinta($html);

        $total = Asociado::publicado()->count();
        $this->assertGreaterThan(0, $total);
        $this->assertStringContainsString($total.' afiliados en el Quindío', $cinta);

        foreach (Iniciativa::publicado()->orderBy('orden')->take(5)->get() as $iniciativa) {
            $this->assertStringContainsString(
                $iniciativa->nombre.' — '.$iniciativa->estado_iniciativa->getLabel(),
                $cinta
            );
        }

        foreach (Beneficio::query()->orderBy('orden')->get() as $beneficio) {
            $this->assertStringContainsString($beneficio->titulo, $cinta);
        }
    }

    public function test_los_enlaces_de_la_cinta_apuntan_a_rutas_reales(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $cinta = $this->fragmentoDeLaCinta($html);

        $this->assertStringContainsString('href="'.route('guia.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('empleo.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('eventos.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('boletin.index').'"', $cinta);
        $this->assertStringContainsString('href="'.route('aliados.index').'"', $cinta);

        $this->assertStringContainsString((string) ajuste('portada_guia_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('portada_empleo_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('eventos_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('boletin_titulo'), $cinta);
        $this->assertStringContainsString((string) ajuste('portada_aliados_titulo'), $cinta);
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

    public function test_el_css_declara_el_desfile_el_brillo_y_el_movimiento_reducido(): void
    {
        $css = File::get(resource_path('css/home-editorial.css'));

        $this->assertStringContainsString('@keyframes home-editorial-cinta-desfile', $css);
        $this->assertStringContainsString('@keyframes home-editorial-cinta-brillo', $css);
        $this->assertStringContainsString('animation-play-state: paused', $css);
        $this->assertStringContainsString('@media (hover: hover) and (pointer: fine)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('.home-editorial-cinta__lista[aria-hidden=\'true\']', $css);
        $this->assertStringContainsString('overflow-x: auto', $css);
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
