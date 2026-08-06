<?php

namespace Database\Seeders;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Asociado;
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
     * Dieciocho meses de mensualidades con estacionalidad.
     *
     * El tablero grafica recaudo mensual; con las transacciones apretadas en
     * los últimos 25 días la gráfica era una línea plana, y una gráfica vacía
     * enseña que el tablero no sirve.
     *
     * La forma es la del sector: diciembre factura, enero y febrero son el
     * valle, y la base de afiliados va creciendo mes a mes.
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

        foreach (range(1, 18) as $mesesAtras) {
            $mes = now()->subMonths($mesesAtras)->startOfMonth();

            // La base crece: hace 18 meses pagaban ~el 40 % de los de hoy.
            $crecimiento = 0.4 + (0.6 * (18 - $mesesAtras) / 18);
            $factor = $estacionalidad[(int) $mes->format('n')] / 100;
            $cuantos = (int) round(count($asociados) * 0.55 * $crecimiento * $factor);
            $cuantos = max(min($cuantos, count($asociados)), 1);

            $pagadores = array_slice($asociados, 0, $cuantos);

            foreach ($pagadores as $asociadoId) {
                $this->crear(
                    ConceptoTransaccion::Mensualidad,
                    CarteraSeeder::MENSUALIDAD,
                    EstadoTransaccion::Aprobada,
                    MetodoPago::Pse,
                    ['asociado_id' => $asociadoId],
                    $mes->copy()->addDays(random_int(0, 26))->addHours(random_int(8, 20)),
                );
            }
        }
    }
}
