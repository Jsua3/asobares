<?php

namespace App\Policies;

use App\Models\Asociado;
use App\Models\User;

class AsociadoPolicy extends PoliticaDeContenido
{
    protected function recurso(): string
    {
        return 'asociado';
    }

    /**
     * Gestionar las fotos del propio establecimiento desde `/mi-cuenta`.
     *
     * Habilidad aparte y SOLO por propiedad, igual que `Vacante::verEnPortal`,
     * y por la misma razón que costó una ronda de revisión en el v6: `view`
     * concede por permiso *o* por propiedad, así que un directivo que además
     * sea dueño de un bar --caso perfectamente real en un gremio-- entraría
     * por el permiso de panel a la galería de cualquier otro establecimiento.
     *
     * Aquí no hay datos de terceros, pero sí la capacidad de subir y borrar
     * archivos en la ficha ajena, que es peor.
     */
    public function gestionarFotosEnPortal(User $usuario, Asociado $asociado): bool
    {
        return $usuario->esAsociado()
            && $usuario->asociado_id !== null
            && $usuario->asociado_id === $asociado->getKey();
    }
}
