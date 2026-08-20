<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoMensaje;
use App\Mail\AcuseDeRadicado;
use App\Models\Aliado;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Cartera;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Mensaje;
use App\Models\User;
use App\Models\Vacante;
use App\Support\Formulario;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FormulariosPublicosTest extends TestCase
{
    use RefreshDatabase;

    // --- Habeas data (Ley 1581 de 2012) ---

    public function test_la_inscripcion_exige_la_autorizacion_de_datos(): void
    {
        $evento = Evento::create([
            'titulo' => 'Capacitación de prueba',
            'slug' => 'capacitacion-de-prueba',
            'fecha_inicio' => now()->addDays(10),
            'precio' => 0,
            'permite_inscripcion' => true,
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $this->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Sin Autorización',
            'correo' => 'sin@ejemplo.test',
            'telefono' => '3145520000',
            // acepta_datos ausente a propósito
        ])->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, Inscripcion::count(), 'Sin autorización no se guarda nada.');
    }

    public function test_el_registro_de_aspirante_exige_la_autorizacion_de_datos(): void
    {
        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Sin Autorización',
            'correo' => 'sin@ejemplo.test',
            'cargo_interes' => 'Bartender',
            'categoria_cargo' => CargoDelSector::Barra->value,
        ])->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, Aspirante::count());
    }

    public function test_el_consentimiento_queda_con_marca_de_tiempo(): void
    {
        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'cargo_interes' => 'Bartender',
            'categoria_cargo' => CargoDelSector::Barra->value,
            'acepta_datos' => '1',
        ]);

        $aspirante = Aspirante::firstOrFail();
        $this->assertTrue($aspirante->acepta_datos);
        $this->assertNotNull($aspirante->consentimiento_at);
    }

    // --- Política de datos (B3: el consentimiento cubre lo que el sistema hace de verdad) ---

    public function test_la_politica_de_datos_explica_la_entrega_a_terceros_al_postularse(): void
    {
        $respuesta = $this->get(route('politica-de-datos'));

        $respuesta->assertSee('Transferencia a terceros', escape: false);
        $respuesta->assertSee('se entregan directamente al establecimiento', escape: false);
    }

    public function test_la_politica_de_datos_incluye_la_foto_del_artista_y_los_contactos_de_artistas_y_proveedores(): void
    {
        $respuesta = $this->get(route('politica-de-datos'));

        $respuesta->assertSee('bolsa de artistas', escape: false);
        $respuesta->assertSee('una foto', escape: false);
    }

    public function test_la_politica_de_datos_cita_los_plazos_reales_de_retencion_de_la_bolsa(): void
    {
        $respuesta = $this->get(route('politica-de-datos'));

        $respuesta->assertSee(config('bolsas.retencion_postulaciones_meses').' meses', escape: false);
        $respuesta->assertSee(config('bolsas.retencion_aspirantes_meses').' meses', escape: false);
    }

    public function test_la_casilla_de_habeas_data_avisa_de_la_entrega_a_terceros(): void
    {
        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee('entrega a terceros', escape: false);
    }

    // --- Antispam ---

    public function test_el_honeypot_descarta_el_envio_sin_dar_pistas(): void
    {
        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Bot',
            'correo' => 'bot@ejemplo.test',
            'cargo_interes' => 'Bartender',
            'acepta_datos' => '1',
            Formulario::CAMPO_TRAMPA => 'soy-un-bot',
        ])->assertStatus(422);

        $this->assertSame(0, Aspirante::count());
    }

    // --- PQR y radicado ---

    public function test_una_pqr_genera_radicado_consecutivo_y_envia_acuse(): void
    {
        Mail::fake();

        foreach (['Primera queja del año', 'Segunda queja del año', 'Tercera queja del año'] as $texto) {
            $this->post(route('contacto.store'), [
                'tipo' => TipoMensaje::Pqr->value,
                'nombre' => 'Carlos Muñoz',
                'correo' => 'carlos@ejemplo.test',
                'mensaje' => $texto.' con suficiente detalle para pasar la validación.',
                'acepta_datos' => '1',
            ])->assertSessionHas('radicado');
        }

        $radicados = Mensaje::whereNotNull('radicado')->orderBy('id')->pluck('radicado')->all();
        $anio = now()->year;

        $this->assertSame(
            ["PQR-{$anio}-0001", "PQR-{$anio}-0002", "PQR-{$anio}-0003"],
            $radicados,
            'Los radicados deben ser consecutivos y sin saltos.'
        );

        Mail::assertSent(AcuseDeRadicado::class, 3);
    }

    public function test_un_mensaje_de_contacto_normal_no_recibe_radicado(): void
    {
        $this->post(route('contacto.store'), [
            'tipo' => TipoMensaje::Contacto->value,
            'nombre' => 'Paula Restrepo',
            'correo' => 'paula@ejemplo.test',
            'mensaje' => 'Quisiera información sobre las cifras del Observatorio.',
            'acepta_datos' => '1',
        ]);

        $this->assertNull(Mensaje::firstOrFail()->radicado);
    }

    /**
     * El formulario real de /afiliate NO manda `tipo` —lo fija el controlador—.
     * Este test lo envía como lo envía el navegador: sin ese campo. Antes se
     * inyectaba a mano, y eso enmascaraba que toda afiliación fallaba.
     */
    public function test_la_afiliacion_se_guarda_como_mensaje_del_tipo_correcto(): void
    {
        $this->post(route('afiliate.store'), [
            'nombre' => 'Sandra Ríos',
            'correo' => 'sandra@ejemplo.test',
            'mensaje' => 'Tengo un gastrobar en Armenia y quiero afiliarme al gremio.',
            'acepta_datos' => '1',
        ])->assertSessionHas('exito');

        $this->assertSame(TipoMensaje::Afiliacion, Mensaje::firstOrFail()->tipo);
    }

    // --- Bolsa de empleo ---

    public function test_una_vacante_sin_publicar_no_aparece_en_la_bolsa(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $publicada = Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Bartender de fin de semana']);
        $pendiente = Vacante::factory()->for($asociado)->pendiente()->create(['cargo' => 'Auxiliar de cocina sin aprobar']);

        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee($publicada->cargo);
        $respuesta->assertDontSee($pendiente->cargo);
    }

    /**
     * B2: el asociado publica su propia vacante, y el afiliado recién
     * llegado —con la ficha todavía pendiente de aprobación— es justo quien
     * más rápido publica una. Enlazar su ficha desde el muro daría un 404,
     * así que se muestra el nombre como texto plano.
     */
    public function test_una_vacante_de_un_asociado_sin_publicar_no_enlaza_su_ficha_en_el_muro(): void
    {
        $asociado = Asociado::factory()->create(['nombre' => 'Bar Recién Afiliado']);
        Vacante::factory()->for($asociado)->publicado()->create();

        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee('Bar Recién Afiliado', escape: false);
        $respuesta->assertDontSee(route('directorio.show', $asociado), escape: false);
    }

    public function test_una_vacante_de_un_asociado_publicado_si_enlaza_su_ficha_en_el_muro(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        Vacante::factory()->for($asociado)->publicado()->create();

        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee(route('directorio.show', $asociado), escape: false);
    }

    public function test_el_detalle_de_una_vacante_de_un_asociado_sin_publicar_no_enlaza_su_ficha(): void
    {
        $asociado = Asociado::factory()->create(['nombre' => 'Bar Recién Afiliado']);
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $respuesta = $this->get(route('empleo.show', $vacante));

        $respuesta->assertSee('Bar Recién Afiliado', escape: false);
        $respuesta->assertDontSee(route('directorio.show', $asociado), escape: false);
    }

    public function test_el_detalle_de_una_vacante_de_un_asociado_publicado_si_enlaza_su_ficha(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $respuesta = $this->get(route('empleo.show', $vacante));

        $respuesta->assertSee(route('directorio.show', $asociado), escape: false);
    }

    // --- Selects obligatorios (N3: sin opción vacía, el navegador preselecciona la primera) ---

    public function test_la_inscripcion_de_artistas_antepone_una_opcion_vacia_a_sus_selects_obligatorios(): void
    {
        $respuesta = $this->get(route('artistas.inscripcion'));

        // «Qué eres» y «Municipio» son selects obligatorios sin opción vacía
        // propia: el componente debe anteponer una a cada uno.
        $this->assertSame(2, substr_count($respuesta->getContent(), 'Selecciona una opción'));
    }

    public function test_la_inscripcion_de_proveedores_antepone_una_opcion_vacia_a_sus_selects_obligatorios(): void
    {
        $respuesta = $this->get(route('proveedores.inscripcion'));

        // «Qué le vendes al sector» y «Municipio» están en el mismo caso.
        $this->assertSame(2, substr_count($respuesta->getContent(), 'Selecciona una opción'));
    }

    public function test_el_muro_de_empleo_no_duplica_la_opcion_vacia_en_los_filtros_que_ya_la_traen(): void
    {
        $respuesta = $this->get(route('empleo.index'));

        // Los filtros de área y municipio ya traen su propia opción vacía
        // («Todas las áreas», «Todos los municipios»); solo el select
        // obligatorio de área del formulario de perfil necesita que el
        // componente le anteponga una.
        $this->assertSame(1, substr_count($respuesta->getContent(), 'Selecciona una opción'));
    }

    public function test_el_directorio_no_necesita_opcion_vacia_porque_sus_filtros_ya_la_traen(): void
    {
        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertDontSee('Selecciona una opción', escape: false);
    }

    // --- /mi-cuenta ---

    public function test_mi_cuenta_redirige_a_quien_no_ha_iniciado_sesion(): void
    {
        $this->get(route('mi-cuenta.index'))->assertRedirect(route('mi-cuenta.entrar'));
    }

    public function test_mi_cuenta_exige_el_rol_asociado(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN] as $rol) {
            $usuario = User::factory()->create();
            $usuario->syncRoles([$rol]);

            $this->actingAs($usuario->fresh())->get(route('mi-cuenta.index'))->assertForbidden();
        }
    }

    public function test_al_equipo_del_gremio_se_le_explica_por_que_no_entra_a_mi_cuenta(): void
    {
        // El panel y /mi-cuenta comparten sesión: llegar aquí con la sesión de
        // la dirección abierta pasa en cada demostración. Un 403 seco deja al
        // usuario sin saber qué hacer.
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create(['name' => 'Natalia Gutiérrez']);
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $respuesta = $this->actingAs($direccion->fresh())->get(route('mi-cuenta.index'));

        $respuesta->assertForbidden();
        $respuesta->assertSee('Natalia Gutiérrez', escape: false);
        $respuesta->assertSee('Cerrar sesión y entrar como afiliado');
        $respuesta->assertSee('Ir al panel del gremio');
    }

    public function test_cerrar_sesion_desde_esa_pantalla_lleva_al_login_del_afiliado(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($direccion->fresh())
            ->post(route('mi-cuenta.salir'), ['destino' => 'entrar'])
            ->assertRedirect(route('mi-cuenta.entrar'));

        $this->assertGuest();
    }

    public function test_el_asociado_ve_su_mora_y_el_detalle_privado_de_los_convenios(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 150000,
            'meses_mora' => 3,
            'actualizado_at' => now(),
        ]);

        $convenio = Aliado::create([
            'nombre' => 'Licorera de prueba',
            'detalle_convenio' => 'Descuento del 12 % sobre lista de precios.',
            'estado' => EstadoPublicacion::Publicado,
            'activo' => true,
        ]);

        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
        $duenio->syncRoles([User::ROL_ASOCIADO]);

        $respuesta = $this->actingAs($duenio->fresh())->get(route('mi-cuenta.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Bruma Gastrobar');
        $respuesta->assertSee('$150.000');
        $respuesta->assertSee($convenio->detalle_convenio);
    }

    public function test_el_asociado_al_dia_ve_el_estado_sin_deuda(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create(['nombre' => 'Bar Al Día']);
        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
        $duenio->syncRoles([User::ROL_ASOCIADO]);

        $respuesta = $this->actingAs($duenio->fresh())->get(route('mi-cuenta.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Bar Al Día');
        $respuesta->assertSee('Estás al día');
        $respuesta->assertDontSee('Pagar ahora');
    }

    public function test_sin_convenios_el_portal_muestra_el_estado_vacio(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create();
        $duenio = User::factory()->create(['asociado_id' => $asociado->id]);
        $duenio->syncRoles([User::ROL_ASOCIADO]);

        $this->actingAs($duenio->fresh())
            ->get(route('mi-cuenta.index'))
            ->assertSuccessful()
            ->assertSee('Todavía no hay convenios publicados');
    }

    public function test_un_asociado_sin_establecimiento_ve_la_explicacion(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $huerfano = User::factory()->create(['name' => 'Dueño sin ficha', 'asociado_id' => null]);
        $huerfano->syncRoles([User::ROL_ASOCIADO]);

        $respuesta = $this->actingAs($huerfano->fresh())->get(route('mi-cuenta.index'));

        $respuesta->assertForbidden();
        $respuesta->assertSee('Dueño sin ficha');
        $respuesta->assertSee('un usuario sin establecimiento vinculado');
        $respuesta->assertSee('Cerrar sesión y entrar como afiliado');
    }

    public function test_un_directivo_que_tambien_es_asociado_ve_los_atajos_del_portal(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $asociado = Asociado::factory()->publicado()->create();
        $directivo = User::factory()->create(['asociado_id' => $asociado->id]);
        $directivo->syncRoles([User::ROL_ASOCIADO, User::ROL_SUBADMIN]);

        $this->actingAs($directivo->fresh())
            ->get(route('contacto'))
            ->assertSuccessful()
            ->assertSee('Ir al panel del gremio')
            ->assertSee('Mi cuenta')
            ->assertSee('Mis vacantes');
    }

    public function test_el_detalle_de_convenio_no_aparece_en_el_sitio_publico(): void
    {
        Aliado::create([
            'nombre' => 'Licorera de prueba',
            'detalle_convenio' => 'Descuento del 12 % sobre lista de precios.',
            'estado' => EstadoPublicacion::Publicado,
            'activo' => true,
        ]);

        $this->get('/')->assertDontSee('Descuento del 12 % sobre lista de precios.');
    }
}
