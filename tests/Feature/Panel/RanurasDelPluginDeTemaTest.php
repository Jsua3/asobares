<?php

namespace Tests\Feature\Panel;

use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Widgets\ChartWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Guardia sobre todas las gráficas del panel, presentes y futuras.
 *
 * Un array PHP vacío se serializa a JSON como `[]` —un array de JavaScript—
 * y Chart.js no envuelve arrays en su resolutor de opciones. La gráfica se
 * crea y pinta una vez, pero el primer `update()` posterior —cambio de tema,
 * ResizeObserver, refresco de datos— muere sin capturar con
 * «this.options.ticks.setContext is not a function» y queda congelada con
 * los colores del tema anterior.
 *
 * Se descubrió el 14 ago 2026 mirando la consola del navegador, no leyendo:
 * las pruebas que exigen la clave (`assertArrayHasKey`) pasan igual con `[]`
 * que con el objeto que Chart.js espera, porque en PHP no hay diferencia
 * entre los dos vacíos. Por eso esta guardia mira el JSON serializado, que
 * es donde la diferencia existe.
 */
class RanurasDelPluginDeTemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    public function test_ninguna_ranura_del_plugin_de_tema_llega_a_chartjs_como_array(): void
    {
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());

        $graficas = $this->graficasDelPanel();
        $this->assertNotEmpty($graficas, 'El barrido no encontró ningún ChartWidget: la guardia quedó vigilando nada.');

        $rotas = [];

        foreach ($graficas as $clase) {
            $instancia = Livewire::test($clase)->instance();
            $opciones = (new ReflectionMethod($clase, 'getOptions'))->invoke($instancia);

            if (! is_array($opciones)) {
                continue;
            }

            $json = json_encode($opciones, JSON_THROW_ON_ERROR);

            if (preg_match_all('/"(ticks|grid|border|labels)":\[\]/', $json, $coincidencias) > 0) {
                $rotas[] = $clase.' → '.implode(', ', array_unique($coincidencias[0]));
            }
        }

        $this->assertSame(
            [],
            $rotas,
            'Estas gráficas mandan una ranura del plugin de tema como `[]`: Chart.js la acepta al crear '
                ."la gráfica y revienta en el primer update(). Usa RanuraDeTema::vacia() en vez de [].\n"
                .implode("\n", $rotas)
        );
    }

    /**
     * Todos los ChartWidget concretos bajo `app/Filament/Widgets`, se
     * registren donde se registren: una gráfica nueva entra sola al barrido.
     *
     * @return list<class-string<ChartWidget>>
     */
    private function graficasDelPanel(): array
    {
        $clases = [];

        foreach (File::allFiles(app_path('Filament/Widgets')) as $archivo) {
            $ruta = str_replace('\\', '/', $archivo->getRelativePathname());
            $clase = 'App\\Filament\\Widgets\\'.str_replace(['/', '.php'], ['\\', ''], $ruta);

            if (! class_exists($clase)) {
                continue;
            }

            $reflexion = new ReflectionClass($clase);

            if ($reflexion->isSubclassOf(ChartWidget::class) && ! $reflexion->isAbstract()) {
                $clases[] = $clase;
            }
        }

        return $clases;
    }
}
