<?php

namespace Tests\Feature;

use App\Enums\EstadoMensaje;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoMensaje;
use App\Filament\Pages\AjustesDelSitio;
use App\Filament\Resources\Asociados\Pages\CreateAsociado;
use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Filament\Resources\Carteras\Pages\ListCarteras;
use App\Filament\Resources\Mensajes\Pages\ListMensajes;
use App\Filament\Resources\Transaccions\Pages\ListTransaccions;
use App\Filament\Resources\Transaccions\TransaccionResource;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Categoria;
use App\Models\Mensaje;
use App\Models\Municipio;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\MunicipioSeeder;
use Database\Seeders\RolYPermisoSeeder;
use Database\Seeders\SettingSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Las acciones del panel solo fallan cuando alguien las pulsa: renderizar la
 * página no las ejercita. Aquí se invocan de verdad.
 */
class AccionesDelPanelTest extends TestCase
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

    /**
     * El archivo lo abre la contadora en Excel, y Excel ejecuta como fórmula
     * toda celda que empiece por `=`. El nombre del establecimiento lo escribe
     * un tercero desde el panel, así que sale neutralizado.
     */
    public function test_la_plantilla_de_cartera_neutraliza_las_formulas_de_excel(): void
    {
        $formula = Asociado::factory()->create(['nombre' => '=WEBSERVICE(A1)', 'slug' => 'bar-formula']);
        $comillas = Asociado::factory()->create(['nombre' => 'Bar "El Roble", Armenia', 'slug' => 'bar-el-roble']);

        foreach ([$formula, $comillas] as $asociado) {
            Cartera::create([
                'asociado_id' => $asociado->id,
                'saldo_pendiente' => 50000,
                'meses_mora' => 1,
                'actualizado_at' => now(),
            ]);
        }

        $csv = Livewire::actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN))
            ->test(ListCarteras::class)
            ->instance()
            ->plantillaDeCartera();

        $this->assertStringContainsString("'=WEBSERVICE(A1)", $csv, 'La fórmula sale como texto.');
        $this->assertStringNotContainsString('"=WEBSERVICE', $csv, 'Ninguna celda puede empezar por = sin neutralizar.');

        // Y el nombre con comillas y coma no puede romper el formato.
        $this->assertStringContainsString('Bar ""El Roble"", Armenia', $csv);
    }

    // --- Acciones de aprobación (el guion 1 del README) ---

    public function test_la_direccion_publica_un_pendiente_con_la_accion_aprobar(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        Livewire::test(ListAsociados::class)
            ->callAction(TestAction::make('aprobar')->table($asociado))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Publicado, $asociado->fresh()->estado);
    }

    public function test_la_direccion_devuelve_a_borrador_con_la_accion(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $asociado = Asociado::factory()->publicado()->create();

        Livewire::test(ListAsociados::class)
            ->callAction(TestAction::make('devolver')->table($asociado))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Borrador, $asociado->fresh()->estado);
    }

    public function test_la_secretaria_no_ve_la_accion_de_aprobar(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        Livewire::test(ListAsociados::class)
            ->assertActionHidden(TestAction::make('aprobar')->table($asociado));
    }

    public function test_aunque_la_secretaria_invoque_aprobar_a_la_fuerza_no_publica(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        // Esconder el botón es interfaz; lo que impide de verdad es la policy
        // más el observer, que reescribe el estado al guardar.
        try {
            Livewire::test(ListAsociados::class)
                ->callAction(TestAction::make('aprobar')->table($asociado));
        } catch (\Throwable) {
            // Filament puede rechazar la acción oculta: también es un resultado válido.
        }

        $this->assertNotSame(
            EstadoPublicacion::Publicado,
            $asociado->fresh()->estado,
            'La secretaría no puede publicar por ninguna vía.'
        );
    }

    // --- Bandeja de mensajes ---

    public function test_marcar_respondido_guarda_la_nota_y_la_fecha(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $mensaje = Mensaje::create([
            'tipo' => TipoMensaje::Pqr,
            'nombre' => 'Carlos Muñoz',
            'correo' => 'carlos@ejemplo.test',
            'mensaje' => 'No me ha llegado el carné de afiliado.',
            'radicado' => 'PQR-2026-0001',
            'estado' => EstadoMensaje::Nuevo,
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ]);

        Livewire::test(ListMensajes::class)
            ->callAction(TestAction::make('responder')->table($mensaje), data: [
                'nota_respuesta' => 'Se verificó el pago y se despachó el carné.',
            ])
            ->assertHasNoErrors();

        $actualizado = $mensaje->fresh();
        $this->assertSame(EstadoMensaje::Respondido, $actualizado->estado);
        $this->assertSame('Se verificó el pago y se despachó el carné.', $actualizado->nota_respuesta);
        $this->assertNotNull($actualizado->respondido_at);
    }

    // --- Creación real a través del formulario ---

    public function test_crear_un_asociado_desde_el_formulario_del_panel(): void
    {
        $this->seed(MunicipioSeeder::class);
        $this->seed(CategoriaSeeder::class);
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(CreateAsociado::class)
            ->fillForm([
                'nombre' => 'Bar de Prueba del Panel',
                'slug' => 'bar-de-prueba-del-panel',
                'categoria_id' => Categoria::first()->id,
                'municipio_id' => Municipio::first()->id,
                'descripcion' => 'Creado desde el formulario real de Filament.',
                'estado' => EstadoPublicacion::Publicado->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('asociados', [
            'slug' => 'bar-de-prueba-del-panel',
            'estado' => EstadoPublicacion::Publicado->value,
        ]);
    }

    public function test_la_secretaria_que_crea_desde_el_formulario_termina_en_pendiente(): void
    {
        $this->seed(MunicipioSeeder::class);
        $this->seed(CategoriaSeeder::class);
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        Livewire::test(CreateAsociado::class)
            ->fillForm([
                'nombre' => 'Bar de la Secretaria',
                'slug' => 'bar-de-la-secretaria',
                'categoria_id' => Categoria::first()->id,
                'municipio_id' => Municipio::first()->id,
                'estado' => EstadoPublicacion::Publicado->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Este es el RF-37 recorrido de punta a punta por la interfaz real.
        $this->assertDatabaseHas('asociados', [
            'slug' => 'bar-de-la-secretaria',
            'estado' => EstadoPublicacion::PendienteAprobacion->value,
        ]);
    }

    // --- Página de ajustes ---

    public function test_guardar_los_ajustes_actualiza_el_sitio(): void
    {
        $this->seed(SettingSeeder::class);
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(AjustesDelSitio::class)
            ->fillForm(['sitio_eslogan' => 'Un lema nuevo para la prueba'])
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertSame('Un lema nuevo para la prueba', Setting::where('clave', 'sitio_eslogan')->value('valor'));

        // La caché se invalida a mano tras la actualización masiva: si no,
        // el panel diría una cosa y el sitio público mostraría otra.
        $this->assertSame('Un lema nuevo para la prueba', ajuste('sitio_eslogan'));
    }

    public function test_la_secretaria_no_entra_a_los_ajustes(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        $this->assertFalse(AjustesDelSitio::canAccess());
    }

    // --- Formulario de usuarios (rol y contraseña) ---

    public function test_crear_un_usuario_con_rol_desde_el_formulario_del_panel(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Persona de la Oficina',
                'email' => 'nueva@asobaresquindio.test',
                'password' => 'ClaveTemporal2026*',
                'roles' => [Role::findByName(User::ROL_SUBADMIN)->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $usuario = User::where('email', 'nueva@asobaresquindio.test')->firstOrFail();

        $this->assertTrue($usuario->hasRole(User::ROL_SUBADMIN));
        $this->assertTrue(Hash::check('ClaveTemporal2026*', $usuario->password));
    }

    public function test_editar_un_usuario_sin_tocar_la_contrasena_no_la_cambia(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $usuario = User::factory()->create(['password' => 'ClaveOriginal2026*']);
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        Livewire::test(EditUser::class, ['record' => $usuario->getRouteKey()])
            ->fillForm([
                'name' => 'Nombre Corregido',
                'password' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $usuario->refresh();

        $this->assertSame('Nombre Corregido', $usuario->name);
        $this->assertTrue(Hash::check('ClaveOriginal2026*', $usuario->password));
    }

    /**
     * El `CreateAction` huérfano de transacciones — hallazgo real de la
     * auditoría del 19 de agosto. El recurso solo declara la página `index`,
     * así que el botón no llevaba a ninguna parte; Filament lo habría
     * resuelto abriendo el formulario en un modal y el personal habría podido
     * fabricar a mano un cobro que la pasarela nunca hizo. Es la misma
     * frontera que `FlujoDePagoTest` guarda del lado de las inscripciones.
     */
    public function test_las_transacciones_son_de_solo_lectura_y_no_ofrecen_crear_a_mano(): void
    {
        $this->assertSame(
            ['index'],
            array_keys(TransaccionResource::getPages()),
            'El recurso de transacciones dejó de ser de solo lectura.'
        );

        $acciones = (new \ReflectionMethod(ListTransaccions::class, 'getHeaderActions'))
            ->invoke(new ListTransaccions);

        $this->assertSame(
            [],
            $acciones,
            'La lista de transacciones volvió a montar una acción de cabecera; si es un CreateAction, no tiene página a la que ir.'
        );
    }

    public function test_la_lista_de_transacciones_carga_sin_el_boton_de_crear(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListTransaccions::class)
            ->assertOk()
            ->assertDontSeeHtml('/admin/transacciones/create');
    }
}
