<?php

namespace App\Console\Commands;

use App\Enums\TipoMensaje;
use App\Models\Mensaje;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Borra los mensajes de contacto y las PQR cuando vence su plazo.
 *
 * La bolsa de empleo ya se depuraba sola, pero los formularios públicos
 * guardaban nombre, correo, teléfono y el texto del mensaje sin caducidad
 * ninguna: datos personales de ciudadanos acumulándose para siempre, que es
 * justo lo que la Ley 1581 no permite.
 */
class DepurarMensajes extends Command
{
    protected $signature = 'mensajes:depurar {--pretend : Solo informa cuántos registros se borrarían}';

    protected $description = 'Depura mensajes de contacto y PQR según los plazos de retención';

    public function handle(): int
    {
        $plazoContacto = $this->plazoEnMeses('retencion.contacto_meses', 'RETENCION_CONTACTO_MESES');
        $plazoPqr = $this->plazoEnMeses('retencion.pqr_meses', 'RETENCION_PQR_MESES');

        if ($plazoContacto === null || $plazoPqr === null) {
            return self::FAILURE;
        }

        $contacto = $this->caducados($plazoContacto, esPqr: false);
        $pqr = $this->caducados($plazoPqr, esPqr: true);

        if ((bool) $this->option('pretend')) {
            $this->info("Se borrarían {$contacto->count()} mensajes de contacto y {$pqr->count()} PQR.");

            return self::SUCCESS;
        }

        $cuantosContacto = $contacto->delete();
        $cuantasPqr = $pqr->delete();

        if ($cuantosContacto > 0 || $cuantasPqr > 0) {
            activity('mensajes')
                ->event('deleted')
                ->log("Depuración de datos: {$cuantosContacto} mensajes de contacto y {$cuantasPqr} PQR eliminados por vencimiento del plazo de retención.");
        }

        $this->info("Depurados {$cuantosContacto} mensajes de contacto y {$cuantasPqr} PQR.");

        return self::SUCCESS;
    }

    /**
     * El plazo cuenta desde que se respondió. Si nunca se respondió, desde que
     * entró: un mensaje abandonado no puede volverse inmortal justamente por
     * no haberlo atendido.
     *
     * @return Builder<Mensaje>
     */
    private function caducados(int $meses, bool $esPqr): Builder
    {
        $limite = now()->subMonths($meses);

        $consulta = $esPqr
            ? Mensaje::query()->where('tipo', TipoMensaje::Pqr)
            : Mensaje::query()->where('tipo', '!=', TipoMensaje::Pqr);

        return $consulta
            ->where(function (Builder $mensaje) use ($limite): void {
                $mensaje
                    ->where('respondido_at', '<=', $limite)
                    ->orWhere(function (Builder $mensaje) use ($limite): void {
                        $mensaje
                            ->whereNull('respondido_at')
                            ->where('created_at', '<=', $limite);
                    });
            });
    }

    /**
     * Valida el plazo leído de configuración. Un plazo menor que 1 —o ausente,
     * como con un `config:cache` viejo— haría que el límite fuera *ahora
     * mismo* y la purga borraría todo. Eso es un error de configuración, no
     * una orden de borrado, así que el comando aborta.
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
