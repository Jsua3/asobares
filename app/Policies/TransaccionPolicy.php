<?php

namespace App\Policies;

use App\Models\Transaccion;
use App\Models\User;

/**
 * Las transacciones son de solo lectura: las escribe la pasarela, nunca una
 * persona. Ni siquiera la dirección puede editarlas.
 */
class TransaccionPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_transaccion');
    }

    public function view(User $usuario, Transaccion $transaccion): bool
    {
        return $usuario->can('ver_transaccion');
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, Transaccion $transaccion): bool
    {
        return false;
    }

    public function delete(User $usuario, Transaccion $transaccion): bool
    {
        return false;
    }
}
