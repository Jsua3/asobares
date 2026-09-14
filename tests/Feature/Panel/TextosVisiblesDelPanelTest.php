<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Artistas\Pages\CreateArtista;
use App\Filament\Resources\RequisitoAperturas\Pages\CreateRequisitoApertura;
use App\Filament\Resources\RequisitoAperturas\Pages\ListRequisitoAperturas;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Lo que lee la oficina en el panel dice la regla, no de qué reunión salió:
 * un código de observación o la fecha de una revisión no le dicen nada a
 * quien llena el formulario.
 */
class TextosVisiblesDelPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());
    }

    public function test_la_tarifa_del_artista_dice_la_regla_sin_codigo_de_observacion(): void
    {
        Livewire::test(CreateArtista::class)
            ->assertDontSee('OBS3-08')
            ->assertSee('Uso interno del gremio: NO se publica en la ficha del artista, donde siempre se lee «a convenir».');
    }

    public function test_el_enlace_del_tramite_pide_la_ruta_puntual_sin_citar_la_revision(): void
    {
        Livewire::test(CreateRequisitoApertura::class)
            ->assertDontSee('28 de agosto')
            ->assertSee('Pega el enlace del TRÁMITE, no la portada de la entidad. Un dominio pelado deja al usuario donde estaba.');
    }

    public function test_el_aviso_de_enlace_a_portada_dice_que_falta_sin_citar_la_revision(): void
    {
        $aPortada = RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => Municipio::factory(),
            'enlace_externo' => 'https://camaraarmenia.org.co',
        ]);

        $leido = null;

        Livewire::test(ListRequisitoAperturas::class)
            ->assertTableColumnExists('enlace_puntual', function (TextColumn $columna) use (&$leido): bool {
                $leido = $columna->getTooltip();

                return true;
            }, $aPortada);

        $this->assertSame('El enlace abre la portada de la entidad; debería abrir el trámite exacto.', $leido);
    }
}
