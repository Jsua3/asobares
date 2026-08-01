<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoInscripcion: string implements HasColor, HasLabel
{
    case Registrada = 'registrada';
    case Confirmada = 'confirmada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Registrada => 'Registrada',
            self::Confirmada => 'Confirmada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Registrada => 'warning',
            self::Confirmada => 'success',
        };
    }
}
