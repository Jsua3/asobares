<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CargoDelSector;
use App\Http\Requests\GuardarAspiranteRequest;
use App\Models\Aspirante;
use App\Models\Municipio;
use App\Models\Vacante;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Bolsa de empleo del sector: un muro donde solo publican los asociados,
 * y un formulario abierto para quien busca trabajo.
 */
class EmpleoController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'categoria' => ['nullable', Rule::enum(CargoDelSector::class)],
            'municipio' => ['nullable', 'string', 'exists:municipios,slug'],
        ]);

        $consulta = Vacante::publicado()
            ->vigente()
            ->with(['asociado.municipio', 'asociado.categoria']);

        if (filled($datos['categoria'] ?? null)) {
            $consulta->where('categoria_cargo', $datos['categoria']);
        }

        if (filled($datos['municipio'] ?? null)) {
            $consulta->whereHas('asociado.municipio', fn ($q) => $q->where('slug', $datos['municipio']));
        }

        return view('publico.empleo.index', [
            'vacantes' => $consulta->latest()->paginate(10)->withQueryString(),
            'municipios' => Municipio::orderBy('nombre')->get(),
            'categorias' => CargoDelSector::cases(),
            'filtros' => $datos,
        ]);
    }

    public function show(Vacante $vacante): View
    {
        // Una vacante cerrada, vencida o sin aprobar no existe para el
        // público: 404, no una página que diga «ya no está disponible».
        abort_unless($vacante->aceptaPostulaciones(), 404);

        $vacante->load(['asociado.municipio', 'asociado.categoria']);

        return view('publico.empleo.show', [
            'vacante' => $vacante,
            'similares' => Vacante::publicado()
                ->vigente()
                ->where('categoria_cargo', $vacante->categoria_cargo)
                ->whereKeyNot($vacante->id)
                ->with('asociado.municipio')
                ->latest()
                ->take(3)
                ->get(),
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
