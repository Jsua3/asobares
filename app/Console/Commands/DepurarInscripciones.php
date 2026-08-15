<?php

namespace App\Console\Commands;

use App\Models\Inscripcion;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Borra las inscripciones a eventos cuando su fin autorizado ya se agotó.
 *
 * El consentimiento fue «para participar en el evento»: pasado el evento y
 * el plazo, conservar nombre, correo y teléfono es justo lo que la Ley 1581
 * no permite. El registro contable no se pierde: la clave foránea de
 * `transacciones.inscripcion_id` es `nullOnDelete`, así que la transacción
 * (referencia, monto, estado) sobrevive sin el dato personal.
 */
class DepurarInscripciones extends Command
{
    protected $signature = 'inscripciones:depurar {--pretend : Solo informa cuántos registros se borrarían}';

    protected $description = 'Depura las inscripciones a eventos según el plazo de retención';

    public function handle(): int
    {
        $plazo = $this->plazoEnMeses('retencion.inscripciones_meses', 'RETENCION_INSCRIPCIONES_MESES');

        if ($plazo === null) {
            return self::FAILURE;
        }

        $caducadas = $this->inscripcionesCaducadas($plazo);

        if ((bool) $this->option('pretend')) {
            $this->info("Se borrarían {$caducadas->count()} inscripciones.");

            return self::SUCCESS;
        }

        $cuantas = $caducadas->delete();

        if ($cuantas > 0) {
            activity('inscripciones')
                ->event('deleted')
                ->log("Depuración de datos: {$cuantas} inscripciones eliminadas por vencimiento del plazo de retención.");
        }

        $this->info("Depuradas {$cuantas} inscripciones.");

        return self::SUCCESS;
    }

    /**
     * Inscripciones de eventos que terminaron hace más del plazo.
     *
     * El reloj corre desde que el evento TERMINA (`fecha_fin`), no desde que
     * empieza: un congreso que arrancó hace dos años pero cerró hace un mes
     * sigue dentro del plazo. Los eventos sin `fecha_fin` usan
     * `fecha_inicio`, o quedarían inmortales. El `orWhere` va en su propio
     * grupo para no ampliar sin querer el conjunto que se borra.
     *
     * @return Builder<Inscripcion>
     */
    private function inscripcionesCaducadas(int $meses): Builder
    {
        $limite = now()->subMonths($meses);

        return Inscripcion::query()->whereHas('evento', function (Builder $evento) use ($limite): void {
            $evento
                ->where('fecha_fin', '<=', $limite)
                ->orWhere(function (Builder $evento) use ($limite): void {
                    $evento
                        ->whereNull('fecha_fin')
                        ->where('fecha_inicio', '<=', $limite);
                });
        });
    }

    /**
     * Valida el plazo leído de configuración. Un plazo menor que 1 —o
     * ausente, como con un `config:cache` viejo— haría que el límite fuera
     * *ahora mismo* y la purga borraría todo. Eso es un error de
     * configuración, no una orden de borrado, así que el comando aborta.
     */
    private function plazoEnMeses(string $clave, string $variable): ?int
    {
        $valor = config($clave);

        if (is_numeric($valor) && (int) $valor >= 1) {
            return (int) $valor;
        }

        $valorMostrado = is_scalar($valor) ? (string) $valor : 'null';

        $this->error("El plazo de retención de «{$clave}» (variable de entorno {$variable}) debe ser un entero mayor o igual a 1. Valor actual: {$valorMostrado}.");

        return null;
    }
}
