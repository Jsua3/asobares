<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

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
class SaludFinanciera extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    private ?MetricasDelObservatorio $metricas = null;

    /** El rótulo `n = …` va junto al título, no escondido en un tooltip. */
    public function getHeading(): string
    {
        return "Salud financiera, últimos 18 meses ({$this->serie()->rotuloDeMuestra()})";
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
                'y' => ['beginAtZero' => true, 'ticks' => [], 'grid' => []],
                'x' => ['ticks' => [], 'grid' => ['display' => false]],
            ],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('ver_observatorio') === true;
    }

    private function metricas(): MetricasDelObservatorio
    {
        return $this->metricas ??= app(MetricasDelObservatorio::class);
    }

    private function serie(): SerieDelObservatorio
    {
        return $this->metricas()->saludFinanciera();
    }
}
