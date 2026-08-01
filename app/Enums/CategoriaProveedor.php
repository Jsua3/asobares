<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CategoriaProveedor: string implements HasLabel
{
    case Hielo = 'hielo';
    case Licores = 'licores';
    case Alimentos = 'alimentos';
    case Aseo = 'aseo';
    case Seguridad = 'seguridad';
    case Mantenimiento = 'mantenimiento';
    case Otros = 'otros';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hielo => 'Hielo',
            self::Licores => 'Licores',
            self::Alimentos => 'Alimentos',
            self::Aseo => 'Aseo',
            self::Seguridad => 'Seguridad',
            self::Mantenimiento => 'Mantenimiento',
            self::Otros => 'Otros',
        };
    }

    /** Icono heroicon que acompaña a la categoría en el sitio público. */
    public function icono(): string
    {
        return match ($this) {
            self::Hielo => 'heroicon-o-cube-transparent',
            self::Licores => 'heroicon-o-beaker',
            self::Alimentos => 'heroicon-o-shopping-bag',
            self::Aseo => 'heroicon-o-sparkles',
            self::Seguridad => 'heroicon-o-shield-check',
            self::Mantenimiento => 'heroicon-o-wrench-screwdriver',
            self::Otros => 'heroicon-o-ellipsis-horizontal-circle',
        };
    }
}
