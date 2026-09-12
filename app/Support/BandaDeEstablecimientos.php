<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * La franja de establecimientos de la portada.
 *
 * La consulta sigue eligiendo un cupo estable y alfabético (OBS3-06).
 * Aquí solo se gira el punto de partida para que cada sesión no vea
 * siempre los mismos tres primero. No toca el directorio ni la base.
 */
class BandaDeEstablecimientos
{
    public const int TOPE = 12;

    /**
     * @template TClave of array-key
     * @template TValor
     *
     * @param  Collection<TClave, TValor>  $items
     * @return Collection<int, TValor>
     */
    public static function paraLaPortada(Collection $items): Collection
    {
        return self::rotar($items, self::origen((string) session()->getId(), $items->count()));
    }

    public static function origen(string $semilla, int $total): int
    {
        if ($total < 2 || $semilla === '') {
            return 0;
        }

        return abs(crc32($semilla)) % $total;
    }

    /**
     * @template TClave of array-key
     * @template TValor
     *
     * @param  Collection<TClave, TValor>  $items
     * @return Collection<int, TValor>
     */
    public static function rotar(Collection $items, int $origen): Collection
    {
        $total = $items->count();

        if ($total < 2) {
            return $items->values();
        }

        $desplazamiento = $origen % $total;

        if ($desplazamiento === 0) {
            return $items->values();
        }

        return $items->slice($desplazamiento)->concat($items->take($desplazamiento))->values();
    }
}
