<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_usuario');
    }

    public function view(User $usuario, User $objetivo): bool
    {
        return $usuario->can('ver_usuario');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear_usuario');
    }

    public function update(User $usuario, User $objetivo): bool
    {
        return $usuario->can('editar_usuario');
    }

    /** Nadie se borra a sí mismo: dejaría el panel sin dueño. */
    public function delete(User $usuario, User $objetivo): bool
    {
        return $usuario->can('eliminar_usuario') && $usuario->isNot($objetivo);
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_usuario');
    }
}
