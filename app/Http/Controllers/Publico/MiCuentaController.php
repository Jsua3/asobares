<?php

namespace App\Http\Controllers\Publico;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Models\Aliado;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Transaccion;
use App\Services\RegistroDePagos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * "La gente no paga porque no sabe cuánto debe, entonces todo el mundo
 * llama a Natalia." Esta pantalla existe para eso.
 */
class MiCuentaController
{
    public function index(Request $request): View
    {
        $asociado = $request->user()->asociado;

        abort_if($asociado === null, 403, 'Tu usuario todavía no está vinculado a un establecimiento.');

        $cartera = $asociado->cartera ?? new Cartera(['saldo_pendiente' => 0, 'meses_mora' => 0]);

        return view('publico.mi-cuenta.index', [
            'asociado' => $asociado->load(['municipio', 'categoria']),
            'cartera' => $cartera,
            // El detalle de los convenios es contenido privado de afiliados.
            'aliados' => Aliado::visible()->get(),
            'transacciones' => $asociado->transacciones()->latest()->take(5)->get(),
        ]);
    }

    public function pagarMensualidad(Request $request, RegistroDePagos $pagos): RedirectResponse
    {
        $asociado = $request->user()->asociado;

        abort_if($asociado === null, 403);

        $cartera = $asociado->cartera;

        if ($cartera === null || $cartera->estaAlDia()) {
            return redirect()->route('mi-cuenta.index')->with('exito', 'Tu cuenta ya está al día.');
        }

        $transaccion = $this->cobroVigente($asociado, (float) $cartera->saldo_pendiente)
            ?? $pagos->cobrarMensualidad($asociado, (float) $cartera->saldo_pendiente);

        try {
            return redirect()->away($pagos->enlaceDePago($transaccion));
        } catch (Throwable $fallo) {
            // La pasarela puede estar caída o mal configurada. El asociado no
            // tiene por qué ver una traza: el cobro queda pendiente y se puede
            // reintentar sin haber duplicado nada.
            Log::error('No se pudo generar el enlace de pago de la mensualidad.', [
                'referencia' => $transaccion->referencia,
                'excepcion' => $fallo->getMessage(),
            ]);

            return redirect()->route('mi-cuenta.index')->with(
                'error',
                'No pudimos abrir la pasarela de pago en este momento. Inténtalo de nuevo en unos minutos.'
            );
        }
    }

    /**
     * Volver a pulsar «Pagar ahora» no puede abrir un cobro nuevo cada vez:
     * se retoma el que ya estaba pendiente, siempre que siga vigente y siga
     * cobrando lo mismo que debe hoy la cartera.
     */
    private function cobroVigente(Asociado $asociado, float $saldo): ?Transaccion
    {
        $pendiente = $asociado->transacciones()
            ->where('concepto', ConceptoTransaccion::Mensualidad)
            ->where('estado', EstadoTransaccion::Pendiente)
            ->where('created_at', '>=', now()->subDay())
            ->latest()
            ->first();

        return $pendiente instanceof Transaccion && (float) $pendiente->monto === $saldo
            ? $pendiente
            : null;
    }
}
