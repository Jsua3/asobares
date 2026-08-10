<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Observatorio\CoberturaDeProveedores;
use App\Filament\Widgets\Observatorio\ComposicionDelSector;
use App\Filament\Widgets\Observatorio\DemandaLaboralPorArea;
use App\Filament\Widgets\Observatorio\OfertaContraDemanda;
use App\Filament\Widgets\Observatorio\PresenciaPorMunicipio;
use App\Filament\Widgets\Observatorio\SaludFinanciera;
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
     * en {@see Observatorio::getFooterWidgets()}. Cada `que` viene de
     * `GraficaDelObservatorio::que()` del widget correspondiente —estático,
     * así que se lee sin instanciar el componente— en vez de repetir la
     * frase aquí: antes vivía escrita a mano en este archivo Y en el widget
     * flaco correspondiente (ver `resources/views/components/panel/sin-muestra.blade.php`),
     * sin ninguna prueba que las atara, la misma causa raíz que ya se cerró
     * para los títulos (ver el punto de abajo). Ahora hay una sola fuente:
     * el widget.
     *
     * Única fuente de verdad para el título de cada serie: antes vivía
     * duplicado aquí y en `todosLosIndicadores()`, y un revisor demostró que
     * las dos copias podían divergir (una se renombraba, la otra no) sin que
     * ninguna prueba lo notara. `todosLosIndicadores()` ahora deriva de esta
     * lista en vez de repetir los títulos a mano, así que renombrar una
     * serie aquí la renombra también en el descargo — no hay un segundo
     * sitio que se pueda olvidar.
     *
     * `clave` es el identificador estable de cada sección en el HTML
     * (`data-serie` en la vista): a diferencia de `titulo`, no cambia si se
     * retoca el rótulo visible, así que sigue sirviendo para ubicar la
     * sección aunque el texto se edite.
     *
     * @return list<array{clave: string, titulo: string, que: string, serie: SerieDelObservatorio}>
     */
    public function series(): array
    {
        $metricas = $this->metricas();

        return [
            [
                'clave' => 'presencia-por-municipio',
                'titulo' => 'Presencia por municipio',
                'que' => PresenciaPorMunicipio::que(),
                'serie' => $metricas->presenciaPorMunicipio(),
            ],
            [
                'clave' => 'composicion-del-sector',
                'titulo' => 'Composición del sector',
                'que' => ComposicionDelSector::que(),
                'serie' => $metricas->composicionDelSector(),
            ],
            [
                'clave' => 'salud-financiera',
                'titulo' => 'Salud financiera, últimos 18 meses',
                'que' => SaludFinanciera::que(),
                'serie' => $metricas->saludFinanciera(),
            ],
            [
                'clave' => 'cobertura-de-proveedores',
                'titulo' => 'Cobertura de proveedores',
                'que' => CoberturaDeProveedores::que(),
                'serie' => $metricas->coberturaDeProveedores(),
            ],
            [
                'clave' => 'demanda-laboral-por-area',
                'titulo' => 'Demanda laboral por área',
                'que' => DemandaLaboralPorArea::que(),
                'serie' => $metricas->demandaLaboralPorArea(),
            ],
            [
                'clave' => 'oferta-contra-demanda',
                'titulo' => 'Oferta contra demanda',
                'que' => OfertaContraDemanda::que(),
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
     * Las seis series toman su título de {@see series()} en vez de
     * repetirlo: solo la tasa de mora, que no tiene tabla propia, se agrega
     * aparte.
     *
     * @return list<array{titulo: string, serie: SerieDelObservatorio}>
     */
    private function todosLosIndicadores(): array
    {
        $seis = array_map(
            fn (array $item): array => ['titulo' => $item['titulo'], 'serie' => $item['serie']],
            $this->series(),
        );

        $seis[] = ['titulo' => 'Tasa de mora actual', 'serie' => $this->metricas()->tasaDeMoraActual()];

        return $seis;
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
