<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Seguimiento de una persona que dejó sus datos: lo comparten las
 * postulaciones a una vacante y los perfiles del banco de talento.
 */
enum EstadoDeGestion: string implements HasColor, HasLabel
{
    case Nuevo = 'nuevo';
    case Contactado = 'contactado';
    case Descartado = 'descartado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nuevo => 'Nuevo',
            self::Contactado => 'Contactado',
            self::Descartado => 'Descartado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Nuevo => 'gray',
            self::Contactado => 'success',
            self::Descartado => 'danger',
        };
    }
}
