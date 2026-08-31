<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoAliado;
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
        // Una sola consulta y dos bandas: `scopeVisible` ya ordena por
        // `orden`, y partir en memoria seis registros es mas barato que ir
        // dos veces a la base.
        $aliados = Aliado::visible()->get();

        return view('publico.inicio', [
            'destacados' => Asociado::publicado()
                ->where('destacado', true)
                ->with(['categoria', 'municipio'])
                ->latest('updated_at')
                ->take(6)
                ->get(),
            'beneficios' => Beneficio::orderBy('orden')->get(),
            'aliadosInstitucionales' => $aliados->where('tipo', TipoAliado::Institucional)->values(),
            'aliadosComerciales' => $aliados->where('tipo', TipoAliado::Comercial)->values(),
            'proximosEventos' => Evento::publicado()->proximo()->take(3)->get(),
            'iniciativas' => Iniciativa::publicado()->orderBy('orden')->take(5)->get(),
            'totalAsociados' => Asociado::publicado()->count(),
        ]);
    }
}
