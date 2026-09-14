<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Models\Transaccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;
use Throwable;

/**
 * Cada código de error que el sitio devuelve tiene su página: sin ella, un
 * 403, 419, 429, 500 o 503 sale con la plantilla del framework, en inglés, con
 * `lang="en"`, sin marca y sin enlace.
 *
 * La 403, la 419 y la 429 van sobre el layout público, como la 404. La 500 y
 * la 503 son autónomas y no pueden consultar la base: puede ser justo lo que
 * falló.
 */
class PaginasDeErrorTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{int, string}> */
    public static function paginasDeError(): array
    {
        return [
            '403' => [403, 'No puedes abrir esta página'],
            '419' => [419, 'La página estuvo abierta demasiado tiempo'],
            '429' => [429, 'Demasiados intentos seguidos'],
            '500' => [500, 'Algo falló de nuestro lado'],
            '503' => [503, 'Estamos haciendo mantenimiento'],
        ];
    }

    #[DataProvider('paginasDeError')]
    public function test_cada_pagina_de_error_existe_en_espanol_y_con_camino_de_vuelta(int $codigo, string $titular): void
    {
        $this->assertTrue(view()->exists("errors.{$codigo}"), "Falta resources/views/errors/{$codigo}.blade.php.");

        $html = view("errors.{$codigo}")->render();
        $principal = $this->contenidoPrincipal($html);

        $this->assertStringContainsString('<html lang="es"', $html);
        $this->assertStringContainsString($titular, $principal);
        $this->assertStringContainsString('href="'.url('/').'"', $principal, 'La página tiene que ofrecer un camino de vuelta propio.');
    }

    public function test_un_enlace_de_pago_sin_firma_da_la_403_propia(): void
    {
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Afiliacion,
            'monto' => 150000,
        ]);

        $respuesta = $this->get(route('pago.estado', $transaccion));

        $respuesta->assertForbidden();
        $respuesta->assertSee('<html lang="es"', escape: false);
        $respuesta->assertSee('No puedes abrir esta página');
        $respuesta->assertDontSee('Invalid signature');
    }

    /**
     * El middleware de CSRF se desactiva solo en pruebas, así que la ruta lanza
     * la misma excepción que él lanza con la sesión caducada. El Referer es de
     * otro dominio a propósito: el botón tiene que volver al formulario, pero
     * dentro del sitio.
     */
    public function test_la_419_invita_a_volver_al_formulario_sin_salir_del_sitio(): void
    {
        Route::middleware('web')->post('/prueba-de-sesion-caducada', function (): never {
            throw new TokenMismatchException('CSRF token mismatch.');
        });

        $respuesta = $this->withHeader('referer', 'https://sitio-ajeno.test/afiliate')
            ->post('/prueba-de-sesion-caducada');

        $respuesta->assertStatus(419);
        $respuesta->assertDontSee('Page Expired');

        $principal = $this->contenidoPrincipal($respuesta->getContent());

        $this->assertStringContainsString('La página estuvo abierta demasiado tiempo', $principal);
        $this->assertStringContainsString('href="'.url('/afiliate').'"', $principal);
        $this->assertStringNotContainsString('sitio-ajeno.test', $respuesta->getContent());
    }

    public function test_el_limite_de_envios_del_contacto_da_la_429_propia(): void
    {
        for ($intento = 1; $intento <= 6; $intento++) {
            $this->from(route('contacto'))->post(route('contacto.store'), []);
        }

        $respuesta = $this->from(route('contacto'))->post(route('contacto.store'), []);

        $respuesta->assertTooManyRequests();
        $respuesta->assertDontSee('Too Many Requests');

        $principal = $this->contenidoPrincipal($respuesta->getContent());

        $this->assertStringContainsString('Demasiados intentos seguidos', $principal);
        $this->assertStringContainsString('href="'.url('/contacto').'"', $principal);
    }

    public function test_un_fallo_del_servidor_da_la_500_propia_sin_consultar_la_base(): void
    {
        config(['app.debug' => false]);
        Exceptions::fake();

        Route::get('/prueba-de-fallo', function (): never {
            throw new RuntimeException('SECRETO_DE_PRUEBA');
        });

        $consultas = [];
        DB::listen(function ($evento) use (&$consultas): void {
            $consultas[] = $evento->sql;
        });

        $respuesta = $this->get('/prueba-de-fallo');

        $respuesta->assertInternalServerError();
        $respuesta->assertSee('<html lang="es"', escape: false);
        $respuesta->assertSee('Algo falló de nuestro lado');
        $respuesta->assertSee('href="'.url('/').'"', escape: false);
        $respuesta->assertDontSee('SECRETO_DE_PRUEBA');
        $respuesta->assertDontSee('Server Error');
        $this->assertSame([], $consultas, 'La página de error 500 no puede consultar la base.');

        Exceptions::assertReported(RuntimeException::class);
    }

    public function test_la_500_se_pinta_con_la_base_caida(): void
    {
        config(['app.debug' => false]);
        Exceptions::fake();

        Route::get('/prueba-de-fallo', function (): never {
            throw new RuntimeException('La base no responde.');
        });

        $respuesta = $this->conLaBaseCaida(fn () => $this->get('/prueba-de-fallo'));

        $respuesta->assertInternalServerError();
        $respuesta->assertSee('<html lang="es"', escape: false);
        $respuesta->assertSee('Algo falló de nuestro lado');
    }

    public function test_el_modo_mantenimiento_da_la_503_propia_con_la_base_caida(): void
    {
        $this->app->maintenanceMode()->activate(['time' => time(), 'status' => 503]);

        try {
            $respuesta = $this->conLaBaseCaida(fn () => $this->get('/'));
        } finally {
            $this->app->maintenanceMode()->deactivate();
        }

        $respuesta->assertServiceUnavailable();
        $respuesta->assertSee('<html lang="es"', escape: false);
        $respuesta->assertSee('Estamos haciendo mantenimiento');
        $respuesta->assertSee('href="'.url('/').'"', escape: false);
        $respuesta->assertDontSee('Service Unavailable');
    }

    /**
     * Ejecuta la petición con la conexión por defecto apuntando a un archivo
     * SQLite que no existe, y comprueba antes que de verdad no se puede abrir.
     *
     * @template TRespuesta
     *
     * @param  callable(): TRespuesta  $peticion
     * @return TRespuesta
     */
    private function conLaBaseCaida(callable $peticion): mixed
    {
        $conexionOriginal = config('database.default');

        config([
            'database.connections.base_caida' => [
                'driver' => 'sqlite',
                'database' => storage_path('framework/testing/base-que-no-existe.sqlite'),
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.default' => 'base_caida',
        ]);

        try {
            try {
                DB::connection()->getPdo();
                $this->fail('La prueba de control falló: la base de la prueba sí se pudo abrir.');
            } catch (Throwable $error) {
                $this->assertStringContainsString('does not exist', $error->getMessage());
            }

            return $peticion();
        } finally {
            DB::purge('base_caida');
            config(['database.default' => $conexionOriginal]);
        }
    }

    /**
     * Lo que la página pone dentro de `<main id="contenido">` del layout
     * público, para no dar por buenos el `lang` o los enlaces que ya traen la
     * barra y el pie. Las páginas autónomas no tienen ese `main`: se devuelve
     * todo el documento.
     */
    private function contenidoPrincipal(string $html): string
    {
        $inicio = strpos($html, '<main id="contenido">');

        if ($inicio === false) {
            return $html;
        }

        return substr($html, $inicio, strpos($html, '</main>', $inicio) - $inicio);
    }
}
