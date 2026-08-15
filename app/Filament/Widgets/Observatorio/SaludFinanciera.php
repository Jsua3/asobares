<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\RanuraDeTema;
use App\Panel\SerieDelObservatorio;

/**
 * Recaudo mensual a dieciocho meses: horizonte analítico, no operativo.
 *
 * Es la misma familia de dato que `RecaudoMensual` del tablero, pero con un
 * propósito distinto a propósito: `RecaudoMensual` mira el año en curso para
 * responder «¿vamos bien este mes?», mientras que esta gráfica cubre dieciocho
 * meses porque el observatorio es el argumento que la dirección lleva a una
 * alcaldía, y una tendencia necesita más de un año calendario para leerse. No
 * es una duplicación: son dos preguntas distintas sobre el mismo recaudo.
 *
 * La tasa de mora no vive aquí: ver el docblock de
 * `MetricasDelObservatorio::calcularTasaDeMoraActual()` para el porqué.
 */
class SaludFinanciera extends GraficaDelObservatorio
{
    protected static ?int $sort = 3;

    public static function titulo(): string
    {
        return 'Salud financiera, últimos 18 meses';
    }

    public static function que(): string
    {
        return 'la salud financiera del gremio';
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            'datasets' => [[
                'label' => 'Recaudo (COP)',
                'data' => $serie->series['Recaudo (COP)'] ?? [],
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $serie->etiquetas,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => RanuraDeTema::vacia(), 'grid' => RanuraDeTema::vacia()],
                'x' => ['ticks' => RanuraDeTema::vacia(), 'grid' => ['display' => false]],
            ],
        ];
    }

    protected function serie(): SerieDelObservatorio
    {
        return $this->metricas()->saludFinanciera();
    }
}
