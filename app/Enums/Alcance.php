<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * De quién es un beneficio: de la Nacional, del capítulo o de un municipio
 * (Acta 07, A-01).
 *
 * No es una etiqueta decorativa. Un afiliado de Circasia que lee «descuento con
 * la Alcaldía» necesita saber si es la suya o la de Armenia, y un beneficio que
 * negoció ASOBARES Colombia no se le puede apuntar el capítulo.
 *
 * El municipal NO se anuncia como «Municipal» en el sitio: se anuncia con el
 * nombre del municipio, que es la información que el lector necesita. La
 * etiqueta de aquí es la del panel, para quien clasifica.
 */
enum Alcance: string implements HasColor, HasLabel
{
    case Nacional = 'nacional';
    case Departamental = 'departamental';
    case Municipal = 'municipal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nacional => 'ASOBARES Colombia',
            self::Departamental => 'ASOBARES Quindío',
            self::Municipal => 'Municipal',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Nacional => 'info',
            self::Departamental => 'success',
            self::Municipal => 'warning',
        };
    }

    /** Lo que se explica en el panel a quien clasifica un beneficio. */
    public function descripcion(): string
    {
        return match ($this) {
            self::Nacional => 'Lo negoció la Nacional y lo tiene cualquier afiliado del país.',
            self::Departamental => 'Lo consiguió el capítulo Quindío para sus afiliados.',
            self::Municipal => 'Solo aplica en un municipio; hay que decir cuál.',
        };
    }

    /** El único alcance que exige municipio, y el único que lo admite. */
    public function exigeMunicipio(): bool
    {
        return $this === self::Municipal;
    }
}
