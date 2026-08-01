<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * La directiva prefiere PSE de forma explícita; la cuenta del gremio es Itaú.
 */
enum MetodoPago: string implements HasLabel
{
    case Pse = 'pse';
    case Tarjeta = 'tarjeta';
    case Otro = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pse => 'PSE',
            self::Tarjeta => 'Tarjeta',
            self::Otro => 'Otro',
        };
    }
}
