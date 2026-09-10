<?php

namespace App\Http\Controllers\Publico;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Beneficio;
use App\Models\Iniciativa;
use App\Support\ReglaDeAlcaldias;
use Illuminate\Contracts\View\View;

class PaginaController
{
    public function quienesSomos(): View
    {
        return view('publico.quienes-somos', [
            'beneficios' => Beneficio::with('municipio')->orderBy('orden')->get(),
            'iniciativas' => Iniciativa::publicado()->orderBy('orden')->get(),
        ]);
    }

    public function politicaDeDatos(): View
    {
        return view('publico.politica-de-datos');
    }

    public function aliados(ReglaDeAlcaldias $reglaDeAlcaldias): View
    {
        $aliados = $reglaDeAlcaldias->filtrar(
            Aliado::visible()->with('municipio')->get()
        );

        return view('publico.aliados.index', [
            'aliadosInstitucionales' => $aliados->where('tipo', TipoAliado::Institucional)->values(),
            'aliadosComerciales' => $aliados->where('tipo', TipoAliado::Comercial)->values(),
        ]);
    }
}
