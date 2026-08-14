<?php

namespace App\Providers;

use App\Models\Aliado;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Iniciativa;
use App\Models\Inscripcion;
use App\Models\Noticia;
use App\Models\Proveedor;
use App\Models\RequisitoApertura;
use App\Models\Vacante;
use App\Observers\ConfirmacionDeInscripcionObserver;
use App\Observers\FlujoDeAprobacionObserver;
use App\Observers\LimpiezaDeArchivosObserver;
use App\Panel\ColaDePendientes;
use Filament\Resources\Resource;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

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
        // El tablero resuelve este servicio varias veces por render —
        // `canView()` del widget, sus filas, y la tarjeta de KPIs de
        // secretaría—. Sin el singleton cada resolución trae una instancia
        // nueva y su memoización interna no sirve de nada: cada una repite
        // las consultas a los nueve modelos publicables.
        //
        // El singleton de Laravel vive lo que vive el contenedor, o sea la
        // petición, así que no arrastra datos entre peticiones. Si algún día
        // el proyecto corriera sobre un servidor de aplicación persistente
        // tipo Octane, el contenedor sobrevive a la petición y este
        // singleton habría que revisarlo.
        $this->app->singleton(ColaDePendientes::class);
    }

    public function boot(): void
    {
        $this->asegurarConfiguracionDeProduccion();

        foreach (self::MODELOS_PUBLICABLES as $modelo) {
            $modelo::observe(FlujoDeAprobacionObserver::class);
        }

        Inscripcion::observe(ConfirmacionDeInscripcionObserver::class);

        foreach (array_keys(LimpiezaDeArchivosObserver::CAMPOS_POR_MODELO) as $modelo) {
            $modelo::observe(LimpiezaDeArchivosObserver::class);
        }

        $this->registrarReglaDeYoutube();
        $this->registrarBitacoraDeSesiones();

        // Filament capitaliza cada palabra de los títulos, que es convención
        // inglesa. En español solo va mayúscula la primera: sin esto se lee
        // «Mensajes Y PQR» o «Iniciativas Del Gremio».
        Resource::titleCaseModelLabel(false);
    }

    /**
     * Producción no arranca con la configuración del demo.
     *
     * Vale más un despliegue que se cae en el primer minuto que uno que sirve
     * la página de error de Laravel con las llaves de Bold dentro, o que
     * escribe cada PQR con los datos del ciudadano en storage/logs.
     */
    private function asegurarConfiguracionDeProduccion(): void
    {
        if (! $this->app->isProduction()) {
            return;
        }

        URL::forceScheme('https');

        if (config('app.debug')) {
            throw new RuntimeException(
                'APP_DEBUG tiene que estar en false en producción: la página de error publica '
                .'el cuerpo de la petición, las cabeceras y las variables de entorno.'
            );
        }

        if (config('mail.default') === 'log') {
            throw new RuntimeException(
                'MAIL_MAILER=log en producción escribe el contenido de cada PQR, con los datos '
                .'personales del ciudadano, en storage/logs. Configura un mailer real.'
            );
        }
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
