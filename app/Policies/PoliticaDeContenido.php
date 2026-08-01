<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Política base del contenido del gremio.
 *
 * La frontera que importa: `publicar` es un permiso aparte de `editar`.
 * La secretaría redacta y corrige, pero solo la dirección publica (RF-37).
 */
abstract class PoliticaDeContenido
{
    /** Nombre del recurso en los permisos: ver_{recurso}, publicar_{recurso}... */
    abstract protected function recurso(): string;

    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_'.$this->recurso());
    }

    public function view(User $usuario, Model $modelo): bool
    {
        return $usuario->can('ver_'.$this->recurso());
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear_'.$this->recurso());
    }

    public function update(User $usuario, Model $modelo): bool
    {
        return $usuario->can('editar_'.$this->recurso());
    }

    public function delete(User $usuario, Model $modelo): bool
    {
        return $usuario->can('eliminar_'.$this->recurso());
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_'.$this->recurso());
    }

    /**
     * Único permiso que separa a la dirección de la secretaría.
     * Lo consulta el observer antes de dejar pasar cualquier publicación,
     * así que no se puede burlar manipulando el formulario.
     */
    public function publicar(User $usuario, Model $modelo): bool
    {
        return $usuario->can('publicar_'.$this->recurso());
    }
}
