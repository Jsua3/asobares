<?php

namespace App\Policies;

use App\Models\Postulacion;
use App\Models\User;

/**
 * Bandeja: las postulaciones nacen del formulario público y nadie las crea a
 * mano. El equipo del gremio supervisa; quien contrata es el asociado dueño
 * de la vacante, y es el único que cambia el estado de gestión.
 */
class PostulacionPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_postulacion');
    }

    public function view(User $usuario, Postulacion $postulacion): bool
    {
        return $usuario->can('ver_postulacion') || $this->esDeSuVacante($usuario, $postulacion);
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, Postulacion $postulacion): bool
    {
        return $usuario->can('editar_postulacion');
    }

    public function delete(User $usuario, Postulacion $postulacion): bool
    {
        return $usuario->can('eliminar_postulacion');
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_postulacion');
    }

    /** Marcar contactado o descartado desde /mi-cuenta. */
    public function gestionar(User $usuario, Postulacion $postulacion): bool
    {
        return $this->esDeSuVacante($usuario, $postulacion);
    }

    private function esDeSuVacante(User $usuario, Postulacion $postulacion): bool
    {
        return $usuario->esAsociado()
            && $usuario->asociado_id !== null
            && $usuario->asociado_id === $postulacion->vacante->asociado_id;
    }
}
