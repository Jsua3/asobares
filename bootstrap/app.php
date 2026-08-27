<?php

use App\Http\Middleware\AsegurarRolAsociado;
use App\Http\Middleware\CabecerasDeSeguridad;
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
        ]);

        // Global, no sólo en `web`: también cubre las descargas y cualquier
        // respuesta que no pase por el grupo del sitio.
        $middleware->append(CabecerasDeSeguridad::class);

        // Rotar la contraseña de un afiliado desde el panel tiene que cerrar la
        // sesión que alguien tuviera abierta con la clave vieja. Lo fija
        // `InvalidacionDeSesionTest`, que se comprobó en rojo sin esta línea.
        $middleware->web(append: [AuthenticateSession::class]);

        // El hosting de producción todavía no está decidido. Cuando se elija,
        // TRUSTED_PROXIES debe listar las IPs del balanceador (o `*` si el
        // proveedor no las publica): sin esto todas las peticiones parecen
        // venir de la misma IP —los límites por IP colapsan en un solo cubo—
        // y las URLs se generan en http, incluida la que recibe Bold.
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
