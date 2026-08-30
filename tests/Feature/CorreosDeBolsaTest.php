<?php

namespace Tests\Feature;

use App\Mail\AcuseDePostulacion;
use App\Mail\NuevaPostulacion;
use App\Mail\VacanteAprobada;
use App\Mail\VacanteDevuelta;
use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use App\Support\DestinatariosDelAsociado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorreosDeBolsaTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_destinatarios_son_los_usuarios_del_establecimiento(): void
    {
        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        User::factory()->create(['asociado_id' => $asociado->id, 'email' => 'duenio@bar.test']);

        $this->assertSame(['duenio@bar.test'], DestinatariosDelAsociado::correos($asociado->fresh()));
    }

    public function test_sin_usuarios_se_cae_al_correo_interno_de_la_ficha(): void
    {
        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);

        $this->assertSame(['oficina@bar.test'], DestinatariosDelAsociado::correos($asociado));
    }

    public function test_sin_usuarios_ni_correo_interno_no_hay_a_quien_escribirle(): void
    {
        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => null]);

        $this->assertSame([], DestinatariosDelAsociado::correos($asociado));
    }

    public function test_el_correo_de_postulacion_nombra_el_cargo_y_al_candidato(): void
    {
        $vacante = Vacante::factory()->publicado()->create(['cargo' => 'Bartender de fin de semana']);
        $postulacion = Postulacion::factory()->for($vacante)->create(['nombre' => 'Duván Marín']);

        $correo = new NuevaPostulacion($postulacion);

        $correo->assertHasSubject('Nueva postulación: Bartender de fin de semana');
        $correo->assertSeeInHtml('Duván Marín');
    }

    public function test_el_acuse_de_postulacion_nombra_el_cargo_y_el_establecimiento(): void
    {
        $asociado = Asociado::factory()->publicado()->create(['nombre' => 'La Terraza de Armenia']);
        $vacante = Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Bartender de fin de semana']);
        $postulacion = Postulacion::factory()->for($vacante)->create(['nombre' => 'Duván Marín']);

        $correo = new AcuseDePostulacion($postulacion);

        $correo->assertHasSubject('Recibimos tu postulación: Bartender de fin de semana');
        $correo->assertSeeInHtml('Duván Marín');
        $correo->assertSeeInHtml('La Terraza de Armenia');
    }

    public function test_el_correo_de_devolucion_explica_el_motivo(): void
    {
        $vacante = Vacante::factory()->create([
            'cargo' => 'Mesero',
            'motivo_devolucion' => 'Falta el horario del turno.',
        ]);

        (new VacanteDevuelta($vacante))->assertSeeInHtml('Falta el horario del turno.');
    }

    public function test_el_correo_de_aprobacion_enlaza_la_vacante_publicada(): void
    {
        $vacante = Vacante::factory()->publicado()->create(['cargo' => 'Chef de cocina']);

        $correo = new VacanteAprobada($vacante);

        $correo->assertHasSubject('Tu vacante ya está publicada: Chef de cocina');
        $correo->assertSeeInHtml(route('empleo.show', $vacante));
    }
}
