<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\SerieDelObservatorio;

/**
 * Presencia del gremio por municipio: asociados, vacantes y consultas de la
 * guía, las tres señales que sostiene `MetricasDelObservatorio::presenciaPorMunicipio()`.
 *
 * Barras horizontales (`indexAxis: 'y'`): doce municipios con nombre largo
 * (p. ej. «Córdoba», «Génova») no caben en un eje vertical sin solaparse.
 *
 * Tres conjuntos de datos con tamaños de muestra muy distintos (asociados,
 * vacantes, consultas): ver el docblock de
 * `SerieDelObservatorio::hayMuestraSuficiente()` para el porqué esta gráfica
 * exige el umbral al más flaco de los tres, no a su suma.
 */
class PresenciaPorMunicipio extends GraficaDelObservatorio
{
    protected static ?int $sort = 1;

    public static function titulo(): string
    {
        return 'Presencia por municipio';
    }

    public static function que(): string
    {
        return 'la presencia del gremio por municipio';
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            // Las tres primeras ranuras de la paleta categórica de la base
            // (Pub Red, Wine, Ambient Purple). El color efectivo lo escribe
            // `panel-graficas.js` desde `--asb-serie-N`, porque Wine no
            // sobrevive al fondo del tema oscuro.
            'datasets' => [
                [
                    'label' => 'Asociados',
                    'data' => $serie->series['Asociados'] ?? [],
                    ...$this->relleno(1),
                ],
                [
                    'label' => 'Vacantes',
                    'data' => $serie->series['Vacantes'] ?? [],
                    ...$this->relleno(2),
                ],
                [
                    'label' => 'Consultas de la guía',
                    'data' => $serie->series['Consultas de la guía'] ?? [],
                    ...$this->relleno(3),
                ],
            ],
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
            'indexAxis' => 'y',
            // Tres series necesitan leyenda para distinguirse; `labels` se
            // declara vacío para que `panel-graficas.js` tenga dónde escribir
            // el color de texto al cambiar de tema.
            'plugins' => [
                'legend' => ['labels' => []],
            ],
            'scales' => [
                // Eje de valor (horizontal en barras invertidas).
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => []],
                // Eje de categoría (los municipios): sin rejilla, como en
                // el resto de gráficas de barras del panel.
                'y' => ['ticks' => [], 'grid' => ['display' => false]],
            ],
        ];
    }

    protected function serie(): SerieDelObservatorio
    {
        return $this->metricas()->presenciaPorMunicipio();
    }
}
