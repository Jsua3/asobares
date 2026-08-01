<?php

namespace App\Http\Controllers\Publico;

use App\Models\Beneficio;
use Illuminate\Contracts\View\View;

class PaginaController
{
    public function quienesSomos(): View
    {
        return view('publico.quienes-somos', [
            'beneficios' => Beneficio::orderBy('orden')->get(),
        ]);
    }

    public function politicaDeDatos(): View
    {
        return view('publico.politica-de-datos');
    }
}
