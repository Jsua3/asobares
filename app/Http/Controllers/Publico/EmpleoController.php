<?php

namespace App\Http\Controllers\Publico;

use App\Http\Requests\GuardarAspiranteRequest;
use App\Models\Aspirante;
use App\Models\Municipio;
use App\Models\Vacante;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Bolsa de empleo del sector: un muro donde solo publican los asociados,
 * y un formulario abierto para quien busca trabajo.
 */
class EmpleoController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'cargo' => ['nullable', 'string', 'max:80'],
            'municipio' => ['nullable', 'string', 'exists:municipios,slug'],
        ]);

        $consulta = Vacante::publicado()->with(['asociado.municipio', 'asociado.categoria']);

        if (filled($datos['cargo'] ?? null)) {
            $consulta->where('cargo', 'like', '%'.$datos['cargo'].'%');
        }

        if (filled($datos['municipio'] ?? null)) {
            $consulta->whereHas('asociado.municipio', fn ($q) => $q->where('slug', $datos['municipio']));
        }

        return view('publico.empleo.index', [
            'vacantes' => $consulta->latest()->paginate(10)->withQueryString(),
            'municipios' => Municipio::orderBy('nombre')->get(),
            'cargos' => Vacante::publicado()->distinct()->orderBy('cargo')->pluck('cargo'),
            'filtros' => $datos,
        ]);
    }

    public function registrarAspirante(GuardarAspiranteRequest $request): RedirectResponse
    {
        Aspirante::create($request->datosDelAspirante());

        return redirect()
            ->route('empleo.index')
            ->with('exito', 'Tu perfil quedó registrado. Cuando un establecimiento asociado busque tu cargo, te contactamos.')
            ->withFragment('perfil');
    }
}
