<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Una contraseña que puso alguien distinto del titular es provisional.
 *
 * La marca vive en `users.contrasena_provisional` y solo la enciende el
 * modelo, para cuentas de rol asociado: el panel y /mi-cuenta son puertas
 * distintas, y al equipo del gremio la marca no le cierra nada.
 */
class ContrasenaProvisionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    public function test_una_cuenta_nace_sin_la_marca(): void
    {
        $usuario = User::factory()->create();

        $this->assertFalse($usuario->fresh()->contrasena_provisional);
    }

    public function test_marcar_una_cuenta_de_afiliado_la_deja_provisional(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }

    /** @return array<string, array{string}> */
    public static function rolesDelEquipo(): array
    {
        return [
            'dirección' => [User::ROL_SUPER_ADMIN],
            'secretaría' => [User::ROL_SUBADMIN],
        ];
    }

    #[DataProvider('rolesDelEquipo')]
    public function test_una_cuenta_del_equipo_no_se_marca(string $rol): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

        $this->assertFalse($usuario->fresh()->contrasena_provisional);
    }

    /**
     * El formulario de Usuarios guarda el rol nuevo antes del gancho que
     * marca, y la instancia puede traer la relación `roles` cargada con el
     * rol viejo. La marca tiene que mirar el rol vigente.
     */
    public function test_la_marca_mira_el_rol_vigente_y_no_la_relacion_en_memoria(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);
        $usuario->load('roles');

        User::query()->findOrFail($usuario->id)->syncRoles([User::ROL_ASOCIADO]);

        $usuario->marcarContrasenaProvisionalSiEsAfiliado();

        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }
}
