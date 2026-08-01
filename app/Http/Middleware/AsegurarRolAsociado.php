<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /mi-cuenta es exclusiva de los dueños de establecimiento. Ni la dirección
 * ni la secretaría entran: ellas tienen el panel.
 */
class AsegurarRolAsociado
{
    public function handle(Request $request, Closure $siguiente): Response
    {
        $usuario = $request->user();

        abort_unless($usuario instanceof User && $usuario->esAsociado(), 403,
            'Esta sección es para los establecimientos afiliados.');

        return $siguiente($request);
    }
}
