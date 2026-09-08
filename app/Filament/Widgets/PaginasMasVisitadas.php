<?php

namespace App\Filament\Widgets;

use App\Models\VisitaDiaria;
use App\Panel\RanuraDeTema;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Qué secciones del sitio se miran más, en el último mes.
 *
 * Qué mide exactamente: **rutas**, no direcciones. Todas las fichas del
 * directorio cuentan juntas bajo `/directorio/{asociado}`, porque lo que se
 * guarda es el nombre de la ruta y no la URL --una URL trae la cadena de
 * consulta, y ahí puede venir cualquier cosa--. Sirve para saber qué secciones
 * interesan; no sirve para saber qué establecimiento se mira más.
 */
class PaginasMasVisitadas extends ChartWidget
{
    protected ?string $heading = 'Secciones más visitadas, últimos 30 días';

    protected ?string $description = 'Agrupa por sección, no por dirección: todas las fichas del directorio cuentan juntas. No dice qué establecimiento se mira más.';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 2,
    ];

    private const int DIAS = 30;

    private const int CUANTAS = 8;

    protected function getData(): array
    {
        $filas = VisitaDiaria::query()
            ->where('dia', '>=', now()->subDays(self::DIAS - 1)->toDateString())
            ->selectRaw('ruta, sum(total) as total')
            ->groupBy('ruta')
            ->orderByDesc('total')
            ->limit(self::CUANTAS)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Páginas servidas',
                'data' => $filas->pluck('total')->map(fn ($total): int => (int) $total)->all(),
            ]],
            'labels' => $filas->pluck('ruta')->map(fn (string $ruta): string => $this->etiqueta($ruta))->all(),
        ];
    }

    /**
     * La ruta se enseña por su URI y no por su nombre interno: «/directorio»
     * lo entiende quien mira el tablero y «directorio.index» no. Si la ruta ya
     * no existe --se renombró y la tabla conserva visitas viejas-- se enseña el
     * nombre crudo, que es la verdad disponible.
     */
    private function etiqueta(string $ruta): string
    {
        $uri = Route::getRoutes()->getByName($ruta)?->uri();

        return $uri === null ? $ruta : '/'.ltrim($uri, '/');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => RanuraDeTema::vacia(), 'grid' => RanuraDeTema::vacia()],
                'y' => ['ticks' => RanuraDeTema::vacia(), 'grid' => ['display' => false]],
            ],
        ];
    }

    /** La misma puerta que `VisitasDelSitio`; el porqué está allí. */
    public static function canView(): bool
    {
        return Auth::user()?->can('ver_observatorio') === true;
    }
}
