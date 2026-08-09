<?php

namespace App\Filament\Widgets\Observatorio;

use App\Enums\CargoDelSector;
use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Vacantes publicadas por mes y por área, doce meses, apiladas — ver el
 * docblock de `MetricasDelObservatorio::calcularDemandaLaboralPorArea()`.
 *
 * Con la semilla de hoy las siete vacantes están todas en el mismo mes: no
 * hay ni serie que trazar, solo una barra solitaria. Apilar eso sugiere una
 * tendencia mensual que todavía no existe, así que la gráfica lo dice en vez
 * de dibujarlo.
 */
class DemandaLaboralPorArea extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    private ?MetricasDelObservatorio $metricas = null;

    /** El rótulo `n = …` va junto al título, dibuje o no la gráfica. */
    public function getHeading(): string
    {
        return "Demanda laboral por área ({$this->serie()->rotuloDeMuestra()})";
    }

    /**
     * `ChartWidget::isEmpty()` de fábrica solo mira si `getData()` vino
     * vacío. Aquí el criterio es otro: puede haber una barra solitaria sin
     * que la muestra alcance el umbral de
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
            'que' => 'la demanda laboral por área y por mes',
        ]);
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        // Un color por cada una de las siete áreas del enum, tomados de la
        // paleta oficial de marca completa (Manual de Marca Asobares
        // Colombia): ninguno es una clase Tailwind cableada, así que la
        // guardia de tema no los vigila — igual que en `PresenciaPorMunicipio`.
        $colores = [
            CargoDelSector::Administracion->value => '#EE4137', // Pub Red
            CargoDelSector::Cocina->value => '#A4161A', // Wine
            CargoDelSector::Barra->value => '#C05299', // Ambient Purple
            CargoDelSector::Servicio->value => '#EA698B', // Ambient Rose
            CargoDelSector::Seguridad->value => '#282628', // Pub Grey
            CargoDelSector::Aseo->value => '#0B090A', // Pub Black
            CargoDelSector::Otros->value => '#F5F3F4', // Ambient White
        ];

        $datasets = [];
        foreach (CargoDelSector::cases() as $cargo) {
            $datasets[] = [
                'label' => $cargo->getLabel(),
                'data' => $serie->series[$cargo->getLabel()] ?? [],
                'backgroundColor' => $colores[$cargo->value],
            ];
        }

        return [
            'datasets' => $datasets,
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
            // Siete series apiladas necesitan leyenda para distinguirse;
            // `labels` vacío es donde `panel-graficas.js` escribe el color
            // de texto al cambiar de tema.
            'plugins' => [
                'legend' => ['labels' => []],
            ],
            'scales' => [
                'x' => ['stacked' => true, 'ticks' => [], 'grid' => ['display' => false]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => []],
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
        return $this->metricas()->demandaLaboralPorArea();
    }
}
