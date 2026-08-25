<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\RequisitoAperturas\Pages\EditRequisitoApertura;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La oficina necesita saber qué trámites lleva sin revisar. La fecha sola no
 * basta: hace falta poder listar la pila de trabajo.
 */
class VigenciaEnElPanelTest extends TestCase
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

    public function test_la_direccion_registra_la_verificacion_desde_el_formulario(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $requisito = RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => Municipio::factory(),
        ]);

        Livewire::test(EditRequisitoApertura::class, ['record' => $requisito->getRouteKey()])
            ->fillForm([
                'verificado_el' => '2026-08-20',
                'verificado_con' => 'Documento oficial de la Alcaldía de Armenia',
                'vigente_hasta' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $requisito->refresh();

        $this->assertSame('2026-08-20', $requisito->verificado_el->toDateString());
        $this->assertSame('Documento oficial de la Alcaldía de Armenia', $requisito->verificado_con);
        $this->assertNull($requisito->vigente_hasta, 'Vacío significa permanente.');
    }

    public function test_la_tabla_muestra_el_nombre_del_municipio_y_no_su_numero(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create(['nombre' => 'Circasia']);
        RequisitoApertura::factory()->publicado()->create(['municipio_id' => $municipio->id]);

        // El recurso tiene slug propio: `/admin/requisitos`, no el que se
        // deduciría del nombre de la clase. Se usa la ruta con nombre.
        $this->get(route('filament.admin.resources.requisitos.index'))
            ->assertSuccessful()
            ->assertSee('Circasia');
    }
}
