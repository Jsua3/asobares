<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoVacante: string implements HasColor, HasLabel
{
    case TiempoCompleto = 'tiempo_completo';
    case PorTurnos = 'por_turnos';
    case Momentaneo = 'momentaneo';

    public function getLabel(): string
    {
        return match ($this) {
            self::TiempoCompleto => 'Tiempo completo',
            self::PorTurnos => 'Por turnos',
            self::Momentaneo => 'Momentáneo (una o dos noches)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TiempoCompleto => 'success',
            self::PorTurnos => 'info',
            self::Momentaneo => 'warning',
        };
    }

    /**
     * Un empleo de una noche sin fecha se queda colgado en el muro para
     * siempre: para ese tipo la fecha límite no es opcional.
     */
    public function exigeFechaLimite(): bool
    {
        return $this === self::Momentaneo;
    }
}
