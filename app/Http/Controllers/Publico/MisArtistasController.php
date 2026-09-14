<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoArtista;
use App\Models\Artista;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * El contacto del artista es contraprestación de la cuota, igual que el del
 * proveedor: quien contrata música en vivo en el Quindío es el establecimiento
 * afiliado, así que el teléfono se entrega detrás de la sesión.
 *
 * La diferencia con proveedores es deliberada y conviene no borrarla: la ficha
 * pública del artista NO se vacía. Nombre, foto, género y video siguen siendo
 * públicos e indexables, porque el escaparate es lo que el artista viene a
 * buscar cuando se inscribe; sacarlo del índice le quitaría el motivo. Lo único
 * que se muda aquí es el contacto.
 */
class MisArtistasController
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

        return view('publico.mi-cuenta.artistas.index', [
            'artistas' => $consulta->orderBy('nombre')->paginate(24)->withQueryString(),
            'tipos' => TipoArtista::cases(),
            'generos' => Artista::publicado()
                ->whereNotNull('genero_musical')
                ->distinct()
                ->orderBy('genero_musical')
                ->pluck('genero_musical'),
            'filtros' => $datos,
        ]);
    }
}
