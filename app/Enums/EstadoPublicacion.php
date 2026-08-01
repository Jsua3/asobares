<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Flujo editorial compartido por todo el contenido publicable.
 *
 * Un subadmin nunca puede saltar a Publicado: al guardar, su contenido
 * cae en PendienteAprobacion y espera al super admin (RF-37).
 */
enum EstadoPublicacion: string implements HasColor, HasLabel
{
    case Borrador = 'borrador';
    case PendienteAprobacion = 'pendiente_aprobacion';
    case Publicado = 'publicado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::PendienteAprobacion => 'Pendiente de aprobación',
            self::Publicado => 'Publicado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::PendienteAprobacion => 'warning',
            self::Publicado => 'success',
        };
    }

    public function esVisiblePublicamente(): bool
    {
        return $this === self::Publicado;
    }
}
