<?php

namespace App\Http\Controllers\Publico;

use App\Enums\UbicacionPublicidad;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Publicidad;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
            // El «no distingue mayúsculas» no es cosmético: es la diferencia
            // entre encontrar diez establecimientos y encontrar cuatro cuando
            // el motor pasa de SQLite a PostgreSQL. Está en el scope, con su
            // explicación y su guardia.
            $consulta->buscarPorNombre($datos['q']);
        }

        if (filled($datos['municipio'] ?? null)) {
            $consulta->whereHas('municipio', fn ($q) => $q->where('slug', $datos['municipio']));
        }

        if (filled($datos['categoria'] ?? null)) {
            $consulta->whereHas('categoria', fn ($q) => $q->where('slug', $datos['categoria']));
        }

        $vista = $datos['vista'] ?? 'grid';

        $this->ordenarParaDirectorio($consulta);

        // En modo mapa se necesitan todos los pines, no una página.
        $asociados = $vista === 'mapa'
            ? $consulta->get()
            : $consulta->paginate(12)->withQueryString();

        return view('publico.directorio.index', [
            'asociados' => $asociados,
            'municipios' => Municipio::orderBy('nombre')->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'filtros' => $datos,
            'publicidadDirectorio' => Publicidad::publicaEn(UbicacionPublicidad::Directorio)->first(),
            'vista' => $vista,
        ]);
    }

    private function ordenarParaDirectorio(Builder $consulta): void
    {
        $consulta
            ->orderByDesc('destacado')
            ->orderByRaw("lower(case when lower(nombre) like 'bar %' then substr(nombre, 5) else nombre end)")
            ->orderBy('nombre');
    }

    public function show(Asociado $asociado): View
    {
        abort_unless($asociado->estaPublicado(), 404);

        $asociado->load(['categoria', 'municipio', 'media']);

        return view('publico.directorio.show', [
            'asociado' => $asociado,
            'vacantes' => $asociado->vacantes()->publicado()->vigente()->get(),
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
