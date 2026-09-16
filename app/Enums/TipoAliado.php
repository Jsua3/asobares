<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Los dos niveles de aliado.
 *
 * No es una etiqueta decorativa: las instituciones --Asobares Colombia, la
 * Cámara de Comercio, el Comité Intergremial y la Gobernación-- se ven por
 * encima y aparte de las marcas con convenio comercial, porque respaldan al
 * gremio en vez de venderle a sus afiliados. Mezclarlas en una sola tira de
 * logos dice que son lo mismo.
 */
enum TipoAliado: string implements HasLabel
{
    case Institucional = 'institucional';
    case Comercial = 'comercial';

    public function getLabel(): string
    {
        return match ($this) {
            self::Institucional => 'Institucional',
            self::Comercial => 'Comercial',
        };
    }
}
