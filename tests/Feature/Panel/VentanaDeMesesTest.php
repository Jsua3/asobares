<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoTransaccion;
use App\Models\Cartera;
use App\Models\Transaccion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Restar meses desborda, y el calendario decide qué días lo destapan.
 *
 * `now()->subMonths(6)` un 30 de agosto no da el 28 de febrero: da el 2 de
 * marzo, porque PHP construye `2026-02-30` y lo deja correr hasta el mes
 * siguiente en vez de recortarlo al último día. Con un `startOfMonth()`
 * detrás, dos cubos distintos —cinco y seis meses atrás— aterrizan en el
 * MISMO mes de calendario, y el mes que se saltaron no lo cubre nadie.
 *
 * Eso rompe la coherencia entre `carteras.meses_mora` y el historial de
 * mensualidades: a quien debe cinco meses se le siembra un pago dentro de su
 * propia ventana de mora, y `/mi-cuenta` acaba diciendo «debes 5 meses» al
 * lado de un pago de ese periodo.
 *
 * La suite no lo veía porque solo ocurre los días 29, 30 y 31 —y solo cuando
 * la resta cruza febrero—, así que pasaba verde veintitantos días de cada mes
 * y se ponía roja los últimos. Por eso este caso **fija la fecha** en vez de
 * confiar en el día en que se ejecute: es la única forma de que la guardia
 * valga los 365 días.
 *
 * El arreglo es de orden, no de método: `startOfMonth()` PRIMERO y la resta
 * después. Restarle meses a un día 1 nunca desborda, porque todos los meses
 * tienen día 1.
 */
class VentanaDeMesesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fechas en las que la resta de meses cruza febrero y desborda.
     *
     * @return array<string, array{string}>
     */
    public static function diasEnQueLaRestaDesborda(): array
    {
        return [
            '30 de agosto: seis meses atrás se va a marzo' => ['2026-08-30 09:00:00'],
            '31 de marzo: un mes atrás se va a marzo' => ['2026-03-31 09:00:00'],
            '31 de mayo: tres meses atrás se va a marzo' => ['2026-05-31 09:00:00'],
            '29 de febrero bisiesto: un año atrás' => ['2028-02-29 09:00:00'],
        ];
    }

    #[DataProvider('diasEnQueLaRestaDesborda')]
    public function test_ningun_asociado_en_mora_recibe_pagos_dentro_de_su_ventana(string $hoy): void
    {
        Carbon::setTestNow(Carbon::parse($hoy));

        $this->seed(DatabaseSeeder::class);

        $enMora = Cartera::where('meses_mora', '>', 0)->get(['asociado_id', 'meses_mora']);

        $this->assertNotEmpty($enMora, 'Sin asociados en mora este caso no prueba nada.');

        foreach ($enMora as $cartera) {
            $inicioDeLaVentana = now()->startOfMonth()->subMonths($cartera->meses_mora);

            $pagos = Transaccion::query()
                ->where('estado', EstadoTransaccion::Aprobada)
                ->where('asociado_id', $cartera->asociado_id)
                ->where('created_at', '>=', $inicioDeLaVentana)
                ->count();

            $this->assertSame(
                0,
                $pagos,
                "Hoy es {$hoy}: el asociado {$cartera->asociado_id} debe {$cartera->meses_mora} meses "
                ."y no puede tener pagos desde {$inicioDeLaVentana->toDateString()}."
            );
        }
    }

    #[DataProvider('diasEnQueLaRestaDesborda')]
    public function test_los_diecinueve_cubos_del_historial_caen_en_meses_distintos(string $hoy): void
    {
        Carbon::setTestNow(Carbon::parse($hoy));

        $meses = collect(range(18, 0))
            ->map(fn (int $mesesAtras): string => now()->startOfMonth()->subMonths($mesesAtras)->format('Y-m'));

        $this->assertCount(
            19,
            $meses->unique(),
            'Dos cubos del historial cayeron en el mismo mes: el recaudo mensual se dibujaría con un hueco '
            .'y un mes de doble tamaño.'
        );
    }

    /**
     * El plazo de retención es un contrato con el titular del dato: si la
     * política dice doce meses, borrar a los once meses y veintiocho días es
     * incumplirlo por el lado que nadie reclama pero que igual es incorrecto.
     */
    #[DataProvider('diasEnQueLaRestaDesborda')]
    public function test_el_limite_de_retencion_respeta_el_plazo_completo(string $hoy): void
    {
        Carbon::setTestNow(Carbon::parse($hoy));

        foreach ([1, 3, 6, 12, 18, 24] as $meses) {
            $limite = now()->subMonthsNoOverflow($meses);

            $this->assertSame(
                $meses,
                (int) $limite->diffInMonths(now()),
                "Un plazo de {$meses} meses tiene que medir {$meses} meses exactos desde {$hoy}."
            );
        }
    }
}
