<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Models\Aliado;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Municipio;
use App\Models\Setting;
use App\Models\Transaccion;
use Database\Seeders\MunicipioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Un evento de aliado pone en el sitio el nombre y la web de alguien que no
 * es el gremio. Estas pruebas fijan cuándo puede hacerlo.
 *
 * - Solo si el aliado sale al sitio él mismo: aprobado, activo y dentro de
 *   «a todos o nada» de las alcaldías. Un aliado nace apagado en el panel.
 * - Nunca atribuido a ASOBARES cuando el aliado ya no está.
 * - Sin inscripción ni cobro del gremio: el dinero entraría a la cuenta de
 *   Bold del gremio y los datos de quien se inscribe quedarían a su cargo.
 *
 * Cada caso mira las cinco salidas públicas —riel, calendario, ficha, JSON-LD
 * y sitemap—, porque el defecto original se colaba por las tres primeras a la
 * vez y una sola compuerta (`Evento::scopeVisibleAlPublico`) las gobierna.
 */
class EventosDeAliadosAlPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_aliado_en_borrador_no_publica_su_evento_ni_su_nombre(): void
    {
        $aliado = Aliado::factory()->create([
            'nombre' => 'Aliado Todavia Sin Aprobar',
            'url' => 'https://aliado-sin-aprobar.test',
            'estado' => EstadoPublicacion::Borrador,
            'activo' => true,
        ]);
        $evento = $this->eventoDe($aliado, 'Foro del aliado en borrador');

        $this->assertNoSaleEnNingunaParte($evento, $aliado);
    }

    public function test_un_aliado_apagado_no_publica_su_evento_ni_su_nombre(): void
    {
        $aliado = Aliado::factory()->create([
            'nombre' => 'Aliado Aprobado Pero Apagado',
            'url' => 'https://aliado-apagado.test',
            'estado' => EstadoPublicacion::Publicado,
            'activo' => false,
        ]);
        $evento = $this->eventoDe($aliado, 'Foro del aliado apagado');

        $this->assertNoSaleEnNingunaParte($evento, $aliado);
    }

    public function test_un_aliado_visible_si_publica_su_evento_en_las_cinco_salidas(): void
    {
        $aliado = Aliado::factory()->visible()->create([
            'nombre' => 'Aliado Visible De Verdad',
            'url' => 'https://aliado-visible.test',
        ]);
        $evento = $this->eventoDe($aliado, 'Foro del aliado visible');

        $this->get(route('eventos.index'))->assertOk()->assertSee('Foro del aliado visible')->assertSee('Aliado Visible De Verdad');
        $this->get($this->calendarioDe($evento))->assertOk()->assertSee('Foro del aliado visible');
        $this->get(route('eventos.show', $evento))->assertOk()->assertSee('Aliado Visible De Verdad')->assertSee('aliado-visible.test', false);
        $this->get(route('sitemap'))->assertOk()->assertSee(route('eventos.show', $evento), false);
    }

    /**
     * Una alcaldía suelta no sale entre los aliados de la portada, así que
     * tampoco puede salir como organizadora de un evento: la ficha la
     * nombraría sola, que es justo lo que la regla existe para impedir.
     */
    public function test_una_alcaldia_suelta_no_sale_como_organizadora(): void
    {
        $this->seed(MunicipioSeeder::class);
        $municipio = Municipio::query()->firstOrFail();
        $alcaldia = Aliado::factory()->visible()->institucional()->create([
            'nombre' => "Alcaldía de {$municipio->nombre} Suelta",
            'url' => 'https://alcaldia-suelta.test',
            'municipio_id' => $municipio->getKey(),
        ]);
        $evento = $this->eventoDe($alcaldia, 'Feria de la alcaldía suelta');

        $this->assertNoSaleEnNingunaParte($evento, $alcaldia);
    }

    public function test_con_todas_las_alcaldias_la_organizadora_si_sale(): void
    {
        $this->seed(MunicipioSeeder::class);
        $alcaldias = Municipio::query()->get()->map(fn (Municipio $municipio): Aliado => Aliado::factory()->visible()->institucional()->create([
            'nombre' => "Alcaldía de {$municipio->nombre}",
            'municipio_id' => $municipio->getKey(),
        ]));
        $evento = $this->eventoDe($alcaldias->first(), 'Feria con el juego completo');

        $this->get(route('eventos.index'))->assertOk()->assertSee('Feria con el juego completo');
        $this->get(route('eventos.show', $evento))->assertOk();
    }

    /**
     * Un comercial atado a un municipio no es una alcaldía: con el juego de
     * alcaldías incompleto, su evento sigue saliendo.
     */
    public function test_un_juego_incompleto_de_alcaldias_no_tumba_a_los_demas_aliados(): void
    {
        $this->seed(MunicipioSeeder::class);
        $municipio = Municipio::query()->firstOrFail();
        Aliado::factory()->visible()->institucional()->create(['municipio_id' => $municipio->getKey()]);
        $comercial = Aliado::factory()->visible()->create([
            'nombre' => 'Comercial Con Sede',
            'municipio_id' => $municipio->getKey(),
        ]);
        $evento = $this->eventoDe($comercial, 'Cata del comercial con sede');

        $this->get(route('eventos.index'))->assertOk()->assertSee('Cata del comercial con sede');
        $this->get(route('eventos.show', $evento))->assertOk();
    }

    /**
     * La llave foránea pone `aliado_id` en null al borrar el aliado. Sin la
     * compuerta, la ficha caía al organizador por defecto y decía «ASOBARES».
     */
    public function test_un_evento_de_aliado_sin_aliado_no_sale_atribuido_al_gremio(): void
    {
        $aliado = Aliado::factory()->visible()->create(['nombre' => 'Aliado Que Se Va A Borrar']);
        $evento = $this->eventoDe($aliado, 'Foro huerfano de aliado');

        $aliado->delete();
        $this->assertNull($evento->refresh()->aliado_id);
        $this->assertSame(OrigenEvento::Aliado, $evento->origen);

        $this->get(route('eventos.index'))->assertOk()->assertDontSee('Foro huerfano de aliado');
        $this->get(route('eventos.show', $evento))->assertNotFound();
        $this->get($this->calendarioDe($evento))->assertOk()->assertDontSee('Foro huerfano de aliado');
    }

    public function test_el_gremio_no_inscribe_ni_cobra_un_evento_de_aliado(): void
    {
        $aliado = Aliado::factory()->visible()->create(['nombre' => 'Aliado Que Cobra Aparte']);
        $evento = $this->eventoDe($aliado, 'Taller pago del aliado', ['precio' => 80000]);

        // Una fila anterior a la regla puede traer la inscripción encendida:
        // se fuerza por la base, saltándose el guardado.
        Evento::query()->whereKey($evento->getKey())->update(['permite_inscripcion' => true]);
        $evento->refresh();
        $this->assertTrue($evento->permite_inscripcion);
        $this->assertFalse($evento->admiteInscripciones());

        $ficha = $this->get(route('eventos.show', $evento))->assertOk();
        $ficha->assertDontSee('id="inscripcion"', false)
            ->assertDontSee('Inscribirme y pagar')
            ->assertSee('La inscripción la gestiona directamente Aliado Que Cobra Aparte.');

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Persona Interesada',
            'correo' => 'persona@ejemplo.test',
            'telefono' => '3145520987',
            'acepta_datos' => '1',
        ])->assertRedirect();

        $this->assertSame(0, Inscripcion::count(), 'El gremio inscribió a nombre de un aliado.');
        $this->assertSame(0, Transaccion::count(), 'El gremio abrió un cobro por un evento de aliado.');
    }

    public function test_guardar_un_evento_de_aliado_apaga_la_inscripcion_en_linea(): void
    {
        $aliado = Aliado::factory()->visible()->create();

        $evento = $this->eventoDe($aliado, 'Evento de aliado con inscripcion', ['permite_inscripcion' => true]);

        $this->assertFalse($evento->refresh()->permite_inscripcion);
    }

    public function test_el_registro_externo_de_un_aliado_no_se_presenta_como_de_la_nacional(): void
    {
        $aliado = Aliado::factory()->visible()->create(['nombre' => 'Camara Con Registro Propio']);
        $evento = $this->eventoDe($aliado, 'Congreso con registro propio', [
            'enlace_externo' => 'https://registro-propio.test',
        ]);

        $this->get(route('eventos.show', $evento))
            ->assertOk()
            ->assertSee('La inscripción de este evento la gestiona directamente Camara Con Registro Propio.')
            ->assertDontSee('Registrarme en la Nacional')
            ->assertDontSee('Asobares Colombia');
    }

    public function test_el_modelo_rechaza_un_evento_que_termina_antes_de_empezar(): void
    {
        $this->expectException(ValidationException::class);

        Evento::factory()->create([
            'fecha_inicio' => now()->addDays(5)->setTime(18, 0),
            'fecha_fin' => now()->addDays(5)->setTime(9, 0),
        ]);
    }

    public function test_la_portada_solo_pinta_eventos_del_gremio(): void
    {
        Evento::factory()->publicado()->create([
            'titulo' => 'Capacitacion propia para la portada',
            'fecha_inicio' => now()->addDays(4)->setTime(9, 0),
        ]);
        $aliado = Aliado::factory()->visible()->create();
        $this->eventoDe($aliado, 'Foro de aliado fuera de la portada');

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('Capacitacion propia para la portada')
            ->assertDontSee('Foro de aliado fuera de la portada');
    }

    public function test_el_calendario_usa_la_entradilla_administrable_y_no_promete_solo_gremio(): void
    {
        Setting::query()->create([
            'clave' => 'eventos_intro',
            'valor' => 'Entradilla escrita por la oficina.',
            'tipo' => 'text',
            'grupo' => 'eventos',
        ]);

        $this->get(route('eventos.calendario', [2026, '09']))
            ->assertOk()
            ->assertSee('Entradilla escrita por la oficina.')
            ->assertDontSee('Solo eventos del gremio');
    }

    public function test_la_migracion_corrige_la_entradilla_sembrada_y_respeta_la_de_la_oficina(): void
    {
        $migracion = require database_path('migrations/2026_09_16_172442_actualiza_la_entradilla_de_eventos_con_los_aliados.php');
        $anterior = 'Solo eventos del gremio: ferias, foros y formación para los establecimientos del Quindío.';

        $sembrada = Setting::query()->create(['clave' => 'eventos_intro', 'valor' => $anterior, 'tipo' => 'text', 'grupo' => 'eventos']);
        $this->assertSame($anterior, ajuste('eventos_intro'));

        $migracion->up();

        $this->assertStringContainsString('sus aliados', $sembrada->refresh()->valor);
        $this->assertStringContainsString('sus aliados', ajuste('eventos_intro'), 'La caché de ajustes siguió sirviendo la entradilla anterior.');

        $sembrada->update(['valor' => 'Texto propio de la oficina.']);
        $migracion->up();

        $this->assertSame('Texto propio de la oficina.', $sembrada->refresh()->valor);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function eventoDe(Aliado $aliado, string $titulo, array $atributos = []): Evento
    {
        return Evento::factory()->publicado()->create([
            'titulo' => $titulo,
            'origen' => OrigenEvento::Aliado,
            'aliado_id' => $aliado->getKey(),
            'fecha_inicio' => now()->addDays(6)->setTime(10, 0),
            ...$atributos,
        ]);
    }

    private function calendarioDe(Evento $evento): string
    {
        return route('eventos.calendario', [$evento->fecha_inicio->year, $evento->fecha_inicio->format('m')]);
    }

    private function assertNoSaleEnNingunaParte(Evento $evento, Aliado $aliado): void
    {
        $this->get(route('eventos.index'))
            ->assertOk()
            ->assertDontSee($evento->titulo)
            ->assertDontSee($aliado->nombre);

        $this->get($this->calendarioDe($evento))
            ->assertOk()
            ->assertDontSee($evento->titulo)
            ->assertDontSee($aliado->nombre);

        $this->get(route('eventos.show', $evento))->assertNotFound();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Persona Interesada',
            'correo' => 'persona@ejemplo.test',
            'telefono' => '3145520987',
            'acepta_datos' => '1',
        ])->assertNotFound();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertDontSee(route('eventos.show', $evento), false);

        $this->assertFalse($evento->esVisibleAlPublico());
    }
}
