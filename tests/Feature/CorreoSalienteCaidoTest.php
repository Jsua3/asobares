<?php

namespace Tests\Feature;

use App\Enums\TipoMensaje;
use App\Models\Asociado;
use App\Models\Mensaje;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

/**
 * El correo saliente se cae —y en producción estuvo caído desde el primer
 * despliegue, porque el SMTP nunca se contrató (D-07)—, y cuando se cae no
 * puede llevarse por delante la petición que lo disparó: la PQR ya quedó
 * radicada y la postulación ya quedó guardada. El ciudadano necesita su
 * número de radicado más que el acuse, y el establecimiento ve la
 * postulación en su cuenta aunque el aviso no llegue (bitácora §33.4, D-23).
 *
 * La suite corre con `MAIL_MAILER=array`, que nunca falla, así que estas
 * pruebas apuntan el transporte SMTP de verdad a un puerto cerrado de la
 * propia máquina: la conexión se rechaza y Symfony Mailer lanza exactamente
 * igual que en Cloud sin proveedor. Nada se simula.
 *
 * El fallo no se traga en silencio: se reporta, para que la oficina vea en el
 * registro que los acuses no están saliendo.
 */
class CorreoSalienteCaidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            // Puerto reservado en el que nadie escucha: la conexión se rechaza
            // al instante, sin esperar ningún tiempo de espera.
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 2,
        ]);

        Exceptions::fake();
    }

    /** @return array<string, string> */
    private function pqr(): array
    {
        return [
            'tipo' => TipoMensaje::Pqr->value,
            'nombre' => 'Carlos Muñoz',
            'correo' => 'carlos@ejemplo.test',
            'mensaje' => 'Una queja con suficiente detalle para pasar la validación.',
            'acepta_datos' => '1',
        ];
    }

    public function test_la_pqr_queda_radicada_aunque_el_acuse_no_salga(): void
    {
        $respuesta = $this->post(route('contacto.store'), $this->pqr());

        $respuesta->assertRedirect();
        $respuesta->assertRedirectContains(route('contacto'));
        $respuesta->assertSessionHas('radicado');
        $respuesta->assertSessionHas(
            'exito',
            fn (string $aviso): bool => str_contains($aviso, 'No pudimos enviarte el acuse')
        );

        $this->assertNotNull(Mensaje::firstOrFail()->radicado, 'La PQR tiene que quedar radicada aunque el correo esté caído.');

        Exceptions::assertReported(TransportException::class);
    }

    /**
     * La otra cara, para que la rama del acuse enviado no pueda desaparecer
     * sin que nada se ponga rojo: con el correo sano, el aviso lo promete y
     * no se reporta nada.
     */
    public function test_con_el_correo_sano_el_aviso_promete_el_acuse(): void
    {
        config(['mail.default' => 'array']);

        $this->post(route('contacto.store'), $this->pqr())->assertSessionHas(
            'exito',
            fn (string $aviso): bool => str_contains($aviso, 'Te enviamos el acuse a carlos@ejemplo.test')
        );

        Exceptions::assertNothingReported();
    }

    public function test_la_postulacion_queda_guardada_aunque_ningun_correo_salga(): void
    {
        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $respuesta = $this->post(route('empleo.postular', $vacante), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'telefono' => '3145598821',
            'experiencia' => 'Tres años en barra de discoteca.',
            'acepta_datos' => '1',
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertRedirectContains(route('empleo.show', $vacante));
        $respuesta->assertSessionHas('exito');

        $this->assertSame(1, Postulacion::count(), 'La postulación tiene que quedar guardada aunque el correo esté caído.');

        // Fallaron los dos envíos —el aviso al establecimiento y el acuse al
        // candidato— y los dos quedaron reportados.
        Exceptions::assertReportedCount(2);
        Exceptions::assertReported(TransportException::class);
    }
}
