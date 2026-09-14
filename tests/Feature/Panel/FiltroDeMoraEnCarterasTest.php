<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Carteras\Pages\ListCarteras;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * «Solo en mora» es la lista de cobro de la oficina: si deja pasar a quien
 * está al día, se llama a quien no debe nada; si no filtra, la lista no sirve.
 */
class FiltroDeMoraEnCarterasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function cartera(int $mesesMora, string $saldo): Cartera
    {
        return Cartera::create([
            'asociado_id' => Asociado::factory()->create()->id,
            'saldo_pendiente' => $saldo,
            'meses_mora' => $mesesMora,
            'actualizado_at' => now(),
        ]);
    }

    /**
     * Rotura: que la consulta del filtro devuelva `$query` sin `enMora()`, o
     * que `Cartera::scopeEnMora()` compare con `>=`.
     */
    public function test_solo_en_mora_lista_a_quien_debe_meses_y_deja_fuera_a_quien_esta_al_dia(): void
    {
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $enMora = $this->cartera(3, '150000');
        $alDia = $this->cartera(0, '0');

        Livewire::actingAs($direccion->fresh())
            ->test(ListCarteras::class)
            ->assertCanSeeTableRecords([$enMora, $alDia])
            ->filterTable('en_mora')
            ->assertCanSeeTableRecords([$enMora])
            ->assertCanNotSeeTableRecords([$alDia]);
    }
}
