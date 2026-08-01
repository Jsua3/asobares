<?php

namespace App\Policies;

use App\Models\Inscripcion;
use App\Models\User;

class InscripcionPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_inscripcion');
    }

    public function view(User $usuario, Inscripcion $inscripcion): bool
    {
        return $usuario->can('ver_inscripcion');
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, Inscripcion $inscripcion): bool
    {
        return $usuario->can('editar_inscripcion');
    }

    public function delete(User $usuario, Inscripcion $inscripcion): bool
    {
        return $usuario->can('eliminar_inscripcion');
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_inscripcion');
    }
}
