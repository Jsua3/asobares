<?php

namespace App\Console\Commands;

use App\Models\Aspirante;
use App\Models\Postulacion;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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
        $simulacro = (bool) $this->option('pretend');

        $postulaciones = $this->postulacionesCaducadas();
        $aspirantes = $this->aspirantesCaducados();

        $cuantasPostulaciones = $postulaciones->count();
        $cuantosAspirantes = $aspirantes->count();

        if ($simulacro) {
            $this->info("Se borrarían {$cuantasPostulaciones} postulaciones y {$cuantosAspirantes} perfiles del banco de talento.");

            return self::SUCCESS;
        }

        $postulaciones->delete();
        $aspirantes->delete();

        if ($cuantasPostulaciones > 0 || $cuantosAspirantes > 0) {
            activity('bolsas')
                ->event('deleted')
                ->log("Depuración de datos: {$cuantasPostulaciones} postulaciones y {$cuantosAspirantes} perfiles eliminados por vencimiento del plazo de retención.");
        }

        $this->info("Depuradas {$cuantasPostulaciones} postulaciones y {$cuantosAspirantes} perfiles del banco de talento.");

        return self::SUCCESS;
    }

    /** Postulaciones cuya vacante cerró o venció hace más del plazo. */
    private function postulacionesCaducadas(): Builder
    {
        $limite = $this->limite('bolsas.retencion_postulaciones_meses');

        return Postulacion::query()->whereHas('vacante', function (Builder $vacante) use ($limite): void {
            $vacante
                ->where('cerrada_at', '<=', $limite)
                ->orWhereDate('fecha_limite', '<=', $limite->toDateString());
        });
    }

    /** Perfiles del banco de talento sin movimiento en más del plazo. */
    private function aspirantesCaducados(): Builder
    {
        return Aspirante::query()->where('updated_at', '<=', $this->limite('bolsas.retencion_aspirantes_meses'));
    }

    private function limite(string $clave): Carbon
    {
        return now()->subMonths((int) config($clave));
    }
}
