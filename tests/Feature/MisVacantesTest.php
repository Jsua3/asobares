<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MisVacantesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function duenioDe(Asociado $asociado): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        return $usuario->fresh();
    }

    public function test_el_asociado_ve_solo_sus_vacantes(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $mia = Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Bartender de mi bar']);
        $ajena = Vacante::factory()->publicado()->create(['cargo' => 'Mesero del bar vecino']);

        $respuesta = $this->actingAs($this->duenioDe($asociado))->get(route('mi-cuenta.vacantes.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($mia->cargo);
        $respuesta->assertDontSee($ajena->cargo);
    }

    public function test_el_equipo_del_gremio_no_entra_al_portal_del_asociado(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        $this->actingAs($usuario->fresh())->get(route('mi-cuenta.vacantes.index'))->assertForbidden();
    }

    /**
     * El aviso decía «seis meses» a mano mientras la política lee el plazo de
     * la configuración: divergían en cuanto alguien cambiara la variable
     * (cabo suelto anotado en la v6). Ahora los dos leen del mismo sitio.
     */
    public function test_el_aviso_de_retencion_publica_el_plazo_de_la_configuracion(): void
    {
        config(['bolsas.retencion_postulaciones_meses' => 7]);

        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->get(route('mi-cuenta.vacantes.show', $vacante))
            ->assertSee('7 meses');
    }

    public function test_la_vacante_recien_creada_queda_pendiente_de_aprobacion(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.store'), [
                'cargo' => 'Bartender de fin de semana',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::PorTurnos->value,
                'descripcion' => 'Barra de alto volumen, viernes y sábados.',
                'franja_horaria' => 'Vie y sáb, 8:00 p. m. – 4:00 a. m.',
                'whatsapp_contacto' => '3151189203',
            ])
            ->assertRedirect(route('mi-cuenta.vacantes.index'));

        $vacante = Vacante::firstOrFail();

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $vacante->estado);
        $this->assertSame($asociado->id, $vacante->asociado_id, 'El dueño sale de la sesión, nunca del formulario.');
    }

    public function test_el_asociado_no_puede_publicar_su_vacante_mandando_el_estado(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.store'), [
                'cargo' => 'Mesero',
                'categoria_cargo' => CargoDelSector::Servicio->value,
                'tipo' => TipoVacante::PorTurnos->value,
                'estado' => EstadoPublicacion::Publicado->value,
                'asociado_id' => Asociado::factory()->publicado()->create()->id,
            ]);

        $vacante = Vacante::firstOrFail();

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $vacante->estado);
        $this->assertSame($asociado->id, $vacante->asociado_id);
    }

    public function test_el_empleo_momentaneo_exige_fecha_limite(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.store'), [
                'cargo' => 'Bartender para una noche',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::Momentaneo->value,
            ])
            ->assertSessionHasErrors('fecha_limite');

        $this->assertSame(0, Vacante::count());
    }

    public function test_la_fecha_limite_no_puede_estar_en_el_pasado(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.store'), [
                'cargo' => 'Bartender para una noche',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::Momentaneo->value,
                'fecha_limite' => now()->subWeek()->toDateString(),
            ])
            ->assertSessionHasErrors('fecha_limite');
    }

    public function test_un_asociado_sin_establecimiento_no_llega_al_formulario(): void
    {
        $huerfano = User::factory()->create(['asociado_id' => null]);
        $huerfano->syncRoles([User::ROL_ASOCIADO]);

        $this->actingAs($huerfano->fresh())
            ->get(route('mi-cuenta.vacantes.crear'))
            ->assertForbidden()
            ->assertSee('Esta sección es para los establecimientos afiliados');
    }

    public function test_editar_una_vacante_publicada_la_devuelve_a_revision(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create([
            'cargo' => 'Bartender',
            'motivo_devolucion' => 'Un motivo viejo que hay que limpiar.',
        ]);

        $this->actingAs($this->duenioDe($asociado))
            ->put(route('mi-cuenta.vacantes.update', $vacante), [
                'cargo' => 'Bartender con experiencia en coctelería',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::PorTurnos->value,
            ])
            ->assertRedirect(route('mi-cuenta.vacantes.index'));

        $actualizada = $vacante->fresh();

        $this->assertSame('Bartender con experiencia en coctelería', $actualizada->cargo);
        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $actualizada->estado);
        $this->assertNull($actualizada->motivo_devolucion, 'Al reenviar se borra el motivo anterior.');
    }

    public function test_un_asociado_no_edita_la_vacante_de_otro(): void
    {
        $ajena = Vacante::factory()->publicado()->create();
        $intruso = $this->duenioDe(Asociado::factory()->publicado()->create());

        $this->actingAs($intruso)
            ->put(route('mi-cuenta.vacantes.update', $ajena), [
                'cargo' => 'Cargo secuestrado',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::PorTurnos->value,
            ])
            ->assertForbidden();

        $this->assertNotSame('Cargo secuestrado', $ajena->fresh()->cargo);
    }

    public function test_cerrar_una_vacante_no_pasa_por_aprobacion(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.cerrar', $vacante))
            ->assertRedirect(route('mi-cuenta.vacantes.index'));

        $cerrada = $vacante->fresh();

        $this->assertTrue($cerrada->estaCerrada());
        $this->assertSame(EstadoPublicacion::Publicado, $cerrada->estado, 'Cerrar no despublica: solo saca del muro.');
    }

    public function test_reabrir_una_vacante_vigente_la_devuelve_al_muro(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->cerrada()->create();

        $this->actingAs($this->duenioDe($asociado))->post(route('mi-cuenta.vacantes.reabrir', $vacante));

        $this->assertTrue($vacante->fresh()->estaVigente());
    }

    public function test_reabrir_una_vacante_vencida_avisa_que_hay_que_cambiar_la_fecha(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->cerrada()->vencida()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.reabrir', $vacante))
            ->assertSessionHas('error');

        $this->assertTrue($vacante->fresh()->estaCerrada(), 'Sigue cerrada: reabrirla no la haría visible igual.');
    }

    public function test_el_dueno_ve_los_datos_de_quienes_se_postularon(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();
        $postulacion = Postulacion::factory()->for($vacante)->create([
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
        ]);

        $respuesta = $this->actingAs($this->duenioDe($asociado))
            ->get(route('mi-cuenta.vacantes.show', $vacante));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Duván Marín');
        $respuesta->assertSee('duvan@ejemplo.test');
        $this->assertSame($vacante->id, $postulacion->vacante_id);
    }

    /**
     * Cada postulación trae un `<select>` de estado y un botón que dice solo
     * «Guardar». En una lista de N son N controles idénticos: un lector de
     * pantalla anuncia «cuadro combinado» y «Guardar» sin decir de quién, y no
     * hay manera de saber cuál se está tocando (SC 4.1.2 y SC 3.3.2, el mismo
     * RNF-12 que el indicador de foco). Lo que los distingue entre sí es el
     * nombre de la persona, y por eso se afirma sobre el HTML rendido y no
     * sobre la plantilla: lo que importa es lo que llega al lector.
     */
    public function test_los_controles_de_cada_postulacion_dicen_de_quien_son(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();
        Postulacion::factory()->for($vacante)->create(['nombre' => 'Duván Marín']);

        $respuesta = $this->actingAs($this->duenioDe($asociado))
            ->get(route('mi-cuenta.vacantes.show', $vacante));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('aria-label="Estado de la postulación de Duván Marín"', false);
        $respuesta->assertSee('aria-label="Guardar el estado de la postulación de Duván Marín"', false);
    }

    public function test_un_asociado_no_ve_las_postulaciones_de_otro(): void
    {
        $ajena = Vacante::factory()->publicado()->create();
        Postulacion::factory()->for($ajena)->create(['nombre' => 'Candidato Ajeno']);

        $intruso = $this->duenioDe(Asociado::factory()->publicado()->create());

        $this->actingAs($intruso)->get(route('mi-cuenta.vacantes.show', $ajena))->assertForbidden();
    }

    public function test_el_dueno_marca_una_postulacion_como_contactada(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $postulacion = Postulacion::factory()
            ->for(Vacante::factory()->for($asociado)->publicado())
            ->create();

        $this->actingAs($this->duenioDe($asociado))
            ->patch(route('mi-cuenta.postulaciones.gestionar', $postulacion), [
                'estado' => EstadoDeGestion::Contactado->value,
            ]);

        $this->assertSame(EstadoDeGestion::Contactado, $postulacion->fresh()->estado);
    }

    public function test_un_asociado_no_gestiona_postulaciones_ajenas(): void
    {
        $postulacion = Postulacion::factory()->for(Vacante::factory()->publicado())->create();
        $intruso = $this->duenioDe(Asociado::factory()->publicado()->create());

        $this->actingAs($intruso)
            ->patch(route('mi-cuenta.postulaciones.gestionar', $postulacion), [
                'estado' => EstadoDeGestion::Descartado->value,
            ])
            ->assertForbidden();

        $this->assertSame(EstadoDeGestion::Nuevo, $postulacion->fresh()->estado);
    }

    public function test_un_intruso_no_puede_cerrar_una_vacante_ajena(): void
    {
        $ajena = Vacante::factory()->publicado()->create();
        $intruso = $this->duenioDe(Asociado::factory()->publicado()->create());

        $this->actingAs($intruso)
            ->post(route('mi-cuenta.vacantes.cerrar', $ajena))
            ->assertForbidden();

        $this->assertFalse($ajena->fresh()->estaCerrada());
    }

    public function test_un_intruso_no_puede_reabrir_una_vacante_ajena(): void
    {
        $ajena = Vacante::factory()->publicado()->cerrada()->create();
        $intruso = $this->duenioDe(Asociado::factory()->publicado()->create());

        $this->actingAs($intruso)
            ->post(route('mi-cuenta.vacantes.reabrir', $ajena))
            ->assertForbidden();

        $this->assertTrue($ajena->fresh()->estaCerrada());
    }

    /**
     * Un directivo del gremio que también es dueño de un establecimiento
     * entra a /mi-cuenta con la sesión de asociado (RolYPermisoSeeder le da
     * `ver_vacante` como subadmin). `VacantePolicy::view()` concede por ese
     * permiso, no solo por propiedad: sin una habilidad específica para el
     * portal, vería las postulaciones de un establecimiento ajeno.
     */
    public function test_un_directivo_que_tambien_es_asociado_no_ve_las_postulaciones_de_otro_establecimiento(): void
    {
        $ajena = Vacante::factory()->publicado()->create();

        $directivo = $this->duenioDe(Asociado::factory()->publicado()->create());
        $directivo->syncRoles([User::ROL_ASOCIADO, User::ROL_SUBADMIN]);

        $this->actingAs($directivo->fresh())
            ->get(route('mi-cuenta.vacantes.show', $ajena))
            ->assertForbidden();
    }

    /**
     * `GuardarVacanteRequest::datosDeLaVacante()` solo deja pasar los campos
     * del formulario: mandar `saltaFlujoDeAprobacion` en el payload no
     * activa el escape de `Vacante` (que además es una propiedad de PHP, no
     * un atributo del modelo, así que un `fill()` jamás la toca).
     */
    public function test_mandar_salta_flujo_de_aprobacion_en_el_payload_no_activa_el_escape(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.vacantes.store'), [
                'cargo' => 'Bartender',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::PorTurnos->value,
                'saltaFlujoDeAprobacion' => '1',
            ]);

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, Vacante::firstOrFail()->estado);

        $publicada = Vacante::factory()->for($asociado)->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->put(route('mi-cuenta.vacantes.update', $publicada), [
                'cargo' => 'Bartender con experiencia',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'tipo' => TipoVacante::PorTurnos->value,
                'saltaFlujoDeAprobacion' => '1',
            ]);

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $publicada->fresh()->estado);
    }

    /**
     * El escape del observer es exclusivo de `Vacante::cerrar()` y
     * `Vacante::reabrir()`: un `update()` normal sobre una vacante publicada,
     * hecho por quien no puede publicar, se sigue degradando a pendiente.
     */
    public function test_actualizar_una_vacante_publicada_sin_pasar_por_cerrar_la_sigue_degradando(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $this->actingAs($this->duenioDe($asociado));
        $vacante->update(['franja_horaria' => 'Turno nuevo']);

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $vacante->fresh()->estado);
    }
}
