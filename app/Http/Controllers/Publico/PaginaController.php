<?php

namespace App\Http\Controllers\Publico;

use App\Models\Beneficio;
use App\Models\Iniciativa;
use Illuminate\Contracts\View\View;

class PaginaController
{
    public function quienesSomos(): View
    {
        return view('publico.quienes-somos', [
            'beneficios' => Beneficio::orderBy('orden')->get(),
            'iniciativas' => Iniciativa::publicado()->orderBy('orden')->get(),
        ]);
    }

    public function politicaDeDatos(): View
    {
        return view('publico.politica-de-datos');
    }
}
