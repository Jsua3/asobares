<?php

namespace App\Panel;

use App\Enums\CargoDelSector;
use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Cartera;
use App\Models\Categoria;
use App\Models\ConsultaGuia;
use App\Models\Municipio;
use App\Models\Postulacion;
use App\Models\Proveedor;
use App\Models\Transaccion;
use App\Models\Vacante;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Las cifras del Observatorio del gremio: seis gráficas más un indicador.
 *
 * Cada método agrega en SQL y devuelve una {@see SerieDelObservatorio}: los
 * datos y el tamaño de muestra que los sostiene. Nada de traer modelos a
 * memoria para contarlos con `groupBy` de Collection — este proyecto ya tuvo
 * que corregirlo una vez (ver `RecaudoMensual`).
 *
 * Solo cuenta lo publicado donde el modelo tiene estado editorial: un
 * asociado, una vacante o un proveedor en borrador no es presencia del
 * gremio. `Aspirante` y `Postulacion` no tienen ese estado editorial —son
 * banco de talento y candidaturas, no contenido del sitio— así que se
 * cuentan completos.
 *
 * `tasaDeMoraActual()` no es una de las seis gráficas: es un indicador aparte
 * que alimenta una tarjeta KPI, no una serie temporal. Ver su docblock para
 * el porqué no vive dentro de `saludFinanciera()`.
 */
class MetricasDelObservatorio
{
    /** Abreviaturas de mes en español: el locale de la app es `en`, así que no basta con Carbon. */
    private const array MESES = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];

    /**
     * Caché de instancia, indexada por nombre de método.
     *
     * La página del observatorio pide varias métricas por render; sin esto
     * cada una repetiría sus consultas. Igual que `ColaDePendientes`.
     *
     * @var array<string, SerieDelObservatorio>
     */
    private array $cache = [];

    public function presenciaPorMunicipio(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularPresenciaPorMunicipio();
    }

    public function composicionDelSector(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularComposicionDelSector();
    }

    public function saludFinanciera(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularSaludFinanciera();
    }

    public function tasaDeMoraActual(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularTasaDeMoraActual();
    }

    public function coberturaDeProveedores(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularCoberturaDeProveedores();
    }

    public function demandaLaboralPorArea(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularDemandaLaboralPorArea();
    }

    public function ofertaContraDemanda(): SerieDelObservatorio
    {
        return $this->cache[__FUNCTION__] ??= $this->calcularOfertaContraDemanda();
    }

    /**
     * Tres señales de presencia por municipio: asociados, vacantes y
     * consultas de la guía. Se combinan solo los municipios que aparecen en
     * al menos una de las tres —no los 12 del Quindío completos, que sería
     * afirmar presencia donde no hay ninguna señal— y se ordenan por
     * consultas descendente: es la señal de demanda ciudadana, la más
     * relevante para una alcaldía.
     */
    private function calcularPresenciaPorMunicipio(): SerieDelObservatorio
    {
        $asociadosPorMunicipio = Asociado::query()
            ->publicado()
            ->selectRaw('municipio_id, count(*) as total')
            ->groupBy('municipio_id')
            ->pluck('total', 'municipio_id');

        // No se usa el scope `publicado()` tal cual porque el join con
        // `asociados` deja dos columnas `estado` en la consulta (vacantes y
        // asociados también la tienen): un `where('estado', ...)` sin
        // calificar es ambiguo y PostgreSQL y SQLite lo rechazan. Se
        // califica a mano para conservar exactamente el mismo filtro.
        $vacantesPorMunicipio = Vacante::query()
            ->join('asociados', 'asociados.id', '=', 'vacantes.asociado_id')
            ->where('vacantes.estado', EstadoPublicacion::Publicado)
            ->selectRaw('asociados.municipio_id as municipio_id, count(*) as total')
            ->groupBy('asociados.municipio_id')
            ->pluck('total', 'municipio_id');

        // Consulta anónima sin flujo editorial: no hay nada que publicar.
        $consultasPorMunicipio = ConsultaGuia::query()
            ->selectRaw('municipio_id, count(*) as total')
            ->groupBy('municipio_id')
            ->pluck('total', 'municipio_id');

        $idsConSenal = $asociadosPorMunicipio->keys()
            ->merge($vacantesPorMunicipio->keys())
            ->merge($consultasPorMunicipio->keys())
            ->unique()
            ->values();

        if ($idsConSenal->isEmpty()) {
            return new SerieDelObservatorio(etiquetas: [], series: [], n: 0, unidad: 'registros');
        }

        $nombresPorId = Municipio::query()->whereIn('id', $idsConSenal)->pluck('nombre', 'id');

        $filas = $idsConSenal
            ->map(fn (int $id): array => [
                'nombre' => $nombresPorId[$id] ?? '',
                'asociados' => (int) ($asociadosPorMunicipio[$id] ?? 0),
                'vacantes' => (int) ($vacantesPorMunicipio[$id] ?? 0),
                'consultas' => (int) ($consultasPorMunicipio[$id] ?? 0),
            ])
            ->sortByDesc('consultas')
            ->values();

        return new SerieDelObservatorio(
            etiquetas: $filas->pluck('nombre')->all(),
            series: [
                'Asociados' => $filas->pluck('asociados')->all(),
                'Vacantes' => $filas->pluck('vacantes')->all(),
                'Consultas de la guía' => $filas->pluck('consultas')->all(),
            ],
            n: $filas->sum('asociados') + $filas->sum('vacantes') + $filas->sum('consultas'),
            unidad: 'registros',
        );
    }

    /** Asociados publicados agrupados por categoría, de mayor a menor. */
    private function calcularComposicionDelSector(): SerieDelObservatorio
    {
        $porCategoria = Asociado::query()
            ->publicado()
            ->selectRaw('categoria_id, count(*) as total')
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');

        if ($porCategoria->isEmpty()) {
            return new SerieDelObservatorio(etiquetas: [], series: [], n: 0, unidad: 'asociados');
        }

        $nombresPorId = Categoria::query()->whereIn('id', $porCategoria->keys())->pluck('nombre', 'id');

        $filas = $porCategoria->sortDesc();

        return new SerieDelObservatorio(
            etiquetas: $filas->keys()->map(fn (int $id): string => $nombresPorId[$id] ?? '')->all(),
            series: ['Asociados' => $filas->values()->map(fn (mixed $total): int => (int) $total)->all()],
            n: (int) $filas->sum(),
            unidad: 'asociados',
        );
    }

    /**
     * Recaudo mensual, dieciocho meses.
     *
     * Solo el recaudo: la tasa de mora no vive aquí (ver
     * {@see calcularTasaDeMoraActual()} para el porqué), así que esta serie
     * es una sola cosa medida de una sola forma, con su `n` real de
     * transacciones aprobadas del periodo.
     */
    private function calcularSaludFinanciera(): SerieDelObservatorio
    {
        $desde = now()->subMonths(17)->startOfMonth();
        $expresionMes = $this->expresionMes('created_at');

        $filasRecaudo = Transaccion::query()
            ->aprobada()
            ->where('created_at', '>=', $desde)
            ->selectRaw("{$expresionMes} as mes, sum(monto) as total, count(*) as cantidad")
            ->groupBy('mes')
            ->get();

        $recaudoPorMes = $filasRecaudo->pluck('total', 'mes');
        $meses = $this->rangoDeMeses($desde, 18);

        return new SerieDelObservatorio(
            etiquetas: array_column($meses, 'etiqueta'),
            series: [
                'Recaudo (COP)' => array_map(
                    fn (array $mes): float => (float) ($recaudoPorMes[$mes['clave']] ?? 0),
                    $meses
                ),
            ],
            n: (int) $filasRecaudo->sum(fn ($fila): int => (int) $fila->cantidad),
            unidad: 'transacciones',
        );
    }

    /**
     * Tasa de mora de hoy, con su propio tamaño de muestra.
     *
     * No es una serie temporal: `carteras` guarda el estado actual del
     * asociado, no su historia, así que no hay dieciocho mediciones reales
     * que graficar. Se separó de `saludFinanciera()` por dos motivos, y el
     * segundo es el que decide:
     *
     * 1. Lectura visual: una recta sin pendiente junto a una curva con
     *    pendiente, en un gráfico que un directivo mira diez segundos, no se
     *    lee como «esto es una foto de hoy» — se lee como «la mora lleva
     *    dieciocho meses igual». Nadie relee la leyenda de cada serie; se lee
     *    la forma. Simular mediciones que nunca ocurrieron es dibujar una
     *    tendencia inventada, aunque el valor sea honesto.
     * 2. El `n` no se puede prestar: las carteras son una muestra propia y
     *    más chica que las transacciones de `saludFinanciera()`. Si esta
     *    cifra heredara el `n` de recaudo, una tasa de mora con muestra
     *    insuficiente (menos de `SerieDelObservatorio::MUESTRA_MINIMA`
     *    carteras) se rotularía «muestra suficiente» solo por viajar en la
     *    misma serie que otra cosa que sí la tiene — justo lo que este
     *    módulo existe para impedir.
     *
     * El día que exista un historial real de mora (por ejemplo, instantáneas
     * mensuales de `carteras`), este método puede volver a ser una serie
     * temporal de verdad, con dieciocho puntos medidos y no repetidos.
     */
    private function calcularTasaDeMoraActual(): SerieDelObservatorio
    {
        $mora = Cartera::query()
            ->selectRaw('count(*) as total, sum(case when meses_mora > 0 then 1 else 0 end) as en_mora')
            ->first();

        $totalCarteras = (int) ($mora->total ?? 0);
        $tasaDeMora = $totalCarteras > 0
            ? round(((int) $mora->en_mora / $totalCarteras) * 100, 1)
            : 0.0;

        return new SerieDelObservatorio(
            etiquetas: ['Hoy'],
            series: ['Tasa de mora (%)' => [$tasaDeMora]],
            n: $totalCarteras,
            unidad: 'carteras',
        );
    }

    /**
     * Proveedores publicados y vigentes por categoría.
     *
     * Recorre las siete categorías del enum, no solo las que tienen filas:
     * que la base no tenga ni un proveedor de mantenimiento es justo la
     * información que el gremio necesita ver, y un `groupBy` que solo
     * devuelve lo que existe la esconde.
     */
    private function calcularCoberturaDeProveedores(): SerieDelObservatorio
    {
        $porCategoria = Proveedor::query()
            ->publicado()
            ->vigente()
            ->selectRaw('categoria_proveedor as categoria, count(*) as total')
            ->groupBy('categoria_proveedor')
            ->pluck('total', 'categoria');

        $etiquetas = [];
        $valores = [];

        foreach (CategoriaProveedor::cases() as $categoria) {
            $etiquetas[] = $categoria->getLabel();
            $valores[] = (int) ($porCategoria[$categoria->value] ?? 0);
        }

        return new SerieDelObservatorio(
            etiquetas: $etiquetas,
            series: ['Proveedores' => $valores],
            n: array_sum($valores),
            unidad: 'proveedores',
        );
    }

    /** Vacantes publicadas por mes y por área, doce meses, para una gráfica apilada. */
    private function calcularDemandaLaboralPorArea(): SerieDelObservatorio
    {
        $desde = now()->subMonths(11)->startOfMonth();
        $expresionMes = $this->expresionMes('created_at');

        $filas = Vacante::query()
            ->publicado()
            ->where('created_at', '>=', $desde)
            ->selectRaw("{$expresionMes} as mes, categoria_cargo as categoria, count(*) as total")
            ->groupBy('mes', 'categoria_cargo')
            ->get();

        $porMesYCategoria = [];
        foreach ($filas as $fila) {
            $porMesYCategoria[$fila->mes][$fila->categoria] = (int) $fila->total;
        }

        $meses = $this->rangoDeMeses($desde, 12);

        $series = [];
        foreach (CargoDelSector::cases() as $cargo) {
            $series[$cargo->getLabel()] = array_map(
                fn (array $mes): int => $porMesYCategoria[$mes['clave']][$cargo->value] ?? 0,
                $meses
            );
        }

        return new SerieDelObservatorio(
            etiquetas: array_column($meses, 'etiqueta'),
            series: $series,
            n: (int) $filas->sum(fn ($fila): int => (int) $fila->total),
            unidad: 'vacantes',
            // Las siete áreas son rebanadas de una sola población —las
            // vacantes publicadas—, no siete medidas independientes: el
            // umbral se le pide al total. Ver `hayMuestraSuficiente()`.
            rebanadasDeUnaMedida: true,
        );
    }

    /**
     * Demanda (vacantes publicadas) contra oferta (aspirantes + postulaciones)
     * por cada área del sector, las siete del enum aunque alguna esté en cero.
     */
    private function calcularOfertaContraDemanda(): SerieDelObservatorio
    {
        $demandaPorCategoria = Vacante::query()
            ->publicado()
            ->selectRaw('categoria_cargo as categoria, count(*) as total')
            ->groupBy('categoria_cargo')
            ->pluck('total', 'categoria');

        $aspirantesPorCategoria = Aspirante::query()
            ->selectRaw('categoria_cargo as categoria, count(*) as total')
            ->groupBy('categoria_cargo')
            ->pluck('total', 'categoria');

        $postulacionesPorCategoria = Postulacion::query()
            ->join('vacantes', 'vacantes.id', '=', 'postulaciones.vacante_id')
            ->selectRaw('vacantes.categoria_cargo as categoria, count(*) as total')
            ->groupBy('vacantes.categoria_cargo')
            ->pluck('total', 'categoria');

        $etiquetas = [];
        $demanda = [];
        $oferta = [];

        foreach (CargoDelSector::cases() as $cargo) {
            $etiquetas[] = $cargo->getLabel();
            $demanda[] = (int) ($demandaPorCategoria[$cargo->value] ?? 0);
            $oferta[] = (int) ($aspirantesPorCategoria[$cargo->value] ?? 0)
                + (int) ($postulacionesPorCategoria[$cargo->value] ?? 0);
        }

        return new SerieDelObservatorio(
            etiquetas: $etiquetas,
            series: ['Demanda' => $demanda, 'Oferta' => $oferta],
            n: array_sum($demanda) + array_sum($oferta),
            unidad: 'registros',
        );
    }

    /**
     * Expresión SQL de mes (`YYYY-MM`) según el motor de la conexión activa.
     *
     * Mismo patrón que `RecaudoMensual`, pero con año además de mes: ese
     * widget solo mira el año en curso, mientras que aquí las series cruzan
     * año calendario (18 y 12 meses hacia atrás). Vive aquí y no repetida en
     * cada método: seis copias del mismo `match` sería la duplicación que
     * este servicio existe para evitar.
     */
    private function expresionMes(string $columna): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char({$columna}, 'YYYY-MM')",
            'mysql', 'mariadb' => "date_format({$columna}, '%Y-%m')",
            default => "strftime('%Y-%m', {$columna})",
        };
    }

    /**
     * Los últimos `$cantidad` meses hasta hoy, del más antiguo al más
     * reciente, con su clave de agrupación (`YYYY-MM`) y su etiqueta corta
     * en español para el eje de la gráfica.
     *
     * @return list<array{clave: string, etiqueta: string}>
     */
    private function rangoDeMeses(Carbon $desde, int $cantidad): array
    {
        return collect(range(0, $cantidad - 1))
            ->map(function (int $i) use ($desde): array {
                $mes = $desde->copy()->addMonths($i);

                return [
                    'clave' => $mes->format('Y-m'),
                    'etiqueta' => self::MESES[(int) $mes->format('n')].' '.$mes->format('y'),
                ];
            })
            ->all();
    }
}
