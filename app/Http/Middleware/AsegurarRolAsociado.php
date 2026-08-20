<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /mi-cuenta es exclusiva de los dueños de establecimiento con ficha vinculada.
 * Ni la dirección ni la secretaría entran: ellas tienen el panel. Un usuario
 * con rol asociado pero sin establecimiento ve la misma explicación.
 *
 * El panel y /mi-cuenta comparten el guard `web`, así que es muy fácil
 * llegar aquí con la sesión del equipo abierta —pasa en cada demostración—.
 * Por eso no se responde un 403 seco: se explica qué sesión hay abierta y
 * se ofrece la salida.
 */
class AsegurarRolAsociado
{
    public function handle(Request $request, Closure $siguiente): Response
    {
        $usuario = $request->user();

        if ($usuario instanceof User && $usuario->esAsociado() && $usuario->asociado_id !== null) {
            return $siguiente($request);
        }

        return response()->view('publico.mi-cuenta.sesion-equivocada', [
            'usuario' => $usuario,
        ], 403);
    }
}
