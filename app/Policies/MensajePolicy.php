<?php

namespace App\Policies;

use App\Models\Mensaje;
use App\Models\User;

/**
 * Bandeja de entrada. La secretaría la gestiona completa; nadie la crea a
 * mano: los mensajes entran por los formularios públicos.
 */
class MensajePolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_mensaje');
    }

    public function view(User $usuario, Mensaje $mensaje): bool
    {
        return $usuario->can('ver_mensaje');
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, Mensaje $mensaje): bool
    {
        return $usuario->can('editar_mensaje');
    }

    public function delete(User $usuario, Mensaje $mensaje): bool
    {
        return $usuario->can('eliminar_mensaje');
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_mensaje');
    }
}
