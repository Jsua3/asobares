<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Evento;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * HOME-FINAL-04: la franja de Eventos de la portada solo pinta agenda real,
 * con enlaces de detalle y secuencia, sin mezclar iniciativas ni empleo.
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
        $seccion = $this->seccionDeEventos($html);

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
        $this->assertStringNotContainsString(ajuste('portada_empleo_titulo'), $seccion);
        $this->assertStringNotContainsString(ajuste('iniciativas_titulo'), $seccion);

        $this->assertSame($proximos->count(), preg_match_all('/class="[^"]*home-editorial-evento(?:\s|")/', $seccion));
        $this->assertStringContainsString('aria-label="Ver el evento anterior"', $seccion);
        $this->assertStringContainsString('aria-label="Ver el evento siguiente"', $seccion);
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
        $seccion = $this->seccionDeEventos($html);

        $this->assertStringContainsString('Foro único de la portada', $seccion);
        $this->assertStringContainsString('href="'.route('eventos.show', $unico).'"', $seccion);
        $this->assertStringContainsString('home-editorial-evento__fallback', $seccion);
        $this->assertStringNotContainsString('videos/asobares-institucional.jpg', $seccion);
        $this->assertSame(1, preg_match_all('/class="[^"]*home-editorial-evento(?:\s|")/', $seccion));
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

    private function seccionDeEventos(string $html): string
    {
        $this->assertTrue(
            (bool) preg_match('/<section class="home-editorial-eventos[^"]*"[^>]*>(.*?)<\/section>/s', $html, $seccion),
            'La portada no pintó la franja editorial de eventos.'
        );

        return $seccion[1];
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
