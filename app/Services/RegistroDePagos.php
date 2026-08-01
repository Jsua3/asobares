<?php

namespace App\Services;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Asociado;
use App\Models\Inscripcion;
use App\Models\Transaccion;
use App\Pagos\PasarelaDePago;
use App\Pagos\ResultadoDePago;
use Illuminate\Support\Facades\DB;

/**
 * Punto único donde un pago cambia el estado del mundo.
 *
 * Regla dura: ninguna inscripción se confirma y ninguna cartera se salda
 * sin una transacción aprobada.
 */
class RegistroDePagos
{
    public function __construct(private readonly PasarelaDePago $pasarela) {}

    public function cobrarInscripcion(Inscripcion $inscripcion): Transaccion
    {
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Evento,
            'inscripcion_id' => $inscripcion->id,
            'monto' => $inscripcion->evento->precio,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
            'payload' => ['pasarela' => $this->pasarela->nombre()],
        ]);

        $inscripcion->update(['transaccion_id' => $transaccion->id]);

        return $transaccion;
    }

    public function cobrarMensualidad(Asociado $asociado, float $monto): Transaccion
    {
        return Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'asociado_id' => $asociado->id,
            'monto' => $monto,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
            'payload' => ['pasarela' => $this->pasarela->nombre()],
        ]);
    }

    public function enlaceDePago(Transaccion $transaccion): string
    {
        return $this->pasarela->crearEnlaceDePago($transaccion);
    }

    /**
     * Aplica una confirmación de pago. Es idempotente: si la transacción ya
     * estaba resuelta, no vuelve a tocar nada (las pasarelas reintentan).
     */
    public function aplicarConfirmacion(ResultadoDePago $resultado): ?Transaccion
    {
        return DB::transaction(function () use ($resultado): ?Transaccion {
            $transaccion = Transaccion::where('referencia', $resultado->referencia)
                ->lockForUpdate()
                ->first();

            if ($transaccion === null) {
                return null;
            }

            if ($transaccion->estado !== EstadoTransaccion::Pendiente) {
                return $transaccion;
            }

            $transaccion->update([
                'estado' => $resultado->estado,
                'metodo' => $resultado->metodo,
                'payload' => array_merge($transaccion->payload ?? [], $resultado->payload),
            ]);

            if ($resultado->fueAprobado()) {
                $this->aplicarEfectos($transaccion->fresh());
            }

            return $transaccion->fresh();
        });
    }

    /** Lo que un pago aprobado desencadena, según su concepto. */
    private function aplicarEfectos(Transaccion $transaccion): void
    {
        match ($transaccion->concepto) {
            ConceptoTransaccion::Evento => $transaccion->inscripcion?->update([
                'estado' => EstadoInscripcion::Confirmada,
            ]),
            ConceptoTransaccion::Mensualidad => $transaccion->asociado?->cartera?->marcarAlDia(),
            ConceptoTransaccion::Afiliacion => null,
        };
    }
}
