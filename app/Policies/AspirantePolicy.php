<?php

namespace App\Policies;

use App\Models\Aspirante;
use App\Models\User;

class AspirantePolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_aspirante');
    }

    public function view(User $usuario, Aspirante $aspirante): bool
    {
        return $usuario->can('ver_aspirante');
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, Aspirante $aspirante): bool
    {
        return $usuario->can('editar_aspirante');
    }

    public function delete(User $usuario, Aspirante $aspirante): bool
    {
        return $usuario->can('eliminar_aspirante');
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_aspirante');
    }
}
