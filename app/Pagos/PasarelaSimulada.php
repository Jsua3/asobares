<?php

namespace App\Pagos;

use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Transaccion;
use Illuminate\Http\Request;

/**
 * Pasarela del demo. Manda a una página interna con la marca del gremio
 * donde se puede aprobar o rechazar el pago a mano, disparando exactamente
 * el mismo flujo que dispararía Bold.
 */
class PasarelaSimulada implements PasarelaDePago
{
    public function crearEnlaceDePago(Transaccion $transaccion): string
    {
        return route('pago.simulado', ['transaccion' => $transaccion->referencia]);
    }

    /**
     * Nadie externo debe poder confirmar un pago por esta pasarela: la
     * resuelve a mano el propio sitio en /pago-simulado. Devolver `true`
     * convertiría el webhook público en un aprobador universal de pagos,
     * así que aquí la respuesta correcta es siempre `false`.
     */
    public function firmaValida(Request $request): bool
    {
        return false;
    }

    public function interpretarConfirmacion(Request $request): ?ResultadoDePago
    {
        $referencia = $request->string('referencia')->toString();

        if ($referencia === '') {
            return null;
        }

        $aprobado = $request->string('decision')->toString() === 'aprobar';

        return new ResultadoDePago(
            referencia: $referencia,
            estado: $aprobado ? EstadoTransaccion::Aprobada : EstadoTransaccion::Rechazada,
            metodo: MetodoPago::tryFrom($request->string('metodo')->toString()) ?? MetodoPago::Pse,
            payload: [
                'pasarela' => 'simulada',
                'decision' => $aprobado ? 'aprobada' : 'rechazada',
                'confirmado_en' => now()->toIso8601String(),
            ],
        );
    }

    public function nombre(): string
    {
        return 'simulada';
    }
}
