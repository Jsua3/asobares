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

        VisitaDiaria::registrar($ruta, $this->esUnaLlegadaAlSitio($request));
    }

    /**
     * Si esta petición es alguien ENTRANDO al sitio, y no navegando por dentro
     * (Acta 08, A-03).
     *
     * La pregunta la contesta el `Referer`: si no viene de nuestro propio host,
     * la página es la primera de una visita. Sin procedencia --alguien que
     * escribe la dirección, la trae en marcadores o llega desde WhatsApp-- es lo
     * más entrada que hay.
     *
     * ⚠️ **El encabezado se mira y no se guarda.** Es el mismo trato que recibe
     * el agente de usuario unas líneas más arriba, y por el mismo motivo: una URL
     * de procedencia puede traer términos de búsqueda, identificadores de campaña
     * o el perfil desde el que se hizo clic. Aquí solo se responde sí o no.
     *
     * Lo que esto NO mide, y hay que decirlo cada vez que se cite la cifra: no
     * son personas distintas. Dos visitas de la misma persona en dos días cuentan
     * dos. Contar personas exige IP, cookie o sesión, que es justo lo que este
     * diseño evita (Acta 07, A-02, y D-19 sin resolver).
     *
     * Sabemos que sobrecuenta un poco: un navegador que borre el `Referer` --modo
     * privado estricto, alguna extensión-- hace que su navegación interna parezca
     * una llegada. Se acepta a propósito. La alternativa es una cookie, y una
     * cookie es exactamente la línea que este módulo no cruza; el sesgo va hacia
     * arriba, es pequeño y es estable, así que la comparación entre semanas
     * --que es para lo que sirve la cifra-- se sostiene igual.
     */
    private function esUnaLlegadaAlSitio(Request $request): bool
    {
        $procedencia = $request->headers->get('referer');

        if (! is_string($procedencia) || $procedencia === '') {
            return true;
        }

        return parse_url($procedencia, PHP_URL_HOST) !== $request->getHost();
    }
}
