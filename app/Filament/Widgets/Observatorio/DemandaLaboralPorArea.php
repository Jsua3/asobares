<?php

namespace App\Filament\Widgets\Observatorio;

use App\Enums\CargoDelSector;
use App\Panel\RanuraDeTema;
use App\Panel\SerieDelObservatorio;

/**
 * Vacantes publicadas por mes y por área, doce meses, apiladas — ver el
 * docblock de `MetricasDelObservatorio::calcularDemandaLaboralPorArea()`.
 *
 * Con la semilla de hoy las siete vacantes están todas en el mismo mes: no
 * hay ni serie que trazar, solo una barra solitaria. Apilar eso sugiere una
 * tendencia mensual que todavía no existe, así que la gráfica lo dice en vez
 * de dibujarlo.
 */
class DemandaLaboralPorArea extends GraficaDelObservatorio
{
    protected static ?int $sort = 5;

    public static function titulo(): string
    {
        return 'Demanda laboral por área';
    }

    public static function que(): string
    {
        return 'la demanda laboral por área y por mes';
    }

    /**
     * La ranura de paleta que le toca a cada área. El color de verdad vive en
     * `--asb-serie-N` (tokens.css) y lo escribe `panel-graficas.js` al pintar
     * y en cada cambio de tema, porque tres de estos siete colores no
     * sobreviven al fondo oscuro y necesitan un valor distinto ahí.
     *
     * Aquí solo se declara qué ranura usa cada área; el hexadecimal que se
     * manda como reserva es el del tema claro, y `ObservatorioTest` comprueba
     * que no se separe del token.
     */
    private const array RANURAS = [
        CargoDelSector::Administracion->value => 1,
        CargoDelSector::Cocina->value => 2,
        CargoDelSector::Barra->value => 3,
        CargoDelSector::Servicio->value => 4,
        CargoDelSector::Seguridad->value => 5,
        CargoDelSector::Aseo->value => 6,
        CargoDelSector::Otros->value => 7,
    ];

    protected function getData(): array
    {
        $serie = $this->serie();

        $datasets = [];
        foreach (CargoDelSector::cases() as $cargo) {
            $datasets[] = [
                'label' => $cargo->getLabel(),
                'data' => $serie->series[$cargo->getLabel()] ?? [],
                ...$this->relleno(self::RANURAS[$cargo->value]),
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
                'legend' => ['labels' => RanuraDeTema::vacia()],
            ],
            'scales' => [
                'x' => ['stacked' => true, 'ticks' => RanuraDeTema::vacia(), 'grid' => ['display' => false]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0], 'grid' => RanuraDeTema::vacia()],
            ],
        ];
    }

    protected function serie(): SerieDelObservatorio
    {
        return $this->metricas()->demandaLaboralPorArea();
    }
}
