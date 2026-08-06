<?php

namespace Database\Seeders;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Evento;
use App\Models\Transaccion;
use Illuminate\Database\Seeder;

class TransaccionSeeder extends Seeder
{
    public function run(): void
    {
        if (Transaccion::count() > 0) {
            return;
        }

        $eventoPago = Evento::where('precio', '>', 0)->first();
        $inscripciones = $eventoPago?->inscripciones()->get() ?? collect();

        // --- Inscripciones a evento pago ---
        if ($inscripciones->isNotEmpty()) {
            $aprobada = $this->crear(ConceptoTransaccion::Evento, 30000, EstadoTransaccion::Aprobada, MetodoPago::Pse, [
                'inscripcion_id' => $inscripciones->first()->id,
            ]);
            $inscripciones->first()->update([
                'transaccion_id' => $aprobada->id,
                'estado' => EstadoInscripcion::Confirmada,
            ]);

            if ($inscripciones->count() > 1) {
                $rechazada = $this->crear(ConceptoTransaccion::Evento, 30000, EstadoTransaccion::Rechazada, MetodoPago::Tarjeta, [
                    'inscripcion_id' => $inscripciones->get(1)->id,
                ]);
                $inscripciones->get(1)->update([
                    'transaccion_id' => $rechazada->id,
                    'estado' => EstadoInscripcion::Registrada,
                ]);
            }
        }

        // --- Mensualidades ---
        $alDia = Asociado::whereHas('cartera', fn ($q) => $q->where('meses_mora', 0))->first();
        $enMora = Asociado::whereHas('cartera', fn ($q) => $q->where('meses_mora', '>', 0))->first();

        $this->crear(ConceptoTransaccion::Mensualidad, CarteraSeeder::MENSUALIDAD, EstadoTransaccion::Aprobada, MetodoPago::Pse, [
            'asociado_id' => $alDia?->id,
        ]);

        $this->crear(ConceptoTransaccion::Mensualidad, CarteraSeeder::MENSUALIDAD * 2, EstadoTransaccion::Pendiente, MetodoPago::Pse, [
            'asociado_id' => $enMora?->id,
        ]);

        // --- Afiliaciones ---
        $this->crear(ConceptoTransaccion::Afiliacion, 150000, EstadoTransaccion::Aprobada, MetodoPago::Pse);
        $this->crear(ConceptoTransaccion::Afiliacion, 150000, EstadoTransaccion::Pendiente, MetodoPago::Tarjeta);

        $this->sembrarHistorialDeMensualidades();
    }

    /** @param  array<string, mixed>  $extra */
    private function crear(
        ConceptoTransaccion $concepto,
        int $monto,
        EstadoTransaccion $estado,
        MetodoPago $metodo,
        array $extra = [],
        ?\DateTimeInterface $fecha = null
    ): Transaccion {
        $cuando = $fecha ?? now()->subDays(random_int(1, 25));

        return Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => $concepto,
            'monto' => $monto,
            'moneda' => 'COP',
            'estado' => $estado,
            'metodo' => $metodo,
            'payload' => [
                'origen' => 'semilla',
                'pasarela' => 'fake',
                'registrado_en' => $cuando->format(\DateTimeInterface::ATOM),
            ],
            'created_at' => $cuando,
            'updated_at' => $cuando,
        ] + $extra);
    }

    /**
     * Dieciocho meses de mensualidades con estacionalidad, más el mes en curso.
     *
     * El tablero grafica recaudo mensual; con las transacciones apretadas en
     * los últimos 25 días la gráfica era una línea plana, y una gráfica vacía
     * enseña que el tablero no sirve.
     *
     * La forma es la del sector: diciembre factura, enero y febrero son el
     * valle, y la base de afiliados va creciendo mes a mes. El mes en curso
     * (mesesAtras = 0) se prorratea por los días ya transcurridos: dejarlo
     * vacío hasta el cierre de mes hacía que el KPI «recaudado este mes» del
     * tablero mostrara una caída falsa contra el mes anterior cualquier día
     * que no fuera fin de mes.
     */
    private function sembrarHistorialDeMensualidades(): void
    {
        /** @var array<int, int> índice de mes (1-12) => factor estacional en centésimas */
        $estacionalidad = [
            1 => 70, 2 => 75, 3 => 90, 4 => 95, 5 => 100, 6 => 105,
            7 => 110, 8 => 105, 9 => 100, 10 => 105, 11 => 115, 12 => 150,
        ];

        $asociados = Asociado::query()->has('cartera')->pluck('id')->all();

        if ($asociados === []) {
            return;
        }

        $totalAsociados = count($asociados);

        // Se carga una sola vez, fuera del bucle: quien debe N meses no pagó
        // ni el mes en curso ni los N-1 meses anteriores. Sin esto la cartera
        // y el historial cuentan historias distintas y /mi-cuenta muestra
        // «debes 3 meses» junto a un pago del mes pasado.
        $morasPorAsociado = Cartera::whereIn('asociado_id', $asociados)->pluck('meses_mora', 'asociado_id')->all();

        $cursor = 0;

        // De 18 (el mes más antiguo) a 0 (el mes en curso, todavía abierto).
        foreach (range(18, 0) as $mesesAtras) {
            $mes = now()->subMonths($mesesAtras)->startOfMonth();
            $esMesEnCurso = $mesesAtras === 0;

            // La base crece: hace 18 meses pagaban ~el 40 % de los de hoy.
            $crecimiento = 0.4 + (0.6 * (18 - $mesesAtras) / 18);
            $factor = $estacionalidad[(int) $mes->format('n')] / 100;
            $cuantos = (int) round($totalAsociados * 0.55 * $crecimiento * $factor);

            if ($esMesEnCurso) {
                // El mes en curso no ha cerrado: se prorratea por la fracción
                // de días que ya transcurrieron, ni vacío ni completo.
                $cuantos = (int) round($cuantos * now()->day / now()->daysInMonth);
            }

            $cuantos = max(min($cuantos, $totalAsociados), 1);

            // Rota el punto de partida en vez de tomar siempre el mismo
            // prefijo: en 18 meses todo asociado con cartera debe aparecer
            // al menos una vez, no siempre los mismos primeros del arreglo.
            $inicio = $cursor % $totalAsociados;
            $rotados = array_merge(array_slice($asociados, $inicio), array_slice($asociados, 0, $inicio));

            // Quien está en mora no pagó ni el mes en curso ni los meses de
            // su ventana de mora (coherencia con CarteraSeeder).
            $candidatos = array_values(array_filter(
                $rotados,
                function (int $asociadoId) use ($morasPorAsociado, $mesesAtras): bool {
                    $moras = $morasPorAsociado[$asociadoId] ?? 0;

                    return ! ($moras > 0 && $mesesAtras <= $moras);
                }
            ));

            $pagadores = array_slice($candidatos, 0, min($cuantos, count($candidatos)));

            foreach ($pagadores as $asociadoId) {
                $fecha = $esMesEnCurso
                    // Nunca en el futuro: acotado a los días ya transcurridos
                    // del mes y recortado contra «ahora» como red de seguridad.
                    ? $mes->copy()
                        ->addDays(random_int(0, max(0, now()->day - 1)))
                        ->addHours(random_int(0, 23))
                        ->min(now())
                    : $mes->copy()->addDays(random_int(0, 26))->addHours(random_int(8, 20));

                $this->crear(
                    ConceptoTransaccion::Mensualidad,
                    CarteraSeeder::MENSUALIDAD,
                    EstadoTransaccion::Aprobada,
                    MetodoPago::Pse,
                    ['asociado_id' => $asociadoId],
                    $fecha,
                );
            }

            $cursor += $cuantos;
        }
    }
}
