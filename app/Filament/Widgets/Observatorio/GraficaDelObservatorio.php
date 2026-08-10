<?php

namespace App\Filament\Widgets\Observatorio;

use App\Panel\MetricasDelObservatorio;
use App\Panel\SerieDelObservatorio;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Base de las seis gráficas del observatorio.
 *
 * El principio del módulo es «ninguna gráfica dibuja si su muestra no la
 * sostiene», y hasta ahora esa regla vivía copiada a mano
 * (`isEmpty()`/`getEmptyState()`, con tres docblocks idénticos palabra por
 * palabra) en las tres gráficas que un día quedaron bajo el umbral.
 * `ComposicionDelSector` no las tenía: se clasificó como «sólida» ese día y
 * nadie volvió a mirarla cuando su muestra bajó de treinta, así que dibujaba
 * sin ningún aviso mientras la tarjeta KPI y el informe impreso marcaban ese
 * mismo dato como «muestra pequeña».
 *
 * Con la regla aquí, las seis gráficas la heredan sin poder olvidarla: una
 * gráfica nueva del observatorio extiende esta clase o no pertenece al
 * observatorio. También sube lo demás que estaba copiado seis veces:
 * `columnSpan`, `canView()` y el acceso memoizado a `MetricasDelObservatorio`.
 */
abstract class GraficaDelObservatorio extends ChartWidget
{
    /**
     * `AdminPanelProvider::panel()` descubre widgets recursivamente en
     * `app/Filament/Widgets` (`discoverWidgets()` recorre subdirectorios), así
     * que sin esto las seis gráficas del observatorio se colaban en el
     * tablero: sus `$sort` (1–6) se intercalaban con los del tablero (0–4) y
     * rompían las tres bandas que el tablero documenta como su razón de
     * existir, además de doblar su coste de consultas en cada carga.
     *
     * `$isDiscovered = false` es el mecanismo nativo de Filament para esto
     * (`Widget::isDiscovered()`, consultado por `discoverComponents()` antes
     * de registrar cada clase encontrada): saca la familia entera del
     * descubrimiento automático sin sacarla del directorio ni de la
     * convención de namespace. `Observatorio::getFooterWidgets()` las sigue
     * registrando de forma explícita — `isDiscovered` solo afecta al barrido
     * automático, no a una referencia directa a la clase.
     */
    protected static bool $isDiscovered = false;

    protected int|string|array $columnSpan = 'full';

    /**
     * Color de reserva de cada ranura de la paleta categórica, para el
     * instante anterior a que `panel-graficas.js` pinte y para un cliente sin
     * JS. Son los valores del tema claro tal como están en `:root`, y
     * `ObservatorioTest` los ata a `--asb-serie-N` para que no diverjan.
     *
     * El color de verdad vive en tokens.css porque son DOS paletas: la del
     * manual de marca está pensada sobre Ambient White, y sobre Pub Black
     * tres de estos siete —Wine, Pub Grey y Pub Black mismo— no llegan a 3:1
     * contra la superficie. Vive aquí, en la base, por lo mismo que la regla
     * del umbral: `PresenciaPorMunicipio` y `DemandaLaboralPorArea` ya
     * cablearon la misma paleta a mano cada una por su lado, y la segunda
     * arrastró el error de la primera.
     */
    protected const array RESERVA_DE_SERIE = [
        1 => '#ee4137', // Pub Red
        2 => '#a4161a', // Wine
        3 => '#c05299', // Ambient Purple
        4 => '#ea698b', // Ambient Rose
        5 => '#282628', // Pub Grey
        6 => '#0b090a', // Pub Black
        7 => '#7d7779', // Noche 400
    ];

    private ?MetricasDelObservatorio $metricas = null;

    /** Título corto de la serie, sin el rótulo de muestra: p. ej. «Composición del sector». */
    abstract public static function titulo(): string;

    /**
     * Completa «El observatorio ya mide …» en el estado vacío de esta
     * gráfica. Estático y sin efectos secundarios a propósito:
     * `InformeDelObservatorio::series()` lo lee sin instanciar el widget, y
     * de paso es la única fuente de esa frase — antes vivía repetida aquí y
     * en el informe.
     */
    abstract public static function que(): string;

    /** La serie que esta gráfica dibuja, ya resuelta desde el servicio. */
    abstract protected function serie(): SerieDelObservatorio;

    /** El rótulo `n = …` va junto al título, dibuje o no la gráfica. */
    public function getHeading(): string
    {
        $titulo = static::titulo();

        return "{$titulo} ({$this->serie()->rotuloDeMuestra()})";
    }

    /**
     * `ChartWidget::isEmpty()` de fábrica solo mira si `getData()` vino
     * vacío, y eso no basta: `getData()` siempre trae la forma del arreglo
     * `datasets` aunque cada serie interna esté vacía, así que de fábrica
     * nunca se ve vacío. El criterio real es el principio del módulo: sin
     * muestra suficiente, no dibuja, tenga datos o no.
     */
    public function isEmpty(): bool
    {
        return ! $this->serie()->hayMuestraSuficiente();
    }

    /**
     * `chart-widget.blade.php` (vendor/filament/widgets) ya bifurca: si
     * `isEmpty()` es cierto, rinde esto en vez del canvas. Es el mecanismo
     * nativo de Filament para que un `ChartWidget` decida no dibujar — no
     * hace falta un `Widget` con vista propia.
     */
    public function getEmptyState(): View
    {
        return view('components.panel.sin-muestra', [
            'serie' => $this->serie(),
            'que' => static::que(),
        ]);
    }

    public static function canView(): bool
    {
        return Auth::user()?->can('ver_observatorio') === true;
    }

    protected function metricas(): MetricasDelObservatorio
    {
        return $this->metricas ??= app(MetricasDelObservatorio::class);
    }

    /**
     * El relleno de un conjunto de datos, para esparcir dentro de él:
     * `...$this->relleno(2)`. Devuelve las dos claves juntas a propósito —el
     * color de reserva y la ranura que `panel-graficas.js` repinta— porque
     * separarlas es justo como se declara una sin la otra.
     *
     * @return array{backgroundColor: string, asobaresSerie: int}
     */
    protected function relleno(int $ranura): array
    {
        return [
            'backgroundColor' => self::RESERVA_DE_SERIE[$ranura],
            // Chart.js conserva las claves que no conoce, así que ésta viaja
            // con el conjunto hasta el cliente sin registrarla en ningún sitio.
            'asobaresSerie' => $ranura,
        ];
    }
}
