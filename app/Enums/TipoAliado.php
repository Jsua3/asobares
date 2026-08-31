<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Los dos niveles de aliado que pidió la revisión del 28 de agosto (OBS3-04).
 *
 * No es una etiqueta decorativa: el directivo pidió que las instituciones
 * --Asobares Colombia, la Cámara de Comercio, el Comité Intergremial y la
 * Gobernación-- se vieran por encima y aparte de las marcas con convenio
 * comercial, porque respaldan al gremio en vez de venderle a sus afiliados
 * (`R21 02:19–03:26`). Mezclarlas en una sola tira de logos, que es lo que
 * había, dice que son lo mismo.
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

    /** Lo que se explica en el panel a quien clasifica un aliado. */
    public function descripcion(): string
    {
        return match ($this) {
            self::Institucional => 'Entidades que respaldan al gremio: agremiaciones, cámaras de comercio y entidades públicas.',
            self::Comercial => 'Marcas con convenio para los afiliados.',
        };
    }
}
