<?php

namespace App\Providers;

use App\Pagos\PasarelaBold;
use App\Pagos\PasarelaDePago;
use App\Pagos\PasarelaSimulada;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class PagosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PasarelaDePago::class, function (): PasarelaDePago {
            $driver = config('pagos.driver');

            return match ($driver) {
                'fake' => new PasarelaSimulada,
                'bold' => new PasarelaBold(
                    apiKey: (string) config('pagos.bold.api_key'),
                    secret: (string) config('pagos.bold.secret'),
                    urlBase: rtrim((string) config('pagos.bold.url'), '/'),
                    sandbox: (bool) config('pagos.bold.sandbox'),
                ),
                default => throw new InvalidArgumentException(
                    "PAYMENT_DRIVER «{$driver}» no existe. Usa `fake` o `bold`."
                ),
            };
        });
    }
}
