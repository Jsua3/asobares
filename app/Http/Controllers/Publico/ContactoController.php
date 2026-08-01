<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoMensaje;
use App\Http\Requests\GuardarMensajeRequest;
use App\Mail\AcuseDeRadicado;
use App\Models\Mensaje;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactoController
{
    public function index(): View
    {
        return view('publico.contacto', [
            'tipos' => [
                TipoMensaje::Contacto,
                TipoMensaje::Pqr,
                TipoMensaje::Aliado,
                TipoMensaje::Proveedor,
            ],
        ]);
    }

    public function store(GuardarMensajeRequest $request): RedirectResponse
    {
        $datos = $request->datosDelMensaje();

        // Solo las PQR reciben radicado consecutivo.
        if ($datos['tipo']->requiereRadicado()) {
            $datos['radicado'] = Mensaje::generarRadicado();
        }

        $mensaje = Mensaje::create($datos);

        if ($mensaje->esPqr()) {
            Mail::to($mensaje->correo)->send(new AcuseDeRadicado($mensaje));

            return redirect()
                ->route('contacto')
                ->with('radicado', $mensaje->radicado)
                ->with('exito', "Tu PQR quedó radicada con el número {$mensaje->radicado}. Te enviamos el acuse a {$mensaje->correo}.")
                ->withFragment('formulario');
        }

        return redirect()
            ->route('contacto')
            ->with('exito', "Recibimos tu mensaje, {$mensaje->nombre}. Te respondemos pronto.")
            ->withFragment('formulario');
    }
}
