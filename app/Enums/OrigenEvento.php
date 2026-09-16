<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrigenEvento: string implements HasColor, HasLabel
{
    case Asobares = 'asobares';
    case Aliado = 'aliado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Asobares => 'ASOBARES',
            self::Aliado => 'Aliado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Asobares => 'danger',
            self::Aliado => 'info',
        };
    }
}
