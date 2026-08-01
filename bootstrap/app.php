<?php

use App\Http\Middleware\AsegurarRolAsociado;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

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
