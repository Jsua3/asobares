<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

/**
 * Asociados publicados por categoría, de mayor a menor.
 *
 * Mismo patrón que `AsociadosPorMunicipio` del tablero (barras verticales,
 * una sola serie), pero agrupado por categoría en vez de por municipio y
 * leyendo de `MetricasDelObservatorio::composicionDelSector()` en vez de
 * consultar por su cuenta.
 */
class ComposicionDelSector extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    private ?MetricasDelObservatorio $metricas = null;

    /** El rótulo `n = …` va junto al título, no escondido en un tooltip. */
    public function getHeading(): string
    {
        return "Composición del sector ({$this->serie()->rotuloDeMuestra()})";
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
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => []],
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
        return $this->metricas()->composicionDelSector();
    }
}
