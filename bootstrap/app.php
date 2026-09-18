<?php

use App\Http\Middleware\AsegurarRolAsociado;
use App\Http\Middleware\CabecerasDeSeguridad;
use App\Http\Middleware\ContarVisitaDelSitio;
use App\Http\Middleware\ExigirContrasenaPropia;
use App\Http\Middleware\RedirigirAlDominioPropio;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol.asociado' => AsegurarRolAsociado::class,
            'contrasena.propia' => ExigirContrasenaPropia::class,
        ]);

        // Global, no sólo en `web`: también cubre las descargas y cualquier
        // respuesta que no pase por el grupo del sitio.
        $middleware->append(CabecerasDeSeguridad::class);

        // Global y detrás de las cabeceras, para que el 301 también las lleve.
        $middleware->append(RedirigirAlDominioPropio::class);

        // Rotar la contraseña de un afiliado desde el panel tiene que cerrar la
        // sesión que alguien tuviera abierta con la clave vieja. Lo fija
        // `InvalidacionDeSesionTest`.
        $middleware->web(append: [AuthenticateSession::class]);

        // En `web` y no global: cuenta páginas servidas, no descargas ni
        // webhooks. Qué es una página lo decide el propio middleware por el
        // `Content-Type` de la respuesta, y excluye por nombre de ruta el
        // portal del afiliado y la pasarela de pago; el panel arma su propia
        // pila y no pasa por este grupo.
        $middleware->web(append: [ContarVisitaDelSitio::class]);

        // TRUSTED_PROXIES lista las IPs del balanceador, o `*` si el proveedor
        // no las publica, que es el caso de Laravel Cloud. Sin esto todas las
        // peticiones parecen venir de la misma IP —los límites por IP colapsan
        // en un solo cubo— y las URLs se generan en http, incluida la que
        // recibe Bold.
        //
        // `env()` y no `config()`: este callback corre al resolver el kernel
        // HTTP, antes de que la aplicación cargue su configuración.
        $proxies = env('TRUSTED_PROXIES');

        if (filled($proxies)) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', (string) $proxies));
        }

        // La sesión del panel y la de /mi-cuenta comparten guard: al expirar,
        // el visitante público vuelve al login de asociados, no al de Filament.
        $middleware->redirectGuestsTo(fn (Request $request): string => $request->is('admin*')
            ? route('filament.admin.auth.login')
            : route('mi-cuenta.entrar'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
