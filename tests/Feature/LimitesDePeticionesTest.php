<?php

namespace Tests\Feature;

use App\Enums\EstadoDeGestion;
use App\Models\Asociado;
use App\Models\Postulacion;
use App\Models\User;
use App\Models\Vacante;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route as Rutas;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PERM-02. `throttle:N,1` sin nombre firma el contador solo con el usuario
 * —o con la IP del visitante anónimo—, nunca con la ruta. Todas las rutas
 * limitadas compartían un contador y cada una lo comparaba con su propio
 * máximo: seis postulaciones gestionadas dejaban «Pagar» (máximo 5) en 429
 * durante un minuto, y siete consultas a la guía (máximo 30) hacían lo mismo
 * con el formulario de afiliación (máximo 6).
 *
 * Estas pruebas fijan las dos mitades del arreglo: que una ruta ya no gasta
 * el cupo de otra, y que cada una sigue rebotando justo en su máximo de
 * siempre.
 */
class LimitesDePeticionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        // El contador vive en la cache: se vacía para que ningún caso herede
        // las peticiones de otro.
        Cache::flush();
    }

    private function duenio(): User
    {
        $asociado = Asociado::factory()->publicado()->create();
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        return $usuario->fresh();
    }

    public function test_gestionar_seis_postulaciones_no_bloquea_el_boton_de_pagar(): void
    {
        $duenio = $this->duenio();
        $postulacion = Postulacion::factory()
            ->for(Vacante::factory()->for($duenio->asociado)->publicado())
            ->create();

        $this->actingAs($duenio);

        foreach ([1, 2, 3, 4, 5, 6] as $vez) {
            $estado = $vez % 2 === 0 ? EstadoDeGestion::Nuevo : EstadoDeGestion::Contactado;

            $this->patch(route('mi-cuenta.postulaciones.gestionar', $postulacion), ['estado' => $estado->value])
                ->assertRedirect(route('mi-cuenta.vacantes.show', $postulacion->vacante));
        }

        $pagar = $this->post(route('mi-cuenta.pagar'));

        $this->assertNotSame(429, $pagar->getStatusCode(), 'El dueño recibió 429 en Pagar por peticiones de otra ruta.');
        $pagar->assertRedirect(route('mi-cuenta.index'));
    }

    public function test_subir_fotos_no_bloquea_el_boton_de_pagar(): void
    {
        $this->actingAs($this->duenio());

        // Sin archivo la subida no pasa la validación, pero el limitador ya
        // la contó: es la misma cuenta que haría una subida real.
        for ($vez = 1; $vez <= 5; $vez++) {
            $this->assertNotSame(429, $this->post(route('mi-cuenta.fotos.store'))->getStatusCode());
        }

        $this->assertNotSame(
            429,
            $this->post(route('mi-cuenta.pagar'))->getStatusCode(),
            'Subir cinco fotos no puede gastar el cupo de Pagar.'
        );
    }

    public function test_el_limite_de_pagar_es_de_cada_usuario_y_no_de_todos(): void
    {
        $primero = $this->duenio();
        $segundo = $this->duenio();

        for ($vez = 1; $vez <= 5; $vez++) {
            $this->actingAs($primero)->post(route('mi-cuenta.pagar'));
        }

        $this->assertSame(429, $this->actingAs($primero)->post(route('mi-cuenta.pagar'))->getStatusCode());

        $this->assertNotSame(
            429,
            $this->actingAs($segundo)->post(route('mi-cuenta.pagar'))->getStatusCode(),
            'Otro afiliado desde la misma IP no puede heredar el límite agotado del primero.'
        );
    }

    public function test_consultar_la_guia_no_bloquea_el_formulario_de_afiliacion(): void
    {
        for ($vez = 1; $vez <= 7; $vez++) {
            $this->get(route('guia.index'))->assertSuccessful();
        }

        $this->assertNotSame(
            429,
            $this->post(route('afiliate.store'))->getStatusCode(),
            'Comparar municipios en la guía no puede gastar el cupo del formulario de afiliación.'
        );
    }

    /**
     * Método, nombre de ruta, parámetros, máximo por minuto y si la petición
     * va con la sesión de un afiliado. Los parámetros apuntan a registros que
     * no existen a propósito: el limitador corre antes de resolver la ruta,
     * así que cuenta igual y la prueba no depende de datos.
     *
     * @return array<string, array{0: string, 1: string, 2: array<string, int|string>, 3: int, 4: bool}>
     */
    public static function rutasLimitadas(): array
    {
        return [
            'pagar la mensualidad' => ['POST', 'mi-cuenta.pagar', [], 5, true],
            'subir una foto' => ['POST', 'mi-cuenta.fotos.store', [], 30, true],
            'borrar una foto' => ['DELETE', 'mi-cuenta.fotos.destroy', ['media' => 999999], 20, true],
            'crear una vacante' => ['POST', 'mi-cuenta.vacantes.store', [], 20, true],
            'editar una vacante' => ['PUT', 'mi-cuenta.vacantes.update', ['vacante' => 999999], 20, true],
            'gestionar una postulación' => ['PATCH', 'mi-cuenta.postulaciones.gestionar', ['postulacion' => 999999], 60, true],
            'consultar la guía' => ['GET', 'guia.index', [], 30, false],
            'descargar un formato' => ['GET', 'guia.formato', ['requisito' => 999999], 10, false],
            'registrar un perfil de empleo' => ['POST', 'empleo.aspirante', [], 6, false],
            'postularse a una vacante' => ['POST', 'empleo.postular', ['vacante' => 999999], 6, false],
            'inscribir un artista' => ['POST', 'artistas.inscripcion.store', [], 6, false],
            'inscribir un proveedor' => ['POST', 'proveedores.inscripcion.store', [], 6, false],
            'inscribirse a un evento' => ['POST', 'eventos.inscribir', ['evento' => 'no-existe'], 6, false],
            'solicitar la afiliación' => ['POST', 'afiliate.store', [], 6, false],
            'escribir al gremio' => ['POST', 'contacto.store', [], 6, false],
            'entrar a mi cuenta' => ['POST', 'mi-cuenta.entrar.post', [], 5, false],
            'definir la contraseña' => ['POST', 'mi-cuenta.password.update', [], 5, false],
            'resolver el pago simulado' => ['POST', 'pago.simulado.resolver', ['transaccion' => 'ASO-0000-NOEXISTE'], 10, false],
            'volver de la pasarela' => ['GET', 'pago.retorno', ['transaccion' => 'ASO-0000-NOEXISTE'], 30, false],
            'recibir el webhook de Bold' => ['POST', 'webhooks.bold', [], 120, false],
        ];
    }

    /** @param  array<string, int|string>  $parametros */
    #[DataProvider('rutasLimitadas')]
    public function test_cada_ruta_limitada_conserva_su_propio_maximo(
        string $metodo,
        string $ruta,
        array $parametros,
        int $maximo,
        bool $conSesion,
    ): void {
        if ($conSesion) {
            $this->actingAs($this->duenio());
        }

        $url = route($ruta, $parametros);

        for ($vez = 1; $vez <= $maximo; $vez++) {
            $this->assertNotSame(
                429,
                $this->call($metodo, $url)->getStatusCode(),
                "La petición {$vez} de {$maximo} a {$ruta} no debía rebotar."
            );
        }

        $this->assertSame(
            429,
            $this->call($metodo, $url)->getStatusCode(),
            "La petición ".($maximo + 1)." a {$ruta} debía rebotar con 429."
        );
    }

    /**
     * La guarda de la regresión: basta con que una ruta vuelva a
     * `throttle:N,1` para que su contador se mezcle otra vez con el del
     * resto. Y un nombre mal escrito no avisa al registrar la ruta: revienta
     * con un 500 en la primera petición.
     */
    public function test_ninguna_ruta_del_sitio_usa_un_limite_sin_nombre(): void
    {
        $limitadores = app(RateLimiter::class);
        $sinNombre = [];
        $sinRegistrar = [];

        foreach (Rutas::getRoutes()->getRoutes() as $rutaRegistrada) {
            /** @var Route $rutaRegistrada */
            if (! str_starts_with($rutaRegistrada->getActionName(), 'App\\')) {
                continue;
            }

            foreach ($rutaRegistrada->gatherMiddleware() as $middleware) {
                if (! is_string($middleware) || ! str_starts_with($middleware, 'throttle:')) {
                    continue;
                }

                $parametro = substr($middleware, strlen('throttle:'));
                $nombre = $rutaRegistrada->getName() ?? $rutaRegistrada->uri();

                if (preg_match('/^\d/', $parametro) === 1) {
                    $sinNombre[] = "{$nombre} ({$middleware})";
                } elseif ($limitadores->limiter($parametro) === null) {
                    $sinRegistrar[] = "{$nombre} ({$middleware})";
                }
            }
        }

        $this->assertSame([], $sinNombre, 'Estas rutas comparten contador con todas las demás.');
        $this->assertSame([], $sinRegistrar, 'Estas rutas nombran un limitador que no existe.');
    }
}
