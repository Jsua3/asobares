<?php

namespace App\Pagos;

use App\Models\Transaccion;
use Illuminate\Http\Request;

/**
 * Contrato de la pasarela. El sitio nunca sabe si detrás está Bold o la
 * simulación: solo pide un enlace de pago y sabe leer una confirmación.
 */
interface PasarelaDePago
{
    /** URL a la que se envía a la persona para pagar. */
    public function crearEnlaceDePago(Transaccion $transaccion): string;

    /** Comprueba que la confirmación venga realmente de la pasarela. */
    public function firmaValida(Request $request): bool;

    /** Traduce la notificación de la pasarela al lenguaje del dominio. */
    public function interpretarConfirmacion(Request $request): ?ResultadoDePago;

    public function nombre(): string;
}
