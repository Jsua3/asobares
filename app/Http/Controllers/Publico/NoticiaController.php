<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CategoriaNoticia;
use App\Models\Noticia;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NoticiaController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'categoria' => ['nullable', Rule::enum(CategoriaNoticia::class)],
        ]);

        $consulta = Noticia::visible();

        if (filled($datos['categoria'] ?? null)) {
            $consulta->where('categoria', $datos['categoria']);
        }

        return view('publico.boletin.index', [
            'noticias' => $consulta->paginate(9)->withQueryString(),
            'categorias' => CategoriaNoticia::cases(),
            'filtros' => $datos,
        ]);
    }

    public function show(Noticia $noticia): View
    {
        abort_unless($noticia->estaPublicado() && $noticia->publicado_at?->isPast(), 404);

        return view('publico.boletin.show', [
            'noticia' => $noticia,
            'relacionadas' => Noticia::visible()->where('id', '!=', $noticia->id)->take(3)->get(),
        ]);
    }
}
