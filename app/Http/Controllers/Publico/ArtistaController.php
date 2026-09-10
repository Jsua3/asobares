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
            'tipos' => $this->tiposConFicha($datos['tipo'] ?? null),
            'filtros' => $datos,
        ]);
    }

    /**
     * Los tipos que de verdad tienen ficha publicada, más el elegido.
     *
     * El desplegable de al lado —«Género musical»— ya salía de las fichas
     * publicadas, y este seguía recorriendo el enum entero desde la propia
     * plantilla. Se veía dentro del MISMO formulario: medido contra producción
     * el 9 de septiembre de 2026, con cero fichas publicadas, «Género musical»
     * se quedaba correctamente en «Todos los géneros» mientras «Tipo» ofrecía
     * DJ, Banda, Solista y Otro, las cuatro muertas.
     *
     * Se recorre el enum y no la consulta para conservar el orden declarado, que
     * es el que la oficina espera. Y lo elegido se conserva aunque se quede sin
     * fichas: si el filtro borrara de la lista lo que el visitante escogió, el
     * desplegable volvería solo a «Todos» mientras la consulta sigue filtrando.
     *
     * @return list<TipoArtista>
     */
    private function tiposConFicha(?string $elegido): array
    {
        $conFicha = Artista::publicado()
            ->distinct()
            ->pluck('tipo')
            ->push(filled($elegido) ? TipoArtista::from($elegido) : null)
            ->filter()
            ->unique();

        return array_values(array_filter(
            TipoArtista::cases(),
            fn (TipoArtista $tipo): bool => $conFicha->contains($tipo)
        ));
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
