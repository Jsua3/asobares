<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mientras la contraseña de un afiliado sea provisional —la genérica de la
 * importación o la que le puso la oficina—, las secciones con datos de otras
 * personas siguen cerradas: banco de talento, proveedores, artistas y la
 * bolsa de empleo con sus postulaciones.
 *
 * La genérica la conocen todos los afiliados que la recibieron: con ella y el
 * correo de un vecino, cualquiera entraría a su cuenta. Lo que queda abierto
 * es lo que no expone a terceros: su estado de cuenta, los convenios y sus
 * fotos. Se cierra en la ruta y no escondiendo botones, para que escribir la
 * URL a mano tampoco sirva.
 */
class ExigirContrasenaPropia
{
    public function handle(Request $request, Closure $siguiente): Response
    {
        $usuario = $request->user();

        if ($usuario instanceof User && $usuario->contrasena_provisional) {
            return redirect()
                ->route('mi-cuenta.seguridad')
                ->with('aviso', 'Esa sección se abre cuando cambies tu contraseña provisional por una tuya.');
        }

        return $siguiente($request);
    }
}
