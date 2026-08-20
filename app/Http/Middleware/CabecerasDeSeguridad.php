<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las cabeceras que el sitio no emitía en ninguna respuesta.
 *
 * La que más rinde aquí es `nosniff`. La galería de asociados se sirve desde
 * /storage, y sin ella el navegador puede adivinar el tipo de un archivo por
 * su contenido en vez de creerle al servidor: un archivo subido con la
 * extensión equivocada vuelve a ejecutarse como HTML en el origen del sitio,
 * que es justo lo que cierra la extensión derivada del MIME.
 *
 * No hay CSP todavía, y no es un olvido: la maqueta pública lleva un <script>
 * en línea que fija el tema antes del primer pintado, atributos `x-init` de
 * Alpine y Leaflet desde un CDN. Ponerla sin preparar eso apagaría medio
 * sitio, así que va aparte, primero en modo `Report-Only`.
 */
class CabecerasDeSeguridad
{
    /** @var array<string, string> */
    private const array CABECERAS = [
        // El navegador respeta el Content-Type declarado y no lo adivina.
        'X-Content-Type-Options' => 'nosniff',
        // Nada de este sitio necesita vivir dentro de un iframe ajeno.
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        // El sitio no usa ninguna de las tres; declararlo evita que un script
        // inyectado las pida en nombre del origen.
        'Permissions-Policy' => 'geolocation=(), camera=(), microphone=()',
        /*
         * Un año, subdominios incluidos y SIN `preload`.
         *
         * Sólo tiene efecto sobre https, que es lo único que sirve el hosting;
         * el navegador la ignora en http, así que no estorba en desarrollo.
         * `preload` queda fuera a propósito: mete el dominio en una lista que
         * traen los navegadores compilada y salir de ella tarda meses. Eso se
         * decide cuando el dominio definitivo del gremio esté en su sitio, no
         * en el primer despliegue de pruebas.
         */
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ];

    public function handle(Request $request, Closure $siguiente): Response
    {
        $respuesta = $siguiente($request);

        foreach (self::CABECERAS as $nombre => $valor) {
            // `headers->set` sobrescribiría lo que una respuesta puso a
            // propósito: la descarga de formatos de la guía ya fija la suya.
            if (! $respuesta->headers->has($nombre)) {
                $respuesta->headers->set($nombre, $valor);
            }
        }

        return $respuesta;
    }
}
