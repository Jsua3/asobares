<?php

namespace App\Http\Controllers\Publico;

use App\Models\Aliado;
use App\Models\Asociado;
use App\Models\Beneficio;
use App\Models\Evento;
use App\Models\Iniciativa;
use Illuminate\Contracts\View\View;

class InicioController
{
    public function __invoke(): View
    {
        return view('publico.inicio', [
            'destacados' => Asociado::publicado()
                ->where('destacado', true)
                ->with(['categoria', 'municipio'])
                ->latest('updated_at')
                ->take(6)
                ->get(),
            'beneficios' => Beneficio::orderBy('orden')->get(),
            'aliados' => Aliado::visible()->get(),
            'proximosEventos' => Evento::publicado()->proximo()->take(3)->get(),
            'iniciativas' => Iniciativa::publicado()->orderBy('orden')->take(5)->get(),
            'totalAsociados' => Asociado::publicado()->count(),
        ]);
    }
}
