<?php

namespace Tests\Feature\Panel;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoPublicacion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Filament\Widgets\InscripcionesDelMes;
use App\Filament\Widgets\PendientesDeAprobacion;
use App\Filament\Widgets\RecaudoMensual;
use App\Filament\Widgets\ResumenDelGremio;
use App\Models\Asociado;
use App\Models\Municipio;
use App\Models\Transaccion;
use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    /**
     * Filament monta el widget llamando primero a `canView()` y luego
     * renderizándolo. Si `ColaDePendientes` no está registrado como
     * singleton, cada llamada resuelve una instancia nueva y su memoización
     * interna no sirve de nada: el cálculo completo (una consulta COUNT y
     * una MIN contra `asociados`) se repite dos veces en vez de una.
     */
    public function test_canview_y_el_render_comparten_la_memoizacion_del_singleton(): void
    {
        Asociado::factory()->create(['estado' => EstadoPublicacion::PendienteAprobacion]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $consultasAAsociados = 0;
        DB::listen(function ($consulta) use (&$consultasAAsociados): void {
            if (str_contains($consulta->sql, 'asociados')) {
                $consultasAAsociados++;
            }
        });

        PendientesDeAprobacion::canView();
        Livewire::test(PendientesDeAprobacion::class);

        $this->assertSame(
            2,
            $consultasAAsociados,
            'canView() y el render deben compartir la misma instancia memoizada: un solo calculo, dos consultas (COUNT y MIN) contra asociados.'
        );
    }

    public function test_la_direccion_ve_cuatro_tarjetas_con_la_plata(): void
    {
        Municipio::factory()->count(3)->create();

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('Recaudado este mes')
            ->assertSee('Cartera en mora')
            ->assertSee('Cobertura territorial');
    }

    /** La secretaría no ve plata: no tiene `ver_transaccion` ni `ver_cartera`. */
    public function test_la_secretaria_ve_su_propio_juego_de_tarjetas(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('Pendientes de moderación')
            ->assertSee('Bandeja sin responder')
            ->assertDontSee('Recaudado este mes')
            ->assertDontSee('Cartera en mora');
    }

    public function test_el_resumen_muestra_exactamente_cuatro_tarjetas(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $widget = new ResumenDelGremio;
        $stats = (fn (): array => $this->getStats())->call($widget);

        $this->assertCount(4, $stats, 'Cuatro tarjetas, no seis: seis es un marcador, no un tablero.');
    }

    public function test_el_recaudo_mensual_reemplazo_a_las_inscripciones_de_30_dias(): void
    {
        $this->assertFalse(
            class_exists(InscripcionesDelMes::class),
            'La gráfica de 30 días era una línea plana en cero con eventos mensuales.'
        );
        $this->assertTrue(class_exists(RecaudoMensual::class));
    }

    /**
     * El widget anterior traía todos los modelos a memoria para agruparlos con
     * `groupBy` de Collection. Se mide el comportamiento —cuántas consultas
     * salen— y no cómo está escrito el archivo: una aserción sobre el texto
     * fuente se rompe con cualquier `->get()` legítimo en otro método.
     *
     * ⚠️ No hay `TransaccionFactory` en el proyecto: las transacciones se
     * crean a mano con la misma forma que usa `TransaccionSeeder`.
     */
    public function test_el_recaudo_mensual_agrega_en_una_sola_consulta(): void
    {
        foreach (range(1, (int) now()->format('n')) as $mes) {
            Transaccion::create([
                'referencia' => Transaccion::generarReferencia(),
                'concepto' => ConceptoTransaccion::Mensualidad,
                'monto' => 50000,
                'moneda' => 'COP',
                'estado' => EstadoTransaccion::Aprobada,
                'metodo' => MetodoPago::Pse,
                'payload' => ['origen' => 'prueba'],
                'created_at' => now()->startOfYear()->addMonths($mes - 1)->addDay(),
            ]);
        }

        $consultas = [];
        DB::listen(function ($evento) use (&$consultas): void {
            $consultas[] = $evento->sql;
        });

        $widget = new RecaudoMensual;
        $datos = (fn (): array => $this->getData())->call($widget);

        $this->assertCount(
            1,
            $consultas,
            'La serie se agrega en SQL: una consulta, no una por mes ni una por fila.'
        );
        $this->assertStringContainsStringIgnoringCase('sum(monto)', $consultas[0]);
        $this->assertCount((int) now()->format('n'), $datos['labels']);
    }

    /**
     * El delta de «Recaudado este mes» comparaba el mes en curso incompleto
     * contra el mes anterior COMPLETO: al principio de mes eso siempre pinta
     * mal (el día 6, $150.000 contra $930.000 del mes pasado entero sería un
     * −84 % que no dice nada). Se siembra marzo cortado al día 15 y febrero
     * con la mitad del dinero antes del día 15 y la otra mitad después, de
     * forma que la ventana equivalente y el mes completo den signos
     * contrarios: si el widget usara el mes anterior entero, o si arrastrara
     * un pago futuro dentro del mes en curso, el resultado sería negativo.
     */
    public function test_el_delta_de_recaudo_compara_el_mismo_tramo_de_dias_no_el_mes_completo(): void
    {
        $this->travelTo(Carbon::create(2026, 3, 15, 12, 0, 0));

        // Marzo, dentro de la ventana comparable (1 al 15): sí cuenta.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 10), 200_000);
        // Marzo, después de «hoy»: un pago que aún no ha ocurrido no puede
        // contar en el recaudo del mes en curso.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 3, 20), 500_000);

        // Febrero, dentro de la ventana equivalente (1 al 15): sí cuenta.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 2, 10), 100_000);
        // Febrero, después del día 15: solo entraría si el cálculo comparara
        // contra el mes anterior completo en vez de la ventana equivalente.
        $this->sembrarTransaccionAprobada(Carbon::create(2026, 2, 20), 900_000);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        // $200.000 (marzo 1-15) vs. $100.000 (febrero 1-15) = +100,0 %. Con
        // el mes anterior completo ($1.000.000) o el pago futuro de marzo
        // sumado ($700.000) el delta habría salido negativo.
        Livewire::test(ResumenDelGremio::class)
            ->assertSee('$200.000')
            ->assertSee('+100,0 % vs. mismo tramo del mes anterior')
            ->assertDontSee('700.000')
            ->assertDontSee('1.000.000');
    }

    /**
     * `created_at` es cuándo Eloquent insertó la fila, no cuándo el
     * establecimiento se afilió al gremio. Este asociado se inserta HOY pero
     * se afilió hace seis meses: con la implementación vieja, que leía
     * `created_at`, esta prueba habría fallado, porque hoy es exactamente
     * cuando se creó la fila.
     */
    public function test_las_altas_del_mes_cuentan_por_fecha_de_afiliacion_no_por_insercion(): void
    {
        Asociado::factory()->publicado()->create([
            'fecha_afiliacion' => now()->subMonths(6)->toDateString(),
            'created_at' => now(),
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ResumenDelGremio::class)
            ->assertSee('0 altas este mes')
            ->assertDontSee('1 altas este mes');
    }

    private function sembrarTransaccionAprobada(Carbon $fecha, float $monto): void
    {
        Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'monto' => $monto,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Aprobada,
            'metodo' => MetodoPago::Pse,
            'payload' => ['origen' => 'prueba'],
            'created_at' => $fecha,
        ]);
    }

    public function test_el_tablero_es_una_pagina_propia_con_titulo_del_gremio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Tablero del gremio');
    }

    public function test_el_tablero_de_fabrica_ya_no_esta_registrado(): void
    {
        $panel = (new AdminPanelProvider($this->app))
            ->panel(Panel::make());

        $paginas = $panel->getPages();

        $this->assertNotContains(Dashboard::class, $paginas);
    }

    public function test_la_secretaria_tambien_entra_al_tablero(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->get('/admin')->assertOk()->assertSee('Tablero del gremio');
    }

    /**
     * El tablero ya no declara `getColumns()`: los cinco widgets son
     * `columnSpan = 'full'`, así que ninguno depende de cuántas columnas
     * tenga la rejilla de la página. Sin esto, cualquier widget que se
     * registre sin declarar ancho hereda el `1` de `Widget` y, bajo
     * cualquier rejilla de más de una columna, queda apretado en una
     * fracción de la fila — es justo lo que le pasó a
     * `AsociadosPorMunicipio` (una gráfica de doce municipios en un cuarto
     * de ancho, con el resto de la fila vacío) cuando la página tenía una
     * rejilla de 4 columnas y este widget no declaraba `columnSpan`.
     */
    public function test_ningun_widget_del_tablero_queda_en_una_fraccion_de_la_fila(): void
    {
        $panel = (new AdminPanelProvider($this->app))->panel(Panel::make());

        foreach ($panel->getWidgets() as $claseWidget) {
            $widget = new $claseWidget;
            $columnSpan = (fn () => $this->columnSpan)->call($widget);

            $this->assertSame(
                'full',
                $columnSpan,
                "{$claseWidget} no declara columnSpan = 'full', asi que hereda el 1 de Widget y puede quedar apretado en una fraccion de la fila."
            );
        }
    }
}
