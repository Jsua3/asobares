<?php

namespace App\Policies;

use App\Models\Cartera;
use App\Models\User;

/**
 * Cartera: información financiera exclusiva de la dirección. La secretaría
 * no la ve. Se alimenta por importación de CSV, no a mano.
 */
class CarteraPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_cartera');
    }

    public function view(User $usuario, Cartera $cartera): bool
    {
        return $usuario->can('ver_cartera');
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, Cartera $cartera): bool
    {
        return $usuario->can('importar_cartera');
    }

    public function delete(User $usuario, Cartera $cartera): bool
    {
        return false;
    }

    public function importar(User $usuario): bool
    {
        return $usuario->can('importar_cartera');
    }
}
