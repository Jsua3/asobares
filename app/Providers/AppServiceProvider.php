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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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

    /**
     * Peticiones por minuto de cada ruta limitada, por nombre de limitador.
     *
     * Un limitador con nombre por ruta: `throttle:N,1` sin nombre firma el
     * contador solo con el usuario —o con la IP del anónimo—, nunca con la
     * ruta, así que todas las rutas compartirían un único contador y cada una
     * lo compararía con su propio máximo. Seis postulaciones gestionadas
     * dejarían «Pagar» (máximo 5) en 429.
     *
     * El porqué de cada máximo vive junto a su ruta en `routes/web.php`.
     * `LimitesDePeticionesTest` fija los dos lados.
     *
     * @var array<string, int>
     */
    public const array LIMITES_POR_MINUTO = [
        'guia' => 30,
        'guia-formato' => 10,
        'empleo-perfil' => 6,
        'empleo-postular' => 6,
        'artistas-inscripcion' => 6,
        'proveedores-inscripcion' => 6,
        'eventos-inscripcion' => 6,
        'afiliate' => 6,
        'contacto' => 6,
        'mi-cuenta-entrar' => 5,
        'mi-cuenta-contrasena' => 5,
        'mi-cuenta-pagar' => 5,
        'mi-cuenta-fotos-subir' => 30,
        'mi-cuenta-fotos-borrar' => 20,
        'mi-cuenta-vacantes-crear' => 20,
        'mi-cuenta-vacantes-editar' => 20,
        'mi-cuenta-postulaciones' => 60,
        'pago-simulado' => 10,
        'pago-retorno' => 30,
        'pago-estado' => 30,
        'webhook-bold' => 120,
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
        $this->asegurarConfiguracionDeEntornoExpuesto();

        foreach (self::MODELOS_PUBLICABLES as $modelo) {
            $modelo::observe(FlujoDeAprobacionObserver::class);
        }

        Inscripcion::observe(ConfirmacionDeInscripcionObserver::class);

        foreach (array_keys(LimpiezaDeArchivosObserver::CAMPOS_POR_MODELO) as $modelo) {
            $modelo::observe(LimpiezaDeArchivosObserver::class);
        }

        $this->registrarReglaDeYoutube();
        $this->registrarBitacoraDeSesiones();
        $this->registrarLimitesDePeticiones();

        // Filament capitaliza cada palabra de los títulos, que es convención
        // inglesa. En español solo va mayúscula la primera: sin esto se lee
        // «Mensajes Y PQR» o «Iniciativas Del Gremio».
        Resource::titleCaseModelLabel(false);
    }

    /**
     * Ningún entorno remoto arranca con la configuración del demo.
     *
     * Vale más un despliegue que se cae en el primer minuto que uno que sirve
     * la página de error de Laravel con las llaves de Bold dentro, o que
     * escribe cada PQR con los datos del ciudadano en storage/logs.
     *
     * Se salta solo en `local` y `testing` y se aplica en todo lo demás, no
     * solo en `production`: el despliegue de Laravel Cloud usa
     * `APP_ENV=staging`, y un entorno remoto que se llame `dev`, `demo` o `qa`
     * también tiene que salir con https forzado, cookie `Secure` y sin
     * `APP_DEBUG=true`. El criterio no es cómo se llama el entorno, es si está
     * expuesto.
     */
    private function asegurarConfiguracionDeEntornoExpuesto(): void
    {
        if ($this->app->environment('local', 'testing')) {
            return;
        }

        URL::forceScheme('https');

        // `forceScheme` sólo cambia las URLs que genera Laravel: no marca la
        // cookie de sesión. Sin el atributo `Secure`, el navegador la manda
        // también por http y cualquiera en la misma red la captura y la
        // reutiliza. Fuera de local no se deja a criterio del .env.
        config(['session.secure' => true]);

        if (config('app.debug')) {
            throw new RuntimeException(
                'APP_DEBUG tiene que estar en false fuera de local: la página de error publica '
                .'el cuerpo de la petición, las cabeceras y las variables de entorno.'
            );
        }

        if (config('mail.default') === 'log') {
            throw new RuntimeException(
                'MAIL_MAILER=log fuera de local escribe el contenido de cada PQR, con los datos '
                .'personales del ciudadano, en storage/logs. Configura un mailer real (smtp).'
            );
        }
    }

    /**
     * Un contador por ruta y por usuario —o por IP si no hay sesión—.
     *
     * El nombre del limitador va también en la clave aunque Laravel ya lo
     * antepone por su cuenta: así la separación por ruta no depende de un
     * detalle interno del framework. Sin usuario ni IP en la clave, un solo
     * afiliado agotaría el límite de todos.
     */
    private function registrarLimitesDePeticiones(): void
    {
        foreach (self::LIMITES_POR_MINUTO as $limitador => $maximo) {
            RateLimiter::for($limitador, fn (Request $request): Limit => Limit::perMinute($maximo)
                ->by($limitador.'|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
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
