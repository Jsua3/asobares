<?php

namespace App\Http\Controllers;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Transaccion;
use App\Pagos\PasarelaDePago;
use App\Pagos\PasarelaSimulada;
use App\Services\RegistroDePagos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PagoController
{
    /** Pasarela simulada: la pantalla que reemplaza a Bold en el demo. */
    public function simulado(Transaccion $transaccion, PasarelaDePago $pasarela): View|RedirectResponse
    {
        abort_unless($pasarela instanceof PasarelaSimulada, 404);

        if ($transaccion->estado !== EstadoTransaccion::Pendiente) {
            return redirect()->route('pago.estado', $transaccion);
        }

        return view('publico.pago.simulado', ['transaccion' => $transaccion->load(['inscripcion.evento', 'asociado'])]);
    }

    public function resolverSimulado(
        Request $request,
        Transaccion $transaccion,
        PasarelaDePago $pasarela,
        RegistroDePagos $pagos
    ): RedirectResponse {
        abort_unless($pasarela instanceof PasarelaSimulada, 404);

        $request->validate([
            'decision' => ['required', 'in:aprobar,rechazar'],
            'metodo' => ['required', Rule::enum(MetodoPago::class)],
        ]);

        // Se pasa por el mismo camino que usaría el webhook de Bold, para que
        // lo que se demuestra sea el flujo real y no un atajo.
        $resultado = $pasarela->interpretarConfirmacion(
            $request->merge(['referencia' => $transaccion->referencia])
        );

        if ($resultado !== null) {
            $pagos->aplicarConfirmacion($resultado);
        }

        return redirect()->route('pago.estado', $transaccion);
    }

    public function estado(Transaccion $transaccion): View
    {
        $transaccion->load(['inscripcion.evento', 'asociado']);

        return view('publico.pago.estado', [
            'transaccion' => $transaccion,
            'volverA' => match ($transaccion->concepto) {
                ConceptoTransaccion::Evento => $transaccion->inscripcion?->evento
                    ? route('eventos.show', $transaccion->inscripcion->evento)
                    : route('eventos.index'),
                ConceptoTransaccion::Mensualidad => route('mi-cuenta.index'),
                ConceptoTransaccion::Afiliacion => route('afiliate'),
            },
        ]);
    }
}
