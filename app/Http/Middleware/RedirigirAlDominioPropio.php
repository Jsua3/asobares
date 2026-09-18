<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manda con un 301 al dominio del gremio a quien llegue por otro host.
 *
 * Laravel Cloud sigue sirviendo el sitio en su host provisional
 * (`*.laravel.cloud`) aunque el gremio ya tenga dominio, y cada página de ahí
 * se declara canónica a sí misma: para Google son dos copias del sitio
 * compitiendo entre sí. El dominio que manda es el de `APP_URL`.
 *
 * Solo se redirige lo que un buscador o una persona abre: GET y HEAD. Un POST
 * —el webhook de Bold, un formulario enviado— se atiende donde llega, porque
 * quien lo manda no repite el cuerpo detrás de un 301. `/up` tampoco se toca:
 * es la comprobación de salud del hosting.
 *
 * No hace nada mientras el sitio no sea indexable (`sitio.indexable`) ni
 * mientras `APP_URL` siga siendo un host de Laravel Cloud: redirigir hacia el
 * host provisional sería mandar el dominio del gremio a la copia que se quiere
 * retirar.
 */
class RedirigirAlDominioPropio
{
    public function handle(Request $request, Closure $siguiente): Response
    {
        $dominio = $this->dominioPropio();

        if ($dominio === null
            || (! $request->isMethod('GET') && ! $request->isMethod('HEAD'))
            || $request->is('up')
            || strcasecmp($request->getHost(), (string) parse_url($dominio, PHP_URL_HOST)) === 0) {
            return $siguiente($request);
        }

        return redirect()->away($dominio.$request->getRequestUri(), Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Esquema y host de `APP_URL`, sin barra final; nulo si no hay a dónde ir.
     */
    private function dominioPropio(): ?string
    {
        if (! config('sitio.indexable')) {
            return null;
        }

        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '' || str_ends_with(strtolower($host), '.laravel.cloud')) {
            return null;
        }

        return (parse_url($url, PHP_URL_SCHEME) ?: 'https').'://'.$host;
    }
}
