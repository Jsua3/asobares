<?php

namespace App\Providers;

use App\Models\Aliado;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Models\Noticia;
use App\Models\Proveedor;
use App\Models\RequisitoApertura;
use App\Models\Vacante;
use App\Observers\FlujoDeAprobacionObserver;
use Filament\Resources\Resource;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
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
        Iniciativa::class,
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
        $this->registrarBitacoraDeSesiones();

        // Filament capitaliza cada palabra de los títulos, que es convención
        // inglesa. En español solo va mayúscula la primera: sin esto se lee
        // «Mensajes Y PQR» o «Iniciativas Del Gremio».
        Resource::titleCaseModelLabel(false);
    }

    /** RF-39: la bitácora también registra entradas y salidas al panel. */
    private function registrarBitacoraDeSesiones(): void
    {
        Event::listen(Login::class, function (Login $evento): void {
            activity('sesion')
                ->causedBy($evento->user)
                ->event('created')
                ->log('inició sesión');
        });

        Event::listen(Logout::class, function (Logout $evento): void {
            if ($evento->user === null) {
                return;
            }

            activity('sesion')
                ->causedBy($evento->user)
                ->event('deleted')
                ->log('cerró sesión');
        });
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
