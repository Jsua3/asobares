<?php

use App\Models\Asociado;
use Illuminate\Database\Migrations\Migration;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Las fotos que ya existían quedan aprobadas (OBS3-13).
 *
 * Antes de la moderación no había forma de que un propietario subiera nada:
 * todo lo que hay en la colección `galeria` lo cargó el gremio desde el panel,
 * y el gremio es justamente quien aprueba. Marcarlas es reconocer un hecho, no
 * conceder un permiso.
 *
 * Va en una migración y no en el sembrador a propósito. El sembrador tiene un
 * guardia --«solo si la galería está vacía»-- que hace que no vuelva a tocar
 * las fichas ya cargadas: se comprobó, y por eso estas dieciocho fotos se
 * quedaban sin propiedad y caían todas en la cola de moderación. Una base ya
 * creada no se arregla sola.
 *
 * El defecto es «sin aprobar», incluido el silencioso: una foto cuya propiedad
 * nunca se escribió no sale al sitio. Eso es correcto para lo que suba un
 * propietario mañana, y es lo que hay que corregir para lo de ayer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Media::query()
            ->where('model_type', Asociado::class)
            ->where('collection_name', 'galeria')
            ->each(function (Media $media): void {
                if ($media->getCustomProperty(Asociado::FOTO_APROBADA) !== null) {
                    return;
                }

                $media->setCustomProperty(Asociado::FOTO_APROBADA, true);
                $media->save();
            });
    }

    /**
     * No se revierte. Quitar la marca dejaría fuera del sitio fotos que llevan
     * publicadas desde antes de que existiera la moderación, y eso es un daño
     * mayor que el de conservar un dato de más en un JSON.
     */
    public function down(): void
    {
        // Intencionalmente vacío.
    }
};
