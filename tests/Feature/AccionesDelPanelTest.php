<?php

namespace Tests\Feature;

use App\Enums\EstadoMensaje;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoMensaje;
use App\Filament\Pages\AjustesDelSitio;
use App\Filament\Resources\Asociados\Pages\CreateAsociado;
use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Filament\Resources\Mensajes\Pages\ListMensajes;
use App\Models\Asociado;
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
use Livewire\Livewire;
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
}
