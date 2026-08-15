<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Transaccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * G12 (Ley 1581): las inscripciones a eventos guardaban nombre, correo y
 * teléfono sin ningún plazo. El fin autorizado —participar en el evento— se
 * agota cuando el evento pasa; el registro contable del pago no se pierde,
 * porque `transacciones.inscripcion_id` es `nullOnDelete`: la transacción
 * (referencia, monto, estado) sobrevive sin el dato personal.
 */
class DepuracionDeInscripcionesTest extends TestCase
{
    use RefreshDatabase;

    private function evento(array $atributos = []): Evento
    {
        return Evento::create(array_merge([
            'titulo' => 'Capacitación de prueba',
            'slug' => 'capacitacion-'.Str::lower(Str::random(8)),
            'descripcion' => 'Para la depuración.',
            'fecha_inicio' => now()->subMonths(25),
            'precio' => 0,
            'permite_inscripcion' => false,
            'estado' => EstadoPublicacion::Publicado,
        ], $atributos));
    }

    private function inscripcion(Evento $evento, array $atributos = []): Inscripcion
    {
        return Inscripcion::create(array_merge([
            'evento_id' => $evento->id,
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'acepta_datos' => true,
            'consentimiento_at' => now()->subMonths(25),
        ], $atributos));
    }

    public function test_borra_las_inscripciones_de_eventos_pasados_hace_mucho(): void
    {
        $this->inscripcion($this->evento());
        $sobreviviente = $this->inscripcion($this->evento(['fecha_inicio' => now()->subMonth()]));

        $this->artisan('inscripciones:depurar')->assertExitCode(0);

        $this->assertSame(1, Inscripcion::count());
        $this->assertNotNull($sobreviviente->fresh());
    }

    /**
     * El plazo corre desde que el evento TERMINA, no desde que empieza: un
     * congreso que arrancó hace 25 meses pero cerró hace uno todavía está
     * dentro del plazo.
     */
    public function test_usa_la_fecha_de_fin_cuando_existe(): void
    {
        $largo = $this->evento([
            'fecha_inicio' => now()->subMonths(25),
            'fecha_fin' => now()->subMonth(),
        ]);

        $this->inscripcion($largo);

        $this->artisan('inscripciones:depurar');

        $this->assertSame(1, Inscripcion::count());
    }

    public function test_la_transaccion_sobrevive_sin_el_dato_personal(): void
    {
        $inscripcion = $this->inscripcion($this->evento());

        $transaccion = Transaccion::create([
            'referencia' => 'PRUEBA-DEPURACION-01',
            'concepto' => 'evento',
            'inscripcion_id' => $inscripcion->id,
            'monto' => 30000,
        ]);

        $this->artisan('inscripciones:depurar');

        $this->assertSame(0, Inscripcion::count());
        $this->assertNotNull($transaccion->fresh(), 'El registro contable no puede borrarse con el dato personal.');
        $this->assertNull($transaccion->fresh()->inscripcion_id);
    }

    public function test_el_plazo_sale_de_la_configuracion(): void
    {
        config(['retencion.inscripciones_meses' => 36]);

        $this->inscripcion($this->evento());

        $this->artisan('inscripciones:depurar');

        $this->assertSame(1, Inscripcion::count(), 'Con 36 meses de plazo, un evento de hace 25 aún no vence.');
    }

    public function test_con_pretend_no_borra_nada(): void
    {
        $this->inscripcion($this->evento());

        $this->artisan('inscripciones:depurar', ['--pretend' => true])->assertExitCode(0);

        $this->assertSame(1, Inscripcion::count());
        $this->assertSame(0, Activity::where('log_name', 'inscripciones')->count());
    }

    public function test_registra_en_la_bitacora_cuando_borra(): void
    {
        $this->inscripcion($this->evento());

        $this->artisan('inscripciones:depurar');

        $registro = Activity::where('log_name', 'inscripciones')->first();

        $this->assertNotNull($registro);
        $this->assertSame('deleted', $registro->event);
        $this->assertStringContainsString('1 inscripciones', $registro->description);
    }

    /**
     * Un plazo en cero convierte la purga en «borra todo» (el límite sería
     * ahora mismo), y a cero se llega solo con una variable vacía o un
     * config:cache viejo. Eso es un error de configuración, no una orden.
     */
    public function test_aborta_si_el_plazo_es_invalido(): void
    {
        config(['retencion.inscripciones_meses' => 0]);

        $this->inscripcion($this->evento());

        $this->artisan('inscripciones:depurar')->assertExitCode(1);

        $this->assertSame(1, Inscripcion::count());
    }
}
