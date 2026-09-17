<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

/**
 * Borra las subidas temporales de Livewire que llevan más de una hora en el
 * disco.
 *
 * Un archivo subido al panel espera en el temporal a que su acción lo procese
 * y lo borre. Si el modal se cierra tras un error de validación, o se cancela,
 * la acción no corre y el archivo se queda: la base del gremio con los datos
 * de los afiliados, o el CSV de cartera con los saldos de todos. Livewire solo
 * purga lo que pasa de 24 horas, y solo cuando llega otra subida. A cada
 * archivo lo acompaña un `.json` con su nombre original, que cae con él.
 *
 * Una hora sobra para llenar un formulario y enviarlo. El calendario la corre
 * cada hora, así que nada pasa de dos en el disco.
 */
class DepurarSubidas extends Command
{
    private const int MINUTOS_EN_EL_TEMPORAL = 60;

    protected $signature = 'subidas:depurar {--pretend : Solo informa cuántos archivos se borrarían}';

    protected $description = 'Depura las subidas temporales de Livewire que pasan de una hora';

    public function handle(): int
    {
        $disco = FileUploadConfiguration::storage();
        $limite = now()->subMinutes(self::MINUTOS_EN_EL_TEMPORAL)->getTimestamp();

        // `exists()` antes de leer la fecha: una acción puede borrar su
        // archivo entre el listado y la lectura.
        $vencidos = array_values(array_filter(
            $disco->allFiles(FileUploadConfiguration::directory()),
            fn (string $archivo): bool => $disco->exists($archivo) && $disco->lastModified($archivo) < $limite,
        ));

        if ((bool) $this->option('pretend')) {
            $this->info('Se borrarían '.count($vencidos).' archivos de subidas temporales.');

            return self::SUCCESS;
        }

        $disco->delete($vencidos);

        $this->info('Depurados '.count($vencidos).' archivos de subidas temporales.');

        return self::SUCCESS;
    }
}
