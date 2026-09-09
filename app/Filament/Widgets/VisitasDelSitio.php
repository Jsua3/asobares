<?php

namespace App\Filament\Widgets;

use App\Models\VisitaDiaria;
use App\Panel\RanuraDeTema;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

/**
 * Cuántas páginas del sitio se sirvieron cada día del último mes.
 *
 * Qué mide exactamente, porque un número sin definición se lee como cualquier
 * cosa: **páginas servidas**, no personas. Un visitante que abre cuatro fichas
 * cuenta cuatro. No hay «visitantes únicos» y no es un olvido: distinguir
 * personas exige guardar IP, cookie o sesión, que es justo lo que este diseño
 * evita para no entrar en la Ley 1581 (Acta 07, A-02).
 *
 * Lo que NO cuenta: el panel, el portal del afiliado, los rastreadores
 * conocidos, todo lo que no sea una respuesta 200 a un GET, y las descargas.
 */
class VisitasDelSitio extends ChartWidget
{
    protected ?string $heading = 'Visitas del sitio, últimos 30 días';

    protected ?string $description = 'Páginas servidas, no personas: quien abre cuatro fichas cuenta cuatro. No incluye el panel, el portal del afiliado ni los rastreadores.';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 4,
    ];

    private const int DIAS = 30;

    protected function getData(): array
    {
        $desde = now()->subDays(self::DIAS - 1)->startOfDay();

        // Se normaliza la clave en PHP y no con una expresión por motor: según
        // el driver, `dia` vuelve como «2026-09-08» o con la hora pegada, y un
        // `match` por driver aquí sería un sitio más donde acordarse de pgsql.
        $porDia = VisitaDiaria::query()
            ->where('dia', '>=', $desde->toDateString())
            ->selectRaw('dia, sum(total) as total')
            ->groupBy('dia')
            ->get()
            ->mapWithKeys(fn ($fila): array => [substr((string) $fila->dia, 0, 10) => (int) $fila->total]);

        $etiquetas = [];
        $valores = [];

        foreach (range(self::DIAS - 1, 0) as $atras) {
            $fecha = now()->subDays($atras);
            $etiquetas[] = $fecha->translatedFormat('d M');
            $valores[] = $porDia[$fecha->toDateString()] ?? 0;
        }

        return [
            'datasets' => [[
                'label' => 'Páginas servidas',
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
                'y' => ['beginAtZero' => true, 'ticks' => RanuraDeTema::vacia(), 'grid' => RanuraDeTema::vacia()],
                'x' => ['ticks' => RanuraDeTema::vacia(), 'grid' => ['display' => false]],
            ],
        ];
    }

    /**
     * Misma puerta que el observatorio, y no un permiso nuevo. Dos razones: es
     * información de dirección, del mismo tipo; y un permiso nuevo obliga a
     * correr `RolYPermisoSeeder` en producción, que es tocar datos y pide visto
     * bueno aparte.
     */
    public static function canView(): bool
    {
        return Auth::user()?->can('ver_observatorio') === true;
    }
}
