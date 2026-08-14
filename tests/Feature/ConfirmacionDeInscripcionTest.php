<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoPublicacion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Transaccion;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * «Ninguna inscripción se confirma sin una transacción aprobada» era una regla
 * escrita en el texto de ayuda del formulario, no en el código: el selector de
 * estado del panel era editable, así que la secretaría podía marcar
 * «Confirmada» a mano y regalar un cupo de un evento de pago.
 */
class ConfirmacionDeInscripcionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        // El cerrojo vigila a quien escribe con sesión abierta en el panel:
        // las semillas, los comandos y el webhook de la pasarela pasan por
        // otro camino. La secretaría es justo el rol del hallazgo.
        $secretaria = User::factory()->create();
        $secretaria->syncRoles([User::ROL_SUBADMIN]);
        $this->actingAs($secretaria->fresh());
    }

    private function evento(float $precio): Evento
    {
        return Evento::create([
            'titulo' => 'ExpoBar de prueba',
            'slug' => 'expobar-'.($precio > 0 ? 'pago' : 'gratis'),
            'descripcion' => 'Evento de prueba.',
            'fecha_inicio' => now()->addDays(20),
            'precio' => $precio,
            'permite_inscripcion' => true,
            'estado' => EstadoPublicacion::Publicado,
        ]);
    }

    private function inscripcion(Evento $evento, EstadoInscripcion $estado = EstadoInscripcion::Registrada): Inscripcion
    {
        return Inscripcion::create([
            'evento_id' => $evento->id,
            'nombre' => 'Marcela Ríos',
            'correo' => 'marcela@ejemplo.test',
            'telefono' => '3145520987',
            'estado' => $estado,
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ]);
    }

    private function transaccion(EstadoTransaccion $estado): Transaccion
    {
        return Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Evento,
            'monto' => 30000,
            'moneda' => 'COP',
            'estado' => $estado,
            'metodo' => MetodoPago::Pse,
        ]);
    }

    public function test_no_se_confirma_a_mano_una_inscripcion_de_pago_sin_transaccion(): void
    {
        $inscripcion = $this->inscripcion($this->evento(50000));

        $this->expectException(AuthorizationException::class);

        $inscripcion->update(['estado' => EstadoInscripcion::Confirmada]);
    }

    public function test_tampoco_vale_una_transaccion_que_no_esta_aprobada(): void
    {
        $inscripcion = $this->inscripcion($this->evento(50000));
        $inscripcion->update(['transaccion_id' => $this->transaccion(EstadoTransaccion::Pendiente)->id]);

        $this->expectException(AuthorizationException::class);

        $inscripcion->update(['estado' => EstadoInscripcion::Confirmada]);
    }

    public function test_con_la_transaccion_aprobada_si_se_confirma(): void
    {
        $inscripcion = $this->inscripcion($this->evento(50000));
        $inscripcion->update(['transaccion_id' => $this->transaccion(EstadoTransaccion::Aprobada)->id]);

        $inscripcion->update(['estado' => EstadoInscripcion::Confirmada]);

        $this->assertTrue($inscripcion->fresh()->estaConfirmada());
    }

    public function test_un_evento_gratuito_se_confirma_sin_transaccion(): void
    {
        $inscripcion = $this->inscripcion($this->evento(0));

        $inscripcion->update(['estado' => EstadoInscripcion::Confirmada]);

        $this->assertTrue($inscripcion->fresh()->estaConfirmada());
    }

    /**
     * El cerrojo vigila el cambio de estado, no cualquier escritura: corregir
     * el teléfono de alguien ya confirmado tiene que seguir funcionando.
     */
    public function test_editar_otros_campos_de_una_inscripcion_confirmada_sigue_funcionando(): void
    {
        $inscripcion = $this->inscripcion($this->evento(0), EstadoInscripcion::Confirmada);

        $inscripcion->update(['telefono' => '3001234567']);

        $this->assertSame('3001234567', $inscripcion->fresh()->telefono);
    }
}
