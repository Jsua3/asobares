<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TipoArtista: string implements HasLabel
{
    case Dj = 'dj';
    case Banda = 'banda';
    case Solista = 'solista';
    case Otro = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dj => 'DJ',
            self::Banda => 'Banda',
            self::Solista => 'Solista',
            self::Otro => 'Otro',
        };
    }
}
