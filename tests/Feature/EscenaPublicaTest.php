<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Primera capa visual del frontal público: hero editorial, materiales
 * (vidrio, luz, revelado) y el interruptor de movimiento reducido.
 *
 * No prueba «que se vea bonito». Prueba que la escena no se pueda desarmar
 * en silencio: si alguien quita el velo de script, el revelado esconde el
 * contenido sin JavaScript; si quita los tokens de geometría, el puntero
 * sigue inclinando fotos a quien pidió menos movimiento.
 */
class EscenaPublicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_portada_sigue_respondiendo(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertOk();
    }

    public function test_el_hero_sin_ranuras_nuevas_rinde_lo_de_siempre(): void
    {
        $html = Blade::render('<x-publico.hero titulo="Un título" subtitulo="Un subtítulo" />');

        $this->assertStringContainsString('class="resplandor-marca border-b border-linea"', $html);
        $this->assertStringNotContainsString('hero-editorial', $html);
        $this->assertStringNotContainsString('luz-ambiente', $html);
        $this->assertStringNotContainsString('hero-con-medio', $html);
    }

    public function test_el_hero_con_escena_y_atmosfera_no_tapa_el_texto_con_el_velo(): void
    {
        $html = Blade::render(
            '<x-publico.hero titulo="Un título" atmosfera>'
            .'<x-slot:escena><img src="/img/prueba.webp" alt=""></x-slot:escena>'
            .'</x-publico.hero>'
        );

        $this->assertStringContainsString('hero-editorial', $html);
        $this->assertStringContainsString('luz-ambiente', $html);
        $this->assertStringNotContainsString('hero-medio', $html);
        $this->assertStringContainsString('/img/prueba.webp', $html);
    }

    public function test_la_portada_usa_video_de_fondo_y_el_revelado(): void
    {
        $vista = File::get(resource_path('views/publico/inicio.blade.php'));

        $this->assertStringContainsString('data-revelar', $vista);
        $this->assertStringContainsString('variante="editorial"', $vista);
        $this->assertStringContainsString('variante="horizontal"', $vista);
        $this->assertStringContainsString('x-slot:medio', $vista);
        $this->assertStringContainsString('hero-video-fondo', $vista);
        $this->assertStringContainsString('portada', $vista);
        $this->assertStringContainsString('atmosfera', $vista);
    }

    public function test_los_portadores_de_escena_existen_en_el_css_publico(): void
    {
        $app = File::get(resource_path('css/app.css'));

        foreach (['.vidrio', '.revelar', '.revelar-visto', '.tarjeta-escena', '.imagen-viva', '.luz-ambiente', '.cta-vivo', '.hero-editorial', '.hero-portada', '.hero-video-fondo', '.imagen-inclinable'] as $clase) {
            $this->assertStringContainsString($clase, $app, "Falta el portador {$clase} en app.css.");
        }

        $this->assertStringContainsString('html.con-script:not(.sin-desplazamiento)', $app);
    }

    public function test_el_revelado_no_esconde_contenido_sin_javascript(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertDoesNotMatchRegularExpression(
            '/^\s*\.revelar:not\(\.revelar-visto\)\s*\{/m',
            $app,
            '`.revelar` no puede esconderse por defecto: sin script la portada quedaría en blanco.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.revelar\s*\{[^}]*opacity:\s*0/',
            $app,
            '`.revelar` no declara opacidad 0 por su cuenta: eso esconde la portada sin JavaScript.'
        );
    }

    public function test_la_geometria_de_la_escena_vive_en_tokens_y_se_apaga_con_menos_movimiento(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        foreach (['--asb-vidrio-velo', '--asb-luz-ambiente', '--asb-zoom-imagen', '--asb-revelar-desplazamiento', '--asb-inclinacion', '--asb-avance-flecha'] as $token) {
            $this->assertStringContainsString($token.':', $tokens, "Falta el token {$token}.");
        }

        $this->assertMatchesRegularExpression(
            '/prefers-reduced-motion:\s*reduce.*?--asb-zoom-imagen:\s*1;/s',
            $tokens,
            'El zoom de imagen tiene que anularse a 1 bajo movimiento reducido.'
        );
        $this->assertMatchesRegularExpression(
            '/prefers-reduced-motion:\s*reduce.*?--asb-revelar-desplazamiento:\s*0px;/s',
            $tokens,
            'El desplazamiento del revelado tiene que anularse bajo movimiento reducido.'
        );
        $this->assertMatchesRegularExpression(
            '/prefers-reduced-motion:\s*reduce.*?--asb-inclinacion:\s*0;/s',
            $tokens,
            'La inclinación tiene que anularse bajo movimiento reducido.'
        );
        $this->assertMatchesRegularExpression(
            '/prefers-reduced-motion:\s*reduce.*?--asb-avance-flecha:\s*0px;/s',
            $tokens,
            'El avance de la flecha tiene que anularse bajo movimiento reducido.'
        );
    }

    public function test_el_script_respeta_movimiento_reducido_y_puntero_fino(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $js);
        $this->assertStringContainsString("matchMedia('(hover: hover) and (pointer: fine)')", $js);
        $this->assertStringContainsString('[data-revelar]', $js);
        $this->assertStringContainsString("Alpine.data('escena'", $js);
    }

    public function test_el_layout_marca_script_y_menos_movimiento_antes_del_pintado(): void
    {
        $layout = File::get(resource_path('views/components/layouts/publico.blade.php'));

        $this->assertStringContainsString("classList.add('con-script')", $layout);
        $this->assertStringContainsString("classList.add('sin-desplazamiento')", $layout);
        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $layout);
    }
}
