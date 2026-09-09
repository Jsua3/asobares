<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoMensaje;
use App\Http\Requests\GuardarMensajeRequest;
use App\Models\Beneficio;
use App\Models\Mensaje;
use App\Support\AvisoDeMensajeAlGremio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AfiliacionController
{
    public function index(): View
    {
        return view('publico.afiliate', [
            // `with` y no lazy: el sello de alcance del beneficio municipal
            // pide su municipio, y sin esto son tantas consultas como filas.
            'beneficios' => Beneficio::with('municipio')->orderBy('orden')->get(),
        ]);
    }

    public function store(GuardarMensajeRequest $request): RedirectResponse
    {
        $mensaje = Mensaje::create([
            ...$request->datosDelMensaje(),
            'tipo' => TipoMensaje::Afiliacion,
        ]);

        // Una solicitud de afiliación es un cliente llamando a la puerta: si
        // nadie se entera hasta que alguien abra el panel, se pierde (Acta 08).
        AvisoDeMensajeAlGremio::enviar($mensaje);

        return redirect()
            ->route('afiliate')
            ->with('exito', "Recibimos tu solicitud, {$mensaje->nombre}. Te contactamos en los próximos días hábiles.")
            ->with('mostrarWhatsapp', true)
            ->withFragment('formulario');
    }
}
