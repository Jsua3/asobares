<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Filament\Pages\InformeDelObservatorio;
use App\Filament\Pages\Observatorio;
use App\Filament\Resources\Carteras\Pages\ListCarteras;
use App\Filament\Resources\Eventos\Pages\ListEventos;
use App\Filament\Resources\Publicidades\Pages\ListPublicidades;
use App\Filament\Resources\Transaccions\Pages\ListTransaccions;
use App\Filament\Widgets\ResumenDelGremio;
use App\Filament\Widgets\UltimasTransacciones;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Evento;
use App\Models\Publicidad;
use App\Models\Transaccion;
use App\Models\User;
use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fija, byte a byte, cómo sale un monto en pesos en cada sitio que lo pinta.
 *
 * El formato es el colombiano de siempre: signo pesos pegado, punto de miles,
 * sin decimales y redondeo al peso (`$1.250.000`, `$-1.500.000`). Lo pintan las
 * vistas públicas, las tablas y el tablero del panel, el observatorio y su
 * informe impreso; si un sitio se aparta, la misma cifra se lee distinta según
 * la pantalla, y ninguna otra prueba compara los sitios entre sí.
 */
class FormatoDePesosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Los montos que admite una columna `decimal:2`, con la salida esperada.
     *
     * @return array<string, array{0: int|float|string, 1: string}>
     */
    private static function montosDeLaBase(): array
    {
        return [
            'cero' => [0, '$0'],
            'menos de mil' => [999, '$999'],
            'mil' => [1000, '$1.000'],
            'millones' => [1250000, '$1.250.000'],
            'decenas de millones con decimales' => [12345678.9, '$12.345.679'],
            'medio peso redondea hacia arriba' => [1234.5, '$1.235'],
            'decimales por debajo del medio' => [1234.49, '$1.234'],
            'negativo' => [-1500000, '$-1.500.000'],
            'cadena decimal como la entrega el cast' => ['2104124.00', '$2.104.124'],
        ];
    }

    /** @return array<string, array{0: int|float|string|null, 1: string}> */
    public static function montosDelAyudante(): array
    {
        return self::montosDeLaBase() + [
            'nulo' => [null, '$0'],
            'cadena vacía' => ['', '$0'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolYPermisoSeeder::class);
        $this->travelTo(Carbon::create(2026, 3, 15, 12, 0, 0));

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($usuario->fresh());
    }

    #[DataProvider('montosDelAyudante')]
    public function test_el_ayudante_de_las_vistas(int|float|string|null $monto, string $esperado): void
    {
        $this->assertSame($esperado, pesos($monto));
    }

    public function test_la_tabla_de_cartera_formatea_el_saldo_y_su_total(): void
    {
        $carteras = [];
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            $carteras[$caso] = [$this->crearCartera($monto), $esperado];
        }

        $tabla = Livewire::test(ListCarteras::class);

        foreach ($carteras as $caso => [$cartera, $esperado]) {
            $this->assertSame($esperado, $this->estadoFormateado($tabla, 'saldo_pendiente', $cartera), $caso);
        }

        $this->assertSame('$14.204.271', $this->totalFormateado($tabla, 'saldo_pendiente'));
    }

    public function test_la_tabla_de_transacciones_formatea_el_monto_y_su_total(): void
    {
        $transacciones = [];
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            $transacciones[$caso] = [$this->crearTransaccion($monto), $esperado];
        }

        $tabla = Livewire::test(ListTransaccions::class);

        foreach ($transacciones as $caso => [$transaccion, $esperado]) {
            $this->assertSame($esperado, $this->estadoFormateado($tabla, 'monto', $transaccion), $caso);
        }

        $this->assertSame('$14.204.271', $this->totalFormateado($tabla, 'monto'));
    }

    /** El widget solo lista las cinco últimas: cada caso se mira solo. */
    public function test_las_ultimas_transacciones_del_tablero_formatean_el_monto(): void
    {
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            Transaccion::query()->delete();
            $transaccion = $this->crearTransaccion($monto);

            $widget = Livewire::test(UltimasTransacciones::class);

            $this->assertSame($esperado, $this->estadoFormateado($widget, 'monto', $transaccion), $caso);
        }
    }

    public function test_la_tabla_de_eventos_formatea_el_precio_y_llama_gratuito_al_cero(): void
    {
        $eventos = [];
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            $eventos[$caso] = [
                Evento::factory()->create(['precio' => $monto]),
                $caso === 'cero' ? 'Gratuito' : $esperado,
            ];
        }

        $tabla = Livewire::test(ListEventos::class);

        foreach ($eventos as $caso => [$evento, $esperado]) {
            $this->assertSame($esperado, $this->estadoFormateado($tabla, 'precio', $evento), $caso);
        }
    }

    /** `Publicidad` rechaza un valor negativo al guardar: ese caso no llega a la tabla. */
    public function test_la_tabla_de_publicidad_formatea_el_valor(): void
    {
        $pautas = [];
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            if ((float) $monto < 0) {
                continue;
            }

            $pautas[$caso] = [Publicidad::factory()->create(['valor' => $monto]), $esperado];
        }

        $tabla = Livewire::test(ListPublicidades::class);

        foreach ($pautas as $caso => [$pauta, $esperado]) {
            $this->assertSame($esperado, $this->estadoFormateado($tabla, 'valor', $pauta), $caso);
        }
    }

    public function test_el_resumen_del_gremio_formatea_el_recaudo_y_el_saldo_en_mora(): void
    {
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            Transaccion::query()->delete();
            Cartera::query()->delete();

            $this->crearTransaccion($monto);
            $this->crearCartera($monto);

            /** @var list<Stat> $tarjetas */
            $tarjetas = (fn (): array => $this->getStats())->call(new ResumenDelGremio);

            $this->assertSame($esperado, $tarjetas[0]->getValue(), "{$caso}: recaudado este mes");
            $this->assertSame($esperado.' por recaudar', $tarjetas[1]->getDescription(), "{$caso}: cartera en mora");
        }
    }

    public function test_el_observatorio_y_su_informe_formatean_el_recaudo(): void
    {
        foreach (self::montosDeLaBase() as $caso => [$monto, $esperado]) {
            $this->app->instance(MetricasDelObservatorio::class, $this->metricasConRecaudo((float) $monto));

            $this->assertSame($esperado, (new Observatorio)->recaudoDelPeriodo(), "{$caso}: banda del observatorio");

            $informe = new InformeDelObservatorio;
            $cabecera = collect($informe->indicadores())->firstWhere('etiqueta', 'Recaudo (18 meses)');

            $this->assertSame($esperado, $cabecera['valor'], "{$caso}: cabecera del informe");
            $this->assertSame($esperado, $informe->formatearCelda('Recaudo (COP)', (float) $monto), "{$caso}: celda del informe");
        }
    }

    private function crearCartera(int|float|string $saldo): Cartera
    {
        return Cartera::create([
            'asociado_id' => Asociado::factory()->create()->id,
            'saldo_pendiente' => $saldo,
            'meses_mora' => 1,
            'actualizado_at' => now(),
        ]);
    }

    private function crearTransaccion(int|float|string $monto): Transaccion
    {
        return Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'monto' => $monto,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Aprobada,
            'metodo' => MetodoPago::Pse,
            'payload' => ['origen' => 'prueba'],
            'created_at' => now()->subHour(),
        ]);
    }

    private function estadoFormateado(Testable $componente, string $columna, Cartera|Transaccion|Evento|Publicidad $registro): mixed
    {
        $componente->assertTableColumnExists($columna);

        $fila = $componente->instance()->getTableRecord((string) $registro->getKey());
        $this->assertNotNull($fila, "La fila {$registro->getKey()} no está en la tabla: el estado saldría nulo y no probaría nada.");

        $celda = $componente->instance()->getTable()->getColumn($columna);
        $celda->record($fila);
        $celda->clearCachedState();

        return $celda->formatState($celda->getState());
    }

    private function totalFormateado(Testable $componente, string $columna): mixed
    {
        $resumen = $componente->instance()->getTable()->getColumn($columna)->getSummarizer('0');
        $resumen->query($componente->instance()->getAllTableSummaryQuery())->selectedState([]);

        return $resumen->formatState($resumen->getState());
    }

    /** Un observatorio cuyo único dato es el recaudo: el resto de cifras no interviene. */
    private function metricasConRecaudo(float $recaudo): MetricasDelObservatorio
    {
        $serie = fn (string $nombre, array $valores): SerieDelObservatorio => new SerieDelObservatorio(
            etiquetas: ['Ene'],
            series: [$nombre => $valores],
            n: 1,
            unidad: 'registros',
        );

        return new class($serie('Recaudo (COP)', [$recaudo]), $serie('Asociados', [0]), $serie('Proveedores', [0]), $serie('Tasa de mora (%)', [0])) extends MetricasDelObservatorio
        {
            public function __construct(
                private SerieDelObservatorio $salud,
                private SerieDelObservatorio $composicion,
                private SerieDelObservatorio $proveedores,
                private SerieDelObservatorio $mora,
            ) {}

            public function saludFinanciera(): SerieDelObservatorio
            {
                return $this->salud;
            }

            public function composicionDelSector(): SerieDelObservatorio
            {
                return $this->composicion;
            }

            public function coberturaDeProveedores(): SerieDelObservatorio
            {
                return $this->proveedores;
            }

            public function tasaDeMoraActual(): SerieDelObservatorio
            {
                return $this->mora;
            }
        };
    }
}
