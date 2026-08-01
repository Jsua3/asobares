<?php

namespace App\Providers;

use App\Models\Aliado;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Proveedor;
use App\Models\RequisitoApertura;
use App\Models\Vacante;
use App\Observers\FlujoDeAprobacionObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Todo lo que pasa por el flujo editorial borrador → pendiente → publicado.
     *
     * @var list<class-string<Model>>
     */
    public const array MODELOS_PUBLICABLES = [
        Asociado::class,
        Evento::class,
        Noticia::class,
        RequisitoApertura::class,
        Vacante::class,
        Artista::class,
        Proveedor::class,
        Aliado::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach (self::MODELOS_PUBLICABLES as $modelo) {
            $modelo::observe(FlujoDeAprobacionObserver::class);
        }

        $this->registrarReglaDeYoutube();
    }

    /**
     * `video_url` de artistas: solo se aceptan URLs de YouTube de las que se
     * pueda extraer un ID de 11 caracteres, porque es lo único que se embebe.
     */
    private function registrarReglaDeYoutube(): void
    {
        Validator::extend('url_youtube', function (string $atributo, mixed $valor): bool {
            if (! is_string($valor) || $valor === '') {
                return true;
            }

            return (new Artista(['video_url' => $valor]))->youtubeId() !== null;
        }, 'El enlace debe ser un video de YouTube válido.');
    }
}
