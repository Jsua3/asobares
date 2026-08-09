<?php

namespace App\Filament\Pages;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Pages\Page;

/**
 * El objeto que la dirección deja sobre la mesa en una alcaldía o en la
 * Cámara de Comercio: mismo permiso y las mismas cifras que el Observatorio,
 * pero pensada para papel, no para pantalla.
 *
 * El PDF lo produce el navegador (Ctrl/Cmd+P → «Guardar como PDF»), no una
 * librería nueva: el CSS de impresión vive en `theme.css` (`@media print`).
 * No tiene entrada propia en el menú — se llega desde el botón «Descargar
 * informe» de {@see Observatorio}.
 */
class InformeDelObservatorio extends Page
{
    protected string $view = 'filament.pages.informe-del-observatorio';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Informe del observatorio';

    protected static ?string $slug = 'observatorio/informe';

    private ?MetricasDelObservatorio $metricas = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_observatorio') === true;
    }

    /** Ver el docblock de {@see Observatorio::mount()} para el porqué de esta defensa. */
    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
    }

    /** Memoizado por instancia, igual que en {@see Observatorio::metricas()}. */
    public function metricas(): MetricasDelObservatorio
    {
        return $this->metricas ??= app(MetricasDelObservatorio::class);
    }

    /** Fecha de generación, tal como la ve quien imprime el informe. */
    public function generadoEl(): string
    {
        return now()->format('d/m/Y');
    }

    /**
     * Las cuatro cifras de cabecera, en el mismo orden que la banda de KPIs
     * del observatorio (ver `Observatorio::totalAsociados()` y hermanas para
     * el mismo cálculo). Se recalculan aquí porque son dos páginas Livewire
     * independientes: no hay una instancia de `Observatorio` de la que tomar
     * prestado el método.
     *
     * @return list<array{etiqueta: string, valor: string, serie: SerieDelObservatorio}>
     */
    public function indicadores(): array
    {
        $metricas = $this->metricas();

        $composicion = $metricas->composicionDelSector();
        $proveedores = $metricas->coberturaDeProveedores();
        $salud = $metricas->saludFinanciera();
        $mora = $metricas->tasaDeMoraActual();

        return [
            [
                'etiqueta' => 'Asociados publicados',
                'valor' => number_format((float) array_sum($composicion->series['Asociados'] ?? []), 0, ',', '.'),
                'serie' => $composicion,
            ],
            [
                'etiqueta' => 'Proveedores vigentes',
                'valor' => number_format((float) array_sum($proveedores->series['Proveedores'] ?? []), 0, ',', '.'),
                'serie' => $proveedores,
            ],
            [
                'etiqueta' => 'Recaudo (18 meses)',
                'valor' => '$'.number_format((float) array_sum($salud->series['Recaudo (COP)'] ?? []), 0, ',', '.'),
                'serie' => $salud,
            ],
            [
                'etiqueta' => 'Tasa de mora actual',
                'valor' => number_format((float) ($mora->series['Tasa de mora (%)'][0] ?? 0), 1, ',', '.').' %',
                'serie' => $mora,
            ],
        ];
    }

    /**
     * Las seis series del observatorio, en el mismo orden que sus gráficas
     * en {@see Observatorio::getFooterWidgets()}. Cada `que` repite la misma
     * frase que ya usa el widget flaco correspondiente en su estado vacío
     * (ver `resources/views/components/panel/sin-muestra.blade.php`), para
     * que el papel diga exactamente lo mismo que la pantalla.
     *
     * @return list<array{titulo: string, que: string, serie: SerieDelObservatorio}>
     */
    public function series(): array
    {
        $metricas = $this->metricas();

        return [
            [
                'titulo' => 'Presencia por municipio',
                'que' => 'la presencia del gremio por municipio',
                'serie' => $metricas->presenciaPorMunicipio(),
            ],
            [
                'titulo' => 'Composición del sector',
                'que' => 'la composición del sector por categoría',
                'serie' => $metricas->composicionDelSector(),
            ],
            [
                'titulo' => 'Salud financiera, últimos 18 meses',
                'que' => 'la salud financiera del gremio',
                'serie' => $metricas->saludFinanciera(),
            ],
            [
                'titulo' => 'Cobertura de proveedores',
                'que' => 'la cobertura de proveedores por categoría',
                'serie' => $metricas->coberturaDeProveedores(),
            ],
            [
                'titulo' => 'Demanda laboral por área',
                'que' => 'la demanda laboral por área y por mes',
                'serie' => $metricas->demandaLaboralPorArea(),
            ],
            [
                'titulo' => 'Oferta contra demanda',
                'que' => 'la oferta contra la demanda laboral',
                'serie' => $metricas->ofertaContraDemanda(),
            ],
        ];
    }

    /**
     * Cada uno de los siete indicadores del observatorio una sola vez —no
     * las listas de arriba, que reparten la misma serie entre la cabecera y
     * su propia tabla (p. ej. `composicionDelSector()` es a la vez «Asociados
     * publicados» y «Composición del sector»)—, para que el descargo de
     * muestra no repita la misma cifra dos veces con dos nombres distintos.
     *
     * @return list<array{titulo: string, serie: SerieDelObservatorio}>
     */
    private function todosLosIndicadores(): array
    {
        $metricas = $this->metricas();

        return [
            ['titulo' => 'Presencia por municipio', 'serie' => $metricas->presenciaPorMunicipio()],
            ['titulo' => 'Composición del sector', 'serie' => $metricas->composicionDelSector()],
            ['titulo' => 'Salud financiera', 'serie' => $metricas->saludFinanciera()],
            ['titulo' => 'Tasa de mora actual', 'serie' => $metricas->tasaDeMoraActual()],
            ['titulo' => 'Cobertura de proveedores', 'serie' => $metricas->coberturaDeProveedores()],
            ['titulo' => 'Demanda laboral por área', 'serie' => $metricas->demandaLaboralPorArea()],
            ['titulo' => 'Oferta contra demanda', 'serie' => $metricas->ofertaContraDemanda()],
        ];
    }

    /**
     * El descargo no es decorativo: un papel con el sello del gremio
     * afirmando algo que su propia muestra no aguanta es peor que no tener
     * informe. Esta lista es lo que hace el descargo específico en vez de
     * una advertencia genérica.
     *
     * @return list<array{titulo: string, serie: SerieDelObservatorio}>
     */
    public function indicadoresSinMuestra(): array
    {
        return array_values(array_filter(
            $this->todosLosIndicadores(),
            fn (array $indicador): bool => ! $indicador['serie']->hayMuestraSuficiente(),
        ));
    }

    /**
     * Formatea una celda de tabla según lo que mide su columna: pesos para
     * el recaudo, porcentaje para la tasa de mora, conteo con separador de
     * miles para el resto.
     */
    public function formatearCelda(string $clave, int|float $valor): string
    {
        return match (true) {
            str_contains($clave, 'COP') => '$'.number_format((float) $valor, 0, ',', '.'),
            str_contains($clave, '%') => number_format((float) $valor, 1, ',', '.').' %',
            default => number_format((float) $valor, 0, ',', '.'),
        };
    }
}
