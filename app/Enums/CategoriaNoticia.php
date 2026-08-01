<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CategoriaNoticia: string implements HasColor, HasLabel
{
    case Noticia = 'noticia';
    case Observatorio = 'observatorio';
    case Proyecto = 'proyecto';

    public function getLabel(): string
    {
        return match ($this) {
            self::Noticia => 'Noticia',
            self::Observatorio => 'Observatorio económico',
            self::Proyecto => 'Próximos proyectos',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Noticia => 'gray',
            self::Observatorio => 'info',
            self::Proyecto => 'warning',
        };
    }
}
