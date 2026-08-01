<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConceptoTransaccion: string implements HasLabel
{
    case Afiliacion = 'afiliacion';
    case Evento = 'evento';
    case Mensualidad = 'mensualidad';

    public function getLabel(): string
    {
        return match ($this) {
            self::Afiliacion => 'Afiliación',
            self::Evento => 'Inscripción a evento',
            self::Mensualidad => 'Mensualidad',
        };
    }
}
