<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Providers\AppServiceProvider;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * La guardia del despliegue remoto (riesgo R-14).
 *
 * Lo que se vigila aquí falla sin avisar: la suite local sigue verde y el
 * defecto solo asoma en el entorno remoto. Son tres familias:
 *
 * 1. La coraza de configuración: qué se endurece y en qué entornos.
 * 2. La semántica que cambia entre SQLite y PostgreSQL. Ojo: la suite corre
 *    sobre SQLite, donde el defecto del buscador NO se reproduce. Por eso
 *    estas pruebas afirman la SQL emitida por cada gramática y no solo el
 *    resultado, que saldría verde con el código roto.
 * 3. Los ficheros de despliegue, para que no se queden atrás del código.
 */
class ConfiguracionDeDespliegueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Arranca el proveedor como si la aplicación estuviera en el entorno dado.
     */
    private function arrancarEn(string $entorno): void
    {
        $this->app->detectEnvironment(fn (): string => $entorno);

        (new AppServiceProvider($this->app))->boot();
    }

    // -----------------------------------------------------------------------
    // 1. La coraza de configuración
    // -----------------------------------------------------------------------

    /**
     * La coraza no se ata solo a `production`: el entorno de Laravel Cloud se
     * llama `staging`, y atarla a `production` dejaría sin endurecer justo el
     * entorno expuesto.
     *
     * @return array<string, array{string}>
     */
    public static function entornosExpuestos(): array
    {
        return [
            'staging' => ['staging'],
            'production' => ['production'],
            'un nombre cualquiera' => ['demo'],
        ];
    }

    #[DataProvider('entornosExpuestos')]
    public function test_fuera_de_local_las_urls_se_generan_en_https(string $entorno): void
    {
        config(['app.debug' => false, 'mail.default' => 'smtp']);

        $this->arrancarEn($entorno);

        $this->assertStringStartsWith('https://', url('/directorio'));
    }

    #[DataProvider('entornosExpuestos')]
    public function test_fuera_de_local_la_cookie_de_sesion_se_marca_segura(string $entorno): void
    {
        config(['session.secure' => null, 'app.debug' => false, 'mail.default' => 'smtp']);

        $this->arrancarEn($entorno);

        $this->assertTrue(config('session.secure'));
    }

    #[DataProvider('entornosExpuestos')]
    public function test_fuera_de_local_no_se_arranca_con_app_debug(string $entorno): void
    {
        config(['app.debug' => true, 'mail.default' => 'smtp']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/APP_DEBUG/');

        $this->arrancarEn($entorno);
    }

    /**
     * `MAIL_MAILER=log` escribe el cuerpo de cada PQR —con el nombre, el
     * correo y el teléfono del ciudadano— en el registro. Es tentador como modo
     * de emergencia sin proveedor de correo, y se rechaza igual: se despliega
     * con SMTP. Los códigos MFA se leen por `stderr`, que no arrastra las PQR.
     */
    #[DataProvider('entornosExpuestos')]
    public function test_fuera_de_local_no_se_arranca_escribiendo_las_pqr_en_el_registro(string $entorno): void
    {
        config(['app.debug' => false, 'mail.default' => 'log']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/MAIL_MAILER/');

        $this->arrancarEn($entorno);
    }

    /**
     * La otra mitad del contrato: en desarrollo la coraza no estorba. Sin esto
     * nadie podría levantar la aplicación en local con el perfil del demo, que
     * es exactamente el que trae `.env.example`.
     *
     * @return array<string, array{string}>
     */
    public static function entornosDeTrabajo(): array
    {
        return [
            'local' => ['local'],
            'testing' => ['testing'],
        ];
    }

    #[DataProvider('entornosDeTrabajo')]
    public function test_en_local_y_en_la_suite_la_coraza_se_aparta(string $entorno): void
    {
        config(['app.debug' => true, 'mail.default' => 'log', 'session.secure' => null]);

        $this->arrancarEn($entorno);

        $this->assertNull(config('session.secure'), 'La coraza no debe tocar la configuración de desarrollo.');
    }

    // -----------------------------------------------------------------------
    // 2. SQLite frente a PostgreSQL
    // -----------------------------------------------------------------------

    /**
     * ESTA es la prueba que caza el defecto.
     *
     * El `LIKE` de SQLite es insensible a mayúsculas para ASCII; el de
     * PostgreSQL es sensible. Con `where('nombre', 'like', …)` el buscador del
     * directorio pasaba verde en la suite y encontraba cuatro de cada diez
     * establecimientos en el despliegue, sin dar un solo error.
     *
     * Afirma la SQL y no el resultado a propósito: sobre SQLite el defecto no
     * se reproduce, así que una prueba de comportamiento sería verde con el
     * código roto. La gramática de PostgreSQL se compila sin PDO —`toSql()` no
     * abre conexión—, de modo que esto corre en cualquier máquina, sin
     * PostgreSQL instalado y sin marcar la prueba para que se omita.
     */
    public function test_el_buscador_del_directorio_emite_ilike_en_postgresql(): void
    {
        $sql = DB::connection('pgsql')
            ->query()
            ->from('asociados')
            ->whereLike('nombre', '%bar%', caseSensitive: false)
            ->toSql();

        $this->assertStringContainsString('ilike', $sql);
        $this->assertStringNotContainsString(' like ', $sql);
    }

    /**
     * Y la otra mitad: `ilike` NO existe en SQLite. Si alguien «arregla» el
     * buscador escribiendo el operador a mano, el directorio revienta en
     * desarrollo y en la suite.
     */
    public function test_el_buscador_del_directorio_emite_like_en_sqlite(): void
    {
        $sql = DB::connection('sqlite')
            ->query()
            ->from('asociados')
            ->whereLike('nombre', '%bar%', caseSensitive: false)
            ->toSql();

        $this->assertStringContainsString('like', $sql);
        $this->assertStringNotContainsString('ilike', $sql);
        $this->assertStringNotContainsString('glob', $sql);
    }

    /** El scope del modelo es el que usa el controlador: se afirma el mismo. */
    public function test_el_scope_del_modelo_es_el_que_lleva_la_insensibilidad(): void
    {
        $enPostgres = Asociado::on('pgsql')->buscarPorNombre('bar')->toSql();
        $enSqlite = Asociado::on('sqlite')->buscarPorNombre('bar')->toSql();

        $this->assertStringContainsString('ilike', $enPostgres);
        $this->assertStringNotContainsString('ilike', $enSqlite);
    }

    /**
     * Comportamiento, para que la prueba signifique algo también leída por
     * alguien que no sepa de gramáticas: buscar en minúsculas encuentra un
     * establecimiento cuyo nombre va en mayúsculas. Pasa en SQLite con `like`
     * y pasa en PostgreSQL sólo con `ilike`.
     */
    public function test_buscar_en_minusculas_encuentra_un_nombre_en_mayusculas(): void
    {
        $asociado = Asociado::factory()->publicado()->create([
            'nombre' => 'BAR MERLÍN',
            'slug' => 'bar-merlin',
        ]);

        $this->get('/directorio?q=merl')->assertSuccessful()->assertSee($asociado->nombre);
        $this->get('/directorio?q=MERL')->assertSuccessful()->assertSee($asociado->nombre);
    }

    // -----------------------------------------------------------------------
    // 3. Sembradores, activos y ficheros de despliegue
    // -----------------------------------------------------------------------

    /**
     * Los datos de demostración no entran en producción: ahí `db:seed --force`
     * inyectaría establecimientos, PQR con datos personales ficticios y pagos
     * aprobados falsos en el directorio real del gremio.
     */
    public function test_los_datos_de_demostracion_no_entran_en_produccion(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        // Con `--force`, que es como se corre de verdad contra un entorno
        // remoto: sin él, `db:seed` sólo pide confirmación por consola y ahí
        // no hay nadie escuchando.
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(0, Asociado::count());
        $this->assertSame(0, DB::table('mensajes')->count());
        $this->assertSame(0, DB::table('transacciones')->count());
    }

    /** Y en staging sí siembran, que es la razón de desplegar en `staging`. */
    public function test_en_staging_los_datos_de_demostracion_si_entran(): void
    {
        $this->app->detectEnvironment(fn (): string => 'staging');

        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, Asociado::count());
    }

    /**
     * El `.svg` de la marca son 49 KB de un PNG envuelto en base64 con el MIME
     * mal escrito. El panel usa el PNG de verdad, igual que el sitio público.
     */
    public function test_el_panel_usa_el_logo_png_y_no_el_falso_vector(): void
    {
        $respuesta = $this->get('/admin/login')->assertSuccessful();

        $respuesta->assertSee('img/logo-asobares.png');
        $respuesta->assertDontSee('img/logo-asobares.svg');
    }

    /**
     * El fichero que se copia y pega en las variables del entorno remoto. Cada
     * línea de aquí abajo fija un valor del que depende el entorno remoto y que
     * la suite local no ejercita: la etiqueta de cada caso dice qué garantiza,
     * y el comentario, cuando lo hay, qué se rompe sin ella.
     *
     * @return array<string, array{string}>
     */
    public static function lineasObligatoriasDelEntornoRemoto(): array
    {
        return [
            // Cloud no inyecta DB_CONNECTION: sin esto el defecto silencioso
            // es sqlite sobre el disco efímero y el sitio entero da 500.
            'el motor es postgres' => ['DB_CONNECTION=pgsql'],
            'la conexión va cifrada' => ['DB_SSLMODE=require'],
            // Sin proxies de confianza no hay nada que genere https.
            'el balanceador es de confianza' => ['TRUSTED_PROXIES=*'],
            // `single` escribe en un disco que nadie puede leer: allí mueren
            // los códigos MFA del panel.
            'el registro sale por stderr' => ['LOG_STACK=stderr'],
            'la cookie de sesión va segura' => ['SESSION_SECURE_COOKIE=true'],
            'la página de error no se publica' => ['APP_DEBUG=false'],
            // `smtp` y `ses` funcionan con lo instalado (`ses` usa aws/aws-sdk-php,
            // que llega con league/flysystem-aws-s3-v3); `resend` y `postmark`
            // necesitarían paquetes. El entorno remoto sale por SMTP.
            'el correo sale por smtp' => ['MAIL_MAILER=smtp'],
            'la caché es compartida' => ['CACHE_STORE=database'],
            'el mantenimiento lo ven todas las instancias' => ['APP_MAINTENANCE_DRIVER=cache'],
        ];
    }

    #[DataProvider('lineasObligatoriasDelEntornoRemoto')]
    public function test_el_ejemplo_del_entorno_remoto_declara(string $linea): void
    {
        $this->assertStringContainsString(
            $linea,
            (string) file_get_contents(base_path('.env.staging.example')),
            "Falta `{$linea}` en .env.staging.example."
        );
    }

    /**
     * Las variables que el código lee: si un ejemplo no las declara, quien
     * monte el entorno no tiene forma de saber que existen.
     *
     * @return array<string, array{string}>
     */
    public static function variablesQueElCodigoLee(): array
    {
        return [
            'TRUSTED_PROXIES' => ['TRUSTED_PROXIES'],
            'SESSION_SECURE_COOKIE' => ['SESSION_SECURE_COOKIE'],
            'VALOR_MENSUALIDAD' => ['VALOR_MENSUALIDAD'],
            // `VALOR_AFILIACION` no va: esta lista es de «variables que el código
            // lee», y esa no la lee nadie. Ver `config/pagos.php`.
            'SITIO_INDEXABLE' => ['SITIO_INDEXABLE'],
            'SEED_GALERIA' => ['SEED_GALERIA'],
            'QUEUE_CONVERSIONS_BY_DEFAULT' => ['QUEUE_CONVERSIONS_BY_DEFAULT'],
            'MEDIA_DISK' => ['MEDIA_DISK'],
            'DISCO_PUBLICO' => ['DISCO_PUBLICO'],
            'DISCO_PRIVADO' => ['DISCO_PRIVADO'],
        ];
    }

    #[DataProvider('variablesQueElCodigoLee')]
    public function test_los_dos_ejemplos_de_entorno_declaran(string $variable): void
    {
        foreach (['.env.example', '.env.staging.example'] as $fichero) {
            $this->assertStringContainsString(
                $variable,
                (string) file_get_contents(base_path($fichero)),
                "Falta `{$variable}` en {$fichero}."
            );
        }
    }

    /**
     * El runbook es la mitad entregable del despliegue: sin él, desplegar es una
     * tarde de sorpresas.
     */
    public function test_el_runbook_existe_y_avisa_de_lo_que_muerde(): void
    {
        $runbook = (string) file_get_contents(base_path('docs/ingenieria/runbook-despliegue.md'));

        foreach (['storage/app/public', 'db:seed', 'cloud environment:logs', 'TRUSTED_PROXIES'] as $aviso) {
            $this->assertStringContainsString($aviso, $runbook, "El runbook ya no menciona `{$aviso}`.");
        }
    }

    /**
     * Un PHP sin `intl` ni `gd` —Herd Lite, por ejemplo— tiene que fallar al
     * instalar y no a mitad de la suite, que le devuelve 194 pruebas rotas 350
     * segundos después en vez de un error inmediato.
     *
     * La causa está medida, y NO es la que parece: `ext-intl` llega exigida de
     * forma transitiva por `filament/support`, pero `ext-gd` no la pide NADIE
     * en todo el árbol de dependencias —`spatie/image` acepta Imagick como
     * alternativa—. Con el `vendor/` ya instalado, `composer install` ni
     * siquiera vuelve a mirar.
     *
     * Declararlas en la raíz las sube al bloque `platform` del `composer.lock`,
     * que es lo que leen `composer install` y `composer check-platform-reqs`:
     * el fallo pasa de 350 segundos disfrazados a un mensaje con el nombre de
     * la extensión que falta. El README las exige en prosa; así las exige
     * también el gestor de paquetes.
     *
     * @return array<string, array{string}>
     */
    public static function extensionesQueElProyectoExige(): array
    {
        return [
            // Filament formatea fechas y moneda con ella: sin intl, HTTP 500
            // al renderizar cualquier tabla del panel.
            'ext-intl' => ['ext-intl'],
            // Las conversiones de imagen de medialibrary: sin gd,
            // imagecreatetruecolor() no existe y toda subida revienta.
            'ext-gd' => ['ext-gd'],
            'ext-exif' => ['ext-exif'],
            'ext-fileinfo' => ['ext-fileinfo'],
            'ext-mbstring' => ['ext-mbstring'],
        ];
    }

    #[DataProvider('extensionesQueElProyectoExige')]
    public function test_la_raiz_declara_la_extension(string $extension): void
    {
        /** @var array{require?: array<string, string>} $composer */
        $composer = json_decode((string) file_get_contents(base_path('composer.json')), true);

        $this->assertArrayHasKey(
            $extension,
            $composer['require'] ?? [],
            "composer.json no exige `{$extension}`: `composer install` pasará limpio en un entorno incompleto y el fallo aparecerá a mitad de la suite."
        );
    }

    /**
     * El bloque `platform` del candado es el que de verdad se comprueba al
     * instalar. Si composer.json y composer.lock se separan, la declaración
     * anterior no sirve de nada.
     */
    #[DataProvider('extensionesQueElProyectoExige')]
    public function test_el_candado_lleva_la_extension_a_su_bloque_de_plataforma(string $extension): void
    {
        /** @var array{platform?: array<string, string>} $candado */
        $candado = json_decode((string) file_get_contents(base_path('composer.lock')), true);

        $this->assertArrayHasKey(
            $extension,
            $candado['platform'] ?? [],
            "composer.lock no lleva `{$extension}` en `platform`: hay que regenerarlo con `composer update --lock`."
        );
    }
}
