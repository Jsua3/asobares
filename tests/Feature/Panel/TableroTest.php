<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicacion;
use App\Filament\Widgets\PendientesDeAprobacion;
use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El tablero deja de ser un marcador y pasa a ser una cola de trabajo.
 */
class TableroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_banda_de_accion_lista_lo_pendiente_con_enlace(): void
    {
        Asociado::factory()->count(3)->create([
            'estado' => EstadoPublicacion::PendienteAprobacion,
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(PendientesDeAprobacion::class)
            ->assertSee('3 asociados esperando tu aprobación')
            ->assertSee('Revisar');
    }

    public function test_la_banda_se_esconde_cuando_no_hay_nada_pendiente(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::Publicado]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->assertFalse(PendientesDeAprobacion::canView());
    }

    /** Un tablero con una cola vacía enseña que el tablero no sirve. */
    public function test_la_banda_se_muestra_cuando_si_hay_pendientes(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->assertTrue(PendientesDeAprobacion::canView());
    }
}
