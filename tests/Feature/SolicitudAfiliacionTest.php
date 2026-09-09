<?php

namespace Tests\Feature;

use App\Enums\EstadoSolicitudAfiliacion;
use App\Filament\Resources\SolicitudAfiliacions\Pages\EditSolicitudAfiliacion;
use App\Filament\Resources\SolicitudAfiliacions\Pages\ListSolicitudAfiliacions;
use App\Mail\BienvenidaAsociado;
use App\Mail\NuevaSolicitudAfiliacion;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Setting;
use App\Models\SolicitudAfiliacion;
use App\Models\Transaccion;
use App\Models\User;
use App\Services\AprobarSolicitudAfiliacion;
use App\Services\ReenviarEnlaceAccesoAsociado;
use App\Support\Formulario;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class SolicitudAfiliacionTest extends TestCase
{
    use RefreshDatabase;

    private Municipio $municipio;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->municipio = Municipio::factory()->create(['nombre' => 'Armenia']);
        $this->categoria = Categoria::factory()->create(['nombre' => 'Gastrobar']);
    }

    /** @return array<string, mixed> */
    private function datosValidos(array $sobrescribir = []): array
    {
        return array_merge([
            'solicitante_nombre' => 'Sandra Ríos',
            'solicitante_identificacion' => '1094.123.456',
            'solicitante_telefono' => '3145551234',
            'solicitante_correo' => 'sandra@ejemplo.test',
            'solicitante_cargo' => 'Propietaria',
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'razon_social' => 'Bruma Gastrobar S.A.S.',
            'nit' => '901234567-8',
            'municipio_id' => $this->municipio->id,
            'direccion' => 'Calle 10 # 12-34',
            'establecimiento_telefono' => '3145555678',
            'establecimiento_correo' => 'hola@bruma.test',
            'categoria_id' => $this->categoria->id,
            'descripcion' => 'Gastrobar con operación nocturna, cocina y música en vivo.',
            'acepta_datos' => '1',
        ], $sobrescribir);
    }

    private function usuario(string $rol): User
    {
        $this->seed(RolYPermisoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_get_afiliate_responde_correctamente(): void
    {
        $this->get(route('afiliate'))
            ->assertSuccessful()
            ->assertSee('Tus datos')
            ->assertSee('Información del establecimiento')
            ->assertSee('Tratamiento de datos');
    }

    public function test_solicitud_valida_se_persiste_como_solicitud_de_afiliacion(): void
    {
        Mail::fake();

        Setting::create([
            'clave' => 'contacto_correo_destino',
            'valor' => 'oficina@asobares.test',
            'tipo' => 'texto',
            'grupo' => 'contacto',
            'etiqueta' => 'Correo que recibe los formularios',
        ]);

        $this->post(route('afiliate.store'), $this->datosValidos())
            ->assertRedirect(route('afiliate').'#formulario')
            ->assertSessionHas('exito');

        $solicitud = SolicitudAfiliacion::firstOrFail();

        $this->assertSame('Sandra Ríos', $solicitud->solicitante_nombre);
        $this->assertSame('Bruma Gastrobar', $solicitud->establecimiento_nombre);
        $this->assertSame($this->municipio->id, $solicitud->municipio_id);
        $this->assertSame($this->categoria->id, $solicitud->categoria_id);
        $this->assertSame(EstadoSolicitudAfiliacion::Pendiente, $solicitud->estado);
        $this->assertTrue($solicitud->acepta_datos);
        $this->assertNotNull($solicitud->consentimiento_at);

        $this->assertSame(0, Asociado::count());
        $this->assertSame(0, User::count());
        $this->assertSame(0, Cartera::count());
        $this->assertSame(0, Transaccion::count());

        Mail::assertSent(NuevaSolicitudAfiliacion::class, fn (NuevaSolicitudAfiliacion $correo): bool => $correo->hasTo('oficina@asobares.test'));
    }

    public function test_aceptacion_de_datos_es_obligatoria(): void
    {
        $this->post(route('afiliate.store'), $this->datosValidos(['acepta_datos' => null]))
            ->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, SolicitudAfiliacion::count());
    }

    public function test_valida_campos_principales_y_municipio_valido(): void
    {
        $this->post(route('afiliate.store'), $this->datosValidos([
            'solicitante_correo' => 'correo-roto',
            'establecimiento_correo' => 'otro-roto',
            'municipio_id' => 999999,
            'categoria_id' => 999999,
            'descripcion' => 'corto',
        ]))->assertSessionHasErrors([
            'solicitante_correo',
            'establecimiento_correo',
            'municipio_id',
            'categoria_id',
            'descripcion',
        ]);

        $this->assertSame(0, SolicitudAfiliacion::count());
    }

    public function test_honeypot_continua_bloqueando_bots(): void
    {
        $this->post(route('afiliate.store'), $this->datosValidos([
            Formulario::CAMPO_TRAMPA => 'soy-un-bot',
        ]))->assertStatus(422);

        $this->assertSame(0, SolicitudAfiliacion::count());
    }

    public function test_throttle_del_formulario_sigue_activo(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post(route('afiliate.store'), $this->datosValidos([
                'solicitante_correo' => "sandra{$i}@ejemplo.test",
                'establecimiento_correo' => "hola{$i}@bruma.test",
            ]))->assertRedirect();
        }

        $this->post(route('afiliate.store'), $this->datosValidos([
            'solicitante_correo' => 'septima@ejemplo.test',
            'establecimiento_correo' => 'septima@bruma.test',
        ]))->assertTooManyRequests();

        $this->assertSame(6, SolicitudAfiliacion::count());
    }

    public function test_admin_autorizado_puede_consultar_solicitudes(): void
    {
        SolicitudAfiliacion::factory()->create([
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN))
            ->get('/admin/solicitudes-afiliacion')
            ->assertSuccessful()
            ->assertSee('Bruma Gastrobar');

        Livewire::test(ListSolicitudAfiliacions::class)->assertOk();
    }

    public function test_asociado_no_puede_entrar_al_panel_de_solicitudes(): void
    {
        $this->actingAs($this->usuario(User::ROL_ASOCIADO))
            ->get('/admin/solicitudes-afiliacion')
            ->assertForbidden();
    }

    public function test_datos_sensibles_de_la_solicitud_no_quedan_expuestos_publicamente(): void
    {
        $this->post(route('afiliate.store'), $this->datosValidos([
            'solicitante_identificacion' => '1094.123.456',
            'nit' => '901234567-8',
        ]))->assertSessionHas('exito');

        $this->get(route('afiliate'))
            ->assertSuccessful()
            ->assertDontSee('1094.123.456')
            ->assertDontSee('901234567-8');
    }

    public function test_transiciones_de_seguimiento(): void
    {
        $admin = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($admin);

        $solicitud = SolicitudAfiliacion::factory()->create([
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
            'estado' => EstadoSolicitudAfiliacion::Pendiente,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('pasar_a_revision')->table($solicitud))
            ->assertHasNoErrors();

        $this->assertSame(EstadoSolicitudAfiliacion::EnRevision, $solicitud->fresh()->estado);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('registrar_visita')->table($solicitud), data: [
                'visita_programada_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'gestion_notas' => 'Visita coordinada con la propietaria.',
            ])
            ->assertHasNoErrors();

        $solicitud = $solicitud->fresh();

        $this->assertSame(EstadoSolicitudAfiliacion::Visita, $solicitud->estado);
        $this->assertNotNull($solicitud->visita_programada_at);
        $this->assertSame('Visita coordinada con la propietaria.', $solicitud->gestion_notas);
    }

    public function test_aprobar_crea_asociado_usuario_rol_y_vinculos(): void
    {
        Mail::fake();
        $admin = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($admin);

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_nombre' => 'Sandra Ríos',
            'solicitante_correo' => 'sandra@ejemplo.test',
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'nit' => '901234567-8',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
            'estado' => EstadoSolicitudAfiliacion::Visita,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud), data: [
                'gestion_notas' => 'Visita aprobada por cumplimiento de requisitos.',
            ])
            ->assertHasNoErrors();

        $solicitud = $solicitud->fresh();
        $asociado = Asociado::firstOrFail();
        $usuario = User::where('email', 'sandra@ejemplo.test')->firstOrFail();

        $this->assertSame(EstadoSolicitudAfiliacion::Aprobada, $solicitud->estado);
        $this->assertSame($asociado->id, $solicitud->asociado_id);
        $this->assertSame($usuario->id, $solicitud->user_id);
        $this->assertSame($admin->id, $solicitud->aprobado_por);
        $this->assertNotNull($solicitud->aprobado_at);
        $this->assertNotNull($solicitud->resuelto_at);
        $this->assertSame('Bruma Gastrobar', $asociado->nombre);
        $this->assertSame('901234567-8', $asociado->documento);
        $this->assertSame('Sandra Ríos', $asociado->representante);
        $this->assertSame($asociado->id, $usuario->asociado_id);
        $this->assertTrue($usuario->hasRole(User::ROL_ASOCIADO));
        $this->assertSame(0, Cartera::count());
        $this->assertSame(0, Transaccion::count());

        Mail::assertSent(BienvenidaAsociado::class, fn (BienvenidaAsociado $correo): bool => $correo->hasTo('sandra@ejemplo.test'));
    }

    public function test_no_se_puede_aprobar_desde_edicion_ordinaria(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
            'estado' => EstadoSolicitudAfiliacion::Pendiente,
        ]);

        Livewire::test(EditSolicitudAfiliacion::class, ['record' => $solicitud->getRouteKey()])
            ->fillForm([
                'estado' => EstadoSolicitudAfiliacion::Aprobada->value,
            ])
            ->call('save')
            ->assertHasErrors(['data.estado']);

        $solicitud = $solicitud->fresh();

        $this->assertSame(EstadoSolicitudAfiliacion::Pendiente, $solicitud->estado);
        $this->assertNull($solicitud->asociado_id);
        $this->assertNull($solicitud->user_id);
        $this->assertNull($solicitud->aprobado_at);
        $this->assertSame(0, Asociado::count());
        $this->assertSame(1, User::count());
    }

    public function test_aprobar_dos_veces_no_crea_duplicados(): void
    {
        Mail::fake();
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_correo' => 'sandra@ejemplo.test',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        try {
            app(AprobarSolicitudAfiliacion::class)($solicitud->fresh(), auth()->user());
        } catch (\LogicException) {
            // Es el resultado esperado: la segunda aprobación se bloquea.
        }

        $this->assertSame(1, Asociado::count());
        $this->assertSame(1, User::where('email', 'sandra@ejemplo.test')->count());
    }

    public function test_duplicado_de_email_bloquea_aprobacion(): void
    {
        Mail::fake();
        $this->usuario(User::ROL_SUPER_ADMIN);
        User::factory()->create(['email' => 'sandra@ejemplo.test']);

        $this->actingAs(User::role(User::ROL_SUPER_ADMIN)->first());

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_correo' => 'sandra@ejemplo.test',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        $this->assertSame(0, Asociado::count());
        $this->assertSame(EstadoSolicitudAfiliacion::Pendiente, $solicitud->fresh()->estado);
    }

    public function test_duplicado_de_nit_bloquea_aprobacion(): void
    {
        Mail::fake();
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));
        Asociado::factory()->create(['documento' => '901234567-8']);

        $solicitud = SolicitudAfiliacion::factory()->create([
            'nit' => '901234567-8',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        $this->assertSame(1, Asociado::count());
        $this->assertNull($solicitud->fresh()->asociado_id);
        $this->assertSame(EstadoSolicitudAfiliacion::Pendiente, $solicitud->fresh()->estado);
    }

    public function test_si_falla_creacion_critica_hace_rollback(): void
    {
        Mail::fake();
        $admin = $this->usuario(User::ROL_SUPER_ADMIN);
        Role::where('name', User::ROL_ASOCIADO)->delete();
        $this->actingAs($admin);

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_correo' => 'sandra@ejemplo.test',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        try {
            Livewire::test(ListSolicitudAfiliacions::class)
                ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud));
        } catch (\Throwable) {
            // La asignación de rol falla y la transacción debe deshacer lo previo.
        }

        $this->assertSame(0, Asociado::count());
        $this->assertSame(1, User::count());
        $this->assertSame(EstadoSolicitudAfiliacion::Pendiente, $solicitud->fresh()->estado);
    }

    public function test_rechazar_no_crea_asociado_ni_usuario(): void
    {
        $admin = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($admin);

        $solicitud = SolicitudAfiliacion::factory()->create([
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('rechazar_solicitud')->table($solicitud), data: [
                'gestion_notas' => 'No cumple los requisitos del gremio.',
            ])
            ->assertHasNoErrors();

        $solicitud = $solicitud->fresh();

        $this->assertSame(EstadoSolicitudAfiliacion::Rechazada, $solicitud->estado);
        $this->assertSame($admin->id, $solicitud->rechazado_por);
        $this->assertNotNull($solicitud->rechazado_at);
        $this->assertSame('No cumple los requisitos del gremio.', $solicitud->gestion_notas);
        $this->assertSame(0, Asociado::count());
        $this->assertSame(1, User::count());
    }

    public function test_token_de_bienvenida_permite_establecer_contrasena_y_entrar_a_mi_cuenta(): void
    {
        Mail::fake();
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_nombre' => 'Sandra Ríos',
            'solicitante_correo' => 'sandra@ejemplo.test',
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        auth()->logout();

        Mail::assertSent(BienvenidaAsociado::class, function (BienvenidaAsociado $correo): bool {
            $this->assertStringNotContainsString('ClaveTemporal', $correo->render());
            $this->assertStringContainsString('Definir mi contraseña', $correo->render());

            $this->post(route('mi-cuenta.password.update'), [
                'token' => $correo->token,
                'email' => 'sandra@ejemplo.test',
                'password' => 'ClavePropia2026*',
                'password_confirmation' => 'ClavePropia2026*',
            ])->assertRedirect(route('mi-cuenta.entrar'));

            return true;
        });

        $usuario = User::where('email', 'sandra@ejemplo.test')->firstOrFail();

        $this->assertTrue(Hash::check('ClavePropia2026*', $usuario->password));

        $this->post(route('mi-cuenta.entrar.post'), [
            'email' => 'sandra@ejemplo.test',
            'password' => 'ClavePropia2026*',
        ])->assertRedirect(route('mi-cuenta.index'));

        $this->get(route('mi-cuenta.index'))
            ->assertSuccessful()
            ->assertSee('Bruma Gastrobar')
            ->assertDontSee('Otro Bar');

        $this->get('/admin')->assertForbidden();
    }

    public function test_reenvio_de_enlace_genera_token_valido_sin_crear_usuario_duplicado(): void
    {
        Mail::fake();
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_nombre' => 'Sandra Ríos',
            'solicitante_correo' => 'sandra@ejemplo.test',
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        Mail::fake();

        $solicitud = $solicitud->fresh();
        $asociadoId = $solicitud->asociado_id;
        $usuarioId = $solicitud->user_id;

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('reenviar_enlace_acceso')->table($solicitud))
            ->assertHasNoErrors();

        $this->assertSame(1, Asociado::count());
        $this->assertSame(2, User::count());
        $this->assertSame($asociadoId, $solicitud->fresh()->asociado_id);
        $this->assertSame($usuarioId, $solicitud->fresh()->user_id);

        auth()->logout();

        Mail::assertSent(BienvenidaAsociado::class, function (BienvenidaAsociado $correo): bool {
            $this->assertTrue($correo->reenvio);
            $this->assertStringNotContainsString('ClaveTemporal', $correo->render());

            $this->post(route('mi-cuenta.password.update'), [
                'token' => $correo->token,
                'email' => 'sandra@ejemplo.test',
                'password' => 'ClaveReenviada2026*',
                'password_confirmation' => 'ClaveReenviada2026*',
            ])->assertRedirect(route('mi-cuenta.entrar'));

            return $correo->hasTo('sandra@ejemplo.test');
        });

        $usuario = User::where('email', 'sandra@ejemplo.test')->firstOrFail();

        $this->assertTrue(Hash::check('ClaveReenviada2026*', $usuario->password));
    }

    public function test_reenvio_de_enlace_no_aparece_para_asociado_no_autorizado(): void
    {
        $usuario = $this->usuario(User::ROL_ASOCIADO);
        $this->actingAs($usuario);

        $solicitud = SolicitudAfiliacion::factory()->create([
            'estado' => EstadoSolicitudAfiliacion::Aprobada,
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        $this->assertFalse($usuario->can('update', $solicitud));

        $this->get('/admin/solicitudes-afiliacion')->assertForbidden();
    }

    public function test_reenvio_de_enlace_fallido_no_modifica_usuario_ni_asociado(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 2,
        ]);
        Exceptions::fake();

        $asociado = Asociado::factory()->create(['nombre' => 'Bruma Gastrobar']);
        $usuario = User::factory()->create([
            'email' => 'sandra@ejemplo.test',
            'asociado_id' => $asociado->id,
        ]);
        $solicitud = SolicitudAfiliacion::factory()->create([
            'estado' => EstadoSolicitudAfiliacion::Aprobada,
            'asociado_id' => $asociado->id,
            'user_id' => $usuario->id,
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        $correoSalio = app(ReenviarEnlaceAccesoAsociado::class)($solicitud);

        $this->assertFalse($correoSalio);
        $this->assertSame(1, Asociado::count());
        $this->assertSame(1, User::count());
        $this->assertSame($asociado->id, $solicitud->fresh()->asociado_id);
        $this->assertSame($usuario->id, $solicitud->fresh()->user_id);
        Exceptions::assertReported(TransportException::class);
    }

    public function test_token_invalido_no_cambia_contrasena(): void
    {
        $usuario = User::factory()->create([
            'email' => 'sandra@ejemplo.test',
            'password' => 'ClaveAnterior2026*',
        ]);
        Password::createToken($usuario);

        $this->post(route('mi-cuenta.password.update'), [
            'token' => 'token-invalido',
            'email' => 'sandra@ejemplo.test',
            'password' => 'ClaveNueva2026*',
            'password_confirmation' => 'ClaveNueva2026*',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('ClaveAnterior2026*', $usuario->fresh()->password));
    }

    public function test_nuevo_asociado_no_ve_datos_de_otro_asociado(): void
    {
        Mail::fake();
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Asociado::factory()->publicado()->create(['nombre' => 'Otro Bar']);
        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_correo' => 'sandra@ejemplo.test',
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        $usuario = User::where('email', 'sandra@ejemplo.test')->firstOrFail();

        $this->actingAs($usuario->fresh())
            ->get(route('mi-cuenta.index'))
            ->assertSuccessful()
            ->assertSee('Bruma Gastrobar')
            ->assertDontSee('Otro Bar');
    }

    public function test_aprobacion_no_crea_cartera_transacciones_ni_pagos(): void
    {
        Mail::fake();
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        $this->assertSame(0, Cartera::count());
        $this->assertSame(0, Transaccion::count());
    }

    public function test_fallo_de_correo_no_corrompe_la_aprobacion(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 2,
        ]);
        Exceptions::fake();

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'solicitante_correo' => 'sandra@ejemplo.test',
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->callAction(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertHasNoErrors();

        $this->assertSame(EstadoSolicitudAfiliacion::Aprobada, $solicitud->fresh()->estado);
        $this->assertSame(1, Asociado::count());
        $this->assertSame(2, User::count());
        Exceptions::assertReported(TransportException::class);
    }

    public function test_acciones_finales_no_aparecen_en_solicitud_resuelta(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $solicitud = SolicitudAfiliacion::factory()->create([
            'estado' => EstadoSolicitudAfiliacion::Aprobada,
            'municipio_id' => $this->municipio->id,
            'categoria_id' => $this->categoria->id,
        ]);

        Livewire::test(ListSolicitudAfiliacions::class)
            ->assertActionHidden(TestAction::make('aprobar_solicitud')->table($solicitud))
            ->assertActionHidden(TestAction::make('rechazar_solicitud')->table($solicitud));

        Livewire::test(EditSolicitudAfiliacion::class, ['record' => $solicitud->getRouteKey()])
            ->assertOk();
    }
}
