<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoEvento: string implements HasColor, HasLabel
{
    case Evento = 'evento';
    case Capacitacion = 'capacitacion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Evento => 'Evento',
            self::Capacitacion => 'Capacitación',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Evento => 'primary',
            self::Capacitacion => 'info',
        };
    }
}
