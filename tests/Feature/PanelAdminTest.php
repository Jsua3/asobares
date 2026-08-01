<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PanelAdminTest extends TestCase
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

    /** @return list<array{0: string}> */
    public static function rutasDelPanel(): array
    {
        return array_map(fn (string $ruta): array => [$ruta], [
            '/admin',
            '/admin/asociados',
            '/admin/eventos',
            '/admin/boletin',
            '/admin/requisitos',
            '/admin/iniciativas',
            '/admin/vacantes',
            '/admin/aspirantes',
            '/admin/artistas',
            '/admin/proveedores',
            '/admin/mensajes',
            '/admin/inscripciones',
            '/admin/aliados',
            '/admin/beneficios',
            '/admin/cartera',
            '/admin/transacciones',
            '/admin/municipios',
            '/admin/categorias',
            '/admin/usuarios',
            '/admin/ajustes',
            '/admin/bitacora',
        ]);
    }

    #[DataProvider('rutasDelPanel')]
    public function test_la_direccion_entra_a_todo_el_panel(string $ruta): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN))
            ->get($ruta)
            ->assertSuccessful();
    }

    public function test_el_asociado_no_entra_al_panel(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_ASOCIADO))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_la_secretaria_no_ve_cartera_ni_transacciones_ni_usuarios(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        foreach (['/admin/cartera', '/admin/transacciones', '/admin/usuarios', '/admin/ajustes', '/admin/bitacora'] as $ruta) {
            $this->actingAs($secretaria)->get($ruta)->assertForbidden();
        }
    }

    public function test_la_secretaria_si_entra_a_los_recursos_de_contenido(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        foreach (['/admin/asociados', '/admin/eventos', '/admin/vacantes', '/admin/mensajes'] as $ruta) {
            $this->actingAs($secretaria)->get($ruta)->assertSuccessful();
        }
    }
}
