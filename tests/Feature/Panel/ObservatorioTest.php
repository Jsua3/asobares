<?php

namespace Tests\Feature\Panel;

use App\Filament\Pages\Observatorio;
use App\Filament\Widgets\Observatorio\ComposicionDelSector;
use App\Filament\Widgets\Observatorio\PresenciaPorMunicipio;
use App\Filament\Widgets\Observatorio\SaludFinanciera;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El observatorio es el argumento que la dirección lleva a una alcaldía.
 */
class ObservatorioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_direccion_entra_al_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get(Observatorio::getUrl())
            ->assertOk()
            ->assertSee('Observatorio del gremio');
    }

    /**
     * La frontera negativa, que es la que prueba algo: el observatorio lleva
     * salud financiera, y la secretaría no ve dinero en ninguna otra pantalla.
     */
    public function test_la_secretaria_no_entra_al_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->assertFalse(Observatorio::canAccess());
        $this->get(Observatorio::getUrl())->assertForbidden();
    }

    public function test_la_banda_de_kpis_usa_el_componente_del_panel_y_rotula_su_n(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $respuesta = $this->get(Observatorio::getUrl());

        $respuesta->assertOk()
            // El componente KPI rinde este enlace cuando recibe `url`.
            ->assertSee('Ver detalle')
            // Y el principio #5 del spec: ninguna cifra sin su n.
            ->assertSee('n = ', false);
    }

    public function test_las_tres_visualizaciones_solidas_dibujan_y_rotulan_su_muestra(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        foreach ([
            PresenciaPorMunicipio::class,
            ComposicionDelSector::class,
            SaludFinanciera::class,
        ] as $widget) {
            Livewire::test($widget)->assertSee('n = ');
        }
    }

    public function test_los_widgets_del_observatorio_dejan_hueco_al_plugin_de_tema(): void
    {
        foreach (File::allFiles(app_path('Filament/Widgets/Observatorio')) as $archivo) {
            $contenido = $archivo->getContents();

            $this->assertStringContainsString(
                "'ticks'",
                $contenido,
                $archivo->getFilename().' debe declarar ticks aunque venga vacio: el plugin de tema solo escribe donde ya hay clave.'
            );
            $this->assertStringContainsString("'grid'", $contenido, $archivo->getFilename().' debe declarar grid.');
        }
    }
}
