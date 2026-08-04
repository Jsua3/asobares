<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoArtista;
use App\Http\Requests\GuardarSolicitudDeArtistaRequest;
use App\Models\Artista;
use App\Models\Municipio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArtistaController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'tipo' => ['nullable', Rule::enum(TipoArtista::class)],
            'genero' => ['nullable', 'string', 'max:80'],
        ]);

        $consulta = Artista::publicado()->with('municipio');

        if (filled($datos['tipo'] ?? null)) {
            $consulta->where('tipo', $datos['tipo']);
        }

        if (filled($datos['genero'] ?? null)) {
            $consulta->where('genero_musical', $datos['genero']);
        }

        return view('publico.artistas.index', [
            'artistas' => $consulta->orderBy('nombre')->paginate(12)->withQueryString(),
            'generos' => Artista::publicado()->whereNotNull('genero_musical')->distinct()->orderBy('genero_musical')->pluck('genero_musical'),
            'filtros' => $datos,
        ]);
    }

    public function show(Artista $artista): View
    {
        abort_unless($artista->estaPublicado(), 404);

        return view('publico.artistas.show', [
            'artista' => $artista->load('municipio'),
            'similares' => Artista::publicado()
                ->where('id', '!=', $artista->id)
                ->where('tipo', $artista->tipo)
                ->take(3)
                ->get(),
        ]);
    }

    public function inscripcion(): View
    {
        return view('publico.artistas.inscripcion', [
            'tipos' => TipoArtista::cases(),
            'municipios' => Municipio::orderBy('nombre')->get(),
        ]);
    }

    public function guardarInscripcion(GuardarSolicitudDeArtistaRequest $request): RedirectResponse
    {
        Artista::create($request->datosDelArtista());

        return redirect()
            ->route('artistas.inscripcion')
            ->with('exito', 'Recibimos tu inscripción. La secretaría la revisa y te avisamos cuando tu ficha esté publicada.');
    }
}
