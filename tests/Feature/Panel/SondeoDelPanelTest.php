<?php

namespace Tests\Feature\Panel;

use App\Filament\Widgets\AsociadosPorMunicipio;
use App\Filament\Widgets\EntradasAlSitio;
use App\Filament\Widgets\Observatorio\CoberturaDeProveedores;
use App\Filament\Widgets\Observatorio\ComposicionDelSector;
use App\Filament\Widgets\Observatorio\DemandaLaboralPorArea;
use App\Filament\Widgets\Observatorio\OfertaContraDemanda;
use App\Filament\Widgets\Observatorio\PresenciaPorMunicipio;
use App\Filament\Widgets\Observatorio\SaludFinanciera;
use App\Filament\Widgets\PaginasMasVisitadas;
use App\Filament\Widgets\PorDondeEntranAlSitio;
use App\Filament\Widgets\RecaudoMensual;
use App\Filament\Widgets\ResumenDelGremio;
use App\Filament\Widgets\VisitasDelSitio;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Ninguna gráfica ni estadística del panel se consulta sola cada pocos
 * segundos.
 *
 * Filament hace que los widgets de estadísticas y de gráficas vuelvan al
 * servidor cada 5 s por defecto. Con el tablero abierto eso eran 12 a 15
 * peticiones por minuto que no cambian nada —recaudo, visitas y afiliados no
 * se mueven de un segundo a otro— y fueron casi la mitad del tráfico de
 * personas en producción. Las cifras se actualizan al entrar o al recargar.
 */
class SondeoDelPanelTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: class-string}> */
    public static function widgets(): array
    {
        return collect([
            ResumenDelGremio::class,
            RecaudoMensual::class,
            AsociadosPorMunicipio::class,
            EntradasAlSitio::class,
            VisitasDelSitio::class,
            PaginasMasVisitadas::class,
            PorDondeEntranAlSitio::class,
            PresenciaPorMunicipio::class,
            ComposicionDelSector::class,
            SaludFinanciera::class,
            CoberturaDeProveedores::class,
            DemandaLaboralPorArea::class,
            OfertaContraDemanda::class,
        ])->mapWithKeys(fn (string $clase): array => [class_basename($clase) => [$clase]])->all();
    }

    #[DataProvider('widgets')]
    public function test_el_widget_no_se_consulta_solo(string $widget): void
    {
        $this->seed(RolYPermisoSeeder::class);
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());

        Livewire::test($widget)
            ->assertOk()
            ->assertDontSeeHtml('wire:poll');
    }
}
