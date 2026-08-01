<?php

namespace App\Models\Concerns;

use App\Enums\EstadoPublicacion;
use Illuminate\Database\Eloquent\Builder;

/**
 * Flujo editorial compartido: borrador -> pendiente_aprobacion -> publicado.
 *
 * Quien no tenga permiso de publicar jamas deja un registro en Publicado;
 * de eso se encargan las policies y el observer (RF-37).
 */
trait EsPublicable
{
    /** Solo el contenido aprobado por la dirección llega al sitio público. */
    public function scopePublicado(Builder $query): Builder
    {
        return $query->where('estado', EstadoPublicacion::Publicado);
    }

    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('estado', EstadoPublicacion::PendienteAprobacion);
    }

    public function estaPublicado(): bool
    {
        return $this->estado === EstadoPublicacion::Publicado;
    }
}
