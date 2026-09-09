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
     * Lo que no es el sitio público: el panel, el portal del afiliado y la
     * pasarela de pago.
     *
     * `mi-cuenta.` es el que trabaja: esas rutas sí pasan por el grupo `web`.
     * `filament.` no llega nunca hoy --el panel arma su propia pila de
     * middleware en `AdminPanelProvider` y no usa este grupo-- y se queda como
     * seguro para el día que eso cambie. Queda dicho para que nadie lea la
     * prueba del panel como si este filtro fuera lo que la hace pasar.
     *
     * `pago.` entró el 9 sep 2026: las pantallas del cobro son zona privada y el
     * propio sitio ya lo declaraba --`robots.txt` lista `/pago/` y
     * `/pago-simulado` junto al panel y a `/mi-cuenta`--. Contarlas mezclaba el
     * tráfico de una pasarela con el interés por el contenido.
     */
    private const FUERA_DEL_SITIO = ['filament.', 'mi-cuenta.', 'pago.'];

    /**
     * Lo que se cuenta es una PÁGINA, y una página es `text/html`.
     *
     * El filtro anterior era por nombre de ruta, y por eso dejaba pasar tres
     * cosas que no son páginas y sí cumplían sus cuatro condiciones --GET, 200,
     * con nombre, fuera de los prefijos excluidos--: la descarga de un formato
     * de la guía (un PDF, y encima ya contado en `consultas_guia`), `robots.txt`
     * y `sitemap.xml`. Las dos últimas las piden casi solo rastreadores, y el
     * filtro por agente de usuario solo atrapa a los conocidos.
     *
     * Mirar el tipo de contenido en vez de mantener una lista de excepciones
     * hace que esto no se vuelva a desalinear: una página nueva se cuenta sola,
     * y una descarga, un canal RSS o un JSON nuevos quedan fuera sin que nadie
     * tenga que acordarse. Es además lo que el contrato de este middleware dice
     * literalmente en `bootstrap/app.php`: «cuenta páginas servidas, no
     * descargas ni webhooks».
     */
    private const TIPO_DE_UNA_PAGINA = 'text/html';

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

        if (! str_starts_with((string) $respuesta->headers->get('Content-Type'), self::TIPO_DE_UNA_PAGINA)) {
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
