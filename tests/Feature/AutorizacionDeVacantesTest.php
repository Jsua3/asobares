<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorizacionDeVacantesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol, ?Asociado $asociado = null): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado?->id]);
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_el_dueno_edita_su_vacante(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $duenio = $this->crearUsuario(User::ROL_ASOCIADO, $asociado);
        $vacante = Vacante::factory()->for($asociado)->create();

        $this->assertTrue($duenio->can('update', $vacante));
        $this->assertTrue($duenio->can('view', $vacante));
        $this->assertTrue($duenio->can('create', Vacante::class));
    }

    public function test_un_asociado_no_toca_la_vacante_de_otro(): void
    {
        $ajeno = $this->crearUsuario(User::ROL_ASOCIADO, Asociado::factory()->publicado()->create());
        $vacante = Vacante::factory()->for(Asociado::factory()->publicado())->create();

        $this->assertFalse($ajeno->can('update', $vacante));
        $this->assertFalse($ajeno->can('view', $vacante));
    }

    public function test_un_asociado_sin_establecimiento_no_publica_nada(): void
    {
        $huerfano = $this->crearUsuario(User::ROL_ASOCIADO);

        $this->assertFalse($huerfano->can('create', Vacante::class));
    }

    public function test_ni_la_secretaria_ni_la_direccion_editan_una_vacante_ajena(): void
    {
        $vacante = Vacante::factory()->create();

        foreach ([User::ROL_SUBADMIN, User::ROL_SUPER_ADMIN] as $rol) {
            $usuario = $this->crearUsuario($rol);

            $this->assertFalse($usuario->can('update', $vacante), "{$rol} no edita contenido ajeno.");
            $this->assertFalse($usuario->can('create', Vacante::class), "{$rol} no publica por el asociado.");
            $this->assertTrue($usuario->can('view', $vacante), "{$rol} sí modera y por tanto lee.");
        }
    }

    public function test_la_secretaria_aprueba_y_solo_la_direccion_elimina(): void
    {
        $vacante = Vacante::factory()->create();
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);

        $this->assertTrue($secretaria->can('publicar', $vacante));
        $this->assertFalse($secretaria->can('delete', $vacante));
        $this->assertTrue($direccion->can('publicar', $vacante));
        $this->assertTrue($direccion->can('delete', $vacante));
    }

    public function test_las_postulaciones_las_gestiona_el_dueno_de_la_vacante(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $duenio = $this->crearUsuario(User::ROL_ASOCIADO, $asociado);
        $ajeno = $this->crearUsuario(User::ROL_ASOCIADO, Asociado::factory()->publicado()->create());

        $postulacion = Postulacion::factory()->for(Vacante::factory()->for($asociado))->create();

        $this->assertTrue($duenio->can('gestionar', $postulacion));
        $this->assertFalse($ajeno->can('gestionar', $postulacion));
    }

    public function test_nadie_crea_postulaciones_a_mano(): void
    {
        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN, User::ROL_ASOCIADO] as $rol) {
            $this->assertFalse($this->crearUsuario($rol)->can('create', Postulacion::class));
        }
    }
}
