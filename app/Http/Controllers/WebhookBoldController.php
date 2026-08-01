<?php

namespace App\Http\Controllers;

use App\Pagos\PasarelaDePago;
use App\Services\RegistroDePagos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe las confirmaciones de pago de Bold.
 *
 * Se verifica la firma ANTES de mirar el contenido: sin firma válida el
 * cuerpo es texto de un desconocido, no una confirmación de pago.
 */
class WebhookBoldController
{
    public function __invoke(Request $request, PasarelaDePago $pasarela, RegistroDePagos $pagos): JsonResponse
    {
        if (! $pasarela->firmaValida($request)) {
            Log::warning('Webhook de Bold rechazado por firma inválida.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['mensaje' => 'Firma inválida.'], 401);
        }

        $resultado = $pasarela->interpretarConfirmacion($request);

        if ($resultado === null) {
            return response()->json(['mensaje' => 'Notificación sin referencia utilizable.'], 422);
        }

        $transaccion = $pagos->aplicarConfirmacion($resultado);

        if ($transaccion === null) {
            Log::warning('Webhook de Bold para una referencia desconocida.', [
                'referencia' => $resultado->referencia,
            ]);

            return response()->json(['mensaje' => 'Transacción no encontrada.'], 404);
        }

        return response()->json([
            'referencia' => $transaccion->referencia,
            'estado' => $transaccion->estado->value,
        ]);
    }
}
