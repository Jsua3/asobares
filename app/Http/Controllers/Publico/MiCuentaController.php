<?php

namespace App\Http\Controllers\Publico;

use App\Models\Aliado;
use App\Models\Cartera;
use App\Services\RegistroDePagos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $transaccion = $pagos->cobrarMensualidad($asociado, (float) $cartera->saldo_pendiente);

        return redirect()->away($pagos->enlaceDePago($transaccion));
    }
}
