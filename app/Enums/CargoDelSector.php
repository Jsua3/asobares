<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Áreas de trabajo de un establecimiento nocturno.
 *
 * El cargo libre sigue existiendo como título del puesto; esta categoría es
 * la que permite filtrar el muro y, más adelante, cruzar vacantes con el
 * banco de talento sin depender de cómo escriba cada quien «bartender».
 */
enum CargoDelSector: string implements HasLabel
{
    case Administracion = 'administracion';
    case Cocina = 'cocina';
    case Barra = 'barra';
    case Servicio = 'servicio';
    case Seguridad = 'seguridad';
    case Aseo = 'aseo';
    case Otros = 'otros';

    public function getLabel(): string
    {
        return match ($this) {
            self::Administracion => 'Administración',
            self::Cocina => 'Cocina',
            self::Barra => 'Barra',
            self::Servicio => 'Servicio y meseros',
            self::Seguridad => 'Seguridad',
            self::Aseo => 'Aseo',
            self::Otros => 'Otros',
        };
    }
}
