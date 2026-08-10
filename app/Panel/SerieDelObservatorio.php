<?php

namespace App\Panel;

/**
 * Una serie del observatorio con su tamaño de muestra.
 *
 * El `n` no es metadato: es parte de la cifra. Este módulo existe para que la
 * dirección lleve datos a una alcaldía, y un porcentaje sin muestra detrás no
 * aguanta la primera pregunta.
 *
 * @param  list<string>  $etiquetas
 * @param  array<string, list<int|float>>  $series
 */
final readonly class SerieDelObservatorio
{
    /**
     * Regla de oro convencional: por debajo de treinta observaciones una
     * cifra no sostiene una afirmación. Es el mismo umbral que marca
     * «muestra pequeña» en la tarjeta KPI, y vive aquí para que no puedan
     * divergir.
     */
    public const int MUESTRA_MINIMA = 30;

    public function __construct(
        public array $etiquetas,
        public array $series,
        public int $n,
        public string $unidad,
        /**
         * Si los conjuntos son rebanadas de UNA misma medida (las vacantes
         * repartidas por área) en vez de medidas independientes que se
         * cruzan (asociados contra vacantes contra consultas). Cambia a qué
         * se le exige el umbral; ver `hayMuestraSuficiente()`.
         */
        public bool $rebanadasDeUnaMedida = false,
    ) {}

    /**
     * Con un único conjunto de datos, el umbral se compara contra `n` tal
     * cual. Con varios, el umbral se exige a CADA conjunto por separado, no
     * a su suma combinada en `n`.
     *
     * `n` combinado es una puerta trasera al principio del módulo: una serie
     * robusta le presta credibilidad a otras que no la tienen. Es justo lo
     * que le pasaba a `MetricasDelObservatorio::presenciaPorMunicipio()`: 24
     * asociados + 6 vacantes + 732 consultas sellaban «muestra suficiente»
     * (n = 762) con el 96 % de ese total viniendo de una sola señal —las
     * consultas—, mientras las barras de «Asociados» descansaban sobre 24
     * observaciones y las de «Vacantes» sobre 6, los mismos dos números que
     * el módulo declara insuficientes en cualquier otra gráfica.
     *
     * La suma de cada conjunto asume que sus valores son observaciones
     * (conteos), no cantidades derivadas: cierto en las seis series del
     * observatorio que hoy traen más de un conjunto de datos. Una serie
     * futura que mezcle un conjunto de dinero o de porcentaje junto a otro
     * de conteos necesitaría un tamaño de muestra explícito por conjunto, no
     * esta derivación por suma.
     *
     * Y hay un caso que NO es ese: `demandaLaboralPorArea()` no cruza medidas
     * independientes, parte una sola —las vacantes— en siete rebanadas por
     * área. Exigirle el umbral a cada rebanada serían doscientas diez
     * vacantes, y «Otros», que es un cajón residual, no las tendría nunca: la
     * gráfica quedaría apagada para siempre mientras el módulo anuncia un
     * umbral de treinta. Por eso la distinción es explícita en la serie y no
     * se infiere de `count($series)`, que no puede saber cuál de los dos
     * casos tiene delante.
     */
    public function hayMuestraSuficiente(): bool
    {
        if ($this->rebanadasDeUnaMedida || count($this->series) <= 1) {
            return $this->n >= self::MUESTRA_MINIMA;
        }

        foreach ($this->series as $valores) {
            if (array_sum($valores) < self::MUESTRA_MINIMA) {
                return false;
            }
        }

        return true;
    }

    public function estaVacia(): bool
    {
        return $this->etiquetas === [] || $this->n === 0;
    }

    public function rotuloDeMuestra(): string
    {
        return "n = {$this->n} {$this->unidad}";
    }

    /**
     * El rótulo de la muestra que de verdad decide, para explicar por qué una
     * serie no alcanza.
     *
     * `rotuloDeMuestra()` da el `n` combinado, y en una serie que cruza
     * medidas independientes ése no es el que falla: puede ser enorme. Poner
     * ese número al lado del umbral producía una contradicción en pantalla
     * —«hoy hay n = 762 registros y hacen falta al menos 30»— justo en el
     * módulo que existe para aguantar la primera pregunta en una alcaldía.
     * Lo honesto es nombrar la señal que no llega.
     */
    public function rotuloDeLaMuestraQueDecide(): string
    {
        $flaco = $this->conjuntoMasFlaco();

        if ($flaco === null) {
            return $this->rotuloDeMuestra();
        }

        [$nombre, $observaciones] = $flaco;

        return "«{$nombre}» solo reúne {$observaciones} {$this->unidad}";
    }

    /**
     * El conjunto con menos observaciones, o `null` cuando no hay ninguno que
     * señalar porque la muestra la decide el total.
     *
     * @return array{string, int}|null
     */
    private function conjuntoMasFlaco(): ?array
    {
        if ($this->rebanadasDeUnaMedida || count($this->series) <= 1) {
            return null;
        }

        $flaco = null;
        foreach ($this->series as $nombre => $valores) {
            $observaciones = (int) array_sum($valores);

            if ($flaco === null || $observaciones < $flaco[1]) {
                $flaco = [(string) $nombre, $observaciones];
            }
        }

        return $flaco;
    }
}
