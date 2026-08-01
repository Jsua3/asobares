<?php

namespace App\Pagos;

use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integración real con Bold (developers.bold.co).
 *
 * Queda implementada según la documentación pública pero SIN credenciales:
 * para activarla basta poner BOLD_API_KEY y BOLD_SECRET en el .env y
 * cambiar PAYMENT_DRIVER a `bold`.
 */
class PasarelaBold implements PasarelaDePago
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $secret,
        private readonly string $urlBase,
        private readonly bool $sandbox,
    ) {}

    /**
     * Crea un link de pago por API y devuelve la URL a la que redirigir.
     */
    public function crearEnlaceDePago(Transaccion $transaccion): string
    {
        if ($this->apiKey === '' || $this->secret === '') {
            throw new RuntimeException(
                'Bold está seleccionado como pasarela pero faltan BOLD_API_KEY y BOLD_SECRET en el .env.'
            );
        }

        $respuesta = Http::withHeaders([
            'Authorization' => "x-api-key {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])
            ->timeout(20)
            ->post("{$this->urlBase}/online/link/v1", [
                'amount_type' => 'CLOSE',
                'amount' => [
                    'currency' => $transaccion->moneda,
                    'total_amount' => (int) round((float) $transaccion->monto),
                ],
                'description' => $transaccion->concepto->getLabel(),
                'reference' => $transaccion->referencia,
                'callback_url' => route('pago.estado', ['transaccion' => $transaccion->referencia]),
                'payment_methods' => ['PSE', 'CREDIT_CARD'],
                'expiration_date' => now()->addDay()->getTimestampMs() * 1_000_000,
            ]);

        $respuesta->throw();

        $url = $respuesta->json('payload.url');

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('Bold no devolvió una URL de pago utilizable.');
        }

        $transaccion->update(['payload' => array_merge($transaccion->payload ?? [], [
            'bold_link' => $respuesta->json('payload'),
        ])]);

        return $url;
    }

    /**
     * Bold firma cada notificación con HMAC-SHA256 sobre el cuerpo crudo,
     * usando la llave secreta, y la envía en el encabezado `x-bold-signature`.
     */
    public function firmaValida(Request $request): bool
    {
        $firmaRecibida = $request->header('x-bold-signature');

        if (! is_string($firmaRecibida) || $firmaRecibida === '' || $this->secret === '') {
            return false;
        }

        $firmaEsperada = base64_encode(hash_hmac('sha256', $request->getContent(), $this->secret, true));

        return hash_equals($firmaEsperada, $firmaRecibida);
    }

    public function interpretarConfirmacion(Request $request): ?ResultadoDePago
    {
        $datos = $request->json()->all();
        $referencia = data_get($datos, 'data.metadata.reference') ?? data_get($datos, 'data.reference');

        if (! is_string($referencia) || $referencia === '') {
            return null;
        }

        $estado = match (data_get($datos, 'type')) {
            'SALE_APPROVED' => EstadoTransaccion::Aprobada,
            'SALE_REJECTED', 'VOID_APPROVED' => EstadoTransaccion::Rechazada,
            default => EstadoTransaccion::Pendiente,
        };

        $metodo = match (strtoupper((string) data_get($datos, 'data.payment_method', ''))) {
            'PSE' => MetodoPago::Pse,
            'CREDIT_CARD', 'DEBIT_CARD' => MetodoPago::Tarjeta,
            default => MetodoPago::Otro,
        };

        return new ResultadoDePago(
            referencia: $referencia,
            estado: $estado,
            metodo: $metodo,
            payload: ['pasarela' => 'bold', 'sandbox' => $this->sandbox, 'evento' => $datos],
        );
    }

    public function nombre(): string
    {
        return 'bold';
    }
}
