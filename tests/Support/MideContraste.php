<?php

namespace Tests\Support;

/**
 * La fórmula de contraste de WCAG 2.1, en un solo sitio.
 *
 * Vivía copiada dentro de `ObservatorioTest`, que la usa para vigilar que la
 * serie de las gráficas del panel se despegue de su superficie. `FocoVisibleTest`
 * necesita exactamente la misma cuenta para el indicador de foco del sitio
 * público, y una segunda copia es una segunda oportunidad de que las dos se
 * separen sin que nadie lo note: el día que una se corrija —el 0.03928 del
 * umbral, el 0.05 del término aditivo— la otra seguiría midiendo con la de
 * antes y dando por buenos colores que ya no lo son.
 *
 * `componer()` no estaba en la copia original y es lo que hace medible un
 * indicador translúcido: un `box-shadow` o un borde con alfa no se compara
 * contra nada, porque el color que se ve no es el declarado sino el que resulta
 * de mezclarlo con lo que tiene detrás. Es la cuenta que hace el navegador y la
 * única forma honesta de decir si un `ring-marca-500/60` cumple el 3:1.
 */
trait MideContraste
{
    /** Razón de contraste WCAG 2.1 entre dos colores hexadecimales. */
    protected function contraste(string $unColor, string $otroColor): float
    {
        $uno = $this->luminanciaRelativa($unColor);
        $otro = $this->luminanciaRelativa($otroColor);

        return (max($uno, $otro) + 0.05) / (min($uno, $otro) + 0.05);
    }

    /** Luminancia relativa WCAG 2.1, la que entra en la razón de contraste. */
    protected function luminanciaRelativa(string $hex): float
    {
        $canales = array_map(
            static function (string $par): float {
                $canal = hexdec($par) / 255;

                return $canal <= 0.03928 ? $canal / 12.92 : (($canal + 0.055) / 1.055) ** 2.4;
            },
            str_split(ltrim($hex, '#'), 2)
        );

        return 0.2126 * $canales[0] + 0.7152 * $canales[1] + 0.0722 * $canales[2];
    }

    /**
     * Compone `$frente` al alfa dado sobre `$fondo` y devuelve el hexadecimal
     * opaco resultante, redondeado a 8 bits por canal igual que hace el
     * navegador antes de pintarlo en sRGB.
     */
    protected function componer(string $frente, float $alfa, string $fondo): string
    {
        $arriba = str_split(ltrim($frente, '#'), 2);
        $abajo = str_split(ltrim($fondo, '#'), 2);

        $mezcla = '#';

        foreach ([0, 1, 2] as $canal) {
            $valor = hexdec($arriba[$canal]) * $alfa + hexdec($abajo[$canal]) * (1 - $alfa);
            $mezcla .= str_pad(dechex((int) round($valor)), 2, '0', STR_PAD_LEFT);
        }

        return $mezcla;
    }
}
