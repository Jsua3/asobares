<?php

namespace App\Support;

final class FormatoMoneda
{
    public static function pesos(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '$0';
        }

        return '$'.number_format((float) $valor, 0, ',', '.');
    }
}
