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
use Illuminate\Support\Facades\Log;

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

            // Una aprobación que no cuadra con lo cobrado no se aplica: la
            // transacción sigue pendiente y queda constancia para revisarla a
            // mano. Es preferible un cobro sin resolver a una deuda saldada
            // con menos dinero del que se debía.
            if ($resultado->fueAprobado() && ! $this->montoConcuerda($transaccion, $resultado)) {
                Log::error('Confirmación de pago descartada: el monto notificado no cuadra.', [
                    'referencia' => $transaccion->referencia,
                    'esperado' => (float) $transaccion->monto,
                    'moneda_esperada' => $transaccion->moneda,
                    'notificado' => $resultado->monto,
                    'moneda_notificada' => $resultado->moneda,
                ]);

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

    /**
     * ¿Lo que la pasarela dice haber cobrado es lo que se cobró?
     *
     * Antes, una notificación sin monto se daba por buena con un aviso en el
     * log. El problema es lo que pasa si el nombre real del campo no es
     * ninguno de los que adivina `PasarelaBold`: entonces NINGÚN pago se
     * concilia nunca y el control queda inerte sin que nadie se entere, que es
     * justo el modo de fallar que no se puede permitir en la parte del dinero.
     *
     * Fallando cerrado, la transacción se queda pendiente y el desajuste se
     * ve en la primera prueba contra el sandbox, que es cuando toca
     * descubrirlo. Ningún pago se pierde: la referencia sigue viva y se
     * concilia a mano o con la notificación corregida.
     */
    private function montoConcuerda(Transaccion $transaccion, ResultadoDePago $resultado): bool
    {
        if ($resultado->monto === null) {
            Log::warning('La pasarela confirmó un pago sin informar el monto: no se concilia y queda pendiente.', [
                'referencia' => $transaccion->referencia,
            ]);

            return false;
        }

        if (round((float) $transaccion->monto, 2) !== round($resultado->monto, 2)) {
            return false;
        }

        return $resultado->moneda === null
            || strtoupper($resultado->moneda) === strtoupper((string) $transaccion->moneda);
    }

    /** Lo que un pago aprobado desencadena, según su concepto. */
    private function aplicarEfectos(Transaccion $transaccion): void
    {
        match ($transaccion->concepto) {
            ConceptoTransaccion::Evento => $transaccion->inscripcion?->update([
                'estado' => EstadoInscripcion::Confirmada,
            ]),
            ConceptoTransaccion::Mensualidad => $transaccion->asociado?->cartera?->abonar((float) $transaccion->monto),
            ConceptoTransaccion::Afiliacion => null,
        };
    }
}
