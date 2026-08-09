<?php

namespace App\Filament\Pages;

use App\Panel\MetricasDelObservatorio;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

/**
 * El observatorio es el argumento que la dirección lleva a una alcaldía:
 * las cifras del gremio, ninguna sin su n al lado.
 *
 * Exclusivo de dirección (permiso `ver_observatorio`): incluye salud
 * financiera, que la secretaría no ve en ninguna otra pantalla, y su
 * propósito es el argumento institucional, que es trabajo de dirección.
 */
class Observatorio extends Page
{
    protected string $view = 'filament.pages.observatorio';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Gremio';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Observatorio del gremio';

    protected static ?string $navigationLabel = 'Observatorio';

    protected static ?string $slug = 'observatorio';

    private ?MetricasDelObservatorio $metricas = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_observatorio') === true;
    }

    /**
     * El guardián real no es este método: es `canAccess()`. El trait
     * `Filament\Pages\Concerns\CanAuthorizeAccess` (que `Page` ya trae)
     * define `mountCanAuthorizeAccess()` e `hydrateCanAuthorizeAccess()`,
     * y cada uno hace su propio `abort_unless(static::canAccess(), 403)`.
     * Livewire invoca esos hooks solo, en cada mount y cada hidratación,
     * y como `canAccess()` está sobrescrito aquí, el enlace tardío hace
     * que el hook del trait ya cierre la puerta antes de que este método
     * se ejecute.
     *
     * El `abort_unless` de abajo es defensa en profundidad, no el cierre
     * principal: sobrevive si algún día el trait cambia de internals.
     * Ninguna prueba lo cubre —ni puede cubrirlo mientras el trait
     * exista, porque comentarlo no rompe nada—, así que no lo tomes como
     * "verificado" ni lo borres por parecer código muerto.
     */
    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
    }

    /**
     * Memoizado por instancia: la vista pide varias métricas por render y
     * `MetricasDelObservatorio` ya memoiza cada una internamente, pero esto
     * evita instanciar el servicio más de una vez.
     */
    public function metricas(): MetricasDelObservatorio
    {
        return $this->metricas ??= app(MetricasDelObservatorio::class);
    }

    /** Total de asociados publicados: cabecera de composición del sector. */
    public function totalAsociados(): int
    {
        return (int) array_sum($this->metricas()->composicionDelSector()->series['Asociados'] ?? []);
    }

    /** Total de proveedores publicados y vigentes, sumados los siete rubros. */
    public function totalProveedores(): int
    {
        return (int) array_sum($this->metricas()->coberturaDeProveedores()->series['Proveedores'] ?? []);
    }

    /** Recaudo acumulado de los últimos dieciocho meses, en pesos. */
    public function recaudoDelPeriodo(): string
    {
        $total = array_sum($this->metricas()->saludFinanciera()->series['Recaudo (COP)'] ?? []);

        return '$'.number_format((float) $total, 0, ',', '.');
    }

    /** Tasa de mora de hoy, formateada como porcentaje. */
    public function tasaDeMora(): string
    {
        $tasa = $this->metricas()->tasaDeMoraActual()->series['Tasa de mora (%)'][0] ?? 0;

        return number_format((float) $tasa, 1, ',', '.').' %';
    }
}
