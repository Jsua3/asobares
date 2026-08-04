<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisosDeBolsaTest extends TestCase
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

    public function test_la_secretaria_publica_las_tres_bolsas(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        foreach (['publicar_vacante', 'publicar_artista', 'publicar_proveedor'] as $permiso) {
            $this->assertTrue($secretaria->can($permiso), "La secretaría debe poder {$permiso}.");
        }
    }

    public function test_la_secretaria_sigue_sin_publicar_el_contenido_que_ella_redacta(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        foreach (['publicar_noticia', 'publicar_evento', 'publicar_asociado', 'publicar_iniciativa'] as $permiso) {
            $this->assertFalse($secretaria->can($permiso), "Nadie aprueba lo que él mismo redacta: {$permiso}.");
        }
    }

    public function test_la_bandeja_de_postulaciones_existe_para_el_equipo(): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);

        $this->assertTrue($secretaria->can('ver_postulacion'));
        $this->assertTrue($secretaria->can('editar_postulacion'));
        $this->assertFalse($secretaria->can('eliminar_postulacion'), 'Eliminar sigue siendo de la dirección.');
        $this->assertTrue($this->crearUsuario(User::ROL_SUPER_ADMIN)->can('eliminar_postulacion'));
    }

    public function test_el_asociado_sigue_sin_permisos_de_panel(): void
    {
        $asociado = $this->crearUsuario(User::ROL_ASOCIADO);

        $this->assertFalse($asociado->can('publicar_vacante'));
        $this->assertFalse($asociado->can('ver_postulacion'));
    }
}
