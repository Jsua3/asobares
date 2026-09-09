<?php

namespace App\Filament\Widgets;

use App\Models\VisitaDiaria;
use App\Panel\RanuraDeTema;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Por dónde entra la gente al sitio (Acta 08, A-03).
 *
 * La otra mitad de «flujo de personas que entran a la página»: no solo cuánta
 * llega, sino por qué puerta. Es la cifra que de verdad decide dónde poner el
 * esfuerzo --si la mayoría entra por la guía normativa y no por la portada, el
 * producto insignia es la guía y la portada es un folleto--.
 *
 * Qué mide exactamente: **llegadas**, o sea la primera página de cada visita.
 * Una persona que entra por `/empleo` y luego abre cuatro vacantes deja una
 * llegada en `/empleo` y nada en las fichas. Por eso el orden de esta gráfica no
 * se parece al de «Secciones más visitadas», que cuenta todo lo que se abre: una
 * lista te dice por dónde llegan y la otra qué miran una vez dentro.
 *
 * ⚠️ No son personas distintas: quien vuelve mañana cuenta otra vez. Ver
 * `ContarVisitaDelSitio` para el porqué.
 */
class PorDondeEntranAlSitio extends ChartWidget
{
    protected ?string $heading = 'Por dónde entran, últimos 30 días';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 3,
    ];

    private const int DIAS = 30;

    private const int CUANTAS = 8;

    /**
     * La advertencia que acompaña a toda cifra de este módulo.
     *
     * Es un método y no una constante para que las dos piezas del flujo la
     * compartan y `FlujoDeEntradasAlSitioTest` pueda exigirla en las dos: una
     * cifra de tráfico sin esta frase se lee como visitantes únicos, que es
     * justo lo que no es.
     */
    public function obtenerAdvertencia(): string
    {
        return 'Llegadas al sitio, no personas distintas: quien vuelve mañana cuenta otra vez. '
            .'Una visita deja una sola llegada, en la página por la que entró.';
    }

    public function getDescription(): ?string
    {
        return $this->obtenerAdvertencia();
    }

    protected function getData(): array
    {
        $filas = VisitaDiaria::query()
            ->where('dia', '>=', now()->subDays(self::DIAS - 1)->toDateString())
            ->selectRaw('ruta, sum(entradas) as entradas')
            ->groupBy('ruta')
            ->havingRaw('sum(entradas) > 0')
            ->orderByDesc('entradas')
            ->limit(self::CUANTAS)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Entradas',
                'data' => $filas->pluck('entradas')->map(fn ($entradas): int => (int) $entradas)->all(),
            ]],
            'labels' => $filas->pluck('ruta')->map(fn (string $ruta): string => $this->etiqueta($ruta))->all(),
        ];
    }

    /**
     * La ruta se enseña por su URI y no por su nombre interno: «/directorio» lo
     * entiende quien mira el tablero y «directorio.index» no. Si la ruta ya no
     * existe --se renombró y la tabla conserva entradas viejas-- se enseña el
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

    /** La misma puerta que el resto del observatorio; el porqué está en `VisitasDelSitio`. */
    public static function canView(): bool
    {
        return Auth::user()?->can('ver_observatorio') === true;
    }
}
