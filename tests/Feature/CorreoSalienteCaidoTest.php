<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoMensaje;
use App\Filament\Resources\Artistas\Pages\ListArtistas;
use App\Filament\Resources\Vacantes\Pages\ListVacantes;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Mensaje;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;
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

    // --- El panel (D-24): aprobar y devolver con el mismo transporte caído ---

    /**
     * En el panel el daño era distinto pero de la misma familia: el estado ya
     * había cambiado cuando el correo lanzaba, y la secretaría veía el error
     * de Livewire con la vacante publicada por debajo. Ahora la acción
     * termina, y el aviso del panel dice que el correo no salió en vez de
     * fingir que sí.
     */
    private function entrarComoSecretaria(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        $this->actingAs($usuario->fresh());
    }

    public function test_la_vacante_se_publica_aunque_el_aviso_al_establecimiento_no_salga(): void
    {
        $this->entrarComoSecretaria();

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('aprobar')->table($vacante))
            ->assertHasNoErrors()
            ->assertNotified('Vacante publicada, pero el correo no salió');

        $this->assertSame(EstadoPublicacion::Publicado, $vacante->fresh()->estado);
        Exceptions::assertReported(TransportException::class);
    }

    public function test_devolver_con_motivo_lo_guarda_aunque_el_correo_no_salga(): void
    {
        $this->entrarComoSecretaria();

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('devolver')->table($vacante), data: [
                'motivo_devolucion' => 'Falta el horario del turno.',
            ])
            ->assertHasNoErrors()
            ->assertNotified('Vacante devuelta al asociado, pero el correo no salió');

        $devuelta = $vacante->fresh();

        $this->assertSame(EstadoPublicacion::Borrador, $devuelta->estado);
        $this->assertSame('Falta el horario del turno.', $devuelta->motivo_devolucion);
        Exceptions::assertReported(TransportException::class);
    }

    public function test_la_ficha_de_artista_se_publica_aunque_el_aviso_no_salga(): void
    {
        $this->entrarComoSecretaria();

        $artista = Artista::factory()->pendiente()->create(['correo' => 'dj@ejemplo.test']);

        Livewire::test(ListArtistas::class)
            ->callAction(TestAction::make('aprobar')->table($artista))
            ->assertHasNoErrors()
            ->assertNotified('Ficha publicada, pero el correo no salió');

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Exceptions::assertReported(TransportException::class);
    }

    /** El lote sigue publicando todo, y cuenta lo que no pudo avisar. */
    public function test_el_lote_de_vacantes_publica_todas_y_cuenta_los_correos_que_no_salieron(): void
    {
        $this->entrarComoSecretaria();

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacantes = Vacante::factory()->for($asociado)->pendiente()->count(2)->create();

        Livewire::test(ListVacantes::class)
            ->selectTableRecords($vacantes->modelKeys())
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors()
            ->assertNotified('2 registros publicados, pero 2 correos no salieron');

        foreach ($vacantes as $vacante) {
            $this->assertSame(EstadoPublicacion::Publicado, $vacante->fresh()->estado);
        }

        Exceptions::assertReportedCount(2);
    }
}
