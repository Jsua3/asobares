<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoMensaje;
use App\Http\Requests\GuardarMensajeRequest;
use App\Models\Beneficio;
use App\Models\Mensaje;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AfiliacionController
{
    public function index(): View
    {
        return view('publico.afiliate', [
            'beneficios' => Beneficio::orderBy('orden')->get(),
        ]);
    }

    public function store(GuardarMensajeRequest $request): RedirectResponse
    {
        $mensaje = Mensaje::create([
            ...$request->datosDelMensaje(),
            'tipo' => TipoMensaje::Afiliacion,
        ]);

        return redirect()
            ->route('afiliate')
            ->with('exito', "Recibimos tu solicitud, {$mensaje->nombre}. Te contactamos en los próximos días hábiles.")
            ->with('mostrarWhatsapp', true)
            ->withFragment('formulario');
    }
}
