<?php

namespace App\Observers;

use App\Models\Aliado;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\RequisitoApertura;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Borra del disco los archivos que dejan de estar referenciados.
 *
 * Antes no se borraba nada: cambiar la foto de un asociado o eliminar su
 * ficha dejaba el archivo accesible por /storage para siempre. Con fotos que
 * el propietario pidió quitar, eso es un problema de datos personales además
 * de un disco que solo crece.
 */
class LimpiezaDeArchivosObserver
{
    /**
     * Campo de archivo por modelo y clase de almacenamiento donde vive
     * (`publico` o `privado`). El disco concreto lo decide
     * `config('almacenamiento.*')`, igual que en la subida.
     *
     * @var array<class-string<Model>, array<string, 'publico'|'privado'>>
     */
    public const array CAMPOS_POR_MODELO = [
        Asociado::class => ['foto_portada' => 'publico'],
        Aliado::class => ['logo' => 'publico'],
        Artista::class => ['foto' => 'publico'],
        Evento::class => ['imagen' => 'publico'],
        Noticia::class => ['imagen' => 'publico'],
        RequisitoApertura::class => ['adjunto' => 'privado'],
    ];

    /** Al reemplazar un archivo, el anterior deja de tener dueño. */
    public function updated(Model $modelo): void
    {
        foreach ($this->camposDe($modelo) as $campo => $almacenamiento) {
            if (! $modelo->wasChanged($campo)) {
                continue;
            }

            $this->borrarSiNadieLoUsa($modelo, $campo, (string) $modelo->getOriginal($campo), $almacenamiento);
        }
    }

    public function deleted(Model $modelo): void
    {
        // Con borrado suave el registro puede volver, y su archivo con él.
        if (method_exists($modelo, 'isForceDeleting') && ! $modelo->isForceDeleting()) {
            return;
        }

        foreach ($this->camposDe($modelo) as $campo => $almacenamiento) {
            $this->borrarSiNadieLoUsa($modelo, $campo, (string) $modelo->getAttribute($campo), $almacenamiento);
        }
    }

    /**
     * Las semillas comparten archivo entre registros a propósito —el mismo
     * formato de bomberos sirve para tres municipios—, así que un borrado
     * ciego se llevaría por delante el adjunto de fichas ajenas.
     */
    private function borrarSiNadieLoUsa(Model $modelo, string $campo, string $ruta, string $almacenamiento): void
    {
        if ($ruta === '') {
            return;
        }

        $loUsaOtro = $modelo->newQuery()
            ->where($campo, $ruta)
            ->whereKeyNot($modelo->getKey())
            ->exists();

        if ($loUsaOtro) {
            return;
        }

        Storage::disk(config('almacenamiento.'.$almacenamiento))->delete($ruta);
    }

    /** @return array<string, string> */
    private function camposDe(Model $modelo): array
    {
        return self::CAMPOS_POR_MODELO[$modelo::class] ?? [];
    }
}
