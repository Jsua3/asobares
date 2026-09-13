<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * En qué punto va cada iniciativa del gremio.
 *
 * Es el vocabulario que la dirección usa en sus presentaciones: una
 * iniciativa nace en formulación, escala y termina en ejecución.
 */
enum EstadoIniciativa: string implements HasColor, HasLabel
{
    case Formulacion = 'formulacion';
    case Escalando = 'escalando';
    case EnEjecucion = 'en_ejecucion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Formulacion => 'En formulación',
            self::Escalando => 'Escalando',
            self::EnEjecucion => 'En ejecución',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Formulacion => 'gray',
            self::Escalando => 'warning',
            self::EnEjecucion => 'success',
        };
    }
}
