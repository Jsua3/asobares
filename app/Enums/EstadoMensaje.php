<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoMensaje: string implements HasColor, HasLabel
{
    case Nuevo = 'nuevo';
    case EnTramite = 'en_tramite';
    case Respondido = 'respondido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nuevo => 'Nuevo',
            self::EnTramite => 'En trámite',
            self::Respondido => 'Respondido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Nuevo => 'danger',
            self::EnTramite => 'warning',
            self::Respondido => 'success',
        };
    }
}
