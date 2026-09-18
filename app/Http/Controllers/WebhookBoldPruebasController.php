<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recibe pruebas de Bold sin acceder a transacciones ni aplicar pagos.
 * La firma con llave vacía no autentica al emisor: esta ruta es solo diagnóstica.
 */
class WebhookBoldPruebasController
{
    public function __invoke(Request $request): JsonResponse
    {
        $linkPermitido = config('pagos.bold.sandbox_webhook_link');

        if (! is_string($linkPermitido) || ! str_starts_with($linkPermitido, 'LNK_') || strlen($linkPermitido) <= 4) {
            abort(404);
        }

        $firma = $request->header('x-bold-signature');

        if (! is_string($firma) || ! hash_equals(hash_hmac('sha256', base64_encode($request->getContent()), ''), strtolower(trim($firma)))) {
            return response()->json(['mensaje' => 'Firma inválida.'], 401);
        }

        $datos = $request->json()->all();
        $referencia = data_get($datos, 'data.metadata.reference');

        if (! is_string($referencia) || ! hash_equals($linkPermitido, $referencia)) {
            return response()->json(['mensaje' => 'Referencia de prueba no autorizada.'], 404);
        }

        if (! in_array(data_get($datos, 'type'), ['SALE_APPROVED', 'SALE_REJECTED', 'VOID_APPROVED', 'VOID_REJECTED'], true)) {
            return response()->json(['mensaje' => 'Evento no admitido.'], 422);
        }

        return response()->json(['mensaje' => 'Webhook de prueba recibido.']);
    }
}
