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

    public function hayMuestraSuficiente(): bool
    {
        return $this->n >= self::MUESTRA_MINIMA;
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
