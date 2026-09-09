<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoPublicidad: string implements HasColor, HasLabel
{
    case Borrador = 'borrador';
    case PendientePago = 'pendiente_pago';
    case Pagada = 'pagada';
    case PendienteAprobacion = 'pendiente_aprobacion';
    case Publicada = 'publicada';
    case Rechazada = 'rechazada';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::PendientePago => 'Pendiente de pago',
            self::Pagada => 'Pagada',
            self::PendienteAprobacion => 'Pendiente de aprobacion',
            self::Publicada => 'Publicada',
            self::Rechazada => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::PendientePago => 'warning',
            self::Pagada => 'info',
            self::PendienteAprobacion => 'warning',
            self::Publicada => 'success',
            self::Rechazada => 'danger',
        };
    }
}
