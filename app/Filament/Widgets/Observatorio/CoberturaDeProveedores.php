<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\RanuraDeTema;
use App\Panel\SerieDelObservatorio;

/**
 * Proveedores publicados y vigentes por categoría, las siete del enum
 * aunque alguna esté en cero — ver el docblock de
 * `MetricasDelObservatorio::calcularCoberturaDeProveedores()` para el porqué.
 *
 * Con la semilla de hoy la base tiene apenas diez proveedores repartidos en
 * siete categorías (n < 30): dibujar barras ahí sugeriría una cobertura que
 * todavía no existe, así que esta gráfica decide no dibujar y lo dice.
 */
class CoberturaDeProveedores extends GraficaDelObservatorio
{
    protected static ?int $sort = 4;

    public static function titulo(): string
    {
        return 'Cobertura de proveedores';
    }

    public static function que(): string
    {
        return 'la cobertura de proveedores por categoría';
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            'datasets' => [[
                'label' => 'Proveedores',
                'data' => $serie->series['Proveedores'] ?? [],
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
        return $this->metricas()->coberturaDeProveedores();
    }
}
