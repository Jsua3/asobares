<?php

namespace App\Pagos;

use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;

/**
 * Lo que devuelve una pasarela al confirmar un pago, ya normalizado.
 */
final readonly class ResultadoDePago
{
    /**
     * `monto` y `moneda` son lo que la pasarela dice haber cobrado de verdad.
     * Van como opcionales porque no toda pasarela los informa; cuando llegan,
     * tienen que cuadrar con la transacción antes de aplicar ningún efecto.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $referencia,
        public EstadoTransaccion $estado,
        public MetodoPago $metodo,
        public array $payload = [],
        public ?float $monto = null,
        public ?string $moneda = null,
    ) {}

    public function fueAprobado(): bool
    {
        return $this->estado === EstadoTransaccion::Aprobada;
    }
}
