<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * RF-37. El requisito que se está evaluando: un subadmin no publica, ni
 * siquiera manipulando el formulario, porque la regla vive en el modelo.
 */
class FlujoDeAprobacionTest extends TestCase
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

    public function test_un_subadmin_no_puede_publicar_aunque_mande_el_estado_publicado(): void
    {
        $subadmin = $this->crearUsuario(User::ROL_SUBADMIN);
        Auth::login($subadmin);

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(
            EstadoPublicacion::PendienteAprobacion,
            $asociado->fresh()->estado,
            'El contenido de un subadmin debe quedar pendiente de aprobación.'
        );
    }

    public function test_un_subadmin_tampoco_puede_publicar_editando_un_borrador(): void
    {
        $subadmin = $this->crearUsuario(User::ROL_SUBADMIN);
        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Borrador]);

        Auth::login($subadmin);
        $asociado->update(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(EstadoPublicacion::PendienteAprobacion, $asociado->fresh()->estado);
    }

    public function test_el_super_admin_si_publica(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        Auth::login($direccion);

        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(EstadoPublicacion::Publicado, $asociado->fresh()->estado);
    }

    public function test_enviar_a_revision_notifica_a_la_direccion(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        $subadmin = $this->crearUsuario(User::ROL_SUBADMIN);

        Auth::login($subadmin);
        Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(
            1,
            $direccion->notifications()->count(),
            'La dirección debe recibir una notificación de base de datos por cada envío a revisión.'
        );
    }

    public function test_las_semillas_y_la_consola_no_pasan_por_el_flujo(): void
    {
        // Sin sesión iniciada (seeders, comandos, jobs) el estado se respeta tal cual.
        $asociado = Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(EstadoPublicacion::Publicado, $asociado->fresh()->estado);
    }
}
