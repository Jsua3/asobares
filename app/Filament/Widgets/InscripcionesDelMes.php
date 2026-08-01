<?php

namespace App\Filament\Widgets;

use App\Models\Inscripcion;
use Filament\Widgets\ChartWidget;

class InscripcionesDelMes extends ChartWidget
{
    protected ?string $heading = 'Inscripciones de los últimos 30 días';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $desde = now()->subDays(29)->startOfDay();

        $porDia = Inscripcion::where('created_at', '>=', $desde)
            ->get()
            ->groupBy(fn (Inscripcion $inscripcion): string => $inscripcion->created_at->toDateString())
            ->map->count();

        $etiquetas = [];
        $valores = [];

        foreach (range(0, 29) as $dias) {
            $fecha = $desde->copy()->addDays($dias);
            $etiquetas[] = $fecha->format('d/m');
            $valores[] = $porDia[$fecha->toDateString()] ?? 0;
        }

        return [
            'datasets' => [[
                'label' => 'Inscripciones',
                'data' => $valores,
                'borderColor' => '#EE4137',
                'backgroundColor' => 'rgba(238, 65, 55, 0.15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $etiquetas,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('ver_inscripcion') === true;
    }
}
