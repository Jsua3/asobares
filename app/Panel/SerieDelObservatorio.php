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
     */
    public function hayMuestraSuficiente(): bool
    {
        if (count($this->series) <= 1) {
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
}
