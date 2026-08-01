<?php

namespace App\Http\Controllers\Publico;

use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorioController
{
    /**
     * Filtros por GET para que las URLs se puedan compartir:
     * /directorio?municipio=salento&categoria=cafe
     */
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'municipio' => ['nullable', 'string', 'exists:municipios,slug'],
            'categoria' => ['nullable', 'string', 'exists:categorias,slug'],
            'vista' => ['nullable', 'in:grid,mapa'],
        ]);

        $consulta = Asociado::publicado()->with(['categoria', 'municipio']);

        if (filled($datos['q'] ?? null)) {
            $consulta->where('nombre', 'like', '%'.$datos['q'].'%');
        }

        if (filled($datos['municipio'] ?? null)) {
            $consulta->whereHas('municipio', fn ($q) => $q->where('slug', $datos['municipio']));
        }

        if (filled($datos['categoria'] ?? null)) {
            $consulta->whereHas('categoria', fn ($q) => $q->where('slug', $datos['categoria']));
        }

        $vista = $datos['vista'] ?? 'grid';

        // En modo mapa se necesitan todos los pines, no una página.
        $asociados = $vista === 'mapa'
            ? $consulta->orderBy('nombre')->get()
            : $consulta->orderByDesc('destacado')->orderBy('nombre')->paginate(12)->withQueryString();

        return view('publico.directorio.index', [
            'asociados' => $asociados,
            'municipios' => Municipio::orderBy('nombre')->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'filtros' => $datos,
            'vista' => $vista,
        ]);
    }

    public function show(Asociado $asociado): View
    {
        abort_unless($asociado->estaPublicado(), 404);

        $asociado->load(['categoria', 'municipio', 'media']);

        return view('publico.directorio.show', [
            'asociado' => $asociado,
            'vacantes' => $asociado->vacantes()->publicado()->get(),
            'similares' => Asociado::publicado()
                ->where('id', '!=', $asociado->id)
                ->where('municipio_id', $asociado->municipio_id)
                ->with(['categoria', 'municipio'])
                ->inRandomOrder()
                ->take(3)
                ->get(),
        ]);
    }
}
