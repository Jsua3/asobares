<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacante;

/**
 * La vacante es del establecimiento que la publicó.
 *
 * Deja de heredar de PoliticaDeContenido a propósito: aquí no manda el
 * permiso sino la propiedad. El gremio modera —aprueba, devuelve y, si hace
 * falta, elimina—, pero no reescribe lo que publicó un tercero.
 */
class VacantePolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_vacante') || $usuario->esAsociado();
    }

    public function view(User $usuario, Vacante $vacante): bool
    {
        return $usuario->can('ver_vacante') || $this->esDelEstablecimiento($usuario, $vacante);
    }

    /** Publicar vacantes es de los asociados: el gremio no publica por ellos. */
    public function create(User $usuario): bool
    {
        return $usuario->esAsociado() && $usuario->asociado_id !== null;
    }

    public function update(User $usuario, Vacante $vacante): bool
    {
        return $this->esDelEstablecimiento($usuario, $vacante);
    }

    /** Cerrar y reabrir no es editar el contenido: no pasa por aprobación. */
    public function cerrar(User $usuario, Vacante $vacante): bool
    {
        return $this->esDelEstablecimiento($usuario, $vacante);
    }

    /** Solo para contenido indebido, y solo la dirección. */
    public function delete(User $usuario, Vacante $vacante): bool
    {
        return $usuario->esSuperAdmin();
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->esSuperAdmin();
    }

    /**
     * Lo consulta el observer antes de dejar pasar cualquier publicación, así
     * que no se puede burlar manipulando el formulario.
     */
    public function publicar(User $usuario, Vacante $vacante): bool
    {
        return $usuario->can('publicar_vacante');
    }

    private function esDelEstablecimiento(User $usuario, Vacante $vacante): bool
    {
        return $usuario->esAsociado()
            && $usuario->asociado_id !== null
            && $usuario->asociado_id === $vacante->asociado_id;
    }
}
