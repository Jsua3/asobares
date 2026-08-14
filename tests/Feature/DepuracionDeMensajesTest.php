<?php

namespace Tests\Feature;

use App\Enums\TipoMensaje;
use App\Models\Mensaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * G12. La bolsa de empleo ya se depuraba sola, pero los formularios públicos
 * de contacto y PQR guardaban nombre, correo, teléfono y el texto del mensaje
 * sin ninguna fecha de caducidad.
 */
class DepuracionDeMensajesTest extends TestCase
{
    use RefreshDatabase;

    private function mensaje(TipoMensaje $tipo, ?string $respondido, string $creado): Mensaje
    {
        return Mensaje::create([
            'tipo' => $tipo,
            'nombre' => 'Ciudadana Ejemplo',
            'correo' => 'ciudadana@ejemplo.test',
            'telefono' => '3145520987',
            'mensaje' => 'Texto con datos personales.',
            'acepta_datos' => true,
            'consentimiento_at' => $creado,
            'respondido_at' => $respondido,
            'radicado' => $tipo === TipoMensaje::Pqr ? Mensaje::generarRadicado() : null,
            'created_at' => $creado,
        ]);
    }

    public function test_borra_el_contacto_respondido_hace_mas_del_plazo(): void
    {
        $viejo = $this->mensaje(TipoMensaje::Contacto, now()->subMonths(13)->toDateTimeString(), now()->subMonths(14)->toDateTimeString());

        $this->artisan('mensajes:depurar')->assertSuccessful();

        $this->assertDatabaseMissing('mensajes', ['id' => $viejo->id]);
    }

    public function test_conserva_el_contacto_reciente(): void
    {
        $reciente = $this->mensaje(TipoMensaje::Contacto, now()->subMonth()->toDateTimeString(), now()->subMonths(2)->toDateTimeString());

        $this->artisan('mensajes:depurar')->assertSuccessful();

        $this->assertDatabaseHas('mensajes', ['id' => $reciente->id]);
    }

    /**
     * Un mensaje que nadie contestó no puede volverse inmortal justamente por
     * no haberlo atendido: ahí el plazo cuenta desde que entró.
     */
    public function test_el_mensaje_nunca_respondido_caduca_desde_que_entro(): void
    {
        $abandonado = $this->mensaje(TipoMensaje::Contacto, null, now()->subMonths(14)->toDateTimeString());

        $this->artisan('mensajes:depurar')->assertSuccessful();

        $this->assertDatabaseMissing('mensajes', ['id' => $abandonado->id]);
    }

    /**
     * Las PQR llevan radicado y valor probatorio ante la SIC, así que su plazo
     * es mayor: a los 13 meses una PQR todavía se guarda.
     */
    public function test_la_pqr_aguanta_mas_que_un_contacto_corriente(): void
    {
        $pqr = $this->mensaje(TipoMensaje::Pqr, now()->subMonths(13)->toDateTimeString(), now()->subMonths(14)->toDateTimeString());

        $this->artisan('mensajes:depurar')->assertSuccessful();

        $this->assertDatabaseHas('mensajes', ['id' => $pqr->id]);
    }

    public function test_la_pqr_tambien_caduca_al_cumplir_su_plazo(): void
    {
        $pqr = $this->mensaje(TipoMensaje::Pqr, now()->subMonths(25)->toDateTimeString(), now()->subMonths(26)->toDateTimeString());

        $this->artisan('mensajes:depurar')->assertSuccessful();

        $this->assertDatabaseMissing('mensajes', ['id' => $pqr->id]);
    }

    public function test_el_simulacro_no_borra_nada(): void
    {
        $viejo = $this->mensaje(TipoMensaje::Contacto, now()->subMonths(13)->toDateTimeString(), now()->subMonths(14)->toDateTimeString());

        $this->artisan('mensajes:depurar', ['--pretend' => true])->assertSuccessful();

        $this->assertDatabaseHas('mensajes', ['id' => $viejo->id]);
    }

    /**
     * Un plazo de 0 haría que el límite fuera *ahora mismo* y la purga
     * borraría todo. Eso es un error de configuración, no una orden.
     */
    public function test_un_plazo_invalido_aborta_en_vez_de_borrarlo_todo(): void
    {
        config(['retencion.contacto_meses' => 0]);
        $reciente = $this->mensaje(TipoMensaje::Contacto, null, now()->toDateTimeString());

        $this->artisan('mensajes:depurar')->assertFailed();

        $this->assertDatabaseHas('mensajes', ['id' => $reciente->id]);
    }
}
