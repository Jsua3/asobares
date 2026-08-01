<?php

namespace App\Pagos;

use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;

/**
 * Lo que devuelve una pasarela al confirmar un pago, ya normalizado.
 */
final readonly class ResultadoDePago
{
    /** @param  array<string, mixed>  $payload */
    public function __construct(
        public string $referencia,
        public EstadoTransaccion $estado,
        public MetodoPago $metodo,
        public array $payload = [],
    ) {}

    public function fueAprobado(): bool
    {
        return $this->estado === EstadoTransaccion::Aprobada;
    }
}
