<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoTransaccion: string implements HasColor, HasLabel
{
    case Pendiente = 'pendiente';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Aprobada => 'Aprobada',
            self::Rechazada => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Aprobada => 'success',
            self::Rechazada => 'danger',
        };
    }
}
