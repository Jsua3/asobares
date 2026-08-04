<?php

namespace App\Support;

use App\Models\Asociado;

/**
 * A quién le escribe el gremio cuando algo pasa en la vacante de un
 * establecimiento.
 *
 * No todos los asociados tienen usuario todavía —la afiliación es anterior a
 * la plataforma—, así que hay un segundo camino por el correo de la ficha
 * antes de darse por vencido.
 */
final class DestinatariosDelAsociado
{
    /** @return array<int, string> */
    public static function correos(Asociado $asociado): array
    {
        $deLosUsuarios = $asociado->usuarios()->pluck('email')->filter()->values()->all();

        if ($deLosUsuarios !== []) {
            return $deLosUsuarios;
        }

        return filled($asociado->correo_interno) ? [$asociado->correo_interno] : [];
    }
}
