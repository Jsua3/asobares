<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoArtista;
use App\Models\Artista;
use Illuminate\Contracts\View\View;
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
}
