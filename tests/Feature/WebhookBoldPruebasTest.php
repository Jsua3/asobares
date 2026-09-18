<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Transaccion;
use App\Pagos\PasarelaDePago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class WebhookBoldPruebasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('pagos.bold.sandbox_webhook_link', 'LNK_PRUEBA_EXPLICITA');
    }

    private function notificar(string $ruta, string $tipo, string $referencia, ?string $firma = null, string $secreto = ''): TestResponse
    {
        $cuerpo = json_encode([
            'id' => 'EVT_PRUEBA_1',
            'type' => $tipo,
            'data' => [
                'payment_id' => 'PAY_PRUEBA_1',
                'metadata' => ['reference' => $referencia],
                'amount' => ['total' => 30000, 'currency' => 'COP'],
            ],
        ], JSON_THROW_ON_ERROR);

        return $this->call('POST', route($ruta), server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => $firma ?? hash_hmac('sha256', base64_encode($cuerpo), $secreto),
        ], content: $cuerpo);
    }

    /** @return array{Transaccion, Cartera} */
    private function cobroReal(string $link): array
    {
        $asociado = Asociado::factory()->publicado()->create();
        $cartera = Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 30000,
            'meses_mora' => 1,
            'actualizado_at' => now(),
        ]);
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'asociado_id' => $asociado->id,
            'monto' => 30000,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
            'bold_payment_link' => $link,
            'payload' => ['pasarela' => 'bold'],
        ]);

        return [$transaccion, $cartera];
    }

    public function test_el_endpoint_de_pruebas_acepta_los_cuatro_eventos_firmados_con_secreto_vacio(): void
    {
        foreach (['SALE_APPROVED', 'SALE_REJECTED', 'VOID_APPROVED', 'VOID_REJECTED'] as $tipo) {
            $this->notificar('webhooks.bold.pruebas', $tipo, 'LNK_PRUEBA_EXPLICITA')->assertOk();
        }

        $this->assertSame(0, Transaccion::count());
    }

    public function test_el_endpoint_de_pruebas_nunca_acredita_una_cartera_real_aunque_coincida_el_link(): void
    {
        [$transaccion, $cartera] = $this->cobroReal('LNK_PRUEBA_EXPLICITA');

        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', 'LNK_PRUEBA_EXPLICITA')->assertOk();

        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->fresh()->estado);
        $this->assertSame('30000.00', $cartera->fresh()->saldo_pendiente);
        $this->assertSame('bold', $transaccion->fresh()->payload['pasarela']);
    }

    public function test_el_endpoint_de_pruebas_rechaza_referencias_no_habilitadas(): void
    {
        [$transaccion, $cartera] = $this->cobroReal('LNK_REAL_1');

        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', 'LNK_REAL_1')->assertNotFound();
        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', $transaccion->referencia)->assertNotFound();

        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->fresh()->estado);
        $this->assertSame('30000.00', $cartera->fresh()->saldo_pendiente);
    }

    public function test_el_endpoint_de_pruebas_rechaza_firma_invalida_y_eventos_desconocidos(): void
    {
        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', 'LNK_PRUEBA_EXPLICITA', 'firma-falsa')
            ->assertUnauthorized();
        $this->notificar('webhooks.bold.pruebas', 'EVENTO_DESCONOCIDO', 'LNK_PRUEBA_EXPLICITA')
            ->assertUnprocessable();
    }

    public function test_un_reintento_de_aprobacion_en_pruebas_no_produce_efectos_economicos(): void
    {
        [$transaccion, $cartera] = $this->cobroReal('LNK_PRUEBA_EXPLICITA');

        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', 'LNK_PRUEBA_EXPLICITA')->assertOk();
        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', 'LNK_PRUEBA_EXPLICITA')->assertOk();

        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->fresh()->estado);
        $this->assertSame('30000.00', $cartera->fresh()->saldo_pendiente);
        $this->assertSame(1, Transaccion::count());
    }

    public function test_sin_un_link_de_prueba_configurado_la_ruta_no_se_habilita(): void
    {
        config()->set('pagos.bold.sandbox_webhook_link', '');

        $this->notificar('webhooks.bold.pruebas', 'SALE_APPROVED', 'LNK_PRUEBA_EXPLICITA')->assertNotFound();
    }

    public function test_el_endpoint_productivo_sigue_exigiendo_su_secreto_y_aplica_solo_el_pago_real(): void
    {
        [$transaccion, $cartera] = $this->cobroReal('LNK_REAL_1');
        config()->set('pagos.driver', 'bold');
        config()->set('pagos.bold.secret', 'secreto-productivo-ficticio');
        config()->set('pagos.bold.sandbox', false);
        app()->forgetInstance(PasarelaDePago::class);

        $this->notificar('webhooks.bold', 'SALE_APPROVED', 'LNK_REAL_1')->assertUnauthorized();
        $this->assertSame(EstadoTransaccion::Pendiente, $transaccion->fresh()->estado);

        $this->notificar('webhooks.bold', 'SALE_APPROVED', 'LNK_REAL_1', secreto: 'secreto-productivo-ficticio')->assertOk();

        $this->assertSame(EstadoTransaccion::Aprobada, $transaccion->fresh()->estado);
        $this->assertSame('0.00', $cartera->fresh()->saldo_pendiente);
    }
}
