<?php

namespace Tests\Feature;

use App\Enums\EstadoDeGestion;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Artistas\Pages\ListArtistas;
use App\Filament\Resources\Aspirantes\Pages\ListAspirantes;
use App\Filament\Resources\Postulaciones\Pages\ListPostulaciones;
use App\Filament\Resources\Proveedors\Pages\ListProveedors;
use App\Filament\Resources\Vacantes\Pages\ListVacantes;
use App\Filament\Support\AccionesDeAprobacion;
use App\Mail\FichaDeBolsaPublicada;
use App\Mail\VacanteAprobada;
use App\Mail\VacanteDevuelta;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Postulacion;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\ActionGroup;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ModeracionDeBolsasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_el_equipo_ve_la_bandeja_de_postulaciones(): void
    {
        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN] as $rol) {
            $this->actingAs($this->crearUsuario($rol))->get('/admin/postulaciones')->assertSuccessful();
        }
    }

    public function test_nadie_crea_una_postulacion_desde_el_panel(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $this->get('/admin/postulaciones/create')->assertNotFound();
    }

    public function test_la_secretaria_cambia_el_estado_de_gestion_de_una_postulacion(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $postulacion = Postulacion::factory()->for(Vacante::factory()->publicado())->create();

        Livewire::test(ListPostulaciones::class)
            ->callAction(TestAction::make('gestionar')->table($postulacion), data: [
                'estado' => EstadoDeGestion::Contactado->value,
            ])
            ->assertHasNoErrors();

        $this->assertSame(EstadoDeGestion::Contactado, $postulacion->fresh()->estado);
    }

    public function test_la_secretaria_aprueba_una_vacante_y_avisa_al_establecimiento(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('aprobar')->table($vacante))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $vacante->fresh()->estado);
        Mail::assertSent(VacanteAprobada::class, 1);
    }

    public function test_devolver_con_motivo_lo_guarda_y_avisa_al_establecimiento(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('devolver')->table($vacante), data: [
                'motivo_devolucion' => 'Falta el horario del turno.',
            ])
            ->assertHasNoErrors();

        $devuelta = $vacante->fresh();

        $this->assertSame(EstadoPublicacion::Borrador, $devuelta->estado);
        $this->assertSame('Falta el horario del turno.', $devuelta->motivo_devolucion);
        Mail::assertSent(VacanteDevuelta::class, 1);
    }

    public function test_devolver_sin_motivo_no_cambia_nada(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->callAction(TestAction::make('devolver')->table($vacante), data: [
                'motivo_devolucion' => '',
            ])
            ->assertHasFormErrors(['motivo_devolucion' => 'required']);

        $sinCambios = $vacante->fresh();

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $sinCambios->estado);
        $this->assertNull($sinCambios->motivo_devolucion);
        Mail::assertNotSent(VacanteDevuelta::class);
    }

    public function test_aprobar_limpia_el_motivo_de_la_devolucion_anterior(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $vacante = Vacante::factory()->pendiente()->create(['motivo_devolucion' => 'Un motivo viejo.']);

        Livewire::test(ListVacantes::class)->callAction(TestAction::make('aprobar')->table($vacante));

        $this->assertNull($vacante->fresh()->motivo_devolucion);
    }

    /**
     * `route()` no descarta un modelo de sobra cuando la ruta pública no
     * declara parámetros: lo cuelga como query string. Este contrato exige
     * que la función que arma la URL de `proveedores.index` —que no toma
     * parámetros— nunca reciba el registro, así el enlace del correo queda
     * limpio.
     */
    public function test_aprobar_ficha_de_bolsa_arma_la_url_publica_sin_parametros_colgantes(): void
    {
        Mail::fake();

        $proveedor = Proveedor::factory()->create(['correo' => 'contacto@proveedor.test']);

        $accion = AccionesDeAprobacion::aprobarFichaDeBolsa(fn (): string => route('proveedores.index'));
        ($accion->getActionFunction())($proveedor);

        Mail::assertSent(
            FichaDeBolsaPublicada::class,
            fn (FichaDeBolsaPublicada $correo): bool => $correo->urlPublica === route('proveedores.index')
                && ! str_contains($correo->urlPublica, '?')
                && $correo->nombreDeLaFicha === $proveedor->nombre
        );
    }

    public function test_el_panel_ya_no_crea_ni_edita_vacantes(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $this->get('/admin/vacantes/create')->assertNotFound();
        $this->get('/admin/vacantes/'.Vacante::factory()->create()->id.'/edit')->assertNotFound();
    }

    public function test_el_listado_de_vacantes_sigue_abierto_para_moderar(): void
    {
        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN] as $rol) {
            $this->actingAs($this->crearUsuario($rol))->get('/admin/vacantes')->assertSuccessful();
        }
    }

    public function test_aprobar_una_ficha_de_artista_avisa_al_solicitante(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => 'dj@ejemplo.test']);

        Livewire::test(ListArtistas::class)
            ->callAction(TestAction::make('aprobar')->table($artista))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_una_ficha_sin_correo_no_intenta_escribir(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => null]);

        Livewire::test(ListArtistas::class)
            ->callAction(TestAction::make('aprobar')->table($artista));

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertNothingSent();
    }

    // --- Presentación controlada de las acciones de Vacantes. ---

    public function test_las_acciones_individuales_de_vacantes_viven_en_un_solo_menu(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $acciones = Livewire::test(ListVacantes::class)
            ->instance()
            ->getTable()
            ->getRecordActions();

        $this->assertCount(1, $acciones);
        $this->assertInstanceOf(ActionGroup::class, $acciones[0]);
        $this->assertSame(
            ['aprobar', 'devolver', 'dejar_de_publicar', 'postulaciones', 'delete'],
            array_keys($acciones[0]->getFlatActions())
        );
    }

    public function test_la_barra_contextual_conserva_solo_las_acciones_masivas_seguras(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        Livewire::test(ListVacantes::class)
            ->assertTableBulkActionExists('aprobar_lote')
            ->assertTableBulkActionExists('delete');
    }

    public function test_el_menu_conserva_las_condiciones_por_estado_y_por_postulaciones(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $pendiente = Vacante::factory()->pendiente()->create();
        $publicadaSinPostulaciones = Vacante::factory()->publicado()->create();
        $publicadaConPostulaciones = Vacante::factory()->publicado()->create();
        Postulacion::factory()->for($publicadaConPostulaciones)->create();

        Livewire::test(ListVacantes::class)
            ->assertActionVisible(TestAction::make('aprobar')->table($pendiente))
            ->assertActionHidden(TestAction::make('dejar_de_publicar')->table($pendiente))
            ->assertActionHidden(TestAction::make('aprobar')->table($publicadaSinPostulaciones))
            ->assertActionVisible(TestAction::make('dejar_de_publicar')->table($publicadaSinPostulaciones))
            ->assertActionVisible(TestAction::make('postulaciones')->table($publicadaSinPostulaciones))
            ->assertActionVisible(TestAction::make('postulaciones')->table($publicadaConPostulaciones));
    }

    public function test_aprobar_en_lote_de_vacantes_publica_limpia_el_motivo_y_avisa(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create(['motivo_devolucion' => 'Un motivo viejo.']);

        Livewire::test(ListVacantes::class)
            ->selectTableRecords([$vacante->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $publicada = $vacante->fresh();
        $this->assertSame(EstadoPublicacion::Publicado, $publicada->estado);
        $this->assertNull($publicada->motivo_devolucion, 'El lote no puede dejar la tarjeta contradictoria: publicada y devuelta a la vez.');
        Mail::assertSent(VacanteAprobada::class, 1);
    }

    public function test_aprobar_en_lote_de_vacantes_no_revienta_si_el_asociado_no_tiene_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => null]);
        $vacante = Vacante::factory()->for($asociado)->pendiente()->create();

        Livewire::test(ListVacantes::class)
            ->selectTableRecords([$vacante->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $vacante->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_aprobar_en_lote_de_vacantes_no_reenvia_el_correo_a_las_ya_publicadas(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $pendiente = Vacante::factory()->for($asociado)->pendiente()->create();
        $yaPublicada = Vacante::factory()->for($asociado)->publicado()->create();

        Livewire::test(ListVacantes::class)
            ->selectTableRecords([$pendiente->getKey(), $yaPublicada->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors()
            ->assertNotified('1 registros publicados');

        Mail::assertSent(VacanteAprobada::class, 1);
    }

    public function test_aprobar_en_lote_de_artistas_publica_y_avisa_por_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => 'dj@ejemplo.test']);

        Livewire::test(ListArtistas::class)
            ->selectTableRecords([$artista->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_en_lote_de_artistas_no_revienta_si_no_tiene_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $artista = Artista::factory()->pendiente()->create(['correo' => null]);

        Livewire::test(ListArtistas::class)
            ->selectTableRecords([$artista->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $artista->fresh()->estado);
        Mail::assertNothingSent();
    }

    /**
     * Mismo defecto que en vacantes, pero para fichas de artista: el lote
     * no puede reescribir ni reavisar a una ficha que ya estaba publicada.
     */
    public function test_aprobar_en_lote_de_artistas_no_reenvia_el_correo_a_las_ya_publicadas(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $pendiente = Artista::factory()->pendiente()->create(['correo' => 'dj@ejemplo.test']);
        $yaPublicado = Artista::factory()->publicado()->create(['correo' => 'otro@ejemplo.test']);

        Livewire::test(ListArtistas::class)
            ->selectTableRecords([$pendiente->getKey(), $yaPublicado->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors()
            ->assertNotified('1 registros publicados');

        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_en_lote_de_proveedores_publica_y_avisa_por_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $proveedor = Proveedor::factory()->pendiente()->create(['correo' => 'contacto@proveedor.test']);

        Livewire::test(ListProveedors::class)
            ->selectTableRecords([$proveedor->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $proveedor->fresh()->estado);
        Mail::assertSent(FichaDeBolsaPublicada::class, 1);
    }

    public function test_aprobar_en_lote_de_proveedores_no_revienta_si_no_tiene_correo(): void
    {
        Mail::fake();
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $proveedor = Proveedor::factory()->pendiente()->create(['correo' => null]);

        Livewire::test(ListProveedors::class)
            ->selectTableRecords([$proveedor->getKey()])
            ->callAction(TestAction::make('aprobar_lote')->table()->bulk())
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $proveedor->fresh()->estado);
        Mail::assertNothingSent();
    }

    public function test_la_bandeja_de_aspirantes_sigue_en_pie(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        Aspirante::factory()->create(['nombre' => 'Duván Marín']);

        $this->get('/admin/aspirantes')->assertSuccessful()->assertSee('Duván Marín');
    }

    /**
     * La puerta del banco de talento. Sin esta acción la columna `aprobado_el`
     * no la pondría nadie nunca y el directorio del afiliado se quedaría vacío
     * para siempre, que es un modo de fallo silencioso: la pantalla existe,
     * responde 200 y no enseña a nadie.
     */
    public function test_la_secretaria_aprueba_un_perfil_del_banco(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $aspirante = Aspirante::factory()->create();

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('aprobar')->table($aspirante))
            ->assertHasNoErrors();

        $this->assertNotNull($aspirante->fresh()->aprobado_el);
    }

    /**
     * Aprobar un perfil del banco **expone el nombre, el teléfono y el correo de
     * una persona a todos los establecimientos afiliados** --lo dice el propio
     * modal de confirmación--. Es la decisión más sensible del panel en materia
     * de datos personales, y era la única de su clase sin rastro: trece modelos
     * alimentaban la bitácora y `Aspirante` no. `aprobado_el` guardaba CUÁNDO,
     * nunca QUIÉN, y retirar el perfil ponía esa columna en nulo, borrando la
     * única huella que quedaba.
     *
     * RF-39 exige bitácora de actividad, y `encargo.md` §9 gobierna esto.
     */
    public function test_aprobar_un_perfil_del_banco_queda_en_la_bitacora_con_su_autor(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);
        $this->actingAs($secretaria);

        $aspirante = Aspirante::factory()->create();

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('aprobar')->table($aspirante))
            ->assertHasNoErrors();

        $entrada = Activity::query()->where('log_name', 'aspirante')->latest('id')->first();

        $this->assertNotNull($entrada, 'Aprobar un perfil del banco tiene que dejar rastro.');
        $this->assertTrue($entrada->causer?->is($secretaria), 'Y el rastro tiene que decir quién lo hizo.');
    }

    /**
     * Retirar también, y por el mismo motivo: es la acción que devuelve a
     * alguien a la invisibilidad y la que borraba `aprobado_el`.
     */
    public function test_retirar_un_perfil_del_banco_queda_en_la_bitacora(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);
        $this->actingAs($secretaria);

        $aspirante = Aspirante::factory()->aprobado()->create();
        Activity::query()->delete();

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('retirar')->table($aspirante))
            ->assertHasNoErrors();

        $this->assertSame(
            1,
            Activity::query()->where('log_name', 'aspirante')->count(),
            'Sacar a alguien del banco tiene que quedar anotado.'
        );
    }

    /**
     * La bitácora la lee la oficina, no un programador: tiene que decir qué
     * pasó en castellano y no `updated`.
     */
    public function test_la_bitacora_del_banco_se_lee_en_castellano(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $aspirante = Aspirante::factory()->create(['nombre' => 'Camila Restrepo']);

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('aprobar')->table($aspirante));

        $descripcion = (string) Activity::query()->where('log_name', 'aspirante')->latest('id')->value('description');

        $this->assertStringContainsString('Camila Restrepo', $descripcion);
        $this->assertStringContainsString('banco de talento', $descripcion);
    }

    public function test_la_secretaria_retira_del_banco_un_perfil_que_ya_habia_aprobado(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $aspirante = Aspirante::factory()->aprobado()->create();

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('retirar')->table($aspirante))
            ->assertHasNoErrors();

        $this->assertNull($aspirante->fresh()->aprobado_el);
    }

    /**
     * El asociado no tiene panel, pero el que sí lo tiene y no puede editar
     * aspirantes tampoco debe poder aprobarlos: la interfaz esconde y la policy
     * impide, como en el resto de las bolsas.
     */
    public function test_quien_no_edita_aspirantes_no_ve_la_accion_de_aprobar(): void
    {
        $usuario = $this->crearUsuario(User::ROL_SUBADMIN);

        // Hoy los dos roles del panel pueden editar aspirantes --es una bandeja
        // de secretaria--, asi que la unica forma de ejercer esta guardia es
        // quitarle el permiso al rol. Lo que se vigila aqui es que la accion le
        // pregunte a la policy; del reparto de permisos se ocupa
        // PermisosDeBolsaTest.
        $usuario->roles->first()->revokePermissionTo('editar_aspirante');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($usuario->fresh());

        $aspirante = Aspirante::factory()->create();

        Livewire::test(ListAspirantes::class)
            ->assertActionHidden(TestAction::make('aprobar')->table($aspirante));
    }
}
