<?php

namespace App\Http\Controllers\Publico;

use App\Http\Requests\GuardarSolicitudAfiliacionRequest;
use App\Mail\NuevaSolicitudAfiliacion;
use App\Models\Beneficio;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\SolicitudAfiliacion;
use App\Services\CorreosInstitucionales;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AfiliacionController
{
    public function __construct(private readonly CorreosInstitucionales $correosInstitucionales) {}

    public function index(): View
    {
        return view('publico.afiliate', [
            'beneficios' => Beneficio::orderBy('orden')->get(),
            'municipios' => Municipio::orderBy('nombre')->pluck('nombre', 'id')->all(),
            'categorias' => Categoria::orderBy('nombre')->pluck('nombre', 'id')->all(),
        ]);
    }

    public function store(GuardarSolicitudAfiliacionRequest $request): RedirectResponse
    {
        $solicitud = SolicitudAfiliacion::create($request->datosDeSolicitud());

        $this->correosInstitucionales->enviar(new NuevaSolicitudAfiliacion($solicitud), 'afiliacion');

        return redirect()
            ->route('afiliate')
            ->with('exito', "Recibimos tu solicitud, {$solicitud->solicitante_nombre}. La revisaremos y te contactaremos para continuar el proceso de visita.")
            ->with('mostrarWhatsapp', true)
            ->withFragment('formulario');
    }
}
