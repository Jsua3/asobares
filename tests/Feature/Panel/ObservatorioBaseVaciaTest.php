<?php

namespace Tests\Feature\Panel;

use App\Filament\Pages\Observatorio;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El día 1 en producción: base recién migrada, sin un solo asociado, vacante,
 * consulta, proveedor ni transacción. Ninguna de las seis gráficas tiene
 * dato que dibujar, y `estaVacia()` —que ya distinguía este caso del de
 * «hay datos pero no alcanzan muestra» en el informe impreso— no se invocaba
 * desde ninguna pantalla: las tres gráficas «sólidas» de entonces rendían un
 * `<canvas>` en blanco, sin texto, porque `ChartWidget::isEmpty()` de fábrica
 * no detectaba nada raro en un arreglo `datasets` con la forma correcta
 * aunque sus valores fueran todos vacíos.
 *
 * Con `GraficaDelObservatorio::isEmpty()` basado en `hayMuestraSuficiente()`
 * (que ya es falso con n = 0) y `sin-muestra.blade.php` ramificando por
 * `estaVacia()`, las seis gráficas caen en el mismo estado vacío legible que
 * ya usaba el informe: «Todavía no hay datos que mostrar», no «hoy hay n = 0
 * y hacen falta 30».
 */
class ObservatorioBaseVaciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Solo roles y permisos: ni un asociado, vacante, consulta,
        // proveedor ni transacción. Es la base tal como queda después de
        // `migrate:fresh`, antes de sembrar ningún contenido.
        $this->seed(RolYPermisoSeeder::class);
    }

    private function direccion(): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        return $usuario->fresh();
    }

    public function test_el_observatorio_carga_sin_reventar_con_la_base_vacia(): void
    {
        $this->actingAs($this->direccion());

        $this->get(Observatorio::getUrl())
            ->assertOk()
            ->assertSee('Observatorio del gremio');
    }

    /**
     * Deriva la lista de gráficas de `Observatorio::getFooterWidgets()`
     * —igual que la prueba del umbral en vivo— en vez de escribirla a mano:
     * una gráfica nueva del observatorio queda cubierta sola.
     */
    public function test_las_seis_graficas_enseñan_texto_legible_en_vez_de_un_lienzo_en_blanco(): void
    {
        $this->actingAs($this->direccion());

        $paginaObservatorio = Livewire::test(Observatorio::class)->instance();
        $widgets = (new \ReflectionMethod(Observatorio::class, 'getFooterWidgets'))->invoke($paginaObservatorio);

        $this->assertNotEmpty($widgets);

        foreach ($widgets as $claseWidget) {
            $prueba = Livewire::test($claseWidget)->assertOk();

            $serie = (new \ReflectionMethod($claseWidget, 'serie'))->invoke($prueba->instance());
            $this->assertTrue(
                $serie->estaVacia(),
                "{$claseWidget}: con la base vacía, su serie debería reconocerse vacía (n = 0)."
            );

            $prueba
                ->assertSeeHtml('style="display: none"')
                ->assertSee('Todavía no hay datos que mostrar')
                ->assertDontSee('Aún sin muestra suficiente');
        }
    }
}
