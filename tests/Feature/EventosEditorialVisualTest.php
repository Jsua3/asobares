<?php

namespace Tests\Feature;

use App\Enums\OrigenEvento;
use App\Models\Aliado;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Capa visual de /eventos: hoja propia, riel de eventos reales, conmutador
 * con URLs y sin filtro-activo compartido.
 */
class EventosEditorialVisualTest extends TestCase
{
    use RefreshDatabase;

    public function test_eventos_carga_la_capa_editorial_con_destinos_reales(): void
    {
        $evento = Evento::factory()->publicado()->create([
            'titulo' => 'Foro vivo de prueba editorial',
            'lugar' => 'Centro de Convenciones',
            'precio' => 30000,
            'fecha_inicio' => now()->addDays(12)->setTime(10, 0),
        ]);

        $html = $this->get(route('eventos.index'))->assertOk()->getContent();

        $this->assertStringContainsString('eventos-editorial', $html);
        $this->assertStringContainsString(
            'href="'.Vite::asset('resources/css/eventos-editorial.css').'"',
            $html,
            'Eventos no enlaza su hoja editorial'
        );
        $this->assertStringContainsString('eventos-editorial-hero', $html);
        $this->assertStringContainsString('eventos-editorial-riel', $html);
        $this->assertStringContainsString('eventos-editorial-agenda__tope', $html);
        $this->assertStringNotContainsString('eventos-editorial-escena', $html);
        $this->assertStringNotContainsString('geometria', $html);
        $this->assertStringContainsString('Organiza', $html);
        $this->assertStringContainsString('ASOBARES Capítulo Quindío', $html);
        $this->assertStringContainsString('eventos-editorial-ficha', $html);
        $this->assertStringContainsString('data-eventos-hero-slot="img/eventos/hero-eventos.webp"', $html);
        $this->assertStringContainsString($evento->titulo, $html);
        $this->assertStringContainsString('Centro de Convenciones', $html);
        $this->assertStringContainsString('Ver evento', $html);
        $this->assertStringContainsString('href="'.route('eventos.index', ['cuando' => 'proximos']).'"', $html);
        $this->assertStringContainsString('href="'.route('eventos.index', ['cuando' => 'pasados']).'"', $html);
        $this->assertStringContainsString('aria-current="true"', $html);
        $this->assertStringNotContainsString('filtro-activo', $html);
        $this->assertStringNotContainsString('sm:col-span-2', $html);
        $this->assertSame(1, substr_count($html, $evento->titulo));
        $this->get(route('eventos.index'))->assertOk();
        $this->assertStringNotContainsString(
            Vite::asset('resources/css/guia-editorial.css'),
            $html
        );
        $this->assertStringNotContainsString(
            Vite::asset('resources/css/directorio-editorial.css'),
            $html
        );
    }

    public function test_el_hero_obedece_titulo_e_intro_administrables(): void
    {
        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertSee('Eventos y capacitaciones');

        $vista = File::get(resource_path('views/publico/eventos/index.blade.php'));
        $this->assertStringContainsString("ajuste('eventos_titulo', 'Eventos y capacitaciones')", $vista);
        $this->assertStringContainsString("ajuste('eventos_intro', 'Eventos, capacitaciones y experiencias del gremio y sus aliados para el sector gastronómico y de entretenimiento del Quindío.')", $vista);
        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertSee('Eventos, capacitaciones y experiencias del gremio y sus aliados para el sector gastronómico y de entretenimiento del Quindío.');
    }

    public function test_los_pasados_se_senalan_como_realizados_sin_duplicar_el_dom(): void
    {
        Evento::factory()->publicado()->create([
            'titulo' => 'Congreso ya cumplido',
            'fecha_inicio' => now()->subDays(10)->setTime(9, 0),
            'fecha_fin' => now()->subDays(9)->setTime(18, 0),
        ]);

        $html = $this->get(route('eventos.index', ['cuando' => 'pasados']))->assertOk()->getContent();

        $this->assertStringContainsString('Congreso ya cumplido', $html);
        $this->assertStringContainsString('eventos-editorial-ficha--realizado', $html);
        $this->assertStringContainsString('Realizado', $html);
        $this->assertSame(1, substr_count($html, 'Congreso ya cumplido'));
        $this->assertStringContainsString('aria-current="true"', $html);
        $this->assertStringNotContainsString('filtro-activo', $html);
    }

    public function test_el_css_y_vite_declaran_la_hoja_de_eventos(): void
    {
        $this->assertFileExists(resource_path('css/eventos-editorial.css'));

        $css = File::get(resource_path('css/eventos-editorial.css'));
        $vite = File::get(base_path('vite.config.js'));
        $vista = File::get(resource_path('views/publico/eventos/index.blade.php'));

        $this->assertStringContainsString('.eventos-editorial-hero', $css);
        $this->assertStringContainsString('.eventos-editorial-hero__foto', $css);
        $this->assertStringNotContainsString('geometria', $css);
        $this->assertStringContainsString('eventos-editorial-riel--hay-mas::after', $css);
        $this->assertStringContainsString('scroll-snap-type: x mandatory', $css);
        $this->assertStringContainsString('flex: 0 0 calc(100% - 3.25rem)', $css);
        $this->assertStringNotContainsString('grayscale', $css);
        $this->assertStringContainsString('scroll-margin-top: var(--evt-tope)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString('public/img/eventos/hero-eventos.webp', $css);
        $this->assertStringContainsString('resources/css/eventos-editorial.css', $vite);
        $this->assertStringContainsString('eventos-editorial-agenda__tope', $vista);
        $this->assertStringContainsString('pista.scrollTo', $vista);
        $this->assertStringNotContainsString('guia-editorial.css', $vista);
        $this->assertStringNotContainsString('app.js', $vista);
    }

    public function test_el_calendario_conserva_urls_rejilla_y_agenda(): void
    {
        $html = $this->get('/eventos/calendario/2026/09')->assertOk()->getContent();
        $vista = File::get(resource_path('views/publico/eventos/calendario.blade.php'));

        $this->assertStringContainsString('eventos-editorial', $html);
        $this->assertStringContainsString('calendario-titulo', $html);
        $this->assertStringContainsString('calendario-rejilla', $html);
        $this->assertStringContainsString('sm:hidden', $html);
        $this->assertStringContainsString('sm:block', $html);
        $this->assertStringContainsString('rel="prev"', $vista);
        $this->assertStringContainsString('rel="next"', $vista);
        $this->assertStringNotContainsString('filtro-activo', $html);
    }

    public function test_el_calendario_pinta_un_evento_de_aliado_con_la_misma_rejilla(): void
    {
        $aliado = Aliado::factory()->visible()->create([
            'nombre' => 'Camara de prueba calendario',
        ]);
        Evento::factory()->publicado()
            ->elDia(Carbon::create(2026, 9, 18), 10)
            ->create([
                'titulo' => 'Agenda del aliado en septiembre',
                'origen' => OrigenEvento::Aliado,
                'aliado_id' => $aliado->id,
            ]);

        $this->get('/eventos/calendario/2026/09')
            ->assertOk()
            ->assertSee('Agenda del aliado en septiembre');
    }

    public function test_el_riel_usa_el_organizador_real_de_asobares_y_de_aliado(): void
    {
        Evento::factory()->publicado()->create([
            'titulo' => 'Capacitacion propia del gremio',
            'fecha_inicio' => now()->addDays(8)->setTime(9, 0),
        ]);

        $aliado = Aliado::factory()->visible()->create([
            'nombre' => 'Camara de Comercio de Armenia y del Quindio',
            'url' => 'https://camaraarmenia.org.co',
        ]);

        Evento::factory()->publicado()->create([
            'titulo' => 'Foro del aliado publicado',
            'origen' => OrigenEvento::Aliado,
            'aliado_id' => $aliado->id,
            'fecha_inicio' => now()->addDays(9)->setTime(10, 0),
        ]);

        $html = $this->get(route('eventos.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Capacitacion propia del gremio', $html);
        $this->assertStringContainsString('Foro del aliado publicado', $html);
        $this->assertStringContainsString(Evento::ORGANIZADOR_ASOBARES, $html);
        $this->assertStringContainsString('Camara de Comercio de Armenia y del Quindio', $html);
        $this->assertStringContainsString('eventos-editorial-ficha__origen">ASOBARES', $html);
        $this->assertStringContainsString('eventos-editorial-ficha__origen">Aliado', $html);
        $this->assertSame(1, substr_count($html, 'Foro del aliado publicado'));
    }

    public function test_la_ficha_y_el_json_ld_siguen_al_organizador_del_modelo(): void
    {
        $asobares = Evento::factory()->publicado()->create([
            'titulo' => 'Ficha asobares de prueba',
        ]);

        $html = $this->get(route('eventos.show', $asobares))->assertOk()->getContent();
        $this->assertStringContainsString(Evento::ORGANIZADOR_ASOBARES, $html);
        $this->assertStringContainsString('>ASOBARES<', $html);

        $ld = $this->jsonLdDe($html);
        $this->assertSame('Event', $ld['@type']);
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $ld['organizer']['name']);
        $this->assertSame(route('inicio'), $ld['organizer']['url']);
        $this->assertArrayNotHasKey('logo', $ld['organizer']);

        $aliado = Aliado::factory()->visible()->create([
            'nombre' => 'Sena Regional Quindio',
            'url' => null,
        ]);
        $eventoAliado = Evento::factory()->publicado()->create([
            'titulo' => 'Ficha aliado de prueba',
            'origen' => OrigenEvento::Aliado,
            'aliado_id' => $aliado->id,
        ]);

        $htmlAliado = $this->get(route('eventos.show', $eventoAliado))->assertOk()->getContent();
        $this->assertStringContainsString('Sena Regional Quindio', $htmlAliado);
        $this->assertStringContainsString('>Aliado<', $htmlAliado);

        $ldAliado = $this->jsonLdDe($htmlAliado);
        $this->assertSame('Sena Regional Quindio', $ldAliado['organizer']['name']);
        $this->assertArrayNotHasKey('url', $ldAliado['organizer']);
        $this->assertArrayNotHasKey('logo', $ldAliado['organizer']);
    }

    public function test_un_evento_con_origen_nulo_no_tumba_el_listado_ni_la_ficha(): void
    {
        $legado = Evento::factory()->publicado()->make([
            'id' => 9999,
            'titulo' => 'Evento legado sin origen en base',
            'origen' => null,
            'aliado_id' => null,
        ]);

        $this->assertNull($legado->origen);
        $this->assertSame(OrigenEvento::Asobares, $legado->origenPublico());
        $this->assertFalse($legado->esDeAliado());
        $this->assertSame(Evento::ORGANIZADOR_ASOBARES, $legado->organizadorVisible());

        $ficha = view('components.publico.evento-ficha', [
            'evento' => $legado,
            'realizado' => false,
        ])->render();

        $this->assertStringContainsString('Evento legado sin origen en base', $ficha);
        $this->assertStringContainsString('ASOBARES', $ficha);

        Evento::factory()->publicado()->create([
            'titulo' => 'Evento publicado para el listado',
        ]);

        $this->get(route('eventos.index'))->assertOk();
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonLdDe(string $html): array
    {
        $this->assertMatchesRegularExpression(
            '/<script type="application\/ld\+json">(.+?)<\/script>/s',
            $html,
            'La ficha no emitió JSON-LD.'
        );
        preg_match('/<script type="application\/ld\+json">(.+?)<\/script>/s', $html, $coincidencias);

        $datos = json_decode($coincidencias[1], true);
        $this->assertIsArray($datos, 'El JSON-LD de la ficha no es JSON válido.');
        $this->assertSame(JSON_ERROR_NONE, json_last_error());

        return $datos;
    }
}
