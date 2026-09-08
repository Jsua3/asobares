<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoSolicitudAfiliacion: string implements HasColor, HasLabel
{
    case Pendiente = 'pendiente';
    case EnRevision = 'en_revision';
    case Visita = 'visita';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::EnRevision => 'En revisión',
            self::Visita => 'Visita',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::EnRevision => 'info',
            self::Visita => 'primary',
            self::Aprobada => 'success',
            self::Rechazada => 'danger',
        };
    }
}
