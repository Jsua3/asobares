<?php

namespace App\Http\Middleware;

use App\Models\VisitaDiaria;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cuenta la visita después de servirla, y nunca a costa de servirla.
 *
 * Va envuelto en `rescue()` por la misma razón por la que lo van los correos
 * del sitio (D-23): una analítica que tumba la página que mide no es una
 * analítica, es una avería. El fallo se reporta al registro y el visitante no
 * se entera.
 */
class ContarVisitaDelSitio
{
    /**
     * Rastreadores conocidos. El navegador se MIRA para decidir; no se guarda.
     * Sin esto la cifra la escribirían los buscadores y el gremio leería como
     * interés lo que es indexación.
     */
    private const RASTREADORES = '/(bot|crawler|spider|crawling|slurp|bingpreview|facebookexternalhit|headlesschrome|lighthouse|curl|wget|python-requests)/i';

    /**
     * Lo que no es el sitio público: el panel y el portal del afiliado.
     *
     * `mi-cuenta.` es el que trabaja: esas rutas sí pasan por el grupo `web`.
     * `filament.` no llega nunca hoy --el panel arma su propia pila de
     * middleware en `AdminPanelProvider` y no usa este grupo-- y se queda como
     * seguro para el día que eso cambie. Queda dicho para que nadie lea la
     * prueba del panel como si este filtro fuera lo que la hace pasar.
     */
    private const FUERA_DEL_SITIO = ['filament.', 'mi-cuenta.'];

    public function handle(Request $request, Closure $siguiente): Response
    {
        $respuesta = $siguiente($request);

        rescue(fn () => $this->contar($request, $respuesta));

        return $respuesta;
    }

    private function contar(Request $request, Response $respuesta): void
    {
        if (! $request->isMethod('GET') || $respuesta->getStatusCode() !== 200) {
            return;
        }

        $ruta = $request->route()?->getName();

        // Una ruta sin nombre no se puede agregar sin caer en la URL, que es
        // justo lo que esta tabla no guarda.
        if ($ruta === null || Str::startsWith($ruta, self::FUERA_DEL_SITIO)) {
            return;
        }

        if (preg_match(self::RASTREADORES, (string) $request->userAgent()) === 1) {
            return;
        }

        VisitaDiaria::registrar($ruta);
    }
}
