<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoMensaje: string implements HasColor, HasLabel
{
    case Contacto = 'contacto';
    case Afiliacion = 'afiliacion';
    case Pqr = 'pqr';
    case Aliado = 'aliado';
    case Proveedor = 'proveedor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Contacto => 'Contacto general',
            self::Afiliacion => 'Solicitud de afiliación',
            self::Pqr => 'PQR',
            self::Aliado => 'Quiero ser aliado',
            self::Proveedor => 'Quiero aparecer en la bolsa de proveedores',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Contacto => 'gray',
            self::Afiliacion => 'success',
            self::Pqr => 'danger',
            self::Aliado => 'info',
            self::Proveedor => 'warning',
        };
    }

    /** Solo las PQR reciben número de radicado consecutivo. */
    public function requiereRadicado(): bool
    {
        return $this === self::Pqr;
    }
}
