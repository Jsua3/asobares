<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

/**
 * Presencia del gremio por municipio: asociados, vacantes y consultas de la
 * guía, las tres señales que sostiene `MetricasDelObservatorio::presenciaPorMunicipio()`.
 *
 * Barras horizontales (`indexAxis: 'y'`): doce municipios con nombre largo
 * (p. ej. «Córdoba», «Génova») no caben en un eje vertical sin solaparse.
 */
class PresenciaPorMunicipio extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    private ?MetricasDelObservatorio $metricas = null;

    /** El rótulo `n = …` va junto al título, no escondido en un tooltip. */
    public function getHeading(): string
    {
        return "Presencia por municipio ({$this->serie()->rotuloDeMuestra()})";
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            'datasets' => [
                [
                    'label' => 'Asociados',
                    'data' => $serie->series['Asociados'] ?? [],
                    // Paleta secundaria oficial de marca (Pub Red, Wine,
                    // Ambient Purple): tres series necesitan tres colores
                    // distinguibles y ninguno es Tailwind cableado, así que
                    // la guardia de tema no los vigila.
                    'backgroundColor' => '#EE4137',
                ],
                [
                    'label' => 'Vacantes',
                    'data' => $serie->series['Vacantes'] ?? [],
                    'backgroundColor' => '#A4161A',
                ],
                [
                    'label' => 'Consultas de la guía',
                    'data' => $serie->series['Consultas de la guía'] ?? [],
                    'backgroundColor' => '#C05299',
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
        return $this->metricas()->presenciaPorMunicipio();
    }
}
