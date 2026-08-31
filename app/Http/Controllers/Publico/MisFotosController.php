<?php

namespace App\Http\Controllers\Publico;

use App\Models\Asociado;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * El propietario sube las fotos de su establecimiento y el gremio las aprueba.
 *
 * OBS3-13. En la demostración del 28 de agosto se afirmó que el afiliado sube
 * fotos y el gremio modera (`R23 00:48`), y el directivo puso la condición:
 * «lo tienen que aprobar ellos, no sea que pongan imágenes… exóticas» (R23
 * 00:45-01:05). El §27.3 punto 5 destapó que nada de eso existía: `/mi-cuenta`
 * tenía índice y vacantes, y el flujo de aprobación era el del estado del
 * registro, no el de una carga del propietario --porque el propietario no
 * cargaba nada--.
 *
 * Así que la moderación no se pudo «activar»: hubo que construir antes la
 * carga que se iba a moderar.
 */
class MisFotosController
{
    /** Cuántas fotos puede tener un establecimiento a la vez. */
    public const int MAXIMO_POR_ESTABLECIMIENTO = 12;

    public function index(Request $request): View
    {
        $asociado = $this->establecimientoDe($request);

        return view('publico.mi-cuenta.fotos.index', [
            'asociado' => $asociado,
            'aprobadas' => $asociado->fotosAprobadas(),
            'pendientes' => $asociado->fotosPendientes(),
            'maximo' => self::MAXIMO_POR_ESTABLECIMIENTO,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $asociado = $this->establecimientoDe($request);

        $request->validate([
            'foto' => [
                'required',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                'dimensions:min_width=600,min_height=400',
            ],
        ], [
            'foto.mimetypes' => 'La foto tiene que ser JPG, PNG o WebP.',
            'foto.max' => 'La foto no puede pesar más de 5 MB.',
            'foto.dimensions' => 'La foto es demasiado pequeña: mínimo 600 × 400 píxeles.',
        ]);

        /*
         * Contra la base, no contra `getMedia()`.
         *
         * Medialibrary cachea la coleccion en la INSTANCIA del modelo: si algo
         * ya la habia cargado antes en esta peticion, el conteo llega viejo y
         * el tope se salta en silencio. Lo destapo la prueba del maximo, donde
         * el usuario autenticado se reutiliza entre peticiones y el
         * establecimiento traia la galeria cargada de la primera.
         *
         * En una peticion HTTP de verdad no deberia ocurrir, pero un tope que
         * depende de que nadie haya tocado el modelo antes no es un tope.
         */
        if ($asociado->media()->where('collection_name', 'galeria')->count() >= self::MAXIMO_POR_ESTABLECIMIENTO) {
            return back()->withErrors([
                'foto' => 'Ya tienes '.self::MAXIMO_POR_ESTABLECIMIENTO.' fotos. Borra alguna antes de subir otra.',
            ]);
        }

        $archivo = $request->file('foto');

        /*
         * La extensión la decide el servidor, nunca el nombre que llega.
         * Un JPEG legítimo llamado «payload.html» pasa la validación de tipo
         * --su MIME es image/jpeg-- y quedaría servido como HTML desde el
         * disco público. Es el mismo razonamiento de `SubidaSegura`, que aquí
         * no se puede reutilizar porque es un componente de Filament.
         */
        $extension = match ($archivo->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };

        $asociado->addMedia($archivo->getRealPath())
            ->usingFileName(Str::ulid().'.'.$extension)
            // El nombre que escribió la persona se conserva solo como
            // etiqueta legible; no toca el disco.
            ->usingName(Str::limit(pathinfo((string) $archivo->getClientOriginalName(), PATHINFO_FILENAME), 60, ''))
            ->withCustomProperties([
                Asociado::FOTO_APROBADA => false,
                'subida_por' => $request->user()->getKey(),
            ])
            ->toMediaCollection('galeria');

        return back()->with('exito', 'Foto enviada. El gremio la revisa antes de que salga en tu ficha.');
    }

    /**
     * El dueño puede retirar cualquier foto suya, aprobada o no.
     *
     * No pasa por aprobación a propósito: pedir permiso para RETIRAR una
     * imagen propia sería absurdo, y además es la vía por la que alguien
     * ejerce su decisión sobre su propia fachada. El §5 del encargo ya lo
     * dice: el propietario decide qué se publica de él.
     */
    public function destroy(Request $request, Media $media): RedirectResponse
    {
        $asociado = $this->establecimientoDe($request);

        // La foto tiene que ser de SU establecimiento y de la galería. Sin
        // esto, el identificador de la ruta permite borrar cualquier medio
        // del sistema, incluidos los adjuntos de la guía normativa.
        abort_unless(
            $media->model_type === Asociado::class
                && (int) $media->model_id === $asociado->getKey()
                && $media->collection_name === 'galeria',
            404
        );

        $media->delete();

        return back()->with('exito', 'Foto eliminada.');
    }

    private function establecimientoDe(Request $request): Asociado
    {
        $asociado = $request->user()->asociado;

        abort_if($asociado === null, 403, 'Tu usuario todavía no está vinculado a un establecimiento.');

        // Solo por propiedad. `view` concedería también por permiso de panel.
        Gate::authorize('gestionarFotosEnPortal', $asociado);

        return $asociado;
    }
}
