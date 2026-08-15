<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\RanuraDeTema;
use App\Panel\SerieDelObservatorio;

/**
 * Asociados publicados por categoría, de mayor a menor.
 *
 * Mismo patrón que `AsociadosPorMunicipio` del tablero (barras verticales,
 * una sola serie), pero agrupado por categoría en vez de por municipio y
 * leyendo de `MetricasDelObservatorio::composicionDelSector()` en vez de
 * consultar por su cuenta.
 *
 * Con la semilla de hoy hay veinticuatro asociados publicados (n = 24, bajo
 * el umbral de treinta): esta gráfica se clasificó como «sólida» cuando se
 * escribió y nadie volvió a mirarla cuando su muestra bajó. Heredar de
 * `GraficaDelObservatorio` es lo que evita que eso vuelva a pasar: la regla
 * de la muestra ya no depende de que alguien la copie a mano aquí.
 */
class ComposicionDelSector extends GraficaDelObservatorio
{
    protected static ?int $sort = 2;

    public static function titulo(): string
    {
        return 'Composición del sector';
    }

    public static function que(): string
    {
        return 'la composición del sector por categoría';
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            'datasets' => [[
                'label' => 'Asociados',
                'data' => $serie->series['Asociados'] ?? [],
                // Pub Red como relleno funciona en los dos temas; lo que no
                // seguía el tema eran ticks y rejilla, y de eso se encarga el
                // plugin `panel-graficas.js`.
                'backgroundColor' => '#EE4137',
                'borderRadius' => 6,
            ]],
            'labels' => $serie->etiquetas,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => RanuraDeTema::vacia()],
                'x' => ['ticks' => RanuraDeTema::vacia(), 'grid' => ['display' => false]],
            ],
        ];
    }

    protected function serie(): SerieDelObservatorio
    {
        return $this->metricas()->composicionDelSector();
    }
}
