<?php

namespace App\Filament\Widgets;

use App\Enums\EstadoMensaje;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Mensaje;
use App\Models\Municipio;
use App\Models\Postulacion;
use App\Models\Proveedor;
use App\Models\Transaccion;
use App\Models\User;
use App\Panel\ColaDePendientes;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Banda 2 del tablero: cuatro cifras, no seis, y distintas según el oficio.
 *
 * Antes eran seis tarjetas fijas que mezclaban contenido, plata, bandeja y
 * bolsa: la dirección y la secretaría abrían la misma pantalla con la misma
 * jerarquía y ninguna veía su trabajo. El discriminador es `ver_transaccion`,
 * que es justo el permiso que `RolYPermisoSeeder` le niega a la secretaría.
 *
 * Toda tarjeta lleva `url()`: un KPI que no es enlace es un número muerto.
 */
class ResumenDelGremio extends StatsOverviewWidget
{
    protected ?string $heading = 'El gremio hoy';

    protected static ?int $sort = 1;

    /** El alcance firmado del proyecto son los 12 municipios del Quindío. */
    private const int MUNICIPIOS_DEL_ALCANCE = 12;

    protected function getStats(): array
    {
        return Auth::user()?->can('ver_transaccion') === true
            ? $this->tarjetasDeDireccion()
            : $this->tarjetasDeSecretaria();
    }

    /** @return array<int, Stat> */
    private function tarjetasDeDireccion(): array
    {
        $hoy = now();
        $inicioDeMes = $hoy->copy()->startOfMonth();

        // `subMonthNoOverflow` y no `subMonth`: en un 31 de marzo, restar un
        // mes sin overflow cae en el 28/29 de febrero; con overflow saltaría
        // al 2 o 3 de marzo, que ya no es "el mes anterior".
        $mesAnterior = $hoy->copy()->subMonthNoOverflow()->startOfMonth();

        // El delta compara el acumulado del mes en curso hasta HOY contra el
        // acumulado del mes anterior hasta el MISMO DÍA, no el mes anterior
        // completo. Comparar un mes incompleto contra uno completo siempre
        // pinta mal a inicio de mes (el día 6 del mes serían $150.000 contra
        // $930.000 del mes pasado entero: un −84 % que no dice nada) y sigue
        // siendo falso a mitad de mes (el día 22 de un mes de 30 días daría
        // ~−25 % igual de artificial). Se acota al último día del mes
        // anterior por si ese mes tiene menos días que hoy (hoy 31, el mes
        // pasado tuvo 30): sin el `min`, `day(31)` se desbordaría a abril.
        $finVentanaAnterior = $mesAnterior->copy()
            ->day(min($hoy->day, $mesAnterior->daysInMonth))
            ->endOfDay();

        $recaudado = $this->recaudadoEntre($inicioDeMes, $hoy);
        $recaudadoAntes = $this->recaudadoEntre($mesAnterior, $finVentanaAnterior);

        $enMora = Cartera::enMora()->count();
        $saldo = (float) Cartera::enMora()->sum('saldo_pendiente');

        $publicados = Asociado::publicado()->count();
        $altasDelMes = Asociado::publicado()->where('created_at', '>=', $inicioDeMes)->count();

        $conPresencia = Municipio::whereHas('asociados', fn ($q) => $q->publicado())->count();

        return [
            Stat::make('Recaudado este mes', $this->pesos($recaudado))
                ->description($this->variacion($recaudado, $recaudadoAntes))
                ->descriptionIcon($recaudado >= $recaudadoAntes
                    ? 'heroicon-o-arrow-trending-up'
                    : 'heroicon-o-arrow-trending-down')
                ->color($recaudado >= $recaudadoAntes ? 'success' : 'danger')
                ->url(route('filament.admin.resources.transacciones.index')),

            Stat::make('Cartera en mora', $enMora)
                ->description($this->pesos($saldo).' por recaudar')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($enMora > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.cartera.index')),

            Stat::make('Asociados publicados', $publicados)
                ->description($altasDelMes.' altas este mes')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('success')
                ->url(route('filament.admin.resources.asociados.index')),

            // Representatividad, que es el propósito #1 de la directiva: el
            // alcance firmado son los 12 municipios del Quindío.
            Stat::make('Cobertura territorial', $conPresencia.' de '.self::MUNICIPIOS_DEL_ALCANCE)
                ->description('municipios con presencia del gremio')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color($conPresencia >= 8 ? 'success' : 'warning')
                ->url(route('filament.admin.resources.municipios.index')),
        ];
    }

    /** @return array<int, Stat> */
    private function tarjetasDeSecretaria(): array
    {
        $usuario = Auth::user();
        $pendientes = $usuario instanceof User
            ? app(ColaDePendientes::class)->total($usuario)
            : 0;

        $sinResponder = Mensaje::where('estado', EstadoMensaje::Nuevo)->count();
        $pqrAbiertos = Mensaje::whereNotNull('radicado')
            ->where('estado', '!=', EstadoMensaje::Respondido)
            ->count();

        $postulaciones = Postulacion::where('created_at', '>=', now()->subWeek())->count();

        $fichas = Artista::query()->pendiente()->count()
            + Proveedor::query()->pendiente()->count();

        return [
            Stat::make('Pendientes de moderación', $pendientes)
                ->description('esperan tu aprobación')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendientes > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.vacantes.index')),

            Stat::make('Bandeja sin responder', $sinResponder)
                ->description($pqrAbiertos.' PQR abiertos')
                ->descriptionIcon('heroicon-o-inbox')
                ->color($pqrAbiertos > 0 ? 'danger' : 'warning')
                ->url(route('filament.admin.resources.mensajes.index')),

            Stat::make('Postulaciones de la semana', $postulaciones)
                ->description('candidatos que llegaron por la bolsa')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('info')
                ->url(route('filament.admin.resources.postulaciones.index')),

            Stat::make('Fichas de bolsa por revisar', $fichas)
                ->description('artistas y proveedores inscritos')
                ->descriptionIcon('heroicon-o-identification')
                ->color($fichas > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.artistas.index')),
        ];
    }

    private function recaudadoEntre(Carbon $desde, Carbon $hasta): float
    {
        return (float) Transaccion::aprobada()
            ->whereBetween('created_at', [$desde, $hasta])
            ->sum('monto');
    }

    /**
     * El texto deja explícito contra qué ventana compara: "mes anterior" a
     * secas sugiere el mes completo, y la comparación real es contra el
     * mismo tramo de días. Ver el porqué en `tarjetasDeDireccion()`.
     */
    private function variacion(float $ahora, float $antes): string
    {
        if ($antes <= 0.0) {
            return $ahora > 0.0
                ? 'primer mes con recaudo en lo que va corrido'
                : 'sin recaudo el mes pasado a estas alturas';
        }

        $porcentaje = (($ahora - $antes) / $antes) * 100;
        $signo = $porcentaje >= 0 ? '+' : '−';

        return $signo.number_format(abs($porcentaje), 1, ',', '.').' % vs. mismo tramo del mes anterior';
    }

    private function pesos(float $monto): string
    {
        return '$'.number_format($monto, 0, ',', '.');
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('ver_asociado') === true;
    }
}
