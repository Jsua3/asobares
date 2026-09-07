<?php

namespace App\Filament\Widgets;

use App\Models\Municipio;
use App\Panel\RanuraDeTema;
use Filament\Widgets\ChartWidget;

class AsociadosPorMunicipio extends ChartWidget
{
    protected ?string $heading = 'Asociados publicados por municipio';

    protected static ?int $sort = 3;

    /**
     * En escritorio (`xl`, 6 columnas) comparte fila con el recaudo: este
     * widget ocupa 2. En `md` y móvil va a todo el ancho para que los doce
     * municipios se lean. Sin un span explícito heredaría el `1` de
     * `Widget` y quedaría en una sexta parte de la fila.
     */
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 2,
    ];

    protected function getData(): array
    {
        $municipios = Municipio::withCount(['asociados' => fn ($query) => $query->publicado()])
            ->orderByDesc('asociados_count')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Asociados',
                'data' => $municipios->pluck('asociados_count')->all(),
                // Pub Red como relleno funciona en los dos temas; lo que no
                // seguía el tema eran ticks y rejilla, y de eso se encarga el
                // plugin `panel-graficas.js`.
                'backgroundColor' => '#EE4137',
                'borderRadius' => 6,
            ]],
            'labels' => $municipios->pluck('nombre')->all(),
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
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                    'grid' => RanuraDeTema::vacia(),
                ],
                'x' => [
                    'ticks' => RanuraDeTema::vacia(),
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('ver_asociado') === true;
    }
}
