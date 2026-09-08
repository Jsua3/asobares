<?php

namespace App\Policies;

use App\Models\SolicitudAfiliacion;
use App\Models\User;

class SolicitudAfiliacionPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver_solicitud_afiliacion');
    }

    public function view(User $usuario, SolicitudAfiliacion $solicitud): bool
    {
        return $usuario->can('ver_solicitud_afiliacion');
    }

    public function create(User $usuario): bool
    {
        return false;
    }

    public function update(User $usuario, SolicitudAfiliacion $solicitud): bool
    {
        return $usuario->can('editar_solicitud_afiliacion');
    }

    public function delete(User $usuario, SolicitudAfiliacion $solicitud): bool
    {
        return $usuario->can('eliminar_solicitud_afiliacion');
    }

    public function deleteAny(User $usuario): bool
    {
        return $usuario->can('eliminar_solicitud_afiliacion');
    }
}
