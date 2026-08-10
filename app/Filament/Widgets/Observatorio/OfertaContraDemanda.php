<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\SerieDelObservatorio;

/**
 * Demanda (vacantes publicadas) contra oferta (aspirantes + postulaciones)
 * por área — ver el docblock de
 * `MetricasDelObservatorio::calcularOfertaContraDemanda()`.
 *
 * Con la semilla de hoy hay siete vacantes, siete aspirantes y cuatro
 * postulaciones repartidos en siete áreas. Es justo el argumento
 * institucional que el observatorio existe para sostener ante el gremio y
 * una alcaldía, así que es una de las gráficas que menos puede permitirse
 * fingir una tendencia que la muestra no aguanta — ver el docblock de
 * `SerieDelObservatorio::hayMuestraSuficiente()`: con dos conjuntos de datos,
 * el umbral se le exige al más flaco de los dos, no a su suma.
 */
class OfertaContraDemanda extends GraficaDelObservatorio
{
    protected static ?int $sort = 6;

    public static function titulo(): string
    {
        return 'Oferta contra demanda';
    }

    public static function que(): string
    {
        return 'la oferta contra la demanda laboral';
    }

    protected function getData(): array
    {
        $serie = $this->serie();

        return [
            // Ranuras 2 y 3 de la paleta categórica de la base (Wine y
            // Ambient Purple). El color efectivo lo escribe
            // `panel-graficas.js` desde `--asb-serie-N`: Wine no sobrevive al
            // fondo del tema oscuro y necesita su propio valor allí.
            'datasets' => [
                [
                    'label' => 'Demanda',
                    'data' => $serie->series['Demanda'] ?? [],
                    ...$this->relleno(2),
                ],
                [
                    'label' => 'Oferta',
                    'data' => $serie->series['Oferta'] ?? [],
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

    protected function serie(): SerieDelObservatorio
    {
        return $this->metricas()->ofertaContraDemanda();
    }
}
