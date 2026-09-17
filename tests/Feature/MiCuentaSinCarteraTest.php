<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Que no haya fila de cartera significa que nadie la ha cargado, no que el
 * afiliado esté al día: en producción no hay ni una cartera, y decirle «Estás
 * al día» a quien debe seis meses es afirmar algo falso con el nombre del
 * gremio encima.
 */
class MiCuentaSinCarteraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function duenio(Asociado $asociado): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        return $usuario->fresh();
    }

    public function test_sin_cartera_no_dice_que_esta_al_dia_ni_ofrece_pagar(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenio($asociado))
            ->get(route('mi-cuenta.index'))
            ->assertOk()
            ->assertSeeText('Tu estado de cuenta todavía no está cargado')
            ->assertDontSeeText('Estás al día')
            ->assertDontSee('Pagar ahora');
    }

    public function test_con_cartera_al_dia_sigue_diciendo_que_esta_al_dia(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 0,
            'meses_mora' => 0,
            'actualizado_at' => now(),
        ]);

        $this->actingAs($this->duenio($asociado))
            ->get(route('mi-cuenta.index'))
            ->assertOk()
            ->assertSeeText('Estás al día')
            ->assertDontSeeText('Tu estado de cuenta todavía no está cargado');
    }

    public function test_pagar_sin_cartera_no_responde_que_esta_al_dia(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenio($asociado))
            ->post(route('mi-cuenta.pagar'))
            ->assertRedirect(route('mi-cuenta.index'))
            ->assertSessionHas('aviso')
            ->assertSessionMissing('exito');
    }
}
