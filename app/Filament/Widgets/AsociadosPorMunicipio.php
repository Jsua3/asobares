<?php

namespace App\Filament\Widgets;

use App\Models\Municipio;
use Filament\Widgets\ChartWidget;

class AsociadosPorMunicipio extends ChartWidget
{
    protected ?string $heading = 'Asociados publicados por municipio';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $municipios = Municipio::withCount(['asociados' => fn ($query) => $query->publicado()])
            ->orderByDesc('asociados_count')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Asociados',
                'data' => $municipios->pluck('asociados_count')->all(),
                'backgroundColor' => '#EE4036',
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
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('ver_asociado') === true;
    }
}
