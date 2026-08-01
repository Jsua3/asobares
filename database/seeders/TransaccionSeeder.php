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
    }

    /** @param  array<string, mixed>  $extra */
    private function crear(
        ConceptoTransaccion $concepto,
        int $monto,
        EstadoTransaccion $estado,
        MetodoPago $metodo,
        array $extra = []
    ): Transaccion {
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
                'registrado_en' => now()->toIso8601String(),
            ],
            'created_at' => now()->subDays(random_int(1, 25)),
        ] + $extra);
    }
}
