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
    protected ?string $heading = 'Flujo del sitio, últimos 30 días';

    protected ?string $description = 'Entradas son llegadas al sitio; páginas servidas es todo lo que se abre. Ninguna de las dos cuenta personas distintas: quien vuelve mañana cuenta otra vez. No incluye el panel, el portal del afiliado ni los rastreadores.';

    protected static ?int $sort = 5;

    /**
     * A todo lo ancho desde el 9 de septiembre de 2026: con dos series --entradas
     * y páginas servidas-- y treinta puntos, cuatro de las seis columnas dejaban
     * las líneas demasiado juntas para leer la comparación, que es justo para lo
     * que existe la gráfica.
     */
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 'full',
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
            ->selectRaw('dia, sum(total) as total, sum(entradas) as entradas')
            ->groupBy('dia')
            ->get()
            ->mapWithKeys(fn ($fila): array => [substr((string) $fila->dia, 0, 10) => [
                'total' => (int) $fila->total,
                'entradas' => (int) $fila->entradas,
            ]]);

        $etiquetas = [];
        $entradas = [];
        $paginas = [];

        foreach (range(self::DIAS - 1, 0) as $atras) {
            $fecha = now()->subDays($atras);
            $delDia = $porDia[$fecha->toDateString()] ?? ['total' => 0, 'entradas' => 0];

            $etiquetas[] = $fecha->translatedFormat('d M');
            $entradas[] = $delDia['entradas'];
            $paginas[] = $delDia['total'];
        }

        /*
         * Dos series y no una (Acta 08, A-03). Juntas cuentan lo que ninguna
         * cuenta sola: si suben las entradas y no las páginas, llega más gente y
         * se va enseguida; si suben las páginas y no las entradas, la misma gente
         * mira más. Esa comparación es el «flujo» que pidió la dirección.
         *
         * Las entradas van PRIMERO porque son la cifra que se pidió; las páginas
         * servidas quedan de contexto.
         */
        return [
            'datasets' => [
                [
                    'label' => 'Entradas al sitio',
                    'data' => $entradas,
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Páginas servidas',
                    'data' => $paginas,
                    'fill' => false,
                    'tension' => 0.3,
                    'borderDash' => [4, 4],
                ],
            ],
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
            // La leyenda vuelve: con dos series, esconderla deja al lector
            // adivinando cuál línea es cuál.
            'plugins' => ['legend' => ['display' => true, 'labels' => ['usePointStyle' => true]]],
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
