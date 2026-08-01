<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoPublicacion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Transaccion;
use App\Models\User;
use App\Pagos\PasarelaBold;
use App\Pagos\PasarelaDePago;
use App\Pagos\ResultadoDePago;
use App\Services\RegistroDePagos;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Regla dura del dominio: ninguna inscripción se confirma y ninguna cartera
 * se salda sin una transacción aprobada.
 */
class FlujoDePagoTest extends TestCase
{
    use RefreshDatabase;

    private function eventoPago(): Evento
    {
        return Evento::create([
            'titulo' => 'ExpoBar de prueba',
            'slug' => 'expobar-de-prueba',
            'descripcion' => 'Evento con costo.',
            'fecha_inicio' => now()->addDays(20),
            'precio' => 30000,
            'permite_inscripcion' => true,
            'estado' => EstadoPublicacion::Publicado,
        ]);
    }

    public function test_inscribirse_a_un_evento_pago_crea_una_transaccion_pendiente_y_redirige_al_pago(): void
    {
        $evento = $this->eventoPago();

        $respuesta = $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Marcela Ríos',
            'correo' => 'marcela@ejemplo.test',
            'telefono' => '3145520987',
            'acepta_datos' => '1',
        ]);

        $inscripcion = Inscripcion::firstOrFail();
        $transaccion = Transaccion::firstOrFail();

        $this->assertSame(EstadoInscripcion::Registrada, $inscripcion->estado, 'Sin pago no hay confirmación.');
        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->estado);
        $this->assertSame(ConceptoTransaccion::Evento, $transaccion->concepto);
        $this->assertSame($transaccion->id, $inscripcion->transaccion_id);

        $respuesta->assertRedirect(route('pago.simulado', $transaccion));
    }

    public function test_un_evento_gratuito_se_inscribe_sin_pasar_por_la_pasarela(): void
    {
        $evento = $this->eventoPago();
        $evento->update(['precio' => 0]);

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Julián Ortiz',
            'correo' => 'julian@ejemplo.test',
            'telefono' => '3106647712',
            'acepta_datos' => '1',
        ])->assertRedirect(route('eventos.show', $evento));

        $this->assertSame(0, Transaccion::count());
        $this->assertSame(1, Inscripcion::count());
    }

    public function test_aprobar_el_pago_simulado_confirma_la_inscripcion(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Tatiana Gómez',
            'correo' => 'tatiana@ejemplo.test',
            'telefono' => '3122290043',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        $this->post(route('pago.simulado.resolver', $transaccion), [
            'decision' => 'aprobar',
            'metodo' => 'pse',
        ])->assertRedirect(route('pago.estado', $transaccion));

        $this->assertSame(EstadoTransaccion::Aprobada, $transaccion->fresh()->estado);
        $this->assertSame(EstadoInscripcion::Confirmada, Inscripcion::firstOrFail()->estado);
    }

    public function test_rechazar_el_pago_deja_la_inscripcion_sin_confirmar(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Ricardo Ibáñez',
            'correo' => 'ricardo@ejemplo.test',
            'telefono' => '3178830156',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        $this->post(route('pago.simulado.resolver', $transaccion), [
            'decision' => 'rechazar',
            'metodo' => 'tarjeta',
        ]);

        $this->assertSame(EstadoTransaccion::Rechazada, $transaccion->fresh()->estado);
        $this->assertSame(EstadoInscripcion::Registrada, Inscripcion::firstOrFail()->estado);
    }

    public function test_pagar_la_mensualidad_deja_la_cartera_al_dia(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create();
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 150000,
            'meses_mora' => 3,
            'actualizado_at' => now(),
        ]);

        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
        $duenio->syncRoles([User::ROL_ASOCIADO]);

        $this->actingAs($duenio->fresh())->post(route('mi-cuenta.pagar'));

        $transaccion = Transaccion::firstOrFail();
        $this->assertSame(ConceptoTransaccion::Mensualidad, $transaccion->concepto);
        $this->assertSame('150000.00', $transaccion->monto);

        $this->post(route('pago.simulado.resolver', $transaccion), [
            'decision' => 'aprobar',
            'metodo' => 'pse',
        ]);

        $cartera = $asociado->cartera->fresh();
        $this->assertTrue($cartera->estaAlDia());
        $this->assertSame(0, $cartera->meses_mora);
        $this->assertSame('0.00', $cartera->saldo_pendiente);
    }

    public function test_aplicar_dos_veces_la_misma_confirmacion_no_duplica_efectos(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Esteban Zuluaga',
            'correo' => 'esteban@ejemplo.test',
            'telefono' => '3134470091',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();
        $registro = app(RegistroDePagos::class);

        $resultado = new ResultadoDePago(
            referencia: $transaccion->referencia,
            estado: EstadoTransaccion::Aprobada,
            metodo: MetodoPago::Pse,
        );

        $registro->aplicarConfirmacion($resultado);

        // Un reintento de la pasarela no debe poder cambiar una transacción resuelta.
        $rechazoTardio = new ResultadoDePago(
            referencia: $transaccion->referencia,
            estado: EstadoTransaccion::Rechazada,
            metodo: MetodoPago::Pse,
        );
        $registro->aplicarConfirmacion($rechazoTardio);

        $this->assertSame(EstadoTransaccion::Aprobada, $transaccion->fresh()->estado);
    }

    public function test_el_webhook_de_bold_rechaza_una_firma_invalida(): void
    {
        config()->set('pagos.driver', 'bold');
        config()->set('pagos.bold.secret', 'secreto-de-prueba');

        $this->postJson(route('webhooks.bold'), ['type' => 'SALE_APPROVED'], [
            'x-bold-signature' => 'firma-falsa',
        ])->assertUnauthorized();
    }

    public function test_la_firma_hmac_de_bold_se_valida_correctamente(): void
    {
        $secreto = 'secreto-de-prueba';
        $pasarela = new PasarelaBold('llave', $secreto, 'https://integrations.api.bold.co', true);

        $cuerpo = json_encode(['type' => 'SALE_APPROVED', 'data' => ['reference' => 'ASO-2026-ABC123']]);
        $firma = base64_encode(hash_hmac('sha256', $cuerpo, $secreto, true));

        $peticion = Request::create('/webhooks/bold', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => $firma,
        ], $cuerpo);

        $this->assertTrue($pasarela->firmaValida($peticion));

        $peticionAlterada = Request::create('/webhooks/bold', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => $firma,
        ], $cuerpo.' ');

        $this->assertFalse($pasarela->firmaValida($peticionAlterada), 'Un cuerpo alterado invalida la firma.');
    }

    public function test_el_webhook_con_firma_valida_actualiza_la_transaccion_y_la_inscripcion(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Viviana Cardona',
            'correo' => 'viviana@ejemplo.test',
            'telefono' => '3160078829',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        $secreto = 'secreto-de-prueba';
        config()->set('pagos.driver', 'bold');
        config()->set('pagos.bold.secret', $secreto);
        app()->forgetInstance(PasarelaDePago::class);

        $cuerpo = json_encode([
            'type' => 'SALE_APPROVED',
            'data' => ['reference' => $transaccion->referencia, 'payment_method' => 'PSE'],
        ]);

        $this->call(
            'POST',
            route('webhooks.bold'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_BOLD_SIGNATURE' => base64_encode(hash_hmac('sha256', $cuerpo, $secreto, true)),
            ],
            content: $cuerpo
        )->assertSuccessful();

        $this->assertSame(EstadoTransaccion::Aprobada, $transaccion->fresh()->estado);
        $this->assertSame(MetodoPago::Pse, $transaccion->fresh()->metodo);
        $this->assertSame(EstadoInscripcion::Confirmada, Inscripcion::firstOrFail()->estado);
    }
}
