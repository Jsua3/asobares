<?php

namespace App\Http\Controllers\Publico;

use App\Enums\CargoDelSector;
use App\Models\Aspirante;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Banco de talento: los perfiles que deja cualquiera en /empleo, visibles
 * solo para los establecimientos afiliados.
 *
 * Es de LECTURA. El campo `estado` pertenece a la secretaria y se gestiona
 * desde el panel: si cada uno de los establecimientos pudiera moverlo, dos
 * bares se pisarian el seguimiento del mismo candidato sin enterarse. El
 * establecimiento que quiera a alguien lo contacta y ya.
 *
 * Y no se ve todo el que se registra: desde el 8 de septiembre hay que aprobar
 * el perfil antes (`aprobado_el`). Los descartados por el gremio tampoco se
 * muestran: descartar y seguir apareciendo es no haber descartado nada. Las dos
 * condiciones viven juntas en `visibleParaAfiliados`.
 */
class MisAspirantesController
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'categoria' => ['nullable', Rule::enum(CargoDelSector::class)],
        ]);

        $consulta = Aspirante::visibleParaAfiliados();

        if (filled($datos['categoria'] ?? null)) {
            $consulta->where('categoria_cargo', $datos['categoria']);
        }

        return view('publico.mi-cuenta.aspirantes.index', [
            'aspirantes' => $consulta->latest()->paginate(20)->withQueryString(),
            'categorias' => CargoDelSector::cases(),
            'filtros' => $datos,
        ]);
    }
}
