<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Demanda (vacantes publicadas) contra oferta (aspirantes + postulaciones)
 * por área — ver el docblock de
 * `MetricasDelObservatorio::calcularOfertaContraDemanda()`.
 *
 * Con la semilla de hoy hay siete vacantes, siete aspirantes y cuatro
 * postulaciones repartidos en siete áreas: n = 17. Es justo el argumento
 * institucional que el observatorio existe para sostener ante el gremio y
 * una alcaldía, así que es la primera gráfica que no puede permitirse
 * fingir una tendencia que la muestra no aguanta.
 */
class OfertaContraDemanda extends ChartWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    private ?MetricasDelObservatorio $metricas = null;

    /** El rótulo `n = …` va junto al título, dibuje o no la gráfica. */
    public function getHeading(): string
    {
        return "Oferta contra demanda ({$this->serie()->rotuloDeMuestra()})";
    }

    /**
     * `ChartWidget::isEmpty()` de fábrica solo mira si `getData()` vino
     * vacío. Aquí el criterio es otro: hay datos (n = 17), pero no alcanzan
     * el umbral de `SerieDelObservatorio::MUESTRA_MINIMA`, y dibujar barras
     * sobre esa muestra sugeriría una tendencia que no existe.
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
            'que' => 'la oferta contra la demanda laboral',
        ]);
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            'datasets' => [
                [
                    'label' => 'Demanda',
                    'data' => $serie->series['Demanda'] ?? [],
                    'backgroundColor' => '#A4161A',
                ],
                [
                    'label' => 'Oferta',
                    'data' => $serie->series['Oferta'] ?? [],
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
            // Dos series necesitan leyenda para distinguirse; `labels` vacío
            // es donde `panel-graficas.js` escribe el color de texto al
            // cambiar de tema.
            'plugins' => [
                'legend' => ['labels' => []],
            ],
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
        return $this->metricas()->ofertaContraDemanda();
    }
}
