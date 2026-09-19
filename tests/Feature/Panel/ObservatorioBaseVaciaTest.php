<?php

namespace Tests\Feature\Panel;

use App\Filament\Pages\InformeDelObservatorio;
use App\Filament\Pages\Observatorio;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El día 1 en producción: base recién migrada, sin un solo asociado, vacante,
 * consulta, proveedor ni transacción. Ninguna de las seis gráficas tiene
 * dato que dibujar, y `ChartWidget::isEmpty()` de fábrica no ve vacío un
 * arreglo `datasets` con la forma correcta aunque todos sus valores lo estén:
 * por sí solo dejaría un `<canvas>` en blanco, sin texto.
 *
 * Por eso `GraficaDelObservatorio::isEmpty()` se apoya en
 * `hayMuestraSuficiente()`, que ya es falso con n = 0, y
 * `sin-muestra.blade.php` separa por `estaVacia()` para decir «Todavía no
 * hay datos que mostrar», igual que el informe impreso, en vez de un «n = 0»
 * frente a un mínimo de 30.
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

    /**
     * Sin una sola cartera ni un solo pago, la banda de cifras decía «Tasa de
     * mora actual: 0,0 %» y «Recaudo: $0»: la primera se lee como «nadie
     * debe» y la segunda como «el gremio no recaudó nada», cuando lo que no
     * hay es información. Es lo que pasa hoy en producción. Los conteos sí
     * son hechos —cero proveedores es verdad— y se quedan en cero.
     */
    public function test_la_banda_de_cifras_no_inventa_una_mora_ni_un_recaudo_sin_datos(): void
    {
        $this->actingAs($this->direccion());

        $this->get(Observatorio::getUrl())
            ->assertOk()
            ->assertDontSee('0,0 %')
            ->assertDontSee('$0')
            ->assertSee('Sin datos');

        $this->get(InformeDelObservatorio::getUrl())
            ->assertOk()
            ->assertDontSee('0,0 %')
            ->assertDontSee('$0')
            ->assertSee('Sin datos');
    }

    /** El control: con carteras cargadas, la tasa se calcula y se muestra. */
    public function test_con_carteras_cargadas_la_mora_si_se_muestra(): void
    {
        $this->actingAs($this->direccion());
        $alDia = Asociado::factory()->create();
        $enMora = Asociado::factory()->create();
        Cartera::query()->create(['asociado_id' => $alDia->id, 'meses_mora' => 0, 'saldo_pendiente' => 0]);
        Cartera::query()->create(['asociado_id' => $enMora->id, 'meses_mora' => 2, 'saldo_pendiente' => 140000]);

        $this->get(Observatorio::getUrl())->assertOk()->assertSee('50,0 %');
        $this->get(InformeDelObservatorio::getUrl())->assertOk()->assertSee('50,0 %');
    }

    /**
     * El informe impreso es la superficie del módulo que se lleva a una
     * alcaldía, así que también se ejercita con la base recién migrada: tiene
     * que distinguir base vacía de muestra insuficiente igual que las gráficas.
     */
    public function test_el_informe_impreso_distingue_la_base_vacia_de_la_muestra_insuficiente(): void
    {
        $this->actingAs($this->direccion());

        $this->get(InformeDelObservatorio::getUrl())
            ->assertOk()
            ->assertSee('Todavía no hay datos que mostrar.')
            ->assertDontSee('todavía no alcanza muestra suficiente')
            // Con la base vacía no hay ninguna cifra que sostener, así que
            // tampoco puede aparecer el cierre que celebra lo contrario.
            ->assertDontSee('Hoy todos los indicadores de este informe alcanzan muestra suficiente.');
    }
}
