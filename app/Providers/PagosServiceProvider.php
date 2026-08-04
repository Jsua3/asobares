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

            if (! is_string($driver) || $driver === '') {
                throw new InvalidArgumentException(
                    'Falta PAYMENT_DRIVER en el entorno. Sin esa variable no se sabe si los pagos '
                    .'son reales o simulados, y adivinarlo es justo lo que no se puede hacer.'
                );
            }

            // La pasarela simulada aprueba cualquier pago con solo pedírselo.
            // Que exista fuera de la máquina de desarrollo no es aceptable ni
            // «por un rato mientras llegan las llaves de Bold».
            if ($driver === 'fake' && ! $this->app->environment('local', 'testing')) {
                throw new InvalidArgumentException(
                    "La pasarela simulada no puede usarse en el entorno «{$this->app->environment()}». "
                    .'Configura PAYMENT_DRIVER=bold con sus credenciales.'
                );
            }

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
