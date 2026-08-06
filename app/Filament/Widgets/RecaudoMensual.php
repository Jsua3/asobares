<?php

namespace App\Filament\Widgets;

use App\Models\Transaccion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Banda 3 del tablero: recaudo del año en curso, mes a mes.
 *
 * Reemplaza a «Inscripciones de los últimos 30 días», que con eventos
 * mensuales era una línea plana en cero unos 28 días de cada 30 — y una
 * gráfica vacía no es neutra, enseña que el tablero no sirve.
 *
 * Horizonte deliberadamente corto y operativo («¿vamos bien este mes?»); la
 * serie larga de 18 meses vive en el Observatorio, con propósito analítico.
 *
 * La agregación va en SQL: el widget anterior traía todos los modelos a
 * memoria para contarlos con `groupBy` de Collection.
 */
class RecaudoMensual extends ChartWidget
{
    protected ?string $heading = 'Recaudo mes a mes, este año';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    private const array MESES = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];

    protected function getData(): array
    {
        // `strftime` es de SQLite. Se aísla aquí y no en un scope para que el
        // día que el motor cambie a PostgreSQL solo haya que tocar este mapa.
        $expresion = match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char(created_at, 'MM')",
            'mysql', 'mariadb' => "date_format(created_at, '%m')",
            default => "strftime('%m', created_at)",
        };

        $porMes = Transaccion::aprobada()
            ->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])
            ->selectRaw("{$expresion} as mes, sum(monto) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $etiquetas = [];
        $valores = [];

        foreach (range(1, (int) now()->format('n')) as $mes) {
            $etiquetas[] = self::MESES[$mes];
            $valores[] = (float) ($porMes[str_pad((string) $mes, 2, '0', STR_PAD_LEFT)] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Recaudado (COP)',
                'data' => $valores,
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

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => [], 'grid' => []],
                'x' => ['ticks' => [], 'grid' => ['display' => false]],
            ],
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('ver_transaccion') === true;
    }
}
