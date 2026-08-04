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
use InvalidArgumentException;
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

        // La vuelta del pago va a una URL firmada y con caducidad, así que se
        // compara la ruta sin la firma, que cambia en cada petición.
        $this->post(route('pago.simulado.resolver', $transaccion), [
            'decision' => 'aprobar',
            'metodo' => 'pse',
        ])->assertRedirectContains(route('pago.estado', $transaccion));

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

    /**
     * Cuerpo y firma congelados a mano según https://developers.bold.co/webhook:
     * HMAC-SHA256 en HEXADECIMAL sobre el cuerpo codificado en Base64.
     *
     * Son literales a propósito: si el test recalculara la firma con la misma
     * fórmula que la implementación, un algoritmo equivocado se validaría a sí
     * mismo y todas las notificaciones reales de Bold caerían en el 401.
     */
    private const CUERPO_FIRMADO_POR_BOLD = '{"type":"SALE_APPROVED","data":{"metadata":{"reference":"ASO-2026-ABC123"}}}';

    private const FIRMA_DE_BOLD = 'd755cf47ba8abbf8862cf145adff446c29850f88e887525519bff34839ab8c79';

    private const LLAVE_DE_IDENTIDAD = 'llave-de-identidad-de-prueba';

    private function peticionDeBold(string $cuerpo, string $firma): Request
    {
        return Request::create('/webhooks/bold', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => $firma,
        ], $cuerpo);
    }

    public function test_la_firma_hmac_de_bold_se_valida_correctamente(): void
    {
        $pasarela = new PasarelaBold('llave', self::LLAVE_DE_IDENTIDAD, 'https://integrations.api.bold.co', true);

        $this->assertTrue(
            $pasarela->firmaValida($this->peticionDeBold(self::CUERPO_FIRMADO_POR_BOLD, self::FIRMA_DE_BOLD)),
            'La firma documentada por Bold debe aceptarse tal cual llega.'
        );

        $this->assertFalse(
            $pasarela->firmaValida($this->peticionDeBold(self::CUERPO_FIRMADO_POR_BOLD.' ', self::FIRMA_DE_BOLD)),
            'Un cuerpo alterado invalida la firma.'
        );

        $this->assertFalse(
            $pasarela->firmaValida($this->peticionDeBold(self::CUERPO_FIRMADO_POR_BOLD, str_repeat('a', 64))),
            'Una firma del largo correcto pero falsa no pasa.'
        );
    }

    /**
     * Fija el FORMATO además del valor: 64 caracteres hexadecimales. Es lo que
     * separa el esquema real de Bold de un HMAC en Base64, que es la confusión
     * que rompía la integración.
     */
    public function test_la_firma_esperada_de_bold_es_hexadecimal_de_64_caracteres(): void
    {
        $this->assertSame(64, strlen(self::FIRMA_DE_BOLD));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', self::FIRMA_DE_BOLD);
    }

    public function test_en_sandbox_bold_firma_con_llave_vacia_y_se_acepta(): void
    {
        $pasarela = new PasarelaBold('llave', '', 'https://integrations.api.bold.co', true);

        $firmaConLlaveVacia = '03554e57ee16d6749255dde76eff6772f30f0189a3254d1d950bb1a24ae232d7';

        $this->assertTrue(
            $pasarela->firmaValida($this->peticionDeBold(self::CUERPO_FIRMADO_POR_BOLD, $firmaConLlaveVacia)),
            'El sandbox de Bold firma con llave vacía; si se rechaza no hay forma de probar la integración.'
        );
    }

    public function test_fuera_de_sandbox_una_llave_vacia_rechaza_cualquier_firma(): void
    {
        $pasarela = new PasarelaBold('llave', '', 'https://integrations.api.bold.co', false);

        $firmaConLlaveVacia = '03554e57ee16d6749255dde76eff6772f30f0189a3254d1d950bb1a24ae232d7';

        $this->assertFalse(
            $pasarela->firmaValida($this->peticionDeBold(self::CUERPO_FIRMADO_POR_BOLD, $firmaConLlaveVacia)),
            'Sin BOLD_SECRET en producción se estaría firmando en blanco: hay que rechazar.'
        );
    }

    /**
     * El sandbox es la única excusa para una llave vacía, así que tiene que
     * pedirse a propósito. Si `BOLD_SANDBOX` valiera `true` por omisión, a un
     * despliegue le bastaría con olvidar la variable para firmar en blanco.
     */
    public function test_sin_la_variable_de_sandbox_la_pasarela_no_queda_en_modo_pruebas(): void
    {
        $anterior = getenv('BOLD_SANDBOX');

        // Se relee el archivo de configuración con la variable ausente, que es
        // el caso que importa: el .env de cada máquina no puede decidir esto.
        unset($_ENV['BOLD_SANDBOX'], $_SERVER['BOLD_SANDBOX']);
        putenv('BOLD_SANDBOX');

        try {
            $configuracion = require config_path('pagos.php');
        } finally {
            if ($anterior !== false) {
                putenv("BOLD_SANDBOX={$anterior}");
                $_ENV['BOLD_SANDBOX'] = $anterior;
                $_SERVER['BOLD_SANDBOX'] = $anterior;
            }
        }

        $this->assertFalse(
            $configuracion['bold']['sandbox'],
            'BOLD_SANDBOX debe fallar cerrado: sin la variable no hay modo pruebas, '
            .'porque el modo pruebas es lo único que permite firmar con llave vacía.'
        );
    }

    // --- Cerrojo del driver de pagos ---

    public function test_el_contenedor_rechaza_la_pasarela_simulada_fuera_de_local(): void
    {
        config()->set('pagos.driver', 'fake');
        $this->app->detectEnvironment(fn (): string => 'production');
        $this->app->forgetInstance(PasarelaDePago::class);

        $this->expectException(InvalidArgumentException::class);

        $this->app->make(PasarelaDePago::class);
    }

    public function test_sin_payment_driver_el_contenedor_no_adivina_la_pasarela(): void
    {
        config()->set('pagos.driver', null);
        $this->app->forgetInstance(PasarelaDePago::class);

        $this->expectException(InvalidArgumentException::class);

        $this->app->make(PasarelaDePago::class);
    }

    public function test_el_webhook_responde_404_cuando_la_pasarela_no_es_bold(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Camila Restrepo',
            'correo' => 'camila@ejemplo.test',
            'telefono' => '3115540098',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        // Driver `fake`: el webhook público no tiene nada legítimo que atender
        // y no puede servir de aprobador universal de pagos.
        $this->postJson(route('webhooks.bold'), [
            'referencia' => $transaccion->referencia,
            'decision' => 'aprobar',
        ])->assertNotFound();

        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->fresh()->estado);
        $this->assertSame(EstadoInscripcion::Registrada, Inscripcion::firstOrFail()->estado);
    }

    // --- La referencia no es una credencial ---

    public function test_la_referencia_de_pago_no_es_enumerable(): void
    {
        $referencias = collect(range(1, 50))->map(fn (): string => Transaccion::generarReferencia());

        $this->assertCount(50, $referencias->unique(), 'No puede haber colisiones.');

        foreach ($referencias as $referencia) {
            $this->assertMatchesRegularExpression('/^ASO-\d{4}-[0-9A-F]{16}$/', $referencia);
        }
    }

    public function test_la_pagina_de_estado_exige_firma_y_no_muestra_el_correo_del_inscrito(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Andrés Lozano',
            'correo' => 'andres.lozano@ejemplo.test',
            'telefono' => '3145520771',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        $this->get(route('pago.estado', $transaccion))
            ->assertForbidden();

        $this->get($transaccion->urlDeEstado())
            ->assertSuccessful()
            ->assertDontSee('andres.lozano@ejemplo.test');
    }

    public function test_el_retorno_de_la_pasarela_tolera_parametros_ajenos_y_entrega_la_pagina_firmada(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Lucía Marín',
            'correo' => 'lucia@ejemplo.test',
            'telefono' => '3128890443',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        // Bold puede añadir lo que quiera a la URL de retorno.
        $this->get(route('pago.retorno', $transaccion).'?bold_order_id=LNK_H7S4xxx&status=approved')
            ->assertRedirectContains(route('pago.estado', $transaccion))
            ->assertRedirectContains('signature=');
    }

    // --- Conciliación económica ---

    public function test_un_abono_parcial_no_salda_la_cartera_completa(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create();
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 500000,
            'meses_mora' => 10,
            'actualizado_at' => now(),
        ]);

        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'asociado_id' => $asociado->id,
            'monto' => 50000,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
        ]);

        app(RegistroDePagos::class)->aplicarConfirmacion(new ResultadoDePago(
            referencia: $transaccion->referencia,
            estado: EstadoTransaccion::Aprobada,
            metodo: MetodoPago::Pse,
            monto: 50000.0,
            moneda: 'COP',
        ));

        $cartera = $asociado->cartera->fresh();

        $this->assertSame('450000.00', $cartera->saldo_pendiente, 'Un abono de 50.000 no borra una deuda de 500.000.');
        $this->assertFalse($cartera->estaAlDia());
        $this->assertGreaterThan(0, $cartera->meses_mora);
        $this->assertLessThan(10, $cartera->meses_mora, 'Pagar tiene que reducir la mora.');
    }

    public function test_una_confirmacion_con_monto_distinto_no_se_aplica(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Paula Henao',
            'correo' => 'paula@ejemplo.test',
            'telefono' => '3167780012',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        // La transacción vale 30.000 y la pasarela dice haber cobrado 1.000.
        app(RegistroDePagos::class)->aplicarConfirmacion(new ResultadoDePago(
            referencia: $transaccion->referencia,
            estado: EstadoTransaccion::Aprobada,
            metodo: MetodoPago::Pse,
            monto: 1000.0,
            moneda: 'COP',
        ));

        $this->assertSame(
            EstadoTransaccion::Pendiente,
            $transaccion->fresh()->estado,
            'Un cobro que no cuadra se queda sin resolver, para revisarlo a mano.'
        );
        $this->assertSame(EstadoInscripcion::Registrada, Inscripcion::firstOrFail()->estado);
    }

    public function test_una_confirmacion_en_otra_moneda_no_se_aplica(): void
    {
        $evento = $this->eventoPago();

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Iván Betancur',
            'correo' => 'ivan@ejemplo.test',
            'telefono' => '3103340876',
            'acepta_datos' => '1',
        ]);

        $transaccion = Transaccion::firstOrFail();

        app(RegistroDePagos::class)->aplicarConfirmacion(new ResultadoDePago(
            referencia: $transaccion->referencia,
            estado: EstadoTransaccion::Aprobada,
            metodo: MetodoPago::Tarjeta,
            monto: 30000.0,
            moneda: 'USD',
        ));

        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->fresh()->estado);
    }

    public function test_pagar_dos_veces_seguidas_no_abre_dos_cobros(): void
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
        $this->actingAs($duenio->fresh())->post(route('mi-cuenta.pagar'));

        $this->assertSame(1, Transaccion::count(), 'Volver a pulsar «Pagar ahora» retoma el cobro pendiente.');
    }

    public function test_una_notificacion_sin_encabezado_de_firma_se_rechaza(): void
    {
        $pasarela = new PasarelaBold('llave', self::LLAVE_DE_IDENTIDAD, 'https://integrations.api.bold.co', true);

        $peticion = Request::create('/webhooks/bold', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], self::CUERPO_FIRMADO_POR_BOLD);

        $this->assertFalse($pasarela->firmaValida($peticion));
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

        config()->set('pagos.driver', 'bold');
        config()->set('pagos.bold.secret', self::LLAVE_DE_IDENTIDAD);
        app()->forgetInstance(PasarelaDePago::class);

        $cuerpo = json_encode([
            'type' => 'SALE_APPROVED',
            'data' => ['metadata' => ['reference' => $transaccion->referencia], 'payment_method' => 'PSE'],
        ]);

        $this->call(
            'POST',
            route('webhooks.bold'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($cuerpo), self::LLAVE_DE_IDENTIDAD),
            ],
            content: $cuerpo
        )->assertSuccessful();

        $this->assertSame(EstadoTransaccion::Aprobada, $transaccion->fresh()->estado);
        $this->assertSame(MetodoPago::Pse, $transaccion->fresh()->metodo);
        $this->assertSame(EstadoInscripcion::Confirmada, Inscripcion::firstOrFail()->estado);
    }
}
