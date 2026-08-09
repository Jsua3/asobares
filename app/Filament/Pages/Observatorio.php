<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Observatorio\CoberturaDeProveedores;
use App\Filament\Widgets\Observatorio\ComposicionDelSector;
use App\Filament\Widgets\Observatorio\DemandaLaboralPorArea;
use App\Filament\Widgets\Observatorio\OfertaContraDemanda;
use App\Filament\Widgets\Observatorio\PresenciaPorMunicipio;
use App\Filament\Widgets\Observatorio\SaludFinanciera;
use App\Panel\MetricasDelObservatorio;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
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
     * El botón que lleva al informe imprimible. No descarga ningún archivo:
     * abre {@see InformeDelObservatorio} y el PDF lo produce el propio
     * navegador (Ctrl/Cmd+P → «Guardar como PDF»). Se eligió esa vía y no
     * una librería de PDF porque el proyecto no tiene ninguna dependencia de
     * ese tipo — sumar una exige aprobación, y así el papel sale con la
     * tipografía y los colores reales del gremio, no con los de una
     * librería genérica.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarInforme')
                ->label('Descargar informe')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => InformeDelObservatorio::getUrl())
                ->openUrlInNewTab(),
        ];
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
     * Las seis gráficas del observatorio: las tres que sí tienen datos que
     * sostienen lo que dibujan (OBS T5), y las tres flacas con su estado
     * vacío honesto (OBS T6). El mecanismo es `getFooterWidgets()` y no
     * `<x-filament-widgets::widgets>`: ese componente está `@deprecated` en
     * `vendor/filament/`, y quien de verdad rinde estos widgets es el
     * envoltorio `<x-filament-panels::page>` (invoca `{{ $this->footerWidgets }}`
     * por dentro) — la vista de esta página no vuelve a llamarlos.
     *
     * @return array<class-string<Widget>>
     */
    protected function getFooterWidgets(): array
    {
        return [
            PresenciaPorMunicipio::class,
            ComposicionDelSector::class,
            SaludFinanciera::class,
            CoberturaDeProveedores::class,
            DemandaLaboralPorArea::class,
            OfertaContraDemanda::class,
        ];
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
