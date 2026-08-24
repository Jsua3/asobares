<?php

namespace App\Console\Commands;

use App\Services\ImportadorDeAsociados;
use Illuminate\Console\Command;

/**
 * Carga la base de establecimientos que mantiene la oficina del gremio.
 *
 *   php artisan asociados:importar "material/Base de datos Cap. Quindio.xlsx" --categoria=Bar
 *
 * Cuando exista el soporte firmado del titular, la misma carga puede dejar la
 * evidencia de habeas data:
 *
 *   php artisan asociados:importar base.xlsx --categoria=Bar \
 *       --autorizacion=2026-08-22 --origen="Formato de autorización firmado, acta 04"
 */
class ImportarAsociados extends Command
{
    protected $signature = 'asociados:importar
        {archivo : Ruta al .xlsx o .csv de la oficina}
        {--categoria= : Categoría a aplicar a las filas que no la traigan}
        {--autorizacion= : Fecha (AAAA-MM-DD) del soporte de habeas data firmado por el titular}
        {--origen= : Descripción del soporte: acta, formato, correo}';

    protected $description = 'Carga o actualiza los asociados desde el archivo del gremio, sin publicarlos.';

    public function handle(ImportadorDeAsociados $importador): int
    {
        $archivo = (string) $this->argument('archivo');

        if (! is_file($archivo)) {
            $this->components->error("No existe el archivo «{$archivo}».");

            return self::FAILURE;
        }

        $autorizacion = $this->option('autorizacion');

        if ($autorizacion !== null && $this->option('origen') === null) {
            $this->components->error(
                'Si declaras --autorizacion tienes que decir con --origen cuál es el soporte. '.
                'Una fecha sin soporte no es evidencia.'
            );

            return self::FAILURE;
        }

        $resultado = $importador->importar(
            $archivo,
            $this->option('categoria'),
            $autorizacion,
            $this->option('origen'),
        );

        foreach ($resultado->avisos() as $aviso) {
            $this->components->warn($aviso);
        }

        foreach ($resultado->errores() as $error) {
            $this->components->error($error);
        }

        $this->components->info($resultado->resumen());

        return $resultado->creados() + $resultado->actualizados() > 0 ? self::SUCCESS : self::FAILURE;
    }
}
