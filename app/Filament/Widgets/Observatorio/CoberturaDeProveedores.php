<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Proveedores publicados y vigentes por categoría, las siete del enum
 * aunque alguna esté en cero — ver el docblock de
 * `MetricasDelObservatorio::calcularCoberturaDeProveedores()` para el porqué.
 *
 * Con la semilla de hoy la base tiene apenas diez proveedores repartidos en
 * siete categorías (n < 30): dibujar barras ahí sugeriría una cobertura que
 * todavía no existe, así que esta gráfica decide no dibujar y lo dice.
 */
class CoberturaDeProveedores extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    private ?MetricasDelObservatorio $metricas = null;

    /** El rótulo `n = …` va junto al título, dibuje o no la gráfica. */
    public function getHeading(): string
    {
        return "Cobertura de proveedores ({$this->serie()->rotuloDeMuestra()})";
    }

    /**
     * `ChartWidget::isEmpty()` de fábrica solo mira si `getData()` vino
     * vacío. Aquí el criterio es otro: puede haber datos (siete categorías,
     * varias en cero) sin que la muestra alcance el umbral de
     * `SerieDelObservatorio::MUESTRA_MINIMA`.
     */
    public function isEmpty(): bool
    {
        return ! $this->serie()->hayMuestraSuficiente();
    }

    /**
     * `chart-widget.blade.php` (vendor/filament/widgets) ya bifurca: si
     * `isEmpty()` es cierto, rinde esto en vez del canvas. Es el mecanismo
     * nativo de Filament para que un `ChartWidget` decida no dibujar — no
     * hace falta un `Widget` con vista propia.
     */
    public function getEmptyState(): View
    {
        return view('components.panel.sin-muestra', [
            'serie' => $this->serie(),
            'que' => 'la cobertura de proveedores por categoría',
        ]);
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
        return $this->metricas()->coberturaDeProveedores();
    }
}
