<?php

namespace App\Filament\Widgets;

use App\Models\VisitaDiaria;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * El flujo de entradas, en tres números (Acta 08, A-03).
 *
 * Es la respuesta corta a «flujo de personas que entran a la página»: cuánta
 * gente entró esta semana, si va subiendo o bajando, y cuánto mira cada quien
 * una vez dentro. Va en tarjetas y no en gráfica porque es lo que se mira de
 * reojo al abrir el panel; las gráficas están debajo para quien quiera detalle.
 *
 * **Un número suelto no es una métrica**: sin la comparación con la semana
 * anterior, «142 entradas» no dice si eso es bueno. Por eso la primera tarjeta
 * lleva siempre el cambio, y calla cuando no hay con qué comparar en vez de
 * inventarse un porcentaje contra cero.
 *
 * ⚠️ No son personas distintas. Ver `ContarVisitaDelSitio` para el porqué, y el
 * Acta 07 para la decisión de no contarlas.
 */
class EntradasAlSitio extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Flujo del sitio';

    /**
     * Desglosado por ancho y no `'full'` a secas: Filament solo aplica el valor
     * escalar desde `lg`, así que en el teléfono el widget caía a una sola pista.
     * Es el defecto que ya se pagó con el widget de pendientes el 7 de septiembre
     * y que `Panel\TableroTest` vigila desde entonces —lo atrapó aquí en cuanto
     * se registró este widget—.
     */
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 'full',
    ];

    /** La misma advertencia que la gráfica de por dónde entran. */
    public function obtenerAdvertencia(): string
    {
        return 'Llegadas al sitio, no personas distintas: quien vuelve mañana cuenta otra vez.';
    }

    public function getDescription(): ?string
    {
        return $this->obtenerAdvertencia();
    }

    /**
     * Público para que la prueba pueda leer las tarjetas sin renderizar el
     * tablero entero, que exige el segundo factor.
     *
     * @return array<int, Stat>
     */
    public function obtenerTarjetas(): array
    {
        $estaSemana = $this->entradasEntre(6, 0);
        $semanaAnterior = $this->entradasEntre(13, 7);
        $delMes = $this->entradasEntre(29, 0);
        $paginasDelMes = $this->paginasEntre(29, 0);

        return [
            Stat::make('Entradas esta semana', (string) $estaSemana)
                ->description($this->comparacion($estaSemana, $semanaAnterior))
                ->descriptionIcon($this->flecha($estaSemana, $semanaAnterior))
                ->color($this->colorDelCambio($estaSemana, $semanaAnterior)),

            Stat::make('Entradas del mes', (string) $delMes)
                ->description('llegadas en los últimos 30 días')
                ->descriptionIcon('heroicon-o-arrow-right-end-on-rectangle')
                ->color('info'),

            /*
             * Cuánto mira quien entra. Sale de dividir dos cifras que ya se
             * tienen y responde algo que ninguna responde sola: si sube, el
             * contenido está reteniendo; si baja, la gente llega y se va.
             */
            Stat::make('Páginas por visita', $delMes === 0 ? '—' : number_format($paginasDelMes / $delMes, 1, ',', '.'))
                ->description('cuánto mira quien entra')
                ->descriptionIcon('heroicon-o-document-duplicate')
                ->color('gray'),
        ];
    }

    /** @return array<int, Stat> */
    protected function getStats(): array
    {
        return $this->obtenerTarjetas();
    }

    /**
     * Sin semana anterior no se inventa un porcentaje.
     *
     * Comparar contra cero da «infinito» o «100 %» según cómo se escriba la
     * división, y las dos son mentira: lo honesto es decir que no hay con qué
     * comparar todavía. Pasa siempre en la primera semana de una métrica nueva,
     * que es justo cuando alguien la va a mirar por primera vez.
     */
    private function comparacion(int $estaSemana, int $semanaAnterior): string
    {
        if ($semanaAnterior === 0) {
            return 'primera semana con datos';
        }

        $cambio = ($estaSemana - $semanaAnterior) / $semanaAnterior * 100;
        $signo = $cambio >= 0 ? '+' : '−';

        return $signo.number_format(abs($cambio), 0, ',', '.').' % respecto a la semana anterior';
    }

    private function flecha(int $estaSemana, int $semanaAnterior): string
    {
        return match (true) {
            $semanaAnterior === 0 => 'heroicon-o-sparkles',
            $estaSemana >= $semanaAnterior => 'heroicon-o-arrow-trending-up',
            default => 'heroicon-o-arrow-trending-down',
        };
    }

    private function colorDelCambio(int $estaSemana, int $semanaAnterior): string
    {
        return match (true) {
            $semanaAnterior === 0 => 'gray',
            $estaSemana >= $semanaAnterior => 'success',
            default => 'warning',
        };
    }

    /** Entradas entre dos días atrás, ambos inclusive. */
    private function entradasEntre(int $desdeHaceDias, int $hastaHaceDias): int
    {
        return (int) $this->ventana($desdeHaceDias, $hastaHaceDias)->sum('entradas');
    }

    private function paginasEntre(int $desdeHaceDias, int $hastaHaceDias): int
    {
        return (int) $this->ventana($desdeHaceDias, $hastaHaceDias)->sum('total');
    }

    /** @return Builder<VisitaDiaria> */
    private function ventana(int $desdeHaceDias, int $hastaHaceDias): Builder
    {
        return VisitaDiaria::query()
            ->where('dia', '>=', now()->subDays($desdeHaceDias)->toDateString())
            ->where('dia', '<=', now()->subDays($hastaHaceDias)->toDateString());
    }

    /** La misma puerta que el resto del observatorio. */
    public static function canView(): bool
    {
        return Auth::user()?->can('ver_observatorio') === true;
    }
}
