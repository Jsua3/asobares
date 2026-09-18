<?php

namespace App\Pagos;

use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if ($this->apiKey === '') {
            throw new RuntimeException(
                'Bold está seleccionado como pasarela pero falta BOLD_API_KEY en el .env.'
            );
        }

        if ($this->secret === '' && ! ($this->sandbox && app()->environment('local', 'testing'))) {
            throw new RuntimeException(
                'Bold está seleccionado como pasarela pero falta BOLD_SECRET en el .env.'
            );
        }

        $existente = $transaccion->fresh();

        if ($existente->estado !== EstadoTransaccion::Pendiente) {
            throw new RuntimeException('Solo se pueden generar enlaces para pagos pendientes.');
        }

        if ($existente->bold_payment_link !== null) {
            $url = data_get($existente->payload, 'bold_checkout_url');

            if (! is_string($url) || $url === '') {
                throw new RuntimeException('El enlace Bold registrado no tiene URL; requiere conciliación manual.');
            }

            return $url;
        }

        $monto = (float) $existente->monto;

        if ($existente->moneda !== 'COP' || $monto <= 0 || $monto !== round($monto)) {
            throw new RuntimeException('Bold API Link requiere un monto positivo en pesos enteros (COP).');
        }

        $respuesta = Http::withHeaders([
            'Authorization' => "x-api-key {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])
            ->timeout(20)
            ->post("{$this->urlBase}/online/link/v1", [
                'amount_type' => 'CLOSE',
                'amount' => [
                    'currency' => $existente->moneda,
                    'tip_amount' => 0,
                    'total_amount' => (int) $monto,
                ],
                'description' => $existente->concepto->getLabel(),
                'reference' => $existente->referencia,
                'callback_url' => route('pago.retorno', ['transaccion' => $existente->referencia]),
                'payment_methods' => ['PSE', 'CREDIT_CARD'],
                'expiration_date' => now()->addDay()->getTimestampMs() * 1_000_000,
            ]);

        $respuesta->throw();

        $payload = $respuesta->json('payload');
        $url = data_get($payload, 'url');

        $paymentLink = data_get($payload, 'payment_link');

        if (! is_string($url) || $url === '' || ! is_string($paymentLink) || ! str_starts_with($paymentLink, 'LNK_')) {
            throw new RuntimeException('Bold no devolvió un enlace de pago identificable.');
        }

        return DB::transaction(function () use ($transaccion, $url, $payload, $paymentLink): string {
            $actual = Transaccion::whereKey($transaccion->id)->lockForUpdate()->firstOrFail();

            if ($actual->bold_payment_link !== null) {
                $urlExistente = data_get($actual->payload, 'bold_checkout_url');

                if (! is_string($urlExistente) || $urlExistente === '') {
                    throw new RuntimeException('El enlace Bold registrado no tiene URL; requiere conciliación manual.');
                }

                return $urlExistente;
            }

            if ($actual->estado !== EstadoTransaccion::Pendiente) {
                throw new RuntimeException('El pago dejó de estar pendiente antes de guardar el enlace Bold.');
            }

            $actual->update([
                'bold_payment_link' => $paymentLink,
                'payload' => array_merge($actual->payload ?? [], [
                    'bold_checkout_url' => $url,
                    'bold_link' => $payload,
                    'bold_payment_link' => $paymentLink,
                ]),
            ]);

            return $url;
        });
    }

    /**
     * Bold firma cada notificación y la envía en el encabezado `x-bold-signature`.
     *
     * El orden importa y no es el habitual: primero se codifica el cuerpo CRUDO
     * en Base64, y sobre ese texto se aplica HMAC-SHA256 con la llave secreta
     * (`BOLD_SECRET`). El resultado se compara en HEXADECIMAL, no en Base64.
     * Ver https://developers.bold.co/webhook.
     */
    public function firmaValida(Request $request): bool
    {
        $firmaRecibida = $request->header('x-bold-signature');

        if (! is_string($firmaRecibida) || $firmaRecibida === '') {
            return false;
        }

        // En pruebas Bold firma con llave vacía a propósito, y sin eso no hay
        // forma de ejercitar la integración. Pero un HMAC con llave vacía lo
        // reproduce cualquiera, así que la excusa vale sólo en la máquina de
        // desarrollo: un servidor de pruebas expuesto a internet con
        // BOLD_SANDBOX=true y la llave sin poner aceptaría notificaciones
        // falsificadas. Fuera de local, llave vacía es rechazo.
        if ($this->secret === '' && ! ($this->sandbox && app()->environment('local', 'testing'))) {
            return false;
        }

        $firmaEsperada = hash_hmac('sha256', base64_encode($request->getContent()), $this->secret);

        return hash_equals($firmaEsperada, strtolower(trim($firmaRecibida)));
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
            'VOID_APPROVED' => EstadoTransaccion::Rechazada,
            default => EstadoTransaccion::Pendiente,
        };

        $metodo = match (strtoupper((string) data_get($datos, 'data.payment_method', ''))) {
            'PSE' => MetodoPago::Pse,
            'CARD', 'CARD_WEB', 'CREDIT_CARD', 'DEBIT_CARD' => MetodoPago::Tarjeta,
            default => MetodoPago::Otro,
        };

        // Bold no documenta un único nombre para el total, así que se prueban
        // las formas conocidas. Si no aparece ninguna, se devuelve null y
        // RegistroDePagos lo registra en el log en vez de bloquear el pago.
        $montoNotificado = data_get($datos, 'data.amount.total')
            ?? data_get($datos, 'data.amount.total_amount')
            ?? data_get($datos, 'data.total_amount');

        return new ResultadoDePago(
            referencia: $referencia,
            estado: $estado,
            metodo: $metodo,
            payload: [
                'pasarela' => 'bold',
                'sandbox' => $this->sandbox,
                'tipo_bold' => data_get($datos, 'type'),
                'evento_id' => data_get($datos, 'id'),
                'payment_id' => data_get($datos, 'data.payment_id'),
                'bold_code' => data_get($datos, 'data.bold_code'),
                'evento' => $datos,
                ...(data_get($datos, 'type') === 'SALE_REJECTED' ? [
                    'ultimo_intento_rechazado' => [
                        'evento_id' => data_get($datos, 'id'),
                        'payment_id' => data_get($datos, 'data.payment_id'),
                    ],
                ] : []),
            ],
            monto: is_numeric($montoNotificado) ? (float) $montoNotificado : null,
            moneda: is_string($moneda = data_get($datos, 'data.amount.currency')) ? $moneda : null,
        );
    }

    public function nombre(): string
    {
        return 'bold';
    }
}
