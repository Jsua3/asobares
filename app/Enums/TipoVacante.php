<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoVacante: string implements HasColor, HasLabel
{
    case TiempoCompleto = 'tiempo_completo';
    case PorTurnos = 'por_turnos';

    public function getLabel(): string
    {
        return match ($this) {
            self::TiempoCompleto => 'Tiempo completo',
            self::PorTurnos => 'Por turnos',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TiempoCompleto => 'success',
            self::PorTurnos => 'info',
        };
    }
}
