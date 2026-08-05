<?php

namespace App\Console\Commands;

use App\Models\Aspirante;
use App\Models\Postulacion;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * Borra los datos personales de las bolsas cuando ya cumplieron su fin.
 *
 * Guardar hojas de vida «por si acaso» es justo lo que la Ley 1581 no
 * permite: el plazo se cumple solo, sin que nadie tenga que acordarse.
 */
class DepurarBolsas extends Command
{
    protected $signature = 'bolsas:depurar {--pretend : Solo informa cuántos registros se borrarían}';

    protected $description = 'Depura postulaciones y perfiles de la bolsa de empleo según los plazos de retención';

    public function handle(): int
    {
        $plazoPostulaciones = $this->plazoEnMeses('bolsas.retencion_postulaciones_meses', 'RETENCION_POSTULACIONES_MESES');
        $plazoAspirantes = $this->plazoEnMeses('bolsas.retencion_aspirantes_meses', 'RETENCION_ASPIRANTES_MESES');

        if ($plazoPostulaciones === null || $plazoAspirantes === null) {
            return self::FAILURE;
        }

        $simulacro = (bool) $this->option('pretend');

        $postulaciones = $this->postulacionesCaducadas($plazoPostulaciones);
        $aspirantes = $this->aspirantesCaducados($plazoAspirantes);

        if ($simulacro) {
            $cuantasPostulaciones = $postulaciones->count();
            $cuantosAspirantes = $aspirantes->count();

            $this->info("Se borrarían {$cuantasPostulaciones} postulaciones y {$cuantosAspirantes} perfiles del banco de talento.");

            return self::SUCCESS;
        }

        $cuantasPostulaciones = $postulaciones->delete();
        $cuantosAspirantes = $aspirantes->delete();

        if ($cuantasPostulaciones > 0 || $cuantosAspirantes > 0) {
            activity('bolsas')
                ->event('deleted')
                ->log("Depuración de datos: {$cuantasPostulaciones} postulaciones y {$cuantosAspirantes} perfiles eliminados por vencimiento del plazo de retención.");
        }

        $this->info("Depuradas {$cuantasPostulaciones} postulaciones y {$cuantosAspirantes} perfiles del banco de talento.");

        return self::SUCCESS;
    }

    /**
     * Postulaciones cuya vacante cerró o venció hace más del plazo.
     *
     * @return Builder<Postulacion>
     */
    private function postulacionesCaducadas(int $meses): Builder
    {
        $limite = now()->subMonths($meses);

        return Postulacion::query()->whereHas('vacante', function (Builder $vacante) use ($limite): void {
            $vacante
                ->where('cerrada_at', '<=', $limite)
                ->orWhereDate('fecha_limite', '<=', $limite->toDateString());
        });
    }

    /**
     * Perfiles del banco de talento cuyo consentimiento venció.
     *
     * Se ancla a `consentimiento_at`, no a `updated_at`: editar el registro
     * desde el panel —incluido que la secretaría cambie el estado de
     * gestión— no debe regalar más plazo sin que la persona haya vuelto a
     * autorizar el tratamiento. `consentimiento_at` solo se resella cuando
     * ella reenvía el formulario.
     *
     * @return Builder<Aspirante>
     */
    private function aspirantesCaducados(int $meses): Builder
    {
        return Aspirante::query()->where('consentimiento_at', '<=', now()->subMonths($meses));
    }

    /**
     * Valida el plazo de retención leído de configuración.
     *
     * Un plazo menor que 1 —o ausente, como con un `config:cache` viejo que
     * no incluya `config/bolsas.php`— haría que `now()->subMonths($plazo)`
     * fuera *ahora mismo*: la purga borraría todo en vez de nada. Eso no es
     * una orden de borrado sino un error de configuración, así que el
     * comando aborta en vez de ejecutar.
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
