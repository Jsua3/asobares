<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UbicacionPublicidad: string implements HasLabel
{
    case Inicio = 'inicio';
    case Directorio = 'directorio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Inicio => 'Inicio',
            self::Directorio => 'Directorio',
        };
    }
}
