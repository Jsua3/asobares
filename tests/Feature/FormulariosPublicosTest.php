<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoMensaje;
use App\Enums\TipoVacante;
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
        ])->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, Aspirante::count());
    }

    public function test_el_consentimiento_queda_con_marca_de_tiempo(): void
    {
        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'cargo_interes' => 'Bartender',
            'acepta_datos' => '1',
        ]);

        $aspirante = Aspirante::firstOrFail();
        $this->assertTrue($aspirante->acepta_datos);
        $this->assertNotNull($aspirante->consentimiento_at);
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

    public function test_la_afiliacion_se_guarda_como_mensaje_del_tipo_correcto(): void
    {
        $this->post(route('afiliate.store'), [
            'tipo' => TipoMensaje::Contacto->value, // el controlador lo fuerza a Afiliacion
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

        $publicada = Vacante::create([
            'asociado_id' => $asociado->id,
            'cargo' => 'Bartender de fin de semana',
            'tipo' => TipoVacante::PorTurnos,
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $pendiente = Vacante::create([
            'asociado_id' => $asociado->id,
            'cargo' => 'Auxiliar de cocina sin aprobar',
            'tipo' => TipoVacante::TiempoCompleto,
            'estado' => EstadoPublicacion::PendienteAprobacion,
        ]);

        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee($publicada->cargo);
        $respuesta->assertDontSee($pendiente->cargo);
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
